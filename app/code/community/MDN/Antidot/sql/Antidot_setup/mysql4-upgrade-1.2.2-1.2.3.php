<?php

$installer = $this;

$installer->startSetup();

/**
 * Alter table 'antidot/export'
 */
$installer->run("
ALTER TABLE `antidot_export` CHANGE COLUMN `element` `element` ENUM('CATALOG', 'CATEGORY', 'STORE', 'ARTICLE') NOT NULL ;
");

/**
 * Upgrade core_config_data vars fro suggest config
 * replace olds vars by the new antidot/suggest/feeds one
 */

$storeIds = array(Mage_Core_Model_App::ADMIN_STORE_ID);
$storeCollection = Mage::getModel('core/store')->getCollection();
foreach ($storeCollection as $store) {
    $storeIds[] = $store->getId();
}
foreach ($storeIds as $storeId) {

    /**
     * Get the old config var directly in database because, cache has been flushed and is not yet reloaded when upgrade modules is precessed
     *
     * First : get ADMIN scope value
     * Second : override with store scope value
     */
    $coll = Mage::getResourceModel('core/config_data_collection')
        ->addScopeFilter('default', Mage_Core_Model_App::ADMIN_STORE_ID, 'antidot/suggest');

    foreach ($coll as $config) {
        switch ($config->getPath()) {
            case 'antidot/suggest/products':
                $products = $config->getValue();
                break;
            case 'antidot/suggest/categories':
                $categories = $config->getValue();
                break;
            case 'antidot/suggest/brands':
                $brands = $config->getValue();
                break;
            case 'antidot/suggest/products_displayed':
                $products_displayed = $config->getValue();
                break;
            case 'antidot/suggest/categories_displayed':
                $categories_displayed = $config->getValue();
                break;
            case 'antidot/suggest/brands_displayed':
                $brands_displayed = $config->getValue();
                break;
            case 'antidot/suggest/order1':
                $order1 = $config->getValue();
                break;
            case 'antidot/suggest/order2':
                $order2 = $config->getValue();
                break;
            case 'antidot/suggest/order3':
                $order3 = $config->getValue();
                break;
        }
    }

    $coll = Mage::getResourceModel('core/config_data_collection')
        ->addScopeFilter($storeId==Mage_Core_Model_App::ADMIN_STORE_ID?'default':'stores', $storeId, 'antidot/suggest');

    foreach ($coll as $config) {
        switch ($config->getPath()) {
            case 'antidot/suggest/products':
                $products = $config->getValue();
                break;
            case 'antidot/suggest/categories':
                $categories = $config->getValue();
                break;
            case 'antidot/suggest/brands':
                $brands = $config->getValue();
                break;
            case 'antidot/suggest/products_displayed':
                $products_displayed = $config->getValue();
                break;
            case 'antidot/suggest/categories_displayed':
                $categories_displayed = $config->getValue();
                break;
            case 'antidot/suggest/brands_displayed':
                $brands_displayed = $config->getValue();
                break;
            case 'antidot/suggest/order1':
                $order1 = $config->getValue();
                break;
            case 'antidot/suggest/order2':
                $order2 = $config->getValue();
                break;
            case 'antidot/suggest/order3':
                $order3 = $config->getValue();
                break;
        }
    }

    if (!isset($order1)) { //In case of installation not upgrade, default values :
        $order1='categories';
        $categories=1;
        $categories_displayed='10';
    }
    if (!isset($order2)) { //In case of installation not upgrade, default values :
        $order2='products';
        $products=1;
        $products_displayed='10';
    }
    if (!isset($order3)) { //In case of installation not upgrade, default values :
        $order3='brands';
        $brands=1;
        $brands_displayed='10';
    }

    /**
     * construct the antidot/suggest/feeds array
     */
    $categoriesArray = array('feed' => 'categories');
    if ($categories) {
        $categoriesArray['active'] = 'on';
    }
    $categoriesArray['suggest_number'] = $categories_displayed;
    $productsArray = array('feed' => 'products');
    if ($products) {
        $productsArray['active'] = 'on';
    }
    $productsArray['suggest_number'] = $products_displayed;
    $brandsArray = array('feed' => 'brands');
    if ($brands) {
        $brandsArray['active'] = 'on';
    }
    $brandsArray['suggest_number'] = $brands_displayed;
    $articlesArray = array('feed' => 'articles', 'suggest_number' => '10');
    $storesArray = array('feed' => 'stores', 'suggest_number' => '10');

    $finalArray = array();
    foreach (array($order1, $order2, $order3) as $order) {
        switch ($order) {
            case 'categories':
                $finalArray[] = $categoriesArray;
                break;
            case 'products':
                $finalArray[] = $productsArray;
                break;
            case 'brands':
                $finalArray[] = $brandsArray;
                break;
        }
    }
    $finalArray[] = $articlesArray;
    $finalArray[] = $storesArray;
    $final = serialize($finalArray);

    if ($storeId == Mage_Core_Model_App::ADMIN_STORE_ID) {
        $finalAdmin = $final;
        // Save on admin scope
        Mage::getConfig()->saveConfig('antidot/suggest/feeds', $finalAdmin, 'default', Mage_Core_Model_App::ADMIN_STORE_ID);
    } else {
        if ($final != $finalAdmin) {
            // Save on scopes that are different from admin
            Mage::getConfig()->saveConfig('antidot/suggest/feeds', $final, 'stores', $storeId);
        }
    }
}

$installer->endSetup();