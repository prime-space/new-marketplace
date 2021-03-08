<?php namespace App\Command;

use App\Crud\Unit\Product\CustomAction\ObjectManipulating;
use App\Crud\Unit\Product\ProductCrudUnit;
use App\Crud\UserProvider\ManualUserProvider;
use App\Entity\Currency;
use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Entity\Review;
use App\Entity\User;
use App\Product\ProductReviewStatActualizer;
use App\Product\ProductStatusResolver;
use Ewll\CrudBundle\Action\ActionInterface;
use Ewll\CrudBundle\Action\CrudAction;
use Ewll\CrudBundle\Action\CustomAction;
use Ewll\CrudBundle\Crud;
use Ewll\CrudBundle\Exception\ValidationException;
use Ewll\DBBundle\DB\Client as DbClient;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AddProductCommand extends Command
{
    const IMAGE_TMP_PATH = '/tmp/marketplaceAddProductImage.jpg';
    const DEFAULT_IMG_NAME = 'defaultProductPicture.jpg';

    const CURRENCY_MAP = [
        '1' => Currency::ID_USD,
        '2' => Currency::ID_UAH,
        '3' => Currency::ID_EUR,
        '4' => Currency::ID_RUB,
    ];

    private $repositoryProvider;
    private $crud;
    private $productStatusResolver;
    private $projectDir;
    private $container;
    private $productReviewStatActualizer;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        Crud $crud,
        ProductStatusResolver $productStatusResolver,
        string $projectDir,
        ContainerInterface $container,
        ProductReviewStatActualizer $productReviewStatActualizer
    )
    {
        parent::__construct();
        $this->repositoryProvider = $repositoryProvider;
        $this->crud = $crud;
        $this->productStatusResolver = $productStatusResolver;
        $this->projectDir = $projectDir;
        $this->container = $container;
        $this->productReviewStatActualizer = $productReviewStatActualizer;
    }

    protected function configure()
    {
        $this
            ->addArgument('userId', InputArgument::REQUIRED)
            ->addArgument('productCategoryId', InputArgument::REQUIRED)
            ->addArgument('connectionName', InputArgument::REQUIRED)
            ->addArgument('conditions', InputArgument::REQUIRED)
            ->addArgument('sourceDomain', InputArgument::REQUIRED)
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Products limit');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $productRepository = $this->repositoryProvider->get(Product::class);

        $addProductsLimit = $input->getOption('limit');

        $userId = $input->getArgument('userId');
        /** @var User $user */
        $user = $this->repositoryProvider->get(User::class)->findById($userId);
        if (null === $user) {
            throw new \RuntimeException("User #$userId not found.");
        }

        $productCategoryId = $input->getArgument('productCategoryId');
        /** @var ProductCategory|null $productCategory */
        $productCategory = $this->repositoryProvider->get(ProductCategory::class)->findById($productCategoryId);
        if (null === $productCategory) {
            throw new \RuntimeException("Category #$productCategoryId not found.");
        }

        $connectionName = $input->getArgument('connectionName');
        /** @var DbClient $connection */
        $connection = $this->container->get(sprintf('ewll.db.client.%s', $connectionName));

        $conditions = $input->getArgument('conditions');
        $sourceDomain = $input->getArgument('sourceDomain');

        $output->writeln("Import products for user #$userId.");
        $sourceProducts = $this->fetchSourceProducts($connection, $conditions, $sourceDomain);
        $sourceProductsAmount = count($sourceProducts);
        $output->writeln("Found products: $sourceProductsAmount.");

//        $sourceProducts = array_slice($sourceProducts, 0, 15);

        $userProvider = new ManualUserProvider($user);
        $unitName = ProductCrudUnit::NAME;

        foreach ($sourceProducts as $sourceProduct) {
            $output->writeLn('');
            $output->writeLn("Product source id: {$sourceProduct['id']}.");
            if (!$sourceProduct['isText']) {
                $output->writeln('Cannot add: it\'s not a test type product.');
                continue;
            }

            $importId = sprintf('%s-%s', $connectionName, $sourceProduct['id']);
            $productDuplicate = $productRepository->findOneBy([Product::FIELD_IMPORT_ID => $importId]);
            if (null !== $productDuplicate) {
                $output->writeln('This product already was added.');
                continue;
            }

            $createData = ['form' => ['typeId' => $sourceProduct['typeId']],];
            $createAction = new CrudAction(
                $userProvider,
                ActionInterface::CREATE,
                $unitName,
                null,
                $createData,
                ActionInterface::NO_CHECK_CSRF
            );
            $createResult = $this->handle($output, $createAction);
            /** @var Product $product */
            $product = $productRepository->findById($createResult['id']);
            $product->importId = $importId;

            $output->writeLn("New id: $product->id");
            $productRepository->update($product, [Product::FIELD_IMPORT_ID]);

            $updateData = [
                'form' => [
                    'imageStorageFileId' => $this->prepareImage($sourceProduct['imgPath']),
                    'productCategoryId' => $productCategory->id,
                    'currencyId' => $sourceProduct['currencyId'],
                    'name' => $sourceProduct['name'],
                    'price' => $sourceProduct['price'],
                    'description' => $sourceProduct['description'],
                    'partnershipFee' => $sourceProduct['partnershipFee'],
                ]
            ];
            $updateAction = new CrudAction(
                $userProvider,
                ActionInterface::UPDATE,
                $unitName,
                $product->id,
                $updateData,
                ActionInterface::NO_CHECK_CSRF
            );
            $this->handle($output, $updateAction);

            $sourceProductObjects = $this->fetchProductObjects($connection, $sourceProduct);
            $sourceProductObjectsAmount = count($sourceProductObjects);
            $output->writeln("Found non-sold product objects: $sourceProductObjectsAmount.");
            if ($sourceProductObjectsAmount > 0) {
                $objectsData = [
                    'form' => [
                        'productObjects' => []
                    ]
                ];
                foreach ($sourceProductObjects as $sourceProductObject) {
                    $objectsData['form']['productObjects'][] = ['data' => $sourceProductObject];
                }
                $objectsAction = new CustomAction(
                    $userProvider,
                    ActionInterface::CUSTOM,
                    $unitName,
                    $objectsData,
                    ObjectManipulating::NAME,
                    $product->id,
                    ActionInterface::NO_CHECK_CSRF
                );
                $this->handle($output, $objectsAction);
            }
            $sourceProductReviews = $this->fetchProductReviews($connection, $sourceProduct);
            $sourceProductReviewsAmount = count($sourceProductReviews);
            $output->writeln("Found product reviews: $sourceProductReviewsAmount.");
            if ($sourceProductReviewsAmount > 0) {
                foreach ($sourceProductReviews as $sourceProductReview) {
                    $reviewAnswerDate = null !== $sourceProductReview['answerDate']
                        ? new \DateTime($sourceProductReview['answerDate'], new \DateTimeZone('Utc'))
                        : null;
                    $review = Review::createForProductImport(
                        $product->id,
                        $sourceProductReview['review'],
                        $sourceProductReview['isGood'],
                        new \DateTime($sourceProductReview['reviewDate'], new \DateTimeZone('Utc')),
                        $sourceProductReview['answer'],
                        $reviewAnswerDate,
                    );
                    $this->repositoryProvider->get(Review::class)->create($review);
                }
                $this->productReviewStatActualizer->actualize($product->id);
            }
            $productRepository->clear();
            /** @var Product $product */
            $product = $productRepository->findById($createResult['id']);
            $product->salesNum = $sourceProduct['salesNum'];
            $product->statusId = $this->productStatusResolver
                ->resolve($product, ProductStatusResolver::ACTION_VERIFICATION_ACCEPT);
            $productRepository->update($product, [Product::FIELD_STATUS_ID, Product::FIELD_SALES_NUM]);

            $output->writeLn('Done');
            if (null !== $addProductsLimit) {
                $addProductsLimit--;
                if (0 === $addProductsLimit) {
                    $output->writeLn('');
                    $output->writeLn('Limit reached');
                    break;
                }
            }
        }

        $output->writeLn('');
        $output->writeLn('Finished.');

        return 0;
    }

    private function handle(OutputInterface $output, ActionInterface $action)
    {
        try {
            $result = $this->crud->handle($action);

            return $result;
        } catch (ValidationException $e) {
            $output->writeln(json_encode(['errors' => $e->getErrors()], JSON_UNESCAPED_UNICODE));

            throw $e;
        }
    }

    private function fetchSourceProducts(DbClient $connection, string $conditions, string $sourceDomain): array
    {
        $statement = $connection->prepare(<<<SQL
SELECT 
    p.id, p.name, p.many, p.price, p.curr, p.descript, p.info, p.typeObject, p.partner, p.sale,
    pic.id as pictureId, pic.path as picturePath
FROM product p
LEFT JOIN picture pic ON pic.id = p.picture
WHERE
    $conditions
    AND p.moderation = 'ok'
SQL
        )
            ->execute();
        $data = $statement->fetchArrays();
        $products = [];
        foreach ($data as $item) {
            $description = sprintf('%s\n\n%s', $item['descript'], $item['info']);
            $description = $this->fixString($description);

            $pictureFullPath = empty($item['pictureId'])
                ? sprintf('%s/src/Resources/%s', $this->projectDir, self::DEFAULT_IMG_NAME)
                : sprintf('https://%s/picture/%srecommended.jpg', $sourceDomain, $item['picturePath']);

            $products[] = [
                'id' => $item['id'],
                'name' => $item['name'],
                'typeId' => $item['many'] === 0 ? Product::TYPE_ID_UNIQUE : Product::TYPE_ID_UNIVERSAL,
                'price' => $item['price'],
                'partnershipFee' => $item['partner'],
                'currencyId' => self::CURRENCY_MAP[$item['curr']],
                'description' => $description,
                'imgPath' => $pictureFullPath,
                'isText' => $item['typeObject'] === 1,
                'salesNum' => $item['sale'],
            ];
        }

        return $products;
    }

    private function fetchProductObjects(DbClient $connection, array $sourceProduct): array
    {
        $statement = $connection->prepare(<<<SQL
SELECT pt.text
FROM product_text pt
WHERE pt.idProduct = :productId
SQL
        )
            ->execute(['productId' => $sourceProduct['id']]);
        $data = $statement->fetchColumns();
        $objects = [];
        foreach ($data as $item) {
            $objects[] = $this->fixString($item);
        }

        return $objects;
    }

    private function prepareImage(string $imgPath): string
    {
        $image = imagecreatefromjpeg($imgPath);
        list($origWidth, $origHeight) = getimagesize($imgPath);//@TODO double download
        $imageP = imagecreatetruecolor(300, 160);
        imagecopyresampled($imageP, $image, 0, 0, 0, 0, 300, 160, $origWidth, $origHeight);
        imagejpeg($imageP, self::IMAGE_TMP_PATH, 100);
        $imageData = file_get_contents(self::IMAGE_TMP_PATH);
        $imageBase64 = 'data:image/jpeg;base64,' . base64_encode($imageData);

        return $imageBase64;
    }

    private function fixString(string $str): string
    {
        $str = htmlspecialchars_decode($str);
        $str = str_replace('<br />', '', $str);
        $str = preg_replace('#<a href.*>(.*)</a>#i', '$1', $str);

        return $str;
    }

    private function fetchProductReviews(DbClient $connection, array $sourceProduct): array
    {
        $statement = $connection->prepare(<<<SQL
SELECT
    r.text as review, r.good as isGood, r.date as reviewDate,
    ra.text as answer, ra.date as answerDate
FROM review r
LEFT JOIN review_answer ra ON ra.reviewId = r.id
WHERE r.idProduct = :productId AND datedel IS NULL
SQL
        )
            ->execute(['productId' => $sourceProduct['id']]);
        $reviews = $statement->fetchArrays();
        foreach ($reviews as &$review) {
            $review['review'] = $this->fixString($review['review']);
            if (!empty($review['answer'])) {
                $review['answer'] = $this->fixString($review['answer']);
            }
        }

        return $reviews;
    }
}
