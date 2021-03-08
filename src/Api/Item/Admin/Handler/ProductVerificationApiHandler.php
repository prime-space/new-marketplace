<?php namespace App\Api\Item\Admin\Handler;

use App\Api\Exception\NotFoundException;
use App\Api\Exception\ValidationException;
use App\Entity\Event;
use App\Entity\Product;
use App\Factory\EventFactory;
use App\Product\ProductStatusResolver;
use App\Repository\UserRepository;
use Ewll\DBBundle\DB\Client as DbClient;
use Ewll\DBBundle\Repository\RepositoryProvider;
use App\Entity\User;
use Exception;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductVerificationApiHandler
{
    private $repositoryProvider;
    private $formFactory;
    private $validator;
    private $eventFactory;
    private $defaultDbClient;
    private $productStatusResolver;
    private $siteName;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        EventFactory $eventFactory,
        DbClient $defaultDbClient,
        ProductStatusResolver $productStatusResolver,
        string $siteName
    ) {
        $this->repositoryProvider = $repositoryProvider;
        $this->formFactory = $formFactory;
        $this->validator = $validator;
        $this->eventFactory = $eventFactory;
        $this->defaultDbClient = $defaultDbClient;
        $this->productStatusResolver = $productStatusResolver;
        $this->siteName = $siteName;
    }

    public function userList(): array
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->repositoryProvider->get(User::class);
        $data = $userRepository->getByProductVerification();

        return $data;
    }

    public function productList(Request $request, int $userId): array
    {
        /** @var Product[] $products */
        $products = $this->repositoryProvider->get(Product::class)->findBy([
            'statusId' => Product::STATUS_ID_VERIFICATION,
            'userId' => $userId,
            'isDeleted' => 0,
        ]);
        $items = [];
        foreach ($products as $product) {
            $items[] = $product->compileAdminVerificationListView();
        }

        return $items;
    }

    /**
     * @throws ValidationException
     * @throws NotFoundException
     */
    public function reject(Request $request, int $productId): array
    {
        $productRepository = $this->repositoryProvider->get(Product::class);
        /** @var Product|null $entity */
        $entity = $productRepository->findById($productId);
        if (null === $entity) {
            throw new NotFoundException();
        }

        $formBuilder = $this->formFactory->createBuilder(FormType\FormType::class, null, ['constraints' => []]);
        $formBuilder->add('reason', FormType\TextType::class, [
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\Length(['max' => 128]),
            ],
        ]);
        $form = $formBuilder->getForm();
        $form->submit($request->request->get('form'));
        if ($entity->statusId !== Product::STATUS_ID_VERIFICATION) {
            $form->addError(new FormError('Expected status of product is "Verification"'));
        }
        if (!$form->isValid()) {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[$error->getOrigin()->getName()] = $error->getMessage();
            }
            throw new ValidationException($errors);
        }
        $data = $form->getData();
        $entity->verificationRejectReason = $data['reason'];
        $entity->statusId = $this->productStatusResolver
            ->resolve($entity, ProductStatusResolver::ACTION_VERIFICATION_REJECT);

        $this->defaultDbClient->beginTransaction();
        try {
            $productRepository->update($entity, ['verificationRejectReason', 'statusId']);
            $this->eventFactory->create(
                $entity->userId,
                Event::TYPE_ID_PRODUCT_VERIFICATION_REJECT,
                $entity->id,
                ['productName' => $entity->name]
            );
            $this->defaultDbClient->commit();
        } catch (Exception $e) {
            $this->defaultDbClient->rollback();

            throw $e;
        }

        return [];
    }

    /**
     * @throws ValidationException
     * @throws NotFoundException
     */
    public function block(Request $request, int $productId): array
    {
        $productRepository = $this->repositoryProvider->get(Product::class);
        /** @var Product|null $entity */
        $entity = $productRepository->findById($productId);
        if (null === $entity) {
            throw new NotFoundException();
        }

        $formBuilder = $this->formFactory->createBuilder(FormType\FormType::class, null, ['constraints' => []]);
        $formBuilder->add('reason', FormType\TextType::class, [
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\Length(['max' => 128]),
            ],
        ]);
        $form = $formBuilder->getForm();
        $form->submit($request->request->get('form'));
        $statuseIds = [
            Product::STATUS_ID_VERIFICATION,
            Product::STATUS_ID_REJECTED,
            Product::STATUS_ID_OK,
            Product::STATUS_ID_DISCONTINUED,
            Product::STATUS_ID_OUT_OF_STOCK
        ];
        if (!in_array($entity->statusId, $statuseIds, true)) {
            $form->addError(new FormError('Expected status of product: Verification, Rejected, Ok, Discontinued, Out of stock'));
        }
        if (!$form->isValid()) {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[$error->getOrigin()->getName()] = $error->getMessage();
            }
            throw new ValidationException($errors);
        }
        $data = $form->getData();
        $entity->verificationRejectReason = $data['reason'];
        $entity->statusId = $this->productStatusResolver
            ->resolve($entity, ProductStatusResolver::ACTION_BLOCK);

        $this->defaultDbClient->beginTransaction();
        try {
            $productRepository->update($entity, ['verificationRejectReason', 'statusId']);
            $this->eventFactory->create(
                $entity->userId,
                Event::TYPE_ID_PRODUCT_BLOCKED,
                $entity->id,
                ['productName' => $entity->name]
            );
            $this->defaultDbClient->commit();
        } catch (Exception $e) {
            $this->defaultDbClient->rollback();

            throw $e;
        }

        return [];
    }

    /**
     * @throws ValidationException
     * @throws NotFoundException
     */
    public function accept(Request $request, int $productId): array
    {
        $productRepository = $this->repositoryProvider->get(Product::class);
        /** @var Product|null $entity */
        $entity = $productRepository->findById($productId);
        if (null === $entity) {
            throw new NotFoundException();
        }

        if ($entity->statusId !== Product::STATUS_ID_VERIFICATION) {
            throw new ValidationException(['form' => 'Expected status of product is "Verification"']);
        }

        $entity->verificationRejectReason = null;
        $entity->statusId = $this->productStatusResolver
            ->resolve($entity, ProductStatusResolver::ACTION_VERIFICATION_ACCEPT);
        $this->defaultDbClient->beginTransaction();
        try {
            $productRepository->update($entity, ['verificationRejectReason', 'statusId']);
            $this->eventFactory->create(
                $entity->userId,
                Event::TYPE_ID_PRODUCT_VERIFICATION_ACCEPT,
                $entity->id,
                ['productName' => $entity->name]
            );
            $this->defaultDbClient->commit();
        } catch (Exception $e) {
            $this->defaultDbClient->rollback();

            throw $e;
        }

        return [];
    }

    /**
     * @throws ValidationException
     * @throws NotFoundException
     */
    public function unblock(Request $request, int $productId): array
    {
        $productRepository = $this->repositoryProvider->get(Product::class);
        /** @var Product|null $entity */
        $entity = $productRepository->findById($productId);
        if (null === $entity) {
            throw new NotFoundException();
        }

        if ($entity->statusId !== Product::STATUS_ID_BLOCKED) {
            throw new ValidationException(['form' => 'Expected status of product is "Blocked"']);
        }

        $entity->verificationRejectReason = null;
        $entity->statusId = $this->productStatusResolver
            ->resolve($entity, ProductStatusResolver::ACTION_VERIFICATION_UNBLOCK);
        $this->defaultDbClient->beginTransaction();
        try {
            $productRepository->update($entity, ['verificationRejectReason', 'statusId']);
            $this->eventFactory->create(
                $entity->userId,
                Event::TYPE_ID_PRODUCT_UNBLOCKED,
                $entity->id,
                ['productName' => $entity->name]
            );
            $this->defaultDbClient->commit();
        } catch (Exception $e) {
            $this->defaultDbClient->rollback();

            throw $e;
        }

        return [];
    }
}
