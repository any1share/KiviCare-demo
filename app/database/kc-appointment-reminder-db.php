<?php

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

global $wpdb;

$charset_collate = $wpdb->get_charset_collate();

$table_name = $wpdb->prefix . 'kc_appointment_reminder_mapping'; // do not forget about tables prefix

$sql = "CREATE TABLE `{$table_name}` (
    id bigint(20) NOT NULL AUTO_INCREMENT,    
    appointment_id bigint(20) default 0,
    msg_send_date date NULL, 
    email_status int(1) default 0,
    sms_status int(1) default 0,
    whatsapp_status int(1) default 0,
    extra longtext NULL,
    PRIMARY KEY  (id)
  ) $charset_collate;";

maybe_create_table($table_name,$sql);
