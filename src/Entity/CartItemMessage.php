<?php namespace App\Entity;

use Ewll\DBBundle\Annotation as Db;

class CartItemMessage
{
    const FIELD_ID = 'id';
    const FIELD_TEXT = 'text';
    const FIELD_IS_ANSWER = 'isAnswer';
    const FIELD_IS_DELETED = 'isDeleted';
    const FIELD_CREATED_TS = 'createdTs';

    /** @Db\BigIntType() */
    public $id;
    /** @Db\BigIntType() */
    public $cartItemId;
    /** @Db\TextType() */
    public $text;
    /** @Db\BoolType() */
    public $isAnswer = false;
    /** @Db\BoolType() */
    public $isDeleted = false;
    /** @Db\TimestampType */
    public $createdTs;
}
