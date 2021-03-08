<?php namespace App\Repository;

use App\Entity\Cart;
use Ewll\DBBundle\Repository\Repository;

class CartItemRepository extends Repository
{
    public function deleteByCart(Cart $cart): void
    {
        $this
            ->dbClient
            ->prepare(<<<SQL
DELETE FROM cartItem
WHERE cartId = :cartId
SQL
            )
            ->execute(['cartId' => $cart->id]);
    }
}
