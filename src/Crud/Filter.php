<?php namespace App\Crud;

use App\Entity\Product;

class Filter
{
//    const FIELDS = [
//        Product::FIELD_NAME,
//        Product::FIELD_GROUP_IDS
//    ];

    public function __get($name)
    {
//        if (!in_array($name, self::FIELDS, true)) {
//            throw new \RuntimeException("Field '$name' isn't defined");
//        }

        return $this->$name ?? [];
    }

    public function __set($name, $value)
    {
//        if (!in_array($name, self::FIELDS, true)) {
//            throw new \RuntimeException("Field '$name' isn't defined");
//        }

        $this->$name = $value;
    }
}
