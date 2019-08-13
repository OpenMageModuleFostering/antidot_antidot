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
class MDN_Antidot_Model_Export_Model_Product  extends Mage_Catalog_Model_Product
{

    /**
     * The export context
     * @var MDN_Antidot_Model_Export_Context $context
     */
    protected $context;

    /**
     * The stores of the current export product
     * @var array $storeIds
     */
    protected $stores;

    /**
     * The websites of the current export product
     * @var array $websites
     */
    protected $websites;

    /**
     * The id of the store to use for loading data attributes
     * @var integer $currentStoreId
     */
    protected $currentStoreId;

    /**
     * The category tree
     * @var Varien_Data_tree $categoryTree
     */
    protected $categoryTree;

    /**
     * Constructor
     *
     *
     * @param $args
     */
    public function __construct($args = array()) {

        if (count($args)>0) {
            $this->context = $args[0];
        }
        $this->stores= array();
        $this->websites= array();
        $this->currentStoreId=null;
        $this->categoryTree=null;

        //initialise model with MDN_Antidot_Model_Resource_Export_Product resource
        $this->_init('Antidot/export_product');

    }

    public function setContext($context, $forVariant = false) {
        $this->context = $context;
        return $this->initWebsitesStores($forVariant);
    }

    /**
     *
     * Define the stores of the current export where the product
     * is visible and searcheable
     *
     */
    public function initWebsitesStores($forVariant = false)
    {
        foreach ($this->context->getWebsiteAndStores() as $wstore) {

            $website = $wstore['website'];
            $store = $wstore['store'];

            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID); //USE EAV table
            $collection = Mage::getModel('catalog/product')->getCollection();
            $collection->addWebsiteFilter($website->getId());
            $collection->setStoreId($store->getId());
            if (!$forVariant) {
                $collection->addAttributeToFilter(
                    'visibility',
                    array(
                        Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH,
                        Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
                    )
                );
            }
            $collection->addAttributeToFilter('status', 1);
            $collection->addAttributeToFilter('entity_id', $this->getId());

            //Mage::log($collection->getSelect()->__toString(), null, 'antidot.log');

            if ($collection->getSize()>0) {
                $this->stores[$store->getId()] = $store;
                if (!isset($this->websites[$website->getId()])) {
                    $this->websites[$website->getId()] = $website;
                }
                //define the default store for this context :
                //take the magento default store if it is this export context
                if ($website->getIsDefault()) {
                    if ($website->getDefaultStore() &&
                        $website->getDefaultStore()->getId() == $store->getId()) {
                        $this->currentStoreId = $store->getId();
                    }
                }
            }

        }

        //if the magento default store is not in this context
        //take the default store of the first website
        if ($this->currentStoreId == null) {
            $website = current($this->websites);
            if ($website && $website->getDefaultStore()
                && in_array($website->getDefaultStore()->getId(), array_keys($this->stores))) {
                $this->currentStoreId = $website->getDefaultStore()->getId();
            }
        }

        //if default store is still not defined
        if ($this->currentStoreId == null) {
            if ($store = current($this->stores)) {
                $this->currentStoreId = $store->getId();
            }
        }

        //if the product is not active on any store of the context, don't export it
        if ($this->currentStoreId == null) {
            return false;
        }

