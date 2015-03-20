<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('giftvoucher/template'), 'giftcard_template_id', "int(11) NOT NULL");
$installer->getConnection()->addColumn($this->getTable('giftvoucher/template'), 'giftcard_template_image', "varchar(255) NULL");
$installer->getConnection()->addColumn($this->getTable('giftvoucher'), 'timezone_to_send', "text(100) default NULL");
$installer->getConnection()->addColumn($this->getTable('giftvoucher'), 'day_store', 'datetime NULL');

$installer->endSetup();