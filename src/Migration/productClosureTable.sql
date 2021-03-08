DROP TRIGGER IF EXISTS `t_addProductCategory`;
DROP TRIGGER IF EXISTS `t_moveProductCategory`;
DROP PROCEDURE IF EXISTS `sp_getProductCategoryTree`;
DROP PROCEDURE IF EXISTS `sp_addProductCategory`;
DROP PROCEDURE IF EXISTS `sp_hideProductCategory`;
DROP PROCEDURE IF EXISTS `sp_moveProductCategory`;

DELIMITER $$

CREATE
    TRIGGER `t_addProductCategory` AFTER INSERT ON `productCategory`
    FOR EACH ROW BEGIN
    CALL sp_addProductCategory(NEW.`id`, NEW.`parentId`);
END;
$$

DELIMITER ;


DELIMITER $$


CREATE TRIGGER `t_moveProductCategory` AFTER UPDATE ON `productCategory`
  FOR EACH ROW BEGIN
  IF OLD.`parentId` != NEW.`parentId` THEN
    CALL sp_moveProductCategory(NEW.`id`, NEW.`parentId`);
  END IF;
END;
$$

DELIMITER ;


DELIMITER $$

CREATE PROCEDURE `sp_getProductCategoryTree`(
    node_id INT UNSIGNED
) COMMENT 'Query all descendants nodes by a node id, return as a result set'
BEGIN
  SELECT
    node.`id` AS t1_id,
    node.`parentId` AS t1_parentId,
    node.`name` AS t1_name,
    node.`code` AS t1_code,
    COUNT(DISTINCT p.id) AS t1_elementsNum,
    node.`isDeleted` AS t1_isDeleted,
    node.`createdTs` AS t1_createdTs,
    CONCAT(
        REPEAT('-', path.`path_length`),
        node.`name`
    ) AS name,
    path.`path_length`,
    GROUP_CONCAT(
        DISTINCT crumbs.`ancestor_id` SEPARATOR ','
    ) AS breadcrumbs
  FROM
    `productCategory` AS node
    JOIN `productCategoryPath` AS path
      ON node.`id` = path.`descendant_id`
    JOIN `productCategoryPath` AS crumbs
      ON crumbs.`descendant_id` = path.`descendant_id`
    LEFT JOIN `product` p
      ON p.productCategoryId = `node`.id
  WHERE path.`ancestor_id` = `node_id`
    AND node.`isDeleted` = 0
  GROUP BY node.`id`, node.`parentId`, node.`name`, node.`code`, node.`isDeleted`, node.`createdTs`, path.`path_length`
  ORDER BY breadcrumbs;
END$$

DELIMITER ;


DELIMITER $$

CREATE PROCEDURE `sp_addProductCategory`(
  param_node_new_id    INT UNSIGNED,
  param_node_parentId INT UNSIGNED
)
COMMENT 'Adding new paths productCategoryPath table'
BEGIN
  -- Update paths information
  INSERT INTO `productCategoryPath` (
    `ancestor_id`,
    `descendant_id`,
    `path_length`
  )
  SELECT
    `ancestor_id`,
    `param_node_new_id`,
    `path_length` + 1
  FROM
    `productCategoryPath`
  WHERE `descendant_id` = `param_node_parentId`
  UNION
  ALL
  SELECT
    `param_node_new_id`,
    `param_node_new_id`,
    0;
END$$

DELIMITER ;


DELIMITER $$

CREATE PROCEDURE `sp_hideProductCategory` (
  `node_id` INT UNSIGNED,
  `deleted` INT UNSIGNED
) COMMENT 'Delete a node and its descendant nodes(update isDeleted = 1)'
BEGIN
  UPDATE
    `productCategory` AS d
    JOIN `productCategoryPath` AS p
      ON d.`id` = p.`descendant_id`
    JOIN `productCategoryPath` AS crumbs
      ON crumbs.`descendant_id` = p.`descendant_id` SET d.`isDeleted` = deleted
  WHERE p.`ancestor_id` = node_id;
END $$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE `sp_moveProductCategory` (
    `node_old_parentId` INT UNSIGNED,
    `node_new_parentId` INT UNSIGNED
) COMMENT 'Update paths when parentId column changed'
BEGIN
  DELETE
    a
  FROM
    `productCategoryPath` AS a
    JOIN `productCategoryPath` AS d
        ON a.`descendant_id` = d.`descendant_id`
    LEFT JOIN `productCategoryPath` AS x
        ON x.`ancestor_id` = d.`ancestor_id`
        AND x.`descendant_id` = a.`ancestor_id`
  WHERE d.`ancestor_id` = `node_old_parentId`
    AND x.`ancestor_id` IS NULL ;

  INSERT `productCategoryPath` (
    `ancestor_id`,
    `descendant_id`,
    `path_length`
  )
  SELECT
    supertree.`ancestor_id`,
    subtree.`descendant_id`,
    supertree.`path_length` + subtree.`path_length` + 1
  FROM
    `productCategoryPath` AS supertree
    JOIN `productCategoryPath` AS subtree
  WHERE subtree.`ancestor_id` = `node_old_parentId`
    AND supertree.`descendant_id` = `node_new_parentId`;
END$$

DELIMITER ;

INSERT INTO `productCategory` VALUES(1, NULL, 'ROOT', '', 0, 0, DEFAULT);
