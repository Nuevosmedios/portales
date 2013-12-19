-- <?php die('Restricted access');# ?>
#
# @file       install.mysql.php
# @version    1.0.0
# @author     Edwin CHERONT         (info@jms2win.com)
#             Edwin2Win sprlu       (www.jms2win.com)
# @copyright  (C) 2010 Edwin2Win sprlu - all right reserved.
#
#;
#
# -------------- multisites_users ------------------------
#
#;
CREATE TABLE IF NOT EXISTS `#__multisites_users` (
   `id`                    int(11)           NOT NULL AUTO_INCREMENT,
   `user_id`               int(10) unsigned  NOT NULL                                  COMMENT 'reference to the joomla user id',
   `site_id`               varchar(100)      NOT NULL                                  COMMENT 'reference to the MULTISITES_ID that created the record',
   `home`                  tinyint(1)        NOT NULL default '0'                      COMMENT 'Default or home websites where the user were registered',
   `params`                text              NULL                                      COMMENT 'Additional parameters.',

   `checked_out`           tinyint(1)        NOT NULL default '0',
   `checked_out_time`      datetime          NOT NULL DEFAULT '0000-00-00 00:00:00',
   `created_dt`            datetime          NOT NULL DEFAULT '0000-00-00 00:00:00'    COMMENT 'Creation date/time',
   `created_by`            int(10) unsigned  NULL                                      COMMENT 'reference to the user id who created the record',
   `modified_dt`           datetime          NOT NULL DEFAULT '0000-00-00 00:00:00'    COMMENT 'Date/Time of the modification',
   `modified_by`           int(10) unsigned  NULL                                      COMMENT 'reference to the user id who modified the record',
   
   PRIMARY KEY (`id`),
   KEY     `idx_usersite`  (`user_id`, `site_id`)
   KEY     `idx_user`      (`user_id`),
   KEY     `idx_site`      (`site_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1;
