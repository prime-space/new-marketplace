<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;
use RuntimeException;

class Migration20200928163100 implements MigrationInterface
{
    public function up(): string
    {
        return <<<SQL
            ALTER TABLE `product`
                ADD COLUMN `importId` varchar (32) NULL AFTER `productCategoryId`,
                ADD UNIQUE INDEX `importId` (`importId`);
            
            ALTER TABLE `review`
                MODIFY `cartItemId` BIGINT(20) UNSIGNED NULL;
        SQL;
    }

    public function down(): string
    {
        throw new RuntimeException('Not realised');
    }

    public function getDescription(): string
    {
        return 'Product importId; review nullable cartItemId';
    }
}
