<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;
use RuntimeException;

class Migration20200818164700 implements MigrationInterface
{
    public function up(): string
    {
        return <<<SQL
            ALTER TABLE `cart`
                ADD COLUMN `customerIpId` BIGINT(20) UNSIGNED NULL AFTER customerId,
                ADD INDEX `customerIpId` (customerIpId);
                
            CREATE TABLE `ip` (
              `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
              `ip` VARCHAR(39) NOT NULL,
              `isDeleted` tinyint(1) UNSIGNED NOT NULL,
              `createdTs` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            )COLLATE='utf8mb4_general_ci' ENGINE=InnoDB;
            
            CREATE TABLE `customerBlockedEntity` (
              `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
              `blockedByUserId` BIGINT(20) UNSIGNED NOT NULL,
              `entityTypeId` TINYINT(1) UNSIGNED NOT NULL,
              `entityId` BIGINT(20) UNSIGNED NOT NULL,
              `isDeleted` tinyint(1) UNSIGNED NOT NULL,
              `createdTs` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              INDEX `blockedByUserId` (`blockedByUserId`),
              UNIQUE INDEX `entityId_entityTypeId` (`entityId`, `entityTypeId`)
            )COLLATE='utf8mb4_general_ci' ENGINE=InnoDB;
            
            CREATE TABLE `customerIp` (
              `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
              `customerId` BIGINT(20) UNSIGNED NOT NULL,
              `ipId` BIGINT(20) UNSIGNED NOT NULL,
              `isDeleted` tinyint(1) UNSIGNED NOT NULL,
              `createdTs` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              INDEX `customerId` (`customerId`),
              INDEX `ipId` (`ipId`)
            )COLLATE='utf8mb4_general_ci' ENGINE=InnoDB;
        SQL;
    }

    public function down(): string
    {
        throw new RuntimeException('Not realised');
    }

    public function getDescription(): string
    {
        return "Added ipId column on cart table, added ip, customerBlockedEntity, customerIp";
    }
}
