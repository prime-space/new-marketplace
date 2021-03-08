<?php namespace App\Entity;

use Ewll\CrudBundle\ReadViewCompiler\Transformer as ReadTransformer;
use Ewll\DBBundle\Annotation as Db;

class Review
{
    const FIELD_ID = 'id';
    const FIELD_CART_ITEM_ID = 'cartItemId';
    const FIELD_PRODUCT_ID = 'productId';
    const FIELD_TEXT = 'text';
    const FIELD_ANSWER = 'answer';
    const FIELD_IS_GOOD = 'isGood';
    const FIELD_IS_DELETED = 'isDeleted';
    const FIELD_ANSWER_TS = 'answerTs';
    const FIELD_CREATED_TS = 'createdTs';

    /** @Db\BigIntType */
    public $id;
    /** @Db\BigIntType */
    public $cartItemId;
    /** @Db\BigIntType */
    public $productId;
    /** @Db\TextType() */
    public $text;
    /** @Db\TextType() */
    public $answer;
    /** @Db\BoolType() */
    public $isGood;
    /** @Db\BoolType() */
    public $isDeleted = false;
    /** @Db\TimestampType */
    public $answerTs;
    /** @Db\TimestampType */
    public $createdTs;

    public static function getViewTransformerFields()
    {
        return [
            Review::FIELD_ID,
            Review::FIELD_TEXT,
            Review::FIELD_ANSWER,
            Review::FIELD_IS_GOOD,
            'dateAnswer' => new ReadTransformer\Date(
                Review::FIELD_ANSWER_TS,
                ReadTransformer\Date::FORMAT_DATE_TIME
            ),
            'dateCreate' => new ReadTransformer\Date(
                Review::FIELD_CREATED_TS,
                ReadTransformer\Date::FORMAT_DATE_TIME
            ),
        ];
    }

    public static function createForProductImport(
        int $productId,
        string $text,
        bool $isGood,
        \DateTime $createdTs,
        string $answer = null,
        \DateTime $answerTs = null
    ) {
        $item = new self();
        $item->productId = $productId;
        $item->text = $text;
        $item->isGood = $isGood;
        $item->createdTs = $createdTs;
        $item->answer = $answer;
        $item->answerTs = $answerTs;

        return $item;
    }
}
