<?php

$installer = $this;

$installer->startSetup();

/**
 * have to load config in setup because it is not yet done by magento
 */
$config = Mage::getConfig();
$dbConfig = Mage::getResourceModel('core/config');
$dbConfig->loadToXml($config);

$ftpConfig = Mage::getStoreConfig('antidot/ftp');
/**
 * if ftp config is done, it means we upgrade an installed module,
 * then keep ftp upload_type
 */
if ($ftpConfig && is_array($ftpConfig) && count($ftpConfig)>1) {
  $config = Mage::getSingleton('core/config');
  $config->saveConfig('antidot/ftp/upload_type', MDN_Antidot_Model_Transport::TRANS_FTP, 'default', 0);
}

$installer->endSetup();