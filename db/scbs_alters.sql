/**
* deverajosephpacelo
* DB NAME : scbs
* MODULE  : customer_relationships
*/
CREATE TABLE `config_relationship_types` (
  `relationship_type_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique identifier',
  `relationship_type` varchar(50) NOT NULL COMMENT 'Name of relationship type',
  `active_flag` enum('Y','N') NOT NULL COMMENT 'Tagging if the record is Active (Y) or Inactive (N)',
  PRIMARY KEY (`relationship_type_id`),
  UNIQUE KEY `relationship_type_UNIQUE` (`relationship_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `scbs`.`config_relationship_types` (`relationship_type_id`, `relationship_type_name`, `active_flag`) VALUES ('1', 'Mother', 'Y');
INSERT INTO `scbs`.`config_relationship_types` (`relationship_type_id`, `relationship_type_name`, `active_flag`) VALUES ('2', 'Father', 'Y');
INSERT INTO `scbs`.`config_relationship_types` (`relationship_type_id`, `relationship_type_name`, `active_flag`) VALUES ('3', 'Sibling', 'Y');
INSERT INTO `scbs`.`config_relationship_types` (`relationship_type_id`, `relationship_type_name`, `active_flag`) VALUES ('4', 'Son', 'Y');
INSERT INTO `scbs`.`config_relationship_types` (`relationship_type_id`, `relationship_type_name`, `active_flag`) VALUES ('5', 'Daughter', 'Y');
INSERT INTO `scbs`.`config_relationship_types` (`relationship_type_id`, `relationship_type_name`, `active_flag`) VALUES ('6', 'Wife', 'Y');
INSERT INTO `scbs`.`config_relationship_types` (`relationship_type_id`, `relationship_type_name`, `active_flag`) VALUES ('7', 'Husband', 'Y');

CREATE TABLE scbs.`customer_relationships` (
  `customer_id` INT(10) unsigned NOT NULL COMMENT 'ID of customer.',
  `relationship_type_id` tinyint(3) unsigned NOT NULL COMMENT 'List of relationship types.',
  PRIMARY KEY (`customer_id`,`relationship_type_id`),
  KEY `fk_customer_id_idx` (`customer_id`),
  KEY `fk_relationship_type_id_idx` (`relationship_type_id`),
  CONSTRAINT `fk_customer_relationships_customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_customer_relationships_relationhip_type_id` FOREIGN KEY (`relationship_type_id`) REFERENCES `config_relationship_types` (`relationship_type_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;