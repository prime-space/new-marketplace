<?php namespace App\Entity;

use Ewll\DBBundle\Annotation as Db;

class StorageFile
{
    const TYPE_ID_PRODUCT_IMAGE = 1;

    /** @Db\BigIntType() */
    public $id;

    /** @Db\TinyIntType() */
    public $typeId;

    /** @Db\VarcharType() */
    public $name;

    /** @Db\VarcharType() */
    public $extension;

    /** @Db\VarcharType() */
    public $directory;

    /** @Db\BoolType() */
    public $isDeleted = false;

    /** @Db\TimestampType */
    public $createdTs;

    public static function create($typeId, $name, $extension, $directory): self
    {
        $item = new self();
        $item->typeId = $typeId;
        $item->name = $name;
        $item->extension = $extension;
        $item->directory = $directory;

        return $item;
    }

    public function compilePath(string $cdn = ''): string
    {
        $cdn = !empty($cdn) ? $cdn.'/' : '';
        return sprintf('%s%s%s.%s', $cdn, $this->directory, $this->name, $this->extension);
    }
}

