<?php namespace App\Entity;

use Ewll\DBBundle\Annotation as Db;

class ProductCategory
{
    const FIELD_CODE_REGEX = '/^[a-z0-9-]+$/';

    /** @Db\IntType() */
    public $id;

    /** @Db\IntType() */
    public $parentId;

    /** @Db\VarcharType() */
    public $name;

    /** @Db\VarcharType() */
    public $code;

    /** @Db\IntType() */
    public $elementsNum = 0;

    /** @Db\TimestampType */
    public $createdTs;

    public function getTreeView()
    {
        return [
            'id' => $this->id,
            'parentId' => $this->parentId,
            'name' => $this->name,
            'code' => $this->code,
            'elementsNum' => $this->elementsNum,
        ];
    }
}

