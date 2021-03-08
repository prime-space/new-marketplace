<?php namespace App\Crud\Unit\Product\CustomAction;

use App\Crud\Unit\Product\Form\Type\ProductObjectType;
use App\Crud\Unit\Product\ProductCrudUnit;
use App\Entity\Product;
use App\Entity\ProductObject;
use App\Product\ProductStatusResolver;
use Ewll\CrudBundle\Exception\ValidationException;
use Ewll\CrudBundle\Form\FormConfig;
use Ewll\CrudBundle\Form\FormErrorCompiler;
use Ewll\CrudBundle\Form\FormFactory;
use Ewll\CrudBundle\Unit\CustomActionTargetInterface;
use Ewll\DBBundle\DB\Client as DbClient;
use Ewll\DBBundle\Repository\Repository;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Exception;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Constraints as Assert;

class ObjectsAdd implements CustomActionTargetInterface
{
    const NAME = 'objectsAdd';

    private $repositoryProvider;
    private $formFactory;
    private $formErrorCompiler;
    private $defaultDbClient;
    private $productStatusResolver;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        FormFactory $formFactory,
        FormErrorCompiler $formErrorCompiler,
        DbClient $defaultDbClient,
        ProductStatusResolver $productStatusResolver
    ) {
        $this->repositoryProvider = $repositoryProvider;
        $this->formFactory = $formFactory;
        $this->formErrorCompiler = $formErrorCompiler;
        $this->defaultDbClient = $defaultDbClient;
        $this->productStatusResolver = $productStatusResolver;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getUnitName(): string
    {
        return ProductCrudUnit::NAME;
    }

    /**
     * @inheritDoc
     * @param $entity Product
     */
    public function action($entity, array $data): array
    {
        $form = $this->formFactory->create($this->getFormConfig($entity));
        $form->submit($data);
        if ($entity->typeId !== Product::TYPE_ID_UNIQUE) {
            $form->addError(new FormError('Товар не является уникальным'));
        }

        if (!$form->isValid()) {
            $errors = $this->formErrorCompiler->compile($form);

            throw new ValidationException($errors);
        }
        $formData = $form->getData();
        /** @var ProductObject[] $productObjects */
        $productObjects = $formData['productObjects'];

        $productRepository = $this->repositoryProvider->get(Product::class);
        $productObjectRepository = $this->repositoryProvider->get(ProductObject::class);

        $this->defaultDbClient->beginTransaction();
        try {
            /** @var Product $product */
            $product = $productRepository->findById($entity->id, Repository::FOR_UPDATE);

            foreach ($productObjects as $productObject) {
                $productObject->productId = $product->id;
                $productObjectRepository->create($productObject);
            }

            $product->inStockNum += count($productObjects);
            $product->statusId = $this->productStatusResolver
                ->resolve($product, ProductStatusResolver::ACTION_OBJECT_ADD);
            $productRepository->update($product, [Product::FIELD_STATUS_ID, Product::FIELD_IN_STOCK_NUM]);

            $this->defaultDbClient->commit();
        } catch (Exception $e) {
            $this->defaultDbClient->rollback();

            throw $e;
        }

        return [];
    }

    /**
     * @inheritDoc
     * @param $entity Product
     */
    public function getFormConfig(object $entity): FormConfig
    {
        $entity->addDynamicalProperty('productObjects', function () use ($entity): array {
            return [];
        });

        $config = new FormConfig();
        $config->addField('productObjects', CollectionType::class, [
            'entry_type' => ProductObjectType::class,
            'allow_add' => true,
            'constraints' => [
                //@TODO не суммируется с тем, что уже есть
                new Assert\Count(['min' => 1, 'max' => 1000]),
            ]
        ]);

        return $config;
    }
}
