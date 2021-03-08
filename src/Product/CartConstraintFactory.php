<?php namespace App\Product;

use App\Entity\CartItem;
use App\Entity\Product;
use App\Form\Constraint\CustomerIsNotBlockedBySeller;
use App\Product\Form\Constraint\SufficientProductObjectAmount;
use Ewll\CrudBundle\Constraint\EntityAccess;
use Ewll\CrudBundle\Constraint\EntityCount;
use Ewll\DBBundle\Repository\FilterExpression;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class CartConstraintFactory
{
    public static function createForProductId(int $cartId): array
    {
        $entityAccessFilterExpressions = [
            new FilterExpression(FilterExpression::ACTION_IN, 'statusId', [
                Product::STATUS_ID_OK,
                Product::STATUS_ID_OUT_OF_STOCK,
            ]),
        ];
        $entityCountFilterExpressions = [
            new FilterExpression(FilterExpression::ACTION_EQUAL, 'cartId', $cartId),
        ];//@TODO Если этот товар уже в корзине и товаров уже 10 - отработает ошибка
        $entityCountTranslations = [EntityCount::MESSAGE_KEY_MAX => 'cart.items.max'];

        return [
            new NotBlank(),
            new EntityAccess(Product::class, $entityAccessFilterExpressions),
            new EntityCount(CartItem::class, $entityCountFilterExpressions, 10, $entityCountTranslations),
        ];
    }

    public static function createForProductIdFixCart(int $cartId): array
    {
        $constraints = self::createForProductId($cartId);
        $constraints[] = new CustomerIsNotBlockedBySeller($cartId);

        return $constraints;
    }

    public static function createForAmount(bool $isControlSufficient = false, int $minimum = 0): array
    {
        $constraints = [
            new NotBlank(),
            new Range(['min' => $minimum, 'max' => 100]),
        ];
        if ($isControlSufficient) {
            $constraints[] = new SufficientProductObjectAmount();
        }

        return $constraints;
    }
}