        return true;

    }


    /**
     * The current export context
     * @return MDN_Antidot_Model_Export_Context
     */
    public function getContext() {
        return $this->context;
    }

    /**
     * The stores of the current export
     * @return array
     */
    public function getStores() {
        return $this->stores;
    }

    /**
     * get the websites of the current export
     * @return array
     */
    function getWebsites() {
        return $this->websites;
    }

    /**
     * Get the category tree
     */
    function getCategoryTree() {

        if (!$this->categoryTree) {

            /**
             * Create the Tree with the root node
             */
            $this->categoryTree = new Varien_Data_Tree();
            $rootNode = new Varien_Data_Tree_Node(
                array(
                    'entity_id' => 1,
                    'parent_id' => 0,
                    'path' => 1,
                    'position' => 0,
                    'level' => 0,
                    'path_id' => 1,
                    'name' => 'Root',
                    'is_active' => 1),
                'entity_id', $this->categoryTree, null);
            $rootNode->setLevel(0);
            $rootNode->setPathId(1);
            $this->categoryTree->addNode($rootNode, null);

            /**
             * Get the category ids linked to the product
             */
            $categoryIds = $this->getCategoryIds();

            /**
             * Run through the context category trees and extract the nodes corresponding
             * to the product categories and construct his category tree
             */
            $trees = $this->context->getCategoryTrees();
            foreach ($trees as $tree) {
                foreach ($categoryIds as $categoryId) {
                    /** @var Varien_Data_Tree_Node  $node */
                    if ($node = $tree->getNodeById($categoryId)) {

                        //Add this category to the product category tree:
                        $path = array();
                        while ($node != null) {
                            $path[] = $node;
                            $node = $node->getParent();
                        }

                        $parentNode = $rootNode;
                        foreach (array_reverse($path) as $node) {
                            if ($node->getLevel() > $parentNode->getLevel()) {
                                $productTreeNode = $this->categoryTree->getNodeById($node->getId());
                                if ($productTreeNode == null) {
                                    $parentNode = $this->categoryTree->appendChild($node->getData(), $parentNode);
                                } else {
                                    $parentNode = $productTreeNode;
                                }
                            }
                        }

                    }
                }
            }

        }

        return $this->categoryTree;
    }

    /**
     *
     */
    public function loadNeededAttributes($forStore = false) {

        $attributeIds = array();
        foreach ($this->context->getAttributesToLoad($forStore) as $attrCode) {
            if ($attribute = $this->getResource()->getAttribute($attrCode)) {
                $attributeIds[] = $attribute->getId();
            }
        }
        $this->getResource()->loadModelAttributes($this, $attributeIds);

    }

    protected function _beforeLoad($id, $field = null)
    {
        //don't dispatch before load events
        return $this;
    }

    /**
     * Load object data
     *
     * @param   integer $id
     * @return  Mage_Core_Model_Abstract
     */
    public function load($id, $field=null)
    {
        $this->_beforeLoad($id, $field);
        $this->_getResource()->load($this, $id, $this->context->getAttributesToLoad(false));
        $this->_afterLoad();
        //don't set origin data (reduce memory consumtion)
        //$this->setOrigData();
        $this->_hasDataChanges = false;
        return $this;
    }

    protected function _afterLoad()
    {
        //Don't dispatch after load events
        return $this;
    }

    public function afterLoad()
    {
        // don't call after load
        //$this->getResource()->afterLoad($this);
        $this->_afterLoad();
        return $this;
    }

    /**
     * Retrieve link instance
     *
     * @return  Mage_Catalog_Model_Product_Link
     */
    public function getLinkInstance()
    {
        if (!$this->_linkInstance) {
            $this->_linkInstance = Mage::getSingleton('Antidot/export_model_product_link');
        }
        return $this->_linkInstance;
    }

    /**
     * Retrieve type instance
     *
     * Type instance implement type depended logic
     *
     * @param  bool $singleton
     * @return Mage_Catalog_Model_Product_Type_Abstract
     */
    public function getTypeInstance($singleton = false)
    {
        //configure the type model in config.xml to let developper implement their own product types
        if ($model = (string) Mage::getConfig()->getNode('default/antidot/export/product_type_'.$this->getTypeID())) {
            return Mage::getSingleton($model);
        }
        return parent::getTypeInstance($singleton = false);
    }

    /**
     * Return qty
     *
     * qty is loaded by product collection join with inventory table
     *
     * @return int
     */
    public function getQty() {
        if (!$this->hasData('qty')) {
            if ($this->hasData('stock_item')) {
                $this->setData('stock_item', Mage::getModel('cataloginventory/stock_item')->loadByProduct($this));
            }
            $this->setData('qty', $this->getStockItem()->getQty());
        }
        return $this->getData('qty');
    }

    /**
     * Return is_in_stock status
     *
     * is_in_stock is loaded by product collection join with inventory table
     * see  MDN_Antidot_Model_Export_Model_Product_Link getProductCollection()
     *
     * @return int
     */
    public function getIsInStock() {
        /**
         * MCNX-264 if config !cataloginventory/item_options/manage_stock : meens all product are available
         */
        if (!Mage::getStoreConfig('cataloginventory/item_options/manage_stock')) {
            return true;
        }
        if (!$this->hasData('is_in_stock')) {
            if ($this->hasData('stock_item')) {
                $this->setData('stock_item', Mage::getModel('cataloginventory/stock_item')->loadByProduct($this));
            }
            $this->setData('is_in_stock', $this->getStockItem()->getIsInStock());
        }
        return $this->getData('is_in_stock');
    }

    /**
     * Retrieve Product URL
     *
     * @param  bool $useSid
     * @return string
     */
    public function getProductUrl($useSid = null)
    {
        Mage::app()->setCurrentStore($this->getStoreId()); //to avoid ?___store param
        if (method_exists($this, 'getUrlModel')) { //compatibility with older magento version where category#getUrlModel doesn't exist
            $this->getUrlModel()->getUrlInstance()->setStore($this->getStoreId());
        } else {
            $this->getUrlInstance()->setStore($this->getStoreId());
        }
        $url = parent::getProductUrl(false);
        $this->unsetData('url'); //unset data in order to force re-generation of product url on next store
        return $url;
    }

    /**
     * release memory
     */
    public function clearInstanceFull() {
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $this->clearInstance();
        $this->stores= array();
        $this->websites= array();
        $this->currentStoreId=null;
        $this->categoryTree=null;
    }

    /**
     * Retrieve Store Id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->currentStoreId;
    }

    /**
     * set Store Id
     *
     * @param int
     */
    public function setStoreId($storeId)
    {
        parent::setStoreId($storeId);
        $this->currentStoreId = $storeId;
        return $this;
    }

    /**
     * Get attribute text by its code
     *
     * @param $attributeCode Code of the attribute
     * @return string
     */
    public function getAttributeText($attributeCode)
    {
        if ($attribute = $this->getResource()->getAttribute($attributeCode)) {
            return $attribute
                ->getSource()
                ->getOptionText($this->getData($attributeCode));
        } else {
            return '';
        }
    }
}
