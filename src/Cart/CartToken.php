<?php namespace App\Cart;

use Ewll\UserBundle\Token\AbstractToken;
use RuntimeException;

class CartToken extends AbstractToken
{
    const TYPE_ID = 101;
    const DATA_KEY = 'cartId';
    const LIFE_TIME = 259200;//half year 60*24*30*6

    public function getTypeId(): int
    {
        return self::TYPE_ID;
    }

    public function getLifeTimeMinutes(): int
    {
        return self::LIFE_TIME; // 6 month
    }

    public function getRoute(): string
    {
        throw new RuntimeException('Not realised');
    }

    public function getIdDataKey(): string
    {
        return self::DATA_KEY;
    }
}
