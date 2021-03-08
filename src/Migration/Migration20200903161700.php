<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;
use RuntimeException;

class Migration20200903161700 implements MigrationInterface
{
    public function up(): string
    {
        return <<<SQL
            ALTER TABLE `cartItem`
                ADD COLUMN `isCustomerNotificationsDisabled` tinyint(1)  UNSIGNED NOT NULL DEFAULT 0 AFTER hasUnreadMessagesByCustomer;
                
            ALTER TABLE `cartItem`
                ADD COLUMN `isSellerNotificationsDisabled` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 AFTER isCustomerNotificationsDisabled;
        SQL;
    }

    public function down(): string
    {
        throw new RuntimeException('Not realised');
    }

    public function getDescription(): string
    {
        return "Added unSubscribeEmail column on cartItem table, added isCustomerNotificationsDisabled, isSellerNotificationsDisabled";
    }
}
