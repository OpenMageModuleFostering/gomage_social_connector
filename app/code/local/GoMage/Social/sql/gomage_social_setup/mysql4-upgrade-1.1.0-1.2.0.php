<?php
$installer = $this;
$installer->startSetup();

$sql = <<<SQLTEXT
DELETE FROM `{$this->getTable('core_config_data')}` WHERE `path` = "gomage_social/information/text"
SQLTEXT;

$installer->run($sql);

$installer->endSetup();
	 