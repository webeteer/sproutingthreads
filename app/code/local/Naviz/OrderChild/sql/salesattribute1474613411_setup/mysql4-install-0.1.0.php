<?php
$installer = $this;
$installer->startSetup();

$installer->addAttribute("order", "child", array("type"=>"varchar"));
$installer->addAttribute("quote", "child", array("type"=>"varchar"));
$installer->endSetup();
	 