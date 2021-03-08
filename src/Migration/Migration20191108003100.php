<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;
use RuntimeException;

class Migration20191108003100 implements MigrationInterface
{
    public function getDescription(): string
    {
        return 'init';
    }

    public function up(): string
    {
        return <<<SQL
CREATE TABLE `product` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `userId` BIGINT(20) UNSIGNED NOT NULL,
  `typeId` TINYINT(3) UNSIGNED NOT NULL,
  `statusId` TINYINT(3) UNSIGNED NOT NULL,
  `imageStorageFileId` BIGINT(20) UNSIGNED NULL,
  `backgroundStorageFileId` BIGINT(20) UNSIGNED NULL,
  `currencyId` TINYINT(3) UNSIGNED NULL,
  `productCategoryId` INT(10) UNSIGNED NULL,
  `name` VARCHAR(256) NULL,
  `price` DECIMAL(18,8) NULL,
  `partnershipFee` DECIMAL(4,2) NOT NULL,
  `description` TEXT NULL,
  `salesNum` INT(10) UNSIGNED NOT NULL,
  `inStockNum` INT(10) UNSIGNED NOT NULL,
  `reviewsNum` INT(10) UNSIGNED NOT NULL,
  `goodReviewsNum` INT(10) UNSIGNED NOT NULL,
  `reviewsPercent` TINYINT(3) UNSIGNED NULL,
  `verificationRejectReason` TEXT NULL,
  `isDeleted` tinyint(1) UNSIGNED NOT NULL,
  `createdTs` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `userId` (`userId`),
  INDEX `statusId` (`statusId`),
  INDEX `productCategoryId` (`productCategoryId`)
)COLLATE='utf8mb4_general_ci' ENGINE=InnoDB;

CREATE TABLE `productGroup` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `userId` BIGINT(20) UNSIGNED NOT NULL,
  `name` VARCHAR(128) NULL,
  `productsNum` INT(10) UNSIGNED NOT NULL,
  `isDeleted` tinyint(1) UNSIGNED NOT NULL,
  `createdTs` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `userId` (`userId`)
)COLLATE='utf8mb4_general_ci' ENGINE=InnoDB;

CREATE TABLE `product_productGroup` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `productId` BIGINT(20) UNSIGNED NOT NULL,
  `productGroupId` BIGINT(20) UNSIGNED NOT NULL,
  `isDeleted` tinyint(1) UNSIGNED NOT NULL,
  `createdTs` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `productGroupId_productId` (`productGroupId`, `productId`)
)COLLATE='utf8mb4_general_ci' ENGINE=InnoDB;

CREATE TABLE `productObject` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `productId` BIGINT(20) UNSIGNED NOT NULL,
  `cartItemId` BIGINT(20) UNSIGNED NULL,
  `data` TEXT NOT NULL,
  `isDeleted` tinyint(1) UNSIGNED NOT NULL,
  `reservedTs` TIMESTAMP NULL,
  `createdTs` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `productId` (`productId`),
  INDEX `reservedTs` (`reservedTs`)
)COLLATE='utf8mb4_general_ci' ENGINE=InnoDB;

CREATE TABLE `productCategory` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parentId` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(128) NOT NULL,
  `code` varchar(64) NOT NULL,
  `elementsNum` int(10) UNSIGNED NOT NULL,
  `isDeleted` tinyint(1) UNSIGNED NOT NULL,
  `createdTs` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `parentId` (`parentId`),
  KEY `name` (`name`),
  UNIQUE INDEX `code` (`code`),
  CONSTRAINT `productCategory_ibfk1` FOREIGN KEY (`parentId`) REFERENCES `productCategory` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB COLLATE='utf8mb4_general_ci';

CREATE TABLE `productCategoryPath` (
  `ancestor_id` int(10) UNSIGNED NOT NULL,
  `descendant_id` int(10) UNSIGNED NOT NULL,
  `path_length` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`ancestor_id`,`descendant_id`),
  KEY `descendant_id` (`descendant_id`),
  CONSTRAINT `productCategoryPath_ibfk_1` FOREIGN KEY (`ancestor_id`)   REFERENCES `productCategory` (`id`) ON DELETE CASCADE,
  CONSTRAINT `productCategoryPath_ibfk_2` FOREIGN KEY (`descendant_id`) REFERENCES `productCategory` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB COLLATE='utf8mb4_general_ci';

CREATE TABLE `storageFile` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `typeId` TINYINT(3) UNSIGNED NOT NULL,
  `name` VARCHAR(32) NULL,
  `extension` VARCHAR(8) NULL,
  `directory` VARCHAR(16) NULL,
  `isDeleted` tinyint(1) UNSIGNED NOT NULL,
  `createdTs` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `name` (`name`),
  INDEX `typeId` (`typeId`)
)COLLATE='utf8mb4_general_ci' ENGINE=InnoDB;

CREATE TABLE `currency` (
    `id` TINYINT(3) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(32) NOT NULL,
    `rate` DECIMAL(12,4) NOT NULL,
    `scale` TINYINT(3) UNSIGNED NOT NULL DEFAULT '2',
    `isDeleted` tinyint(1) UNSIGNED NOT NULL,
    `createdTs` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
)COLLATE='utf8mb4_general_ci' ENGINE=InnoDB;
INSERT INTO currency(id, name, rate, scale, isDeleted) VALUES (1, 'usd', 63.7101, 2, 0);
INSERT INTO currency(id, name, rate, scale, isDeleted) VALUES (2, 'eur', 70.5207, 2, 0);
INSERT INTO currency(id, name, rate, scale, isDeleted) VALUES (3, 'rub', 1.0000, 2, 0);
INSERT INTO currency(id, name, rate, scale, isDeleted) VALUES (4, 'uah', 2.6337, 2, 0);

CREATE TABLE `event` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `userId` BIGINT(20) UNSIGNED NOT NULL,
  `typeId` TINYINT(3) UNSIGNED NOT NULL,
  `referenceId` BIGINT(20) UNSIGNED NOT NULL,
  `data` TEXT NOT NULL,
  `isRead` tinyint(1) UNSIGNED NOT NULL,
  `isDeleted` tinyint(1) UNSIGNED NOT NULL,
  `createdTs` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `userId` (`userId`),
  INDEX `typeId` (`typeId`),
  INDEX `referenceId` (`referenceId`)
)COLLATE='utf8mb4_general_ci' ENGINE=InnoDB;
/*
    This script does not work during migration, it needs to run manually from console
 */
/*ALTER TABLE `user`
    ADD COLUMN `apiKey` VARCHAR(64) NULL AFTER `accessRights`,
    ADD COLUMN `isApiEnabled` tinyint(1) UNSIGNED NOT NULL AFTER `apiKey`,
    ADD COLUMN `isPublicAgent` tinyint(1) UNSIGNED NOT NULL AFTER `isApiEnabled`,
    ADD COLUMN `agentInfo` TEXT NOT NULL AFTER `isPublicAgent`,
    ADD COLUMN `agentRating` DECIMAL(16,2) NOT NULL AFTER `agentInfo`,
    ADD COLUMN `agentSalesNum` INT(10) UNSIGNED NOT NULL AFTER `agentRating`,
    ADD COLUMN `agentPartnershipsNum` INT(10) UNSIGNED NOT NULL AFTER `agentSalesNum`,
    ADD COLUMN `nickname` VARCHAR(24) NULL AFTER `pass`,
    ADD UNIQUE INDEX `nickname` (`nickname`),
    ADD COLUMN `contact` VARCHAR(32) NULL AFTER `isPublicAgent`,
    ADD COLUMN `contactTypeId` TINYINT(3) UNSIGNED NULL AFTER `contact`,
    ADD COLUMN `partnerSellActionId` tinyint(1) UNSIGNED NOT NULL AFTER `agentPartnershipsNum`,
    ADD COLUMN `partnerDefaultFee` DECIMAL(4,2) NOT NULL AFTER `partnerSellActionId`,
    ADD COLUMN `tariffId` INT(10) UNSIGNED NOT NULL AFTER `id`
;*/

CREATE TABLE `partnership` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sellerUserId` BIGINT(20) UNSIGNED NOT NULL,
  `agentUserId` BIGINT(20) UNSIGNED NOT NULL,
  `statusId` TINYINT(3) UNSIGNED NOT NULL,
  `fee` DECIMAL(4,2) NOT NULL,
  `isDeleted` tinyint(1) UNSIGNED NOT NULL,
  `createdTs` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `sellerUserId_agentUserId` (`sellerUserId`, `agentUserId`),
  INDEX `agentUserId` (`agentUserId`),
  INDEX `statusId` (`statusId`)
)COLLATE='utf8mb4_general_ci' ENGINE=InnoDB;
  
CREATE TABLE `cart` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `customerId` BIGINT(20) UNSIGNED NULL,
  `statusId` TINYINT(3) UNSIGNED NOT NULL,
  `currencyId` TINYINT(3) UNSIGNED NULL,
  `locale` VARCHAR(2) NULL,
  `totalProductsAmount` DECIMAL(18,8) NULL,
  `isDeleted` tinyint(1) UNSIGNED NOT NULL,
  `paidTs` TIMESTAMP NULL,
  `createdTs` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `customerId_statusId` (`customerId`, `statusId`)
)COLLATE='utf8mb4_general_ci' ENGINE=InnoDB;
  
CREATE TABLE `customer` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(64) NOT NULL,
  `isDeleted` tinyint(1) UNSIGNED NOT NULL,
  `createdTs` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `email` (`email`)
)COLLATE='utf8mb4_general_ci' ENGINE=InnoDB;
  
CREATE TABLE `cartItem` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `customerId` BIGINT(20) UNSIGNED NULL,
  `cartId` BIGINT(20) UNSIGNED NOT NULL,
  `productId` BIGINT(20) UNSIGNED NOT NULL,
  `productUserId` BIGINT(20) UNSIGNED NOT NULL,
  `partnerId` BIGINT(20) UNSIGNED NULL,
  `currencyId` TINYINT(3) UNSIGNED NULL,
  `productObjectIds` TEXT NOT NULL,
  `calculations` TEXT NOT NULL,
  `amount` INT(10) UNSIGNED NOT NULL,
  `price` DECIMAL(18,8) NULL,
  `productPrice` DECIMAL(18,8) NULL,
  `amountInFact` INT(10) UNSIGNED NULL,
  `isPaid` tinyint(1) UNSIGNED NOT NULL,
  `hasUnreadMessagesBySeller` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `hasUnreadMessagesByCustomer` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `isDeleted` tinyint(1) UNSIGNED NOT NULL,
  `createdTs` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `cartId_productId` (`cartId`, `productId`),
  INDEX `customerId` (`customerId`),
  INDEX `productUserId` (`productUserId`),
  INDEX `partnerId` (`partnerId`)
)COLLATE='utf8mb4_general_ci' ENGINE=InnoDB;
  
