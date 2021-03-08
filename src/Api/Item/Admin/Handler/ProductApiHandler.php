<?php namespace App\Api\Item\Admin\Handler;

use App\Api\Exception\NotFoundException;
use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Entity\ProductObject;
use App\Entity\StorageFile;
use Ewll\DBBundle\Repository\FilterExpression;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductApiHandler
{
    private $repositoryProvider;
    private $formFactory;
    private $validator;
    private $translator;
    private $siteName;
    private $cdn;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        string $siteName,
        string $cdn
    ) {
        $this->repositoryProvider = $repositoryProvider;
        $this->formFactory = $formFactory;
        $this->validator = $validator;
        $this->translator = $translator;
        $this->siteName = $siteName;
        $this->cdn = $cdn;
    }

    /** @throws NotFoundException */
    public function page(Request $request, int $productId): array
    {
        $product = $this->getProductById($productId);
        $productCategory = $this->repositoryProvider->get(ProductCategory::class)
            ->findById($product->productCategoryId);
        $imageStorageFile = $this->repositoryProvider->get(StorageFile::class)->findById($product->imageStorageFileId);
        $backgroundStorageFile = null !== $product->backgroundStorageFileId
            ? $this->repositoryProvider->get(StorageFile::class)->findById($product->backgroundStorageFileId)
            : null;
        $view = $product->compileAdminView($this->translator, $productCategory, $imageStorageFile, $this->cdn, $backgroundStorageFile);

        return $view;
    }

    /** @throws NotFoundException */
    public function objects(Request $request, int $productId): array
    {
        $product = $this->getProductById($productId);
        /** @var ProductObject[] $objects */
        $objects = $this->repositoryProvider->get(ProductObject::class)
            ->findBy([
                'productId' => $product->id,
                new FilterExpression(FilterExpression::ACTION_IS_NULL, ProductObject::FIELD_CART_ITEM_ID)
            ]);
        $items = [];
        foreach ($objects as $object) {
            $items[] = $object->compileAdminListView();
        }

        return $items;
    }

    /** @throws NotFoundException */
    private function getProductById(int $productId): Product
    {
        /** @var Product|null $product */
        $product = $this->repositoryProvider->get(Product::class)->findOneBy([
            'id' => $productId,
            new FilterExpression(FilterExpression::ACTION_NOT_EQUAL, 'statusId', Product::STATUS_ID_FETUS),
        ]);
        if (null === $product) {
            throw new NotFoundException();
        }

        return $product;
    }
}
