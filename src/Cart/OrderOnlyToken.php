<?php namespace App\Cart;

use Ewll\UserBundle\Token\AbstractToken;
use RuntimeException;

class OrderOnlyToken extends AbstractToken
{
    const TYPE_ID = 102;
    const DATA_KEY = 'cartId';
    const LIFE_TIME = 1440;//1 day 60*24

    public function getTypeId(): int
    {
        return self::TYPE_ID;
    }

    public function getLifeTimeMinutes(): int
    {
        return self::LIFE_TIME;
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