CREATE TABLE `cartItemMessage` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cartItemId` BIGINT(20) UNSIGNED NOT NULL,
  `text` TEXT NOT NULL,
  `isAnswer` tinyint(1) UNSIGNED NOT NULL,
  `isDeleted` tinyint(1) UNSIGNED NOT NULL,
  `createdTs` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `cartItemId` (`cartItemId`)
)COLLATE='utf8mb4_general_ci' ENGINE=InnoDB;

CREATE TABLE `account` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `userId` BIGINT(20) UNSIGNED NOT NULL,
  `currencyId` TINYINT(3) UNSIGNED NOT NULL,
  `balance` DECIMAL(18,8) NOT NULL,
  `hold` DECIMAL(18,8) NOT NULL,
  `lastTransactionId` BIGINT(20) UNSIGNED NOT NULL,
  `isDeleted` tinyint(1) UNSIGNED NOT NULL,
  `createdTs` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `userId_currencyId` (`userId`, `currencyId`)
)COLLATE='utf8_general_ci' ENGINE=InnoDB;

CREATE TABLE `transaction` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `userId` BIGINT(20) UNSIGNED NOT NULL,
  `accountId` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
  `methodId` TINYINT(3) UNSIGNED NOT NULL,
  `amount` DECIMAL(18,8) NOT NULL,
  `descriptionData` TEXT NOT NULL,
  `currencyId` TINYINT(3) UNSIGNED NOT NULL,
  `balance` DECIMAL(18,8) NULL DEFAULT NULL,
  `accountOperationId` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
  `isDeleted` tinyint(1) UNSIGNED NOT NULL,
  `executingTs` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `createdTs` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `userId` (`userId`),
  INDEX `accountId` (`accountId`),
  INDEX `methodId` (`methodId`),
  INDEX `currencyId` (`currencyId`),
  INDEX `accountOperationId` (`accountOperationId`)
)COLLATE='utf8_general_ci' ENGINE=InnoDB;

CREATE TABLE `payoutMethod` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `fee` DECIMAL(4,2) NOT NULL,
  `name` VARCHAR(64) NOT NULL,
  `isEnabled` tinyint(1) UNSIGNED NOT NULL,
  `isDeleted` tinyint(1) UNSIGNED NOT NULL,
  `createdTs` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `name` (`name`)
)COLLATE='utf8_general_ci' ENGINE=InnoDB;
INSERT INTO `payoutMethod` (`id`, `fee`, `name`, `isEnabled`, `isDeleted`, `createdTs`) VALUES (1, 3.00, 'Qiwi', 1, 0, '2020-04-01 13:30:56');
INSERT INTO `payoutMethod` (`id`, `fee`, `name`, `isEnabled`, `isDeleted`, `createdTs`) VALUES (2, 3.00, 'Yandex', 1, 0, '2020-04-01 13:30:56');


CREATE TABLE `payout` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `externalId` BIGINT(20) UNSIGNED NULL,
  `accountId` BIGINT(20) UNSIGNED NOT NULL,
  `payoutMethodId` INT(10) UNSIGNED NOT NULL,
  `userId` BIGINT(20) UNSIGNED NOT NULL,
  `receiver` VARCHAR(64) NOT NULL,
  `amount` DECIMAL(18,8) NOT NULL,
  `fee` DECIMAL(4,2) NOT NULL,
  `writeOff` DECIMAL(18,8) NOT NULL,
  `statusId` TINYINT(3) UNSIGNED NOT NULL,
  `isDeleted` tinyint(1) UNSIGNED NOT NULL,
  `createdTs` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `externalId` (`externalId`),
  INDEX `accountId` (`accountId`),
  INDEX `payoutMethodId` (`payoutMethodId`),
  INDEX `userId` (`userId`),
  INDEX `receiver` (`receiver`),
  INDEX `statusId` (`statusId`)
)COLLATE='utf8_general_ci' ENGINE=InnoDB AUTO_INCREMENT=300000100;

CREATE TABLE `ticket` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `userId` BIGINT(20) UNSIGNED NOT NULL,
    `subject` VARCHAR(256) NOT NULL,
    `messagesSendingNum` INT(10) UNSIGNED NOT NULL,
    `messagesNum` INT(10) UNSIGNED NOT NULL,
    `hasUnreadMessage` TINYINT(3) UNSIGNED NOT NULL,
    `isDeleted` tinyint(1) UNSIGNED NOT NULL,
    `lastMessageTs` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `createdTs` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `userId` (`userId`)
)COLLATE='utf8_general_ci' ENGINE=InnoDB;

CREATE TABLE `review` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `cartItemId` BIGINT(20) UNSIGNED NOT NULL,
    `productId` BIGINT(20) UNSIGNED NOT NULL,
    `text` TEXT NOT NULL,
    `answer` TEXT NULL,
    `isGood` tinyint(1) UNSIGNED NOT NULL,
    `isDeleted` tinyint(1) UNSIGNED NOT NULL,
    `answerTs` TIMESTAMP NULL DEFAULT NULL,
    `createdTs` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `cartItemId` (`cartItemId`),
    INDEX `productId` (`productId`)
)COLLATE='utf8_general_ci' ENGINE=InnoDB;

CREATE TABLE `tariff` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(64) NOT NULL,
    `saleFee` DECIMAL(4,2) NOT NULL,
    `holdSeconds` INT(10) UNSIGNED NOT NULL,
    `price` DECIMAL(18,8) NOT NULL,
    `icon` VARCHAR(32) NOT NULL,
    `isHidden` tinyint(1) UNSIGNED NOT NULL,
    `isDeleted` tinyint(1) UNSIGNED NOT NULL,
    `createdTs` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
)COLLATE='utf8_general_ci' ENGINE=InnoDB;
INSERT INTO `tariff` (`id`, `name`, `saleFee`, `holdSeconds`, `price`, `icon`, `isHidden`, `isDeleted`) VALUES (1, 'Продавец', 6, 172800, 0, 'mdi-account-tie', 0, 0);
INSERT INTO `tariff` (`id`, `name`, `saleFee`, `holdSeconds`, `price`, `icon`, `isHidden`, `isDeleted`) VALUES (2, 'Первопроходец', 2, 172800, 0, 'mdi-numeric-1-circle', 1, 0);

CREATE TABLE `country` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(250) NOT NULL,
  `fullname` VARCHAR(250) NOT NULL,
  `alpha2` VARCHAR(2) NOT NULL,
  `alpha3` VARCHAR(3) NOT NULL,
  `localeId` TINYINT(3) NOT NULL,
  `currencyId` TINYINT(3) UNSIGNED NOT NULL,
  `isDeleted` tinyint(1) UNSIGNED NOT NULL,
  `createdTs` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `alpha2` (`alpha2`)
)
COLLATE='utf8mb4_general_ci' ENGINE=InnoDB;
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (4, 'АФГАНИСТАН', 'ПЕРЕХОДНОЕ ИСЛАМСКОЕ ГОСУДАРСТВО АФГАНИСТАН', 'AF', 'AFG', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (8, 'АЛБАНИЯ', 'РЕСПУБЛИКА АЛБАНИЯ', 'AL', 'ALB', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (10, 'АНТАРКТИДА', 'АНТАРКТИДА', 'AQ', 'ATA', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (12, 'АЛЖИР', 'АЛЖИРСКАЯ НАРОДНАЯ ДЕМОКРАТИЧЕСКАЯ РЕСПУБЛИКА', 'DZ', 'DZA', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (16, 'ВОСТОЧНОЕ САМОА', 'АМЕРИКАНСКОЕ САМОА (США)', 'AS', 'ASM', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (20, 'АНДОРРА', 'КНЯЖЕСТВО АНДОРРА', 'AD', 'AND', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (24, 'АНГОЛА', 'РЕСПУБЛИКА АНГОЛА', 'AO', 'AGO', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (28, 'АНТИГУА И БАРБУДА', 'АНТИГУА И БАРБУДА', 'AG', 'ATG', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (31, 'АЗЕРБАЙДЖАН', 'РЕСПУБЛИКА АЗЕРБАЙДЖАН', 'AZ', 'AZE', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (32, 'АРГЕНТИНА', 'АРГЕНТИНСКАЯ РЕСПУБЛИКА', 'AR', 'ARG', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (36, 'АВСТРАЛИЯ', 'АВСТРАЛИЯ', 'AU', 'AUS', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (40, 'АВСТРИЯ', 'АВСТРИЙСКАЯ РЕСПУБЛИКА', 'AT', 'AUT', 1, 2, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (44, 'БАГАМЫ', 'СОДРУЖЕСТВО БАГАМЫ', 'BS', 'BHS', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (48, 'БАХРЕЙН', 'КОРОЛЕВСТВО БАХРЕЙН', 'BH', 'BHR', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (50, 'БАНГЛАДЕШ', 'НАРОДНАЯ РЕСПУБЛИКА БАНГЛАДЕШ', 'BD', 'BGD', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (51, 'АРМЕНИЯ', 'РЕСПУБЛИКА АРМЕНИЯ', 'AM', 'ARM', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (52, 'БАРБАДОС', 'БАРБАДОС', 'BB', 'BRB', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (56, 'БЕЛЬГИЯ', 'КОРОЛЕВСТВО БЕЛЬГИИ', 'BE', 'BEL', 1, 2, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (60, 'БЕРМУДЫ', 'БЕРМУДСКИЕ ОСТРОВА', 'BM', 'BMU', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (64, 'БУТАН', 'КОРОЛЕВСТВО БУТАН', 'BT', 'BTN', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (68, 'БОЛИВИЯ', 'РЕСПУБЛИКА БОЛИВИЯ', 'BO', 'BOL', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (70, 'БОСНИЯ И ГЕРЦЕГОВИНА', 'БОСНИЯ И ГЕРЦЕГОВИНА', 'BA', 'BIH', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (72, 'БОТСВАНА', 'РЕСПУБЛИКА БОТСВАНА', 'BW', 'BWA', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (74, 'БУВЕ', 'ОСТРОВ БУВЕ', 'BV', 'BVT', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (76, 'БРАЗИЛИЯ', 'ФЕДЕРАТИВНАЯ РЕСПУБЛИКА БРАЗИЛИЯ', 'BR', 'BRA', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (84, 'БЕЛИЗ', 'БЕЛИЗ', 'BZ', 'BLZ', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (86, 'БРИТАН. ТЕРРИТ.', 'БРИТАНСКАЯ ТЕРРИТОРИЯ В ИНДИЙСКОМ ОКЕАНЕ (БРИТ.)', 'IO', 'IOT', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (90, 'СОЛОМОНОВЫ О-ВА', 'СОЛОМОНОВЫ ОСТРОВА', 'SB', 'SLB', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (92, 'ВИРГИН. О-ВА, БРИТАНСКИЕ', 'БРИТАНСКИЕ ВИРГИНСКИЕ ОСТРОВА', 'VG', 'VGB', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (96, 'БРУНЕЙ', 'БРУНЕЙ-ДАРУССАЛАМ', 'BN', 'BRN', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (100, 'БОЛГАРИЯ', 'РЕСПУБЛИКА БОЛГАРИЯ', 'BG', 'BGR', 1, 2, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (104, 'МЬЯНМА', 'СОЮЗ МЬЯНМА', 'MM', 'MMR', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (108, 'БУРУНДИ', 'РЕСПУБЛИКА БУРУНДИ', 'BI', 'BDI', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (112, 'БЕЛАРУСЬ', 'РЕСПУБЛИКА БЕЛАРУСЬ', 'BY', 'BLR', 2, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (116, 'КАМБОДЖА', 'КОРОЛЕВСТВО КАМБОДЖА', 'KH', 'KHM', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (120, 'КАМЕРУН', 'РЕСПУБЛИКА КАМЕРУН', 'CM', 'CMR', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (124, 'КАНАДА', 'КАНАДА', 'CA', 'CAN', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (132, 'КАБО-ВЕРДЕ', 'РЕСПУБЛИКА КАБО-ВЕРДЕ', 'CV', 'CPV', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (136, 'КАЙМАН', 'ОСТРОВА КАЙМАН', 'KY', 'CYM', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (140, 'ЦЕНТР. - АФР. РЕСПУБЛИКА', 'ЦЕНТРАЛЬНО-АФРИКАНСКАЯ РЕСПУБЛИКА (ЦАР)', 'CF', 'CAF', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (144, 'ШРИ-ЛАНКА', 'ДЕМОКРАТИЧЕСКАЯ СОЦИАЛИСТИЧЕСКАЯ РЕСПУБЛИКА ШРИ-ЛАНКА', 'LK', 'LKA', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (148, 'ЧАД', 'РЕСПУБЛИКА ЧАД', 'TD', 'TCD', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (152, 'ЧИЛИ', 'РЕСПУБЛИКА ЧИЛИ', 'CL', 'CHL', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (156, 'КИТАЙ', 'КИТАЙСКАЯ НАРОДНАЯ РЕСПУБЛИКА (КНР)', 'CN', 'CHN', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (158, 'ТАЙВАНЬ', 'ТАЙВАНЬ (В СОСТАВЕ КИТАЯ)', 'TW', 'TWN', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (162, 'О-В РОЖДЕСТВА', 'ОСТРОВ РОЖДЕСТВА (АВСТРАЛ.)', 'CX', 'CXR', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (166, 'КОКОСОВЫЕ О-ВА', 'КОКОСОВЫЕ (КИЛИНГ) ОСТРОВА', 'CC', 'CCK', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (170, 'КОЛУМБИЯ', 'РЕСПУБЛИКА КОЛУМБИЯ', 'CO', 'COL', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (174, 'КОМОРЫ', 'СОЮЗ КОМОРЫ', 'KM', 'COM', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (175, 'МАЙОТТА', 'МАЙОТТА', 'YT', 'MYT', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (178, 'КОНГО', 'РЕСПУБЛИКА КОНГО', 'CG', 'COG', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (180, 'КОНГО', 'ДЕМОКРАТИЧЕСКАЯ РЕСПУБЛИКА КОНГО', 'CD', 'COD', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (184, 'О-ВА КУКА', 'ОСТРОВА КУКА (Н. ЗЕЛ.)', 'СК', 'COK', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (188, 'КОСТА-РИКА', 'РЕСПУБЛИКА КОСТА-РИКА', 'CR', 'CRI', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (191, 'ХОРВАТИЯ', 'РЕСПУБЛИКА ХОРВАТИЯ', 'HR', 'HRV', 1, 2, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (192, 'КУБА', 'РЕСПУБЛИКА КУБА', 'CU', 'CUB', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (196, 'КИПР', 'РЕСПУБЛИКА КИПР', 'CY', 'CYP', 1, 2, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (203, 'ЧЕХИЯ', 'ЧЕШСКАЯ РЕСПУБЛИКА', 'CZ', 'CZE', 1, 2, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (204, 'БЕНИН', 'РЕСПУБЛИКА БЕНИН', 'BJ', 'BEN', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (208, 'ДАНИЯ', 'КОРОЛЕВСТВО ДАНИЯ', 'DK', 'DNK', 1, 2, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (212, 'ДОМИНИКА', 'СОДРУЖЕСТВО ДОМИНИКИ', 'DM', 'DMA', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (214, 'ДОМИНИКАНСКАЯ РЕСПУБЛИКА', 'ДОМИНИКАНСКАЯ РЕСПУБЛИКА', 'DO', 'DOM', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (218, 'ЭКВАДОР', 'РЕСПУБЛИКА ЭКВАДОР', 'EC', 'ECU', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (222, 'ЭЛЬ-САЛЬВАДОР', 'РЕСПУБЛИКА ЭЛ-САЛЬВАДОР', 'SV', 'SLV', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (226, 'ЭКВАТОРИАЛЬНАЯ ГВИНЕЯ', 'РЕСПУБЛИКА ЭКВАТОРИАЛЬНАЯ ГВИНЕЯ', 'GQ', 'GNQ', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (231, 'ЭФИОПИЯ', 'ФЕДЕРАТИВНАЯ ДЕМОКРАТИЧЕСКАЯ  РЕСПУБЛИКА ЭФИОПИЯ', 'ET', 'ETH', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (232, 'ЭРИТРЕЯ', 'ЭРИТРЕЯ', 'ER', 'ERI', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (233, 'ЭСТОНИЯ', 'ЭСТОНСКАЯ РЕСПУБЛИКА', 'EE', 'EST', 1, 2, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (234, 'ФАРЕРСКИЕ О-ВА', 'ФАРЕРСКИЕ ОСТРОВА (В СОСТАВЕ ДАНИИ)', 'FO', 'FRO', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (238, 'ФОЛКЛЕНДСКИЕ О-ВА', 'ФОЛКЛЕНДСКИЕ ОСТРОВА (МАЛЬВИНСКИЕ)', 'FK', 'FLK', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (239, 'ЮЖНАЯ ДЖОРДЖИЯ И ЮЖНЫЕ САНДВИЧЕВЫ ОСТРОВА', 'ЮЖНАЯ ДЖОРДЖИЯ И ЮЖНЫЕ САНДВИЧЕВЫ ОСТРОВА', 'GS', 'SGS', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (242, 'ФИДЖИ', 'РЕСПУБЛИКА ОСТРОВОВ ФИДЖИ', 'FJ', 'FJI', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (246, 'ФИНЛЯНДИЯ', 'ФИНЛЯНДСКАЯ РЕСПУБЛИКА', 'FI', 'FIN', 1, 2, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (248, 'ЭЛАНДСКИЕ ОСТРОВА', 'ЭЛАНДСКИЕ ОСТРОВА', 'AX', 'ALA', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (250, 'ФРАНЦИЯ', 'ФРАНЦУЗСКАЯ РЕСПУБЛИКА', 'FR', 'FRA', 1, 2, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (254, 'ГВИАНА', 'ФРАНЦУЗСКАЯ ГВИАНА (ФР.)', 'GF', 'GUF', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (258, 'ФРАНЦУЗСКАЯ ПОЛИНЕЗИЯ', 'ФРАНЦУЗСКАЯ ПОЛИНЕЗИЯ (ФР.)', 'PF', 'PYF', 1, 2, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (260, 'ФР. ЮЖНЫЕ ТЕРРИТОРИИ', 'ФРАНЦУЗСКИЕ ЮЖНЫЕ ТЕРРИТОРИИ (ФР.)', 'TF', 'ATF', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (262, 'ДЖИБУТИ', 'РЕСПУБЛИКА ДЖИБУТИ', 'DJ', 'DJI', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (266, 'ГАБОН', 'ГАБОНСКАЯ РЕСПУБЛИКА', 'GA', 'GAB', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (268, 'ГРУЗИЯ', 'РЕСПУБЛИКА ГРУЗИЯ', 'GE', 'GEO', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (270, 'ГАМБИЯ', 'РЕСПУБЛИКА ГАМБИЯ', 'GM', 'GMB', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (275, 'ПАЛЕСТИНСКАЯ ТЕРРИТОРИЯ, ОККУПИРОВАННАЯ', 'ОККУПИРОВАННАЯ ПАЛЕСТИНСКАЯ ТЕРРИТОРИЯ', 'PS', 'PSE', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (276, 'ГЕРМАНИЯ', 'ФЕДЕРАТИВНАЯ РЕСПУБЛИКА ГЕРМАНИЯ', 'DE', 'DEU', 1, 2, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (288, 'ГАНА', 'РЕСПУБЛИКА ГАНА', 'GH', 'GHA', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (292, 'ГИБРАЛТАР', 'ГИБРАЛТАР (БРИТ.)', 'GI', 'GIB', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (296, 'КИРИБАТИ', 'РЕСПУБЛИКА КИРИБАТИ', 'KI', 'KIR', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (300, 'ГРЕЦИЯ', 'ГРЕЧЕСКАЯ РЕСПУБЛИКА', 'GR', 'GRC', 1, 2, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (304, 'ГРЕНЛАНДИЯ', 'ГРЕНЛАНДИЯ', 'GL', 'GRL', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (308, 'ГРЕНАДА', 'ГРЕНАДА', 'GD', 'GRD', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (312, 'ГВАДЕЛУПА', 'ГВАДЕЛУПА (ФР.)', 'GP', 'GLP', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (316, 'ГУАМ', 'ГУАМ (США)', 'GU', 'GUM', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (320, 'ГВАТЕМАЛА', 'РЕСПУБЛИКА ГВАТЕМАЛА', 'GT', 'GTM', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (324, 'ГВИНЕЯ', 'ГВИНЕЙСКАЯ РЕСПУБЛИКА', 'GN', 'GIN', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (328, 'ГАЙАНА', 'РЕСПУБЛИКА ГАЙАНА', 'GY', 'GUY', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (332, 'ГАИТИ', 'РЕСПУБЛИКА ГАИТИ', 'HT', 'HTI', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (334, 'ХЕРД И МАКДОНАЛЬД', 'ОСТРОВ ХЕРД И ОСТРОВА МАКДОНАЛЬД', 'HM', 'HMD', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (336, 'ВАТИКАН', 'ПАПСКИЙ ПРЕСТОЛ (ГОСУДАРСТВО-ГОРОД ВАТИКАН)', 'VA', 'VAT', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (340, 'ГОНДУРАС', 'РЕСПУБЛИКА ГОНДУРАС', 'HN', 'HND', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (344, 'ГОНКОНГ', 'СПЕЦИАЛЬНЫЙ АДМИНИСТРАТИВНЫЙ РЕГИОН КИТАЯ ГОНКОНГ', 'HK', 'HKG', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (348, 'ВЕНГРИЯ', 'ВЕНГЕРСКАЯ РЕСПУБЛИКА', 'HU', 'HUN', 1, 2, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (352, 'ИСЛАНДИЯ', 'РЕСПУБЛИКА ИСЛАНДИЯ', 'IS', 'ISL', 1, 2, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (356, 'ИНДИЯ', 'РЕСПУБЛИКА ИНДИЯ', 'IN', 'IND', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (360, 'ИНДОНЕЗИЯ', 'РЕСПУБЛИКА ИНДОНЕЗИЯ', 'ID', 'IDN', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (364, 'ИРАН', 'ИСЛАМСКАЯ РЕСПУБЛИКА ИРАН', 'IR', 'IRN', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (368, 'ИРАК', 'РЕСПУБЛИКА ИРАК', 'IQ', 'IRQ', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (372, 'ИРЛАНДИЯ', 'ИРЛАНДИЯ', 'IE', 'IRL', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (376, 'ИЗРАИЛЬ', 'ГОСУДАРСТВО ИЗРАИЛЬ', 'IL', 'ISR', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (380, 'ИТАЛИЯ', 'ИТАЛЬЯНСКАЯ РЕСПУБЛИКА', 'IT', 'ITA', 1, 2, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (384, 'КОТ Д\'ИВУАР', 'РЕСПУБЛИКА КОТ Д\'ИВУАР', 'CI', 'CIV', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (388, 'ЯМАЙКА', 'ЯМАЙКА', 'JM', 'JAM', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (392, 'ЯПОНИЯ', 'ЯПОНИЯ', 'JP', 'JPN', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (398, 'КАЗАХСТАН', 'РЕСПУБЛИКА КАЗАХСТАН', 'KZ', 'KAZ', 2, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (400, 'ИОРДАНИЯ', 'ИОРДАНСКОЕ ХАШИМИТСКОЕ КОРОЛЕВСТВО', 'JO', 'JOR', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (404, 'КЕНИЯ', 'РЕСПУБЛИКА КЕНИЯ', 'KE', 'KEN', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (408, 'КОРЕЯ (КНДР)', 'КОРЕЙСКАЯ НАРОДНО-ДЕМОКРАТИЧЕСКАЯ РЕСПУБЛИКА', 'KP', 'PRK', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (410, 'КОРЕЯ', 'РЕСПУБЛИКА КОРЕЯ', 'KR', 'KOR', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (414, 'КУВЕЙТ', 'ГОСУДАРСТВО КУВЕЙТ', 'KW', 'KWT', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (417, 'КЫРГЫЗСТАН', 'РЕСПУБЛИКА КЫРГЫЗСТАН', 'KG', 'KGZ', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (418, 'ЛАОС', 'ЛАОССКАЯ НАРОДНО-ДЕМОКРАТИЧЕСКАЯ РЕСПУБЛИКА', 'LA', 'LAO', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (422, 'ЛИВАН', 'ЛИВАНСКАЯ РЕСПУБЛИКА', 'LB', 'LBN', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (426, 'ЛЕСОТО', 'КОРОЛЕВСТВО ЛЕСОТО', 'LS', 'LSO', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (428, 'ЛАТВИЯ', 'ЛАТВИЙСКАЯ РЕСПУБЛИКА', 'LV', 'LVA', 1, 2, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (430, 'ЛИБЕРИЯ', 'РЕСПУБЛИКА ЛИБЕРИЯ', 'LR', 'LBR', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (434, 'ЛИВИЯ', 'СОЦИАЛИСТИЧЕСКАЯ НАРОДНАЯ ЛИВИЙСКАЯ АРАБСКАЯ ДЖАМАХИРИЯ', 'LY', 'LBY', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (438, 'ЛИХТЕНШТЕЙН', 'КНЯЖЕСТВО ЛИХТЕНШТЕЙН', 'LI', 'LIE', 1, 2, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (440, 'ЛИТВА', 'ЛИТОВСКАЯ РЕСПУБЛИКА', 'LT', 'LTU', 1, 2, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (442, 'ЛЮКСЕМБУРГ', 'ВЕЛИКОЕ ГЕРЦОГСТВО ЛЮКСЕМБУРГ', 'LU', 'LUX', 1, 2, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (446, 'МАКАО', 'СПЕЦИАЛЬНЫЙ АДМИНИСТРАТИВНЫЙ РЕГИОН КИТАЯ МАКАО', 'MO', 'MAC', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (450, 'МАДАГАСКАР', 'ДЕМОКРАТИЧЕСКАЯ РЕСПУБЛИКА МАДАГАСКАР', 'MG', 'MDG', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (454, 'МАЛАВИ', 'РЕСПУБЛИКА МАЛАВИ', 'MW', 'MWI', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (458, 'МАЛАЙЗИЯ', 'МАЛАЙЗИЯ', 'MY', 'MYS', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (462, 'МАЛЬДИВЫ', 'МАЛЬДИВСКАЯ РЕСПУБЛИКА', 'MV', 'MDV', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (466, 'МАЛИ', 'РЕСПУБЛИКА МАЛИ', 'ML', 'MLI', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (470, 'МАЛЬТА', 'РЕСПУБЛИКА МАЛЬТА', 'MT', 'MLT', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (474, 'МАРТИНИКА', 'МАРТИНИКА (ФР.)', 'MQ', 'MTQ', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (478, 'МАВРИТАНИЯ', 'ИСЛАМСКАЯ РЕСПУБЛИКА МАВРИТАНИЯ', 'MR', 'MRT', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (480, 'МАВРИКИЙ', 'РЕСПУБЛИКА МАВРИКИЙ', 'MU', 'MUS', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (484, 'МЕКСИКА', 'МЕКСИКАНСКИЕ СОЕДИНЕННЫЕ ШТАТЫ', 'MX', 'MEX', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (492, 'МОНАКО', 'КНЯЖЕСТВО МОНАКО', 'MC', 'MCO', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (496, 'МОНГОЛИЯ', 'МОНГОЛИЯ', 'MN', 'MHG', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (498, 'МОЛДОВА', 'РЕСПУБЛИКА МОЛДОВА', 'MD', 'MDA', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (499, 'ЧЕРНОГОРИЯ', 'РЕСПУБЛИКА ЧЕРНОГОРИЯ', 'ME', 'MNE', 1, 2, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (500, 'МОНТСЕРРАТ', 'МОНТСЕРРАТ (БРИТ.)', 'MS', 'MSR', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (504, 'МАРОККО', 'КОРОЛЕВСТВО МАРОККО', 'MA', 'MAR', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (508, 'МОЗАМБИК', 'РЕСПУБЛИКА МОЗАМБИК', 'MZ', 'MOZ', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (512, 'ОМАН', 'СУЛТАНАТ ОМАН', 'OM', 'OMN', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (516, 'НАМИБИЯ', 'РЕСПУБЛИКА НАМИБИЯ', 'NA', 'NAM', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (520, 'НАУРУ', 'РЕСПУБЛИКА НАУРУ', 'NR', 'NRU', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (524, 'НЕПАЛ', 'КОРОЛЕВСТВО НЕПАЛ', 'NP', 'NPL', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (528, 'НИДЕРЛАНДЫ', 'КОРОЛЕВСТВО НИДЕРЛАНДЫ', 'NL', 'NLD', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (530, 'НИДЕРЛАНДСКИЕ АНТИЛЫ', 'НИДЕРЛАНДСКИЕ АНТИЛЫ', 'AN', 'ANT', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (533, 'АРУБА', 'ОСТРОВ АРУБА', 'AW', 'ABW', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (540, 'НОВАЯ КАЛЕДОНИЯ', 'НОВАЯ КАЛЕДОНИЯ', 'NC', 'NCL', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (548, 'ВАНУАТУ', 'РЕСПУБЛИКА ВАНУАТУ', 'VU', 'VUT', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (554, 'НОВАЯ ЗЕЛАНДИЯ', 'НОВАЯ ЗЕЛАНДИЯ', 'NZ', 'NZL', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (558, 'НИКАРАГУА', 'РЕСПУБЛИКА НИКАРАГУА', 'NI', 'NIC', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (562, 'НИГЕР', 'РЕСПУБЛИКА НИГЕР', 'NE', 'NER', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (566, 'НИГЕРИЯ', 'ФЕДЕРАТИВНАЯ РЕСПУБЛИКА НИГЕРИЯ', 'NG', 'NGA', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (570, 'НИУЭ', 'РЕСПУБЛИКА НИУЭ', 'NU', 'NIU', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (574, 'НОРФОЛК', 'ОСТРОВ НОРФОЛК', 'NF', 'NFK', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (578, 'НОРВЕГИЯ', 'КОРОЛЕВСТВО НОРВЕГИЯ', 'NO', 'NOR', 1, 2, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (580, 'МАРИАНСКИЕ ОСТРОВА', 'СОДРУЖЕСТВО СЕВЕРНЫХ МАРИАНСКИХ ОСТРОВОВ', 'MP', 'MNP', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (581, 'МАЛЫЕ ТИХООК. ОСТРОВА (США)', 'МАЛЫЕ ТИХООКЕАНСКИЕ ОТДАЛЕННЫЕ ОСТРОВА (США)', 'UM', 'UMI', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (583, 'МИКРОНЕЗИЯ', 'ФЕДЕРАТИВНЫЕ ШТАТЫ МИКРОНЕЗИИ', 'FM', 'FSM', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (584, 'МАРШАЛЛОВЫ ОСТРОВА', 'РЕСПУБЛИКА МАРШАЛЛОВЫ ОСТРОВА', 'MH', 'MHL', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (585, 'ПАЛАУ', 'РЕСПУБЛИКА ПАЛАУ', 'PW', 'PLW', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (586, 'ПАКИСТАН', 'ИСЛАМСКАЯ РЕСПУБЛИКА ПАКИСТАН', 'PK', 'PAK', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (591, 'ПАНАМА', 'РЕСПУБЛИКА ПАНАМА', 'PA', 'PAN', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (598, 'ПАПУА - НОВАЯ ГВИНЕЯ', 'ПАПУА - НОВАЯ ГВИНЕЯ', 'PG', 'PNG', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (600, 'ПАРАГВАЙ', 'РЕСПУБЛИКА ПАРАГВАЙ', 'PY', 'PRY', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (604, 'ПЕРУ', 'РЕСПУБЛИКА ПЕРУ', 'PE', 'PER', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (608, 'ФИЛИППИНЫ', 'РЕСПУБЛИКА ФИЛИППИНЫ', 'PH', 'PHL', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (612, 'ПИТКЭРН', 'ПИТКЭРН (БРИТ.)', 'PN', 'PCN', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (616, 'ПОЛЬША', 'РЕСПУБЛИКА ПОЛЬША', 'PL', 'POL', 1, 2, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (620, 'ПОРТУГАЛИЯ', 'ПОРТУГАЛЬСКАЯ РЕСПУБЛИКА', 'PT', 'PRT', 1, 2, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (624, 'ГВИНЕЯ-БИСАУ', 'РЕСПУБЛИКА ГВИНЕЯ-БИСАУ', 'GW', 'GNB', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (626, 'ТИМОР-ЛЕСТЕ', 'ДЕМОКРАТИЧЕСКАЯ РЕСПУБЛИКА ТИМОР-ЛЕСТЕ', 'TP', 'TMP', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (630, 'ПУЭРТО-РИКО', 'ПУЭРТО-РИКО', 'PR', 'PRI', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (634, 'КАТАР', 'ГОСУДАРСТВО КАТАР', 'QA', 'QAT', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (638, 'РЕЮНЬОН', 'РЕЮНЬОН', 'RE', 'REU', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (642, 'РУМЫНИЯ', 'РУМЫНИЯ', 'RO', 'ROM', 1, 2, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (643, 'РОССИЯ', 'РОССИЙСКАЯ ФЕДЕРАЦИЯ', 2, 'RUS', 2, 3, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (646, 'РУАНДА', 'РУАНДИЙСКАЯ РЕСПУБЛИКА', 'RW', 'RWA', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (654, 'СВЯТАЯ ЕЛЕНА', 'ОСТРОВ СВЯТОЙ ЕЛЕНЫ (БРИТ.)', 'SH', 'SHN', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (659, 'СЕНТ-КИТС И НЕВИС', 'ФЕДЕРАЦИЯ СЕНТ-КИТС (СЕНТ-КРИСТОФЕР) И НЕВИС', 'KN', 'KNA', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (660, 'АНГИЛЬЯ', 'АНГИЛЬЯ', 'AI', 'AIA', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (662, 'СЕНТ-ЛЮСИЯ', 'СЕНТ-ЛЮСИЯ', 'LC', 'LCA', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (666, 'СЕН-ПЬЕР И МИКЕЛОН', 'СЕН-ПЬЕР И МИКЕЛОН (ФР.)', 'PM', 'SPM', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (670, 'СЕНТ-ВИНСЕНТ И ГРЕНАДИНЫ', 'СЕНТ-ВИНСЕНТ И ГРЕНАДИНЫ', 'VC', 'VCT', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (674, 'САН-МАРИНО', 'РЕСПУБЛИКА САН-МАРИНО', 'SM', 'SMR', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (678, 'САН-ТОМЕ И ПРИНСИПИ', 'ДЕМОКРАТИЧЕСКАЯ РЕСПУБЛИКА САН-ТОМЕ И ПРИНСИПИ', 'ST', 'STR', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (682, 'САУДОВСКАЯ АРАВИЯ', 'КОРОЛЕВСТВО САУДОВСКАЯ АРАВИЯ', 'SA', 'SAU', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (686, 'СЕНЕГАЛ', 'РЕСПУБЛИКА СЕНЕГАЛ', 'SN', 'SEN', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (688, 'СЕРБИЯ', 'РЕСПУБЛИКА СЕРБИЯ', 'RS', 'SRB', 1, 2, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (690, 'СЕЙШЕЛЫ', 'РЕСПУБЛИКА СЕЙШЕЛЫ', 'SC', 'SYC', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (694, 'СЬЕРРА-ЛЕОНЕ', 'РЕСПУБЛИКА СЬЕРРА-ЛЕОНЕ', 'SL', 'SLE', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (702, 'СИНГАПУР', 'РЕСПУБЛИКА СИНГАПУР', 'SG', 'SGP', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (703, 'СЛОВАКИЯ', 'СЛОВАЦКАЯ РЕСПУБЛИКА', 'SK', 'SVK', 1, 2, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (704, 'ВЬЕТНАМ', 'СОЦИАЛИСТИЧЕСКАЯ РЕСПУБЛИКА ВЬЕТНАМ', 'VN', 'VNM', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (705, 'СЛОВЕНИЯ', 'РЕСПУБЛИКА СЛОВЕНИЯ', 'SI', 'SVN', 1, 2, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (706, 'СОМАЛИ', 'СОМАЛИЙСКАЯ РЕСПУБЛИКА', 'SO', 'SOM', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (710, 'ЮЖНАЯ АФРИКА', 'ЮЖНО-АФРИКАНСКАЯ РЕСПУБЛИКА', 'ZA', 'ZAF', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (716, 'ЗИМБАБВЕ', 'РЕСПУБЛИКА ЗИМБАБВЕ', 'ZW', 'ZWE', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (724, 'ИСПАНИЯ', 'КОРОЛЕВСТВО ИСПАНИЯ', 'ES', 'ESP', 1, 2, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (732, 'ЗАПАДНАЯ САХАРА', 'ЗАПАДНАЯ САХАРА', 'EH', 'ESH', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (736, 'СУДАН', 'РЕСПУБЛИКА СУДАН', 'SD', 'SDN', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (740, 'СУРИНАМ', 'РЕСПУБЛИКА СУРИНАМ', 'SR', 'SUR', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (744, 'ШПИЦБЕРГЕН И ЯН-МАЙЕН', 'ШПИЦБЕРГЕН И ЯН-МАЙЕН (НОРВ.)', 'SJ', 'SJM', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (748, 'СВАЗИЛЕНД', 'КОРОЛЕВСТВО СВАЗИЛЕНД', 'SZ', 'SWZ', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (752, 'ШВЕЦИЯ', 'КОРОЛЕВСТВО ШВЕЦИЯ', 'SE', 'SWE', 1, 2, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (756, 'ШВЕЙЦАРИЯ', 'ШВЕЙЦАРСКАЯ КОНФЕДЕРАЦИЯ', 'CH', 'CHE', 1, 2, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (760, 'СИРИЯ', 'СИРИЙСКАЯ АРАБСКАЯ РЕСПУБЛИКА', 'SY', 'SYR', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (762, 'ТАДЖИКИСТАН', 'РЕСПУБЛИКА ТАДЖИКИСТАН', 'TJ', 'TJK', 2, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (764, 'ТАИЛАНД', 'КОРОЛЕВСТВО ТАИЛАНД', 'TH', 'THA', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (768, 'ТОГО', 'ТОГОЛЕЗСКАЯ РЕСПУБЛИКА', 'TG', 'TGO', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (772, 'ТОКЕЛАУ', 'ТОКЕЛАУ (ЮНИОН) (Н. ЗЕЛ.)', 'TK', 'TKL', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (776, 'ТОНГА', 'КОРОЛЕВСТВО ТОНГА', 'TO', 'TON', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (780, 'ТРИНИДАД И ТОБАГО', 'РЕСПУБЛИКА ТРИНИДАД И ТОБАГО', 'TT', 'TTO', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (784, 'ОБЪЕД. АРАБСКИЕ ЭМИРАТЫ', 'ОБЪЕДИНЕННЫЕ АРАБСКИЕ ЭМИРАТЫ', 'AE', 'ARE', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (788, 'ТУНИС', 'ТУНИССКАЯ РЕСПУБЛИКА', 'TN', 'TUN', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (792, 'ТУРЦИЯ', 'ТУРЕЦКАЯ РЕСПУБЛИКА', 'TR', 'TUR', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (795, 'ТУРКМЕНИЯ', 'ТУРКМЕНИСТАН', 'TM', 'TKM', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (796, 'ТЕРКС И КАЙКОС', 'ОСТРОВА ТЕРКС И КАЙКОС (БРИТ.)', 'TC', 'TCA', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (798, 'ТУВАЛУ', 'ТУВАЛУ', 'TV', 'TUV', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (800, 'УГАНДА', 'РЕСПУБЛИКА УГАНДА', 'UG', 'UGA', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (804, 'УКРАИНА', 'УКРАИНА', 'UA', 'UKR', 2, 4, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (807, 'МАКЕДОНИЯ', 'РЕСПУБЛИКА МАКЕДОНИЯ', 'MK', 'MKD', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (818, 'ЕГИПЕТ', 'АРАБСКАЯ РЕСПУБЛИКА ЕГИПЕТ (АРЕ)', 'EG', 'EGY', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (826, 'СОЕДИНЕННОЕ КОРОЛЕВСТВО', 'СОЕДИНЕННОЕ КОРОЛЕВСТВО ВЕЛИКОБРИТАНИИ И СЕВЕРНОЙ ИРЛАНДИИ', 'GB', 'GBR', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (831, 'ГЕРНСИ', 'ГЕРНСИ', 'GG', 'GGY', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (832, 'ДЖЕРСИ', 'ДЖЕРСИ', 'JE', 'JEY', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (833, 'О-В МЭН', 'ОСТРОВ МЭН', 'IM', 'IMY', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (834, 'ТАНЗАНИЯ', 'ОБЪЕДИНЕННАЯ РЕСПУБЛИКА ТАНЗАНИЯ (ОРТ)', 'TZ', 'TZA', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (840, 'США', 'СОЕДИНЕННЫЕ ШТАТЫ АМЕРИКИ', 'US', 'USA', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (850, 'ВИРГИН. О-ВА', 'ВИРГИНСКИЕ ОСТРОВА (США)', 'VI', 'VIR', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (854, 'БУРКИНА-ФАСО', 'БУРКИНА-ФАСО', 'BF', 'BFA', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (858, 'УРУГВАЙ', 'ВОСТОЧНАЯ РЕСПУБЛИКА УРУГВАЙ', 'UY', 'URY', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (860, 'УЗБЕКИСТАН', 'РЕСПУБЛИКА УЗБЕКИСТАН', 'UZ', 'UZB', 2, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (862, 'ВЕНЕСУЭЛА', 'БОЛИВАРИЙСКАЯ РЕСПУБЛИКА ВЕНЕСУЭЛА', 'VE', 'VEN', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (876, 'УОЛЛИС И ФУТУНА', 'ОСТРОВА УОЛЛИС И ФУТУНА', 'WF', 'WLF', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (882, 'САМОА', 'НЕЗАВИСИМОЕ ГОСУДАРСТВО САМОА', 'WS', 'WSM', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (887, 'ЙЕМЕН', 'ЙЕМЕНСКАЯ РЕСПУБЛИКА', 'YE', 'YEM', 1, 1, 0);
INSERT INTO `country` (`id`, `name`, `fullname`, `alpha2`, `alpha3`, `localeId`, `currencyId`, `isDeleted`) VALUES (894, 'ЗАМБИЯ', 'РЕСПУБЛИКА ЗАМБИЯ', 'ZM', 'ZMB', 1, 1, 0);

SQL;
    }

    public function down(): string
    {
        throw new RuntimeException('Not realised');
    }
}
