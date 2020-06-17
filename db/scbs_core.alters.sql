/**
* deverajosephpacelo
* DB NAME : scbs_core
* MODULE  : SITE SETTINGS
*/
UPDATE `scbs_core`.`site_settings` SET `setting_value` = 'A web-based system developed by Stratos Core' WHERE (`setting_type` = 'GENERAL') and (`setting_name` = 'system_description');
UPDATE `scbs_core`.`site_settings` SET `setting_value` = 'The Stratos Core' WHERE (`setting_type` = 'GENERAL') and (`setting_name` = 'system_tagline');
UPDATE `scbs_core`.`site_settings` SET `setting_value` = 'blue' WHERE (`setting_type` = 'THEME') and (`setting_name` = 'skins');
UPDATE `scbs_core`.`site_settings` SET `setting_value` = 'blue' WHERE (`setting_type` = 'LAYOUT') and (`setting_name` = 'sidebar_menu');
