<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2015 Antidot (http://www.antidot.net)
 * @author : Antidot devmagento@antidot.net
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_Antidot_Model_Export_Context  extends Mage_Core_Model_Abstract
{
    /**
     * @var string $runType
     */
    protected $runType;
    /**
     * @var string $lang
     */
    protected $lang;
    /**
     * Array of the websites, key=websiteId
     *
     * @var array
     */
    protected $websites = array();
    /**
     * MultiDimentional Array of the stores, key=websiteId, storeId
     * @var array
     */
    protected $stores = array();
    /**
     * Array of store ids
     * @var array
     */
    protected $storeIds = array();
    /**
     * Array of categoryTrees
     *
     * @var array
     */
    protected $categoryTrees = array();
    /**
     * Array of rootCategoryIds
     *
     * @var array
     */
    protected $rootCategoryIds = array();

    /**
     * Array of attributes to load in products
     *
     * @var array $attributesToLoad
     */
    protected $attributesToLoad = array();

    /**
     * @param $runType
     * @param $lang
     */
    function __construct($args) {
        $this->lang = $args[0];
        $this->runType = $args[1];
    }

    function getLang() {
        return $this->lang;
    }

    function getRunType() {
        return $this->runType;
    }

    /**
     *  Add a store to the current Export context
     *
     * @param $store Mage_Core_Model_Store
     */
    function addStore($store) {
        if ($store->getIsActive()) {
            $website = $store->getWebsite();
            if (!isset($this->websites[$website->getId()])) {
                $this->websites[$website->getId()] = $website;
            }
            if (!isset($this->stores[$website->getId()][$store->getId()])) {
                //construct lists of stores grouped by websiteid
                $this->stores[$website->getId()][$store->getId()] = $store;
                //construct list of all storeids
                $this->storeIds[] = $store->getId();
                //construct list of rootcategoryIds
                if (!isset($this->rootCategoryIds[$store->getRootCategoryId()])) {
                    $this->rootCategoryIds[$store->getRootCategoryId()] = array();
                }
                $this->rootCategoryIds[$store->getRootCategoryId()][] = $store;
            }
        }

    }

    /**
     *  Load and init the category tree corresponding to the stores
     *  of the current export context
     */
    function initCategoryTree() {

        Varien_Profiler::start("export_product_initCategoryTree");

        foreach ($this->rootCategoryIds as $rootId => $stores) {
            /**
             * define the store tu used to load the category tree :
             * use default store first
             */
            $defaultStoreId = null;
            //first take the default store of the default website
            foreach ($stores as $store) {
                $website = $store->getWebsite();
                if ($website->getIsDefault() && $website->getDefautStore() && $website->getDefautStore()->getId() == $store->getId()) {
                    $defaultStoreId = $store->getId();
                }
            }
            //if not found take the first store of the default website
            if ($defaultStoreId == null) {
                foreach ($stores as $store) {
                    $website = $store->getWebsite();
                    if ($website->getIsDefault() && $defaultStoreId == null) {
                        $defaultStoreId = $store->getId();
                    }
                }
            }
            //if not found take the first store of the first website
            if ($defaultStoreId == null) {
                $store = current($stores);
                $defaultStoreId = $store->getId();
            }

            //LOAD TREE with $rootId and $defaultStoreId
            $tree = Mage::getResourceModel('catalog/category_tree')
                ->load($rootId);

            $collection = Mage::getModel('catalog/category')->getCollection();
            /** @var $collection Mage_Catalog_Model_Resource_Category_Collection */

            //Set Store Id
            $collection->setStoreId($defaultStoreId);

            //add attributes to display
            $collection->addAttributeToSelect(array('name', 'image'));

            //exclude categories not actives and without name
            $collection->addAttributeToFilter('name', array('neq' => '')); //Exclude empty name categories
            $collection->addAttributeToFilter('is_active', 1);

            //filter on the tree categories
            $nodeIds = array();
            foreach ($tree->getNodes() as $node) {
                $nodeIds[] = $node->getId();
            }
            $collection->addIdFilter($nodeIds);

            //join url-rewrite table
            if (class_exists ('Mage_Catalog_Model_Factory', false)) {
                Mage::getSingleton('catalog/factory')->getCategoryUrlRewriteHelper()
                    ->joinTableToEavCollection($collection, $defaultStoreId);
            } else {
                /**
                 * Join url rewrite table to eav collection
                 *
                 * @param Mage_Eav_Model_Entity_Collection_Abstract $collection
                 * @param int $storeId
                 * @return Mage_Catalog_Helper_Category_Url_Rewrite
                 */

                $collection->joinTable(
                    'core/url_rewrite',
                    'category_id=entity_id',
                    array('request_path'),
                    "{{table}}.is_system=1 AND " .
                    "{{table}}.store_id='{$defaultStoreId}' AND " .
                    "{{table}}.id_path LIKE 'category/%'",
                    'left'
                );

            }

            //Add collection data to the tre nodes see Mage_Catalog_Model_Resource_Category_Tree#addCollectionData
            foreach ($collection as $category) {
                if ($node = $tree->getNodeById($category->getId())) {

                    /* Calculate the url to export */
                    if (method_exists($category, 'getUrlModel')) { //compatibility with older magento version where category#getUrlModel doesn't exist
                        $category->getUrlModel()->getUrlInstance()->setStore($defaultStoreId);
                    } else {
                        $category->getUrlInstance()->setStore($defaultStoreId);
                    }
                    $category->setData('url', $category->getUrl());

                    $node->addData($category->getData());
                }
            }

            foreach ($tree->getNodes() as $node) {
                //if the node is not in the collection (not active), remove it and all his descendant from the tree (except if it's the root node)
                if ($collection->getItemById($node->getId()) == null && $node->getLevel() > 1) {
                    $this->removeBranch($tree, $node);
                }
            }

            //Mage::log("LOAD TREE ".$rootId. " ". $defaultStoreId . ' '. spl_object_hash($tree), null, 'antidot.log');
            //Mage::log($collection->getSelect()->__toString() , null, 'antidot.log');
            //$i=0;
            //foreach ($tree->getNodes() as $node) {
            //    $i++;
            //}
            //Mage::log('Tree nodes '.$i , null, 'antidot.log');

            $this->categoryTrees[] = $tree;

        }

        Varien_Profiler::stop("export_product_initCategoryTree");

    }

    /**
     * recursive call of removeNode on tree
     */
    private function removeBranch($tree, $node) {
        foreach ($node->getChildren() as $child) {
            $this->removeBranch($tree, $child);
        }
        $tree->removeNode($node);
    }


    /**
     *  Get the category trees
     *
     * @return array
     */
    function getCategoryTrees() {
        return $this->categoryTrees;
    }

    /**
     * Get the list of websites
     * @return array
     */
    function getWebsites() {
        return $this->websites;
    }

    /**
     *  Get the list of the pair website/store of the current context
     *
     * @return array
     */
    function getWebsiteAndStores() {
        $list = array();
        foreach($this->stores as $websiteId => $stores) {
            $website = $this->websites[$websiteId];
            foreach ($stores as $store) {
                $list[] = array('website' => $website, 'store' => $store);
            }
        }
        return $list;
    }

    /**
     *  Get the website corresponding to the storeId
     *
     * @return Mage_Core_Model_Website
     */
    function getWebSiteByStore($storeId) {
        foreach($this->stores as $websiteId => $stores) {
            foreach($stores as $store) {
                if ($store->getId() == $storeId) {
                    return $this->websites[$websiteId];
                }
            }
        }
        return null;
    }

    /**
     *  Get the list of website ids of the current context
     *
     * @return array
     */
    function getWebsiteIds() {
        return array_keys($this->websites);
    }

    /**
     *  Get the list of store ids of the current context
     *
     * @return array
     */
    function getStoreIds() {
        return $this->storeIds;
    }

    /**
     *
     */
    function addAttributeToLoad($fields) {

        //Theses attributes are configured in BO System > config > AfsStore :
        $this->attributesToLoad = array();
        foreach ($fields as $afsCode => $attributeCode) {
            if ($afsCode == 'in_stock_only') {
                continue; //not an attribute
            }

            if (is_array($attributeCode)) { //description, identifier, properties
                foreach ($attributeCode as $code) {
                    if ($afsCode == 'properties') {
                        $this->attributesToLoad[] = array('code' => $code['value'], 'on_store' => false);
                    } else {
                        $this->attributesToLoad[] = array('code' => $code, 'on_store' => false);
                    }
                }
            } else {

                if ($attributeCode) {
                    //Theses attributes must be reloaded on each stores
                    if (in_array($afsCode, array('is_new', 'is_best_sale', 'is_featured'))) {
                        $this->attributesToLoad[] = array('code' => $attributeCode, 'on_store' => true);
                    } else {
                        $this->attributesToLoad[] = array('code' => $attributeCode, 'on_store' => false);
                    }
                }
            }
        }

        //Theses attributes must be reloaded on each stores
        $this->attributesToLoad[] = array('code' => 'image', 'on_store' => true);
        $this->attributesToLoad[] = array('code' => 'thumbnail', 'on_store' => true);

    }

    function getAttributesToLoad($forStore = false) {

        $attributes = array();
        foreach ($this->attributesToLoad as $attribute) {
            if ($attribute['on_store'] || !$forStore) {
                if ($attribute['code'] != 'sku') { //sku already loaded on catalog_product_entity
                    $attributes[] = $attribute['code'];
                }
            }
        }
        return $attributes;

    }

}
