<?php namespace App\Api\Item\Main\Handler\Partner;

use App\Api\Exception\NotFoundException;
use App\Api\Exception\ValidationException;
use App\Entity\Partnership;
use App\Entity\Product;
use App\Entity\Product_ProductGroup;
use App\Entity\ProductGroup;
use App\Entity\StorageFile;
use App\Entity\User;
use App\Sphinx\SphinxClient;
use Ewll\DBBundle\Query\QueryBuilder;
use Ewll\DBBundle\Repository\FilterExpression;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Constraint;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductApiHandler
{
    private $repositoryProvider;
    private $formFactory;
    private $translator;
    private $domain;
    private $cdn;
    private $sphinxClient;
    private $domainBuy;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        FormFactoryInterface $formFactory,
        TranslatorInterface $translator,
        string $domain,
        string $cdn,
        SphinxClient $sphinxClient,
        string $domainBuy
    ) {
        $this->repositoryProvider = $repositoryProvider;
        $this->formFactory = $formFactory;
        $this->translator = $translator;
        $this->domain = $domain;
        $this->cdn = $cdn;
        $this->sphinxClient = $sphinxClient;
        $this->domainBuy = $domainBuy;
    }

    /** @throws ValidationException */
    public function list(Request $request, User $user): array
    {
        $productRepository = $this->repositoryProvider->get(Product::class);
        $storageFileRepository = $this->repositoryProvider->get(StorageFile::class);

        $formBuilder = $this->formFactory->createBuilder();
        $formBuilder
            ->add('sellerIds', FormType\ChoiceType::class, [
                'required' => false,
                'choices' => $this->getPartnerSellerIds($user),
                'multiple' => true,
                'invalid_message' => 'partnership.seller-not-you-partner',
            ])
            ->add('showMissing', FormType\CheckboxType::class, [
                'required' => false,
                'false_values' => [null, '0'],
            ])
            ->add('groupId', FormType\IntegerType::class, [//@TODO разрешены чужие группы
                'required' => false,
            ])
            ->add('itemsPerPage', FormType\IntegerType::class, [
                'required' => false,
                'constraints' => [new Constraint\Range(['min' => 5, 'max' => 100])],
                'empty_data' => '15',
            ])
            ->add('page', FormType\IntegerType::class, [
                'required' => false,
                'constraints' => [new Constraint\GreaterThan(0)],
                'empty_data' => '1',
            ])
            ->add('query', FormType\TextType::class, [
                'required' => false,
            ]);

        $form = $formBuilder->getForm();
        $form->submit($request->query->all());
        $this->validateForm($form);
        $data = $form->getData();

        $qb = new QueryBuilder($productRepository);

        if (!empty($data['query'])) {
            $queryIds = $this->sphinxClient->find(SphinxClient::ENTITY_PRODUCT, $data['query']);
            if (count($queryIds) > 0) {
                $qb->addCondition(new FilterExpression(FilterExpression::ACTION_IN, 'id', $queryIds));
            } else {
                $qb->addCondition(new FilterExpression(FilterExpression::ACTION_EQUAL, 'id', 0));
            }
        }

        if (!empty($data['sellerIds'])) {
            $qb->addCondition(new FilterExpression(FilterExpression::ACTION_IN, 'userId', $data['sellerIds']));
        }

        $productStatusIds = [Product::STATUS_ID_OK];
        if (true === $data['showMissing']) {
            $productStatusIds[] = Product::STATUS_ID_OUT_OF_STOCK;
        }
        $qb->addCondition(new FilterExpression(FilterExpression::ACTION_IN, 'statusId', $productStatusIds));

        if (!empty($data['groupId'])) {
            $this->addGroupCondition($qb, $data['groupId']);
        }

        $qb
            ->setFlag(QueryBuilder::FLAG_CALC_ROWS)
            ->setIndex('id')
            ->setPage($data['page'], $data['itemsPerPage']);

        /** @var Product[] $products */
        $products = $productRepository->find($qb);
        $total = $productRepository->getFoundRows();
        $imageStorageFiles = $storageFileRepository->findByRelativeIndexed($products, 'imageStorageFileId');
        $items = [
            'items' => [],
            'itemsNum' => $total,
        ];
        $cdn = empty($this->cdn) ? "https://{$this->domain}" : $this->cdn;
        foreach ($products as $product) {
            $items['items'][] = $product->compileApiPartnerListView(
                $imageStorageFiles[$product->imageStorageFileId],
                $cdn,
                $this->domainBuy,
                $user->id
            );
        }

        return $items;
    }

    /** @throws NotFoundException */
    public function item(Request $request, User $user, int $id): array
    {
        $productRepository = $this->repositoryProvider->get(Product::class);
        $storageFileRepository = $this->repositoryProvider->get(StorageFile::class);

//        $partnerSellerIds = $this->getPartnerSellerIds($user);
        $productStatusesIds = [Product::STATUS_ID_OK, Product::STATUS_ID_OUT_OF_STOCK];
        $qb = new QueryBuilder($productRepository);
        $qb
            ->setLimit(1)
            ->addConditions([
                new FilterExpression(FilterExpression::ACTION_EQUAL, 'id', $id),
//                new FilterExpression(FilterExpression::ACTION_IN, 'userId', $partnerSellerIds),
                new FilterExpression(FilterExpression::ACTION_IN, 'statusId', $productStatusesIds)
            ]);
        /** @var Product|null $product */
        $product = $productRepository->find($qb);
        if (null === $product) {
            throw new NotFoundException();
        }
        $imageStorageFile = $storageFileRepository->findById($product->imageStorageFileId);
        $cdn = empty($this->cdn) ? "https://{$this->domain}" : $this->cdn;
        $view = $product->compileApiPartnerView($imageStorageFile, $cdn, $this->domainBuy, $user->id);

        return $view;
    }


    /** @throws ValidationException */
    private function validateForm(FormInterface $form): void
    {
        if (!$form->isValid()) {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[$error->getOrigin()->getName()] = $error->getMessage();
            }
            throw new ValidationException($errors);
        }
    }

    private function getPartnerSellerIds(User $user): array
    {
        $partnershipRepository = $this->repositoryProvider->get(Partnership::class);
        $partnershipConditions = ['agentUserId' => $user->id, 'statusId' => Partnership::STATUS_ID_OK,];
        $partnerships = $partnershipRepository->findBy($partnershipConditions, 'sellerUserId');
        $partnerSellerIds = array_keys($partnerships);

        return $partnerSellerIds;
    }

    private function addGroupCondition(QueryBuilder $qb, int $groupId): void
    {
        $prefix = 't' . (count($qb->getJoins()) + 2);
        $relationRepository = $this->repositoryProvider->get(Product_ProductGroup::class);
        $relationTableName = $relationRepository->getEntityConfig()->tableName;
        $joinCondition = sprintf('%s.id = %s.%s', $qb->getPrefix(), $prefix, Product_ProductGroup::FIELD_PRODUCT_ID);
        $param1 = [$prefix, Product_ProductGroup::FIELD_PRODUCT_GROUP_ID];
        $qb
            ->addJoin($relationTableName, $prefix, $joinCondition)
            ->addCondition(new FilterExpression(FilterExpression::ACTION_EQUAL, $param1, $groupId));
    }
}
