<?php namespace App\Api\Item\Admin\Finder\Item;

use App\Api\Item\Admin\Finder\FinderEntityView;
use App\Api\Item\Admin\Finder\IdFinder;
use App\Entity\Product;
use App\Entity\User;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductFinder implements IdFinder
{
    private $repositoryProvider;
    private $translator;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        TranslatorInterface $translator
    ) {
        $this->repositoryProvider = $repositoryProvider;
        $this->translator = $translator;
    }

    public function getEntityClass(): string
    {
        return Product::class;
    }

    public function findById(int $id): array
    {
        $views = [];
        /** @var Product|null $item */
        $item = $this->repositoryProvider->get(Product::class)->findById($id);
        if (null !== $item) {
            /** @var User $seller */
            $seller = $this->repositoryProvider->get(User::class)->findById($item->userId);
            $status = $this->translator->trans("status.$item->statusId", [], 'product');
            $isBlocked = $item->statusId === Product::STATUS_ID_BLOCKED;
            $info = sprintf("Name: %s, Seller: %s\n", $item->name, $seller->getName());
            $views[] = new FinderEntityView($item->id, $info, $status, $isBlocked);
        }

        return $views;
    }
}
