<?php namespace App\Api\Item\Admin\Handler;

use App\Api\Exception\NotFoundException;
use App\Api\Exception\ValidationException;
use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Form\Constraint\TreeIsEmpty;
use App\Form\Constraint\Unique;
use App\Repository\ProductCategoryRepository;
use Ewll\DBBundle\Repository\Repository;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductCategoryApiHandler
{
    private $repositoryProvider;
    private $formFactory;
    private $validator;
    private $siteName;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        string $siteName
    ) {
        $this->repositoryProvider = $repositoryProvider;
        $this->formFactory = $formFactory;
        $this->validator = $validator;
        $this->siteName = $siteName;
    }

    public function getFlat(): array
    {
        /** @var ProductCategoryRepository $productCategoryRepository */
        $productCategoryRepository = $this->repositoryProvider->get(ProductCategory::class);
        $data = ['treeFlat' => $productCategoryRepository->getFlat()];

        return $data;
    }

    /** @throws ValidationException */
    public function create(Request $request): array
    {
        $formBuilder = $this->getFormBuilder();
        $this->fillCreateFormBuilder($formBuilder);
        $form = $formBuilder->getForm();
        $form->submit($request->request->get('form'));
        $this->validateForm($form);
        $data = $form->getData();
        $entity = new ProductCategory;
        $this->fillEntity($entity, $data);
        $this->repositoryProvider->get(ProductCategory::class)->create($entity);

        return ['id' => $entity->id];
    }

    /**
     * @throws ValidationException
     * @throws NotFoundException
     */
    public function update(Request $request, int $id): array
    {
        $formBuilder = $this->getFormBuilder();
        $this->fillUpdateFormBuilder($formBuilder, $id);
        $form = $formBuilder->getForm();
        $form->submit($request->request->get('form'));
        $this->validateForm($form);
        $data = $form->getData();
        $repository = $this->repositoryProvider->get(ProductCategory::class);
        $entity = $this->getEntityById($repository, $id);
        $this->fillEntity($entity, $data);
        $this->repositoryProvider->get(ProductCategory::class)->update($entity);

        return [];
    }

    /**
     * @throws ValidationException
     * @throws NotFoundException
     */
    public function delete(Request $request, int $id): array
    {
        $repository = $this->repositoryProvider->get(ProductCategory::class);
        $entity = $this->getEntityById($repository, $id);
        $violations = $this->validator->validate($entity, $this->getDeleteConstraints());
        if (count($violations) > 0) {
            throw new ValidationException(['form' => $violations[0]->getMessage()]);
        }
        $repository->delete($entity, true);

        return [];
    }

    private function getFormBuilder(array $constraints = []): FormBuilderInterface
    {
        return $this->formFactory->createBuilder(FormType\FormType::class, null, ['constraints' => $constraints]);
    }

    private function fillUpdateFormBuilder(FormBuilderInterface $formBuilder, int $id = null): FormBuilderInterface
    {
        $formBuilder
            ->add('name', FormType\TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 128]),
                ],
            ])
            ->add('code', FormType\TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 64]),
                    new Assert\Regex([
                        'message' => 'product-category.code-regex',
                        'pattern' => ProductCategory::FIELD_CODE_REGEX,
                    ]),
                    new Unique(ProductCategory::class, 'code', [], $id),
                ],
            ]);


        return $formBuilder;
    }

    private function fillCreateFormBuilder(FormBuilderInterface $formBuilder): FormBuilderInterface
    {
        $this->fillUpdateFormBuilder($formBuilder)
            ->add('parentId', FormType\IntegerType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Positive(),
                ]
            ]);

        return $formBuilder;
    }

    private function getDeleteConstraints(): array
    {
        $constraints = [new TreeIsEmpty(['related' => Product::class])];

        return $constraints;
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

    private function fillEntity($entity, $data)
    {
        foreach ($data as $propertyName => $propertyValue) {
            if (!property_exists(get_class($entity), $propertyName)) {
                throw new \RuntimeException("Property not exists: $propertyName");
            }
//            @TODO
//            if (!in_array($propertyName, $allowedProperties, true)) {
//                throw new \RuntimeException("Property not allowed: $propertyName");
//            }
            $entity->$propertyName = $propertyValue;
        }
    }

    /** @throws NotFoundException */
    private function getEntityById(Repository $repository, int $id)
    {
        $entity = $repository->findById($id);
        if (null === $entity) {
            throw new NotFoundException();
        }

        return $entity;
    }
}
