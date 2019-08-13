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
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_Antidot_Model_Export_Product extends MDN_Antidot_Model_Export_Abstract 
{
    
    const TYPE = 'CATALOG';
    const FILENAME_XML = 'catalog-mdn_%s-%s.xml';
    const FILENAME_ZIP = '%s_full_mdn_catalog.zip';
    const FILENAME_ZIP_INC = '%s_inc_mdn_catalog.zip';
    const XSD   = 'http://ref.antidot.net/store/latest/catalog.xsd';
    
    const PRODUCT_LIMIT  = 1000;
    
    protected $file;
    
    protected $productGenerated = array();

    protected $categories = array();

    protected $enabledStores = null;

    protected $onlyProductsWithStock;

    protected $autoCompleteProducts;

    protected $propertyLabel = array();

    protected $productsWithOperation = null;
    
    protected $productVisible = array(
        Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH,
        Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
    );

    protected $productMultiple = array(
        Mage_Catalog_Model_Product_Type::TYPE_BUNDLE,
        Mage_Catalog_Model_Product_Type::TYPE_GROUPED,
        Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
    );

    /**
     * Write the xml file
     * 
     * @param array $context
     * @param string $filename
     * @param string $type Incremantal or full
     * @return int nb items generated
     */
    public function writeXml($context, $filename, $type) 
    {
        Mage::log('Starting product XML export, filename = '.$filename, null, 'antidot.log');

        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $db->getProfiler()->setEnabled(false);

        $this->onlyProductsWithStock = !(boolean)Mage::getStoreConfig('antidot/fields_product/in_stock_only');
        $this->autoCompleteProducts  = Mage::getStoreConfig('antidot/suggest/enable') === 'Antidot/engine_antidot' ? 'on' : 'off';

        $productIds = $this->getProductIds($context['store_id'], $context['website_ids'],  $type);
        
        Mage::log('Products to export : '.count($productIds), null, 'antidot.log');
		if (count($productIds) > 0) {
        
	        $this->initXml();
	        $this->initPropertyLabel();
	        $this->initProductsWithOperations();
	        $this->initFields('product');
	        $this->initEnabledStores();
	        $this->setFilename($filename);
	        
	        $this->xml->push('catalog', array('xmlns' => "http://ref.antidot.net/store/afs#"));
	        $this->writeHeader($context);
	        $this->writePart($this->xml->flush());
	        
	        $this->lang = $context['lang'];
	
	        $chunkId = 1;
	        $chunkCount = (int)(count($productIds) / 500);
	        $lastExecutionTime = time();
	        foreach(array_chunk($productIds, 500) as $productId) {
	
	            Mage::log('Process chunk # '.$chunkId .' / '.$chunkCount. ' - memory usage = '.memory_get_usage().' - last took '.(time() - $lastExecutionTime).' sec', null, 'antidot.log');
	            $lastExecutionTime = time();
	            //force current store to admin to prevent the use of the flat catalog
	            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
	
	            $collection = Mage::getModel('catalog/product')
	                ->getCollection()
	                ->addAttributeToSelect('*')
	                ->addAttributeToFilter('entity_id', array('in', $productId))
	                ->joinField('qty',
	                            'cataloginventory/stock_item',
	                            'qty',
	                            'product_id = entity_id',
	                            '{{table}}.stock_id = 1')
	            ;
	
	            foreach($collection as $product) {
	                //if($context['langs'] > 1) {
	                    $store = current($this->getProductStores($product, $context)); //we take the "first" store of the current lang
	                    if ($store)
	                        $product = Mage::getModel('catalog/product')->setStoreId($store->getId())->load($product->getId());
	                //}
	                $this->writeProduct($product, $context);
	
	                $product->clearInstance();  //memory flush
	            }
	            $this->writePart($this->xml->flush());
	
	            $chunkId++;
	        }
	        $this->xml->pop();
	        $this->writePart($this->xml->flush(), true);
		}

        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID); //in order to stay on the admin and not be redirected to the last indexed frontend store
        Mage::log('Products parsing complete', null, 'antidot.log');

        return count($productIds);
    }

    /**
     * List only enabled stores
     */
    protected function initEnabledStores()
    {
        $stores = Mage::getModel('core/store')->getCollection();
        foreach($stores as $store)
        {
            if ($store->getis_active())
                $this->enabledStores[] = $store->getId();
        }
    }

    /**
     * Init properties label
     */
    protected function initPropertyLabel()
    {
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection');
        foreach($attributes as $att) {
            $k = $att->getAttributeCode();
            $this->propertyLabel[$k] = array();
            $this->propertyLabel[$k]['default'] = $att->getfrontend_label();
            $this->propertyLabel[$k]['per_store'] = $att->getStoreLabels();

            $this->propertyLabel[$k]['options'] = array();
            $options = $att->getSource()->getAllOptions(true);
            foreach($options as $option) {
                if (empty($option['value']) || is_array($option['value'])) {
                    continue;
                }

                $this->propertyLabel[$k]['options'][$option['value']] = array();
                $this->propertyLabel[$k]['options'][$option['value']]['per_store'] = array();
                $query = 'SELECT store_id, value FROM '
                    . Mage::getConfig()->getTablePrefix().'eav_attribute_option_value '
                    . 'WHERE option_id = "'.$option['value'].'"';

                $valuesCollection = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchAll($query);
                foreach($valuesCollection as $item) {
                    $this->propertyLabel[$k]['options'][$option['value']]['per_store'][$item['store_id']] = $item['value'];
                }
            }
        }
    }
    
    /**
     * Write the xml header
     * 
     */
    protected function writeHeader($context)
    {
        $this->xml->push('header');
        $this->xml->element('owner', $context['owner']);
        $this->xml->element('feed', 'product');
        $this->xml->element('generated_at', date('c', Mage::getModel('core/date')->timestamp(time())));
        $this->xml->pop();
    }
    
    /**
     * Write the product
     * 
     * @param Product $product
     * @param Array $context
     */
    protected function writeProduct($product, $context)
    {
        $stores = $this->getProductStores($product, $context);
        
        //skip product if no websites
        if (count($stores) == 0)
            return;

        $this->xml->push('product', array('id' => $product->getId(), 'xml:lang' => $this->lang, 'autocomplete' => $this->autoCompleteProducts));

        $this->xml->push('websites');
        $websites = array();
        $rootCategoriesIds = array(); //the root categories of the stores of this context, used for exporting only the categories within theses roots
        foreach($stores as $store) {
        	$rootCategoriesIds[] = $store->getRootCategoryId();
            $website = $this->getWebSiteByStore($store);
            if (!array_key_exists($website->getId(), $websites))
            {
                $websites[$website->getId()] = $website->getName();
                $this->xml->element('website', $website->getName(), array('id' => $website->getId()));
            }
        }
        $this->xml->pop();

        //$this->xml->element('created_at', $product->getCreated_at());     AFM-83
        //$this->xml->element('last_updated_at', $product->getUpdated_at());    AFM-92

        $this->xml->element('name', $this->xml->encloseCData($this->utf8CharacterValidation($this->getField($product, 'name'))));
        if($shortName = $this->getField($product, 'short_name')) {
            $this->xml->element('short_name', $this->xml->encloseCData(mb_substr($shortName, 0, 45, 'UTF-8')), array('autocomplete' => 'off'));
        }

        if ($keywords = $this->getField($product, 'keywords')) {
            $this->xml->element('keywords', $this->xml->encloseCData($keywords));
        }
        $this->writeDescriptions($product);
        $this->xml->element('url', $this->xml->encloseCData($product->getProductUrl()));
        $this->writeImageUrl($product);
        $this->writeClassification($product, $rootCategoriesIds);
        $this->writeProperties($product, $stores);
        $this->writeBrand($product);
        $this->writeMaterials($product);
        $this->writeColors($product);
        $this->writeModels($product);
        $this->writeSizes($product);
        $this->writeGenders($product);
        $this->writeMisc($product);

        $this->writeVariants($product, $stores);

        $this->xml->pop();
    }
    
    /**
     * Write the store's informations
     *
     * @param Product $product
     * @param array $stores
     */
    protected function writeStore($product, $stores, $variantProduct)
    {
        $this->xml->push('stores');
        foreach($stores as $store) {
            Mage::app()->setCurrentStore($store->getId());
            $variantProduct = Mage::getModel('catalog/product')->setStoreId($store->getId())->load($variantProduct->getId());

            $this->xml->push('store', array('id' => $store->getId(), 'name' => $store->getName()));
            $storeContext['currency'] = $store->getCurrentCurrencyCode();
            $storeContext['country']  = $this->getStoreLang($store->getId());

            $operations = $this->getOperations($product, $store);
            $this->writePrices($variantProduct, $product, $storeContext, $store, $operations);
            $this->writeMarketing($variantProduct, $operations);

            $isAvailable = $variantProduct->isSalable() || (in_array($variantProduct->getTypeId(), $this->productMultiple) && $product->isInStock());
            $this->xml->element('is_available', (int)$isAvailable);

            $qty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($variantProduct)->getQty();
            $qty = ($qty > 0 ? $qty : 0);
            $this->xml->element('stock', (int)$qty);

            $this->xml->element('url', $this->xml->encloseCData($variantProduct->getProductUrl()));
            $this->xml->pop();

            $variantProduct->clearInstance();
        }
        $this->xml->pop();
    }
    
    /**
     * Get catalog/product model
     *
     * @return Model
     */
    protected function getCatalogProduct()
    {
        if(!$this->catalogProduct) {
            $this->catalogProduct = Mage::getModel('catalog/product');
        }

        return $this->catalogProduct;
    }

    /**
     * Get product stores
     * 
     * @param Product $product
     * @param array $context
     */
    protected function getProductStores($product, $context)
    {
        $stores = array();

        $storeIds = array_intersect($product->getStoreIds(), $context['store_id']);
        foreach($storeIds as $storeId) {
            if (in_array($storeId, $this->enabledStores))
                $stores[] = $context['stores'][$storeId];
        }
        
        return $stores;
    }
    
    /**
     * Write the product descriptions
     * 
     * @param Product $product
     */
    protected function writeDescriptions($product)
    {
        if(!empty($this->fields['description'])) {
            $this->xml->push('descriptions');
            foreach($this->fields['description'] as $description) {
                if ($value = $this->getField($product, $description)) {
                	$value = $this->utf8CharacterValidation($value);	
                    $this->xml->element('description', $this->xml->encloseCData(substr($value, 0, 20000)), array('type' => $description));
                }
            }
            $this->xml->pop();
        }
    }
    
    /**
     * Remove non utf8 characters from string
     * 
     * @param string $value
     */
    protected function utf8CharacterValidation($value) {
    	
    	$value = preg_replace('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]'.
    			'|(?<=^|[\x00-\x7F])[\x80-\xBF]+'.
    			'|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*'.
    			'|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})'.
    			'|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/',
    			'', $value );
    	
    	$value = preg_replace('/\xE0[\x80-\x9F][\x80-\xBF]'.
    			'|\xED[\xA0-\xBF][\x80-\xBF]/S','', $value );
    	
    	return $value;
    	 
    }
    
    /**
     * Write the product identifiers
     * 
     * @param Product $product
     */
    protected function writeIdentifiers($product)
    {
        if($gtin = $this->getField($product, 'gtin')) {
            if(!preg_match('/^[0-9]{12,14}$/', $gtin)) {
                $gtin = false;
            }
        }

        $identifiers = array();
        if(!empty($this->fields['identifier'])) {
            foreach($this->fields['identifier'] as $identifier) {
                if ($value = $this->getField($product, $identifier)) {
                    $identifiers[$identifier] = mb_substr($value, 0, 40, 'UTF-8');
                }
            }
        }

        if($gtin ||!empty($identifiers)) {
            $this->xml->push('identifiers');
            if($gtin) {
                $this->xml->element('gtin', $gtin);
            }

            if(!empty($identifiers)) {
                foreach($identifiers as $identifier => $value) {
                    $this->xml->element('identifier', $value, array('type' => $identifier));
                }
            }

            $this->xml->pop();
        }
    }
    
    /**
     * Write the product identifiers
     * 
     * @param Product $product
     */
    protected function writeBrand($product)
    {
        if ($manufacturer = $this->getField($product, 'manufacturer')) {
            if(!empty($manufacturer)) {
                $field = empty($this->fields['manufacturer']) ? 'manufacturer' : $this->fields['manufacturer'];
                $brand = mb_substr($product->getAttributeText($field), 0, 40, 'UTF-8');
                $brandUrl = Mage::helper('catalogsearch')->getResultUrl($brand, $product->getStoreId());
                $brandUrl = parse_url($brandUrl, PHP_URL_PATH).'?'.parse_url($brandUrl, PHP_URL_QUERY);
                $brandUrl = str_replace('antidotExport.php', 'index.php', $brandUrl);
                if(!empty($brand)) {
                    $this->xml->element('brand', $this->xml->encloseCData($brand), array('id' => $manufacturer, 'url' => $brandUrl));
                }
            }
        }
    }
    
    /**
     * Write the product urls
     * 
     * @param Product $product
     * @param string $urlImg
     */
    protected function writeImageUrl($product, $urlImg = true)
    {
        try {
            if ($product->getThumbnail() && ($product->getThumbnail() != 'no_selection')) {
                $this->xml->element('url_thumbnail', $this->xml->encloseCData(Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getThumbnail())));
            }
        } catch(Exception $e) {}

        try {
            if ($urlImg && $product->getImage() && ($product->getImage() != 'no_selection')) {
                $this->xml->element('url_image', $this->xml->encloseCData(Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getImage())));
            }
        } catch(Exception $e) {}
    }

    /**
     * Write the product classification
     *
     * @param Product $product
     */
    protected function writeClassification($product, $rootCategoriesIds)
    {
        $categories = $this->getProductCategories($product, $rootCategoriesIds);
        if(count($categories) > 0) {
            $this->xml->push('classification');
            foreach($categories as $category) {
                    $this->writeCategory($category);
            }
            $this->xml->pop();
        }
    }

    /**
     * Write category node with their parents
     *
     * @param      $category
     * @param bool $first
     * @param int  $level
     */
    protected function writeCategory($category, $first = true, &$level = 0)
    {
        if (!$category->getName())
            return false;

        if($category->getParentId() == 1 || $category->getParentId() == 0)
            return false;
        
        if($category->getParentId() !== 1 && $category->getParentId() !== 0) {
            $level++;
            if(!$this->writeCategory($this->getCategoryById($category->getStoreId(), $category->getParentId()), false, $level)) {
                $level--;
            }
        }


        $categoryUrl = $this->getUri($category->getUrl());
        $categoryUrl = str_replace('antidotExport.php', 'index.php', $categoryUrl);
        $attributes = array('id' => $category->getId(), 'label' => $category->getName(), 'url' => $categoryUrl);
        if ($category->getImage()) {
            $attributes['img'] = $category->getImage();
        }
        $this->xml->push('category', $attributes);

        if($first) {
            // close  xml elements
            for($i = 0; $i <= $level; $i++) {
                $this->xml->pop();
            }
        }

        return true;
    }

    /**
     * Get category by id
     *
     * @param int $categoryId
     * @return Category
     */
    protected function getCategoryById($storeId, $categoryId)
    {
    	if(!isset($this->categories[$storeId][$categoryId])) {
            $this->categories[$storeId][$categoryId] = Mage::getModel('catalog/category')->setStoreId($storeId)->load($categoryId);
        }
        
        return $this->categories[$storeId][$categoryId];
    }

    /**
     * Return the top level category
     *
     * @param Product $product
     * @return array
     */
    protected function getProductCategories($product, $rootCategoriesIds)
    {
        $categories = $product->getCategoryCollection();
        $categories->setStoreId($product->getStoreId());
        $categories->addAttributeToSelect('name');
        $categories->addAttributeToSelect('image');
        $categories->addAttributeToSelect('url_key');
        $categories->addAttributeToFilter('name', array('neq' => ''));

        //Add a filter on the path in order to export only the categories whose root category is used on the exported stores 
        $rootCategoryCondition = array();
        foreach ($rootCategoriesIds as $rootCategoryId) {
        	$rootCategoryCondition[] = array('like' => '1/'.$rootCategoryId.'/%');
        }
        $categories->addAttributeToFilter('path', $rootCategoryCondition);
        
        $productCategories = array();
        foreach($categories as $category) {
            //Force the store on the url in order to generate the store code in url if it is configured in system > config
        	$category->setStoreId($product->getStoreId()); 
            if (method_exists($category, 'getUrlModel')) { //compatibility with older magento version where category#getUrlModel doesn't exist
            	$category->getUrlModel()->getUrlInstance()->setStore($product->getStoreId());
            } else {
            	$category->getUrlInstance()->setStore($product->getStoreId());
            }
        	if (($category->getparent_id() == 0) || ($category->getparent_id() == 1))
                continue;
            $productCategories[$category->getId()] = $category;
            $this->categories[$product->getStoreId()][$category->getId()] = $category; //cache categories already loaded here to avoid full load in getCategoryById()
            $parentCategory[] = $category->getParentId();
        }

        foreach($productCategories as $category) {
            if(in_array($category->getParentId(), $parentCategory)) {
                unset($productCategories[$category->getParentId()]);
            }
        }

        return $productCategories;
    }

    /**
     * Write the product materials
     * 
     * @param Product $product
     */
    protected function writeMaterials($product)
    {
        if(!empty($this->fields['materials']) && $materials = $product->getAttributeText($this->fields['materials'])) {
            $this->xml->push('materials');
            $this->xml->element('material', $this->xml->encloseCData($materials));
            $this->xml->pop();
        }
    }
    
    /**
     * Write the product colors
     * 
     * @param Product $product
     */
    protected function writeColors($product)
    {
        if(!empty($this->fields['colors']) && $color = $product->getAttributeText($this->fields['colors'])) {
            $this->xml->push('colors');
            $this->xml->element('color', $this->xml->encloseCData($color));
            $this->xml->pop();
        }
    }
    
    /**
     * Write the product models
     * 
     * @param Product $product
     */
    protected function writeModels($product)
    {
        if(!empty($this->fields['models']) && $models = $this->getField($product, $this->fields['models'])) {
            $this->xml->push('models', array('autocomplete' => 'off'));
            $this->xml->element('model', $this->xml->encloseCData(substr($models, 0, 40)));
            $this->xml->pop();
        }
    }
    
    /**
     * Write the product sizes
     * 
     * @param Product $product
     */
    protected function writeSizes($product)
    {
        if(!empty($this->fields['sizes']) && $size = $product->getAttributeText($this->fields['sizes'])) {
            $this->xml->push('sizes');
            $this->xml->element('size', $this->xml->encloseCData($size));
            $this->xml->pop();
        }
    }

    /**
     * Write the product genders
     *
     * @param Product $product
     */
    protected function writeGenders($product)
    {
        if(!empty($this->fields['gender']) && $gender = $product->getAttributeText($this->fields['gender'])) {
            $this->xml->push('audience');
                $this->xml->push('genders');
                $this->xml->element('gender', $this->xml->encloseCData($gender));
                $this->xml->pop();
            $this->xml->pop();
        }
    }
    
  /**
     * Write the product properties
     * 
     * @param Product $product
     * @param array   $stores List product store
     */
    protected function writeProperties($product, $stores)
    {
        $properties = array();
        if(!empty($this->fields['properties'])) {
            foreach($this->fields['properties'] as $property) {
                $id = $this->getField($product, $property['value']);
                if($id !== null) {
                    
                    
                    $attribute = $product->getResource()->getAttribute($property['value']);
                    if ($attribute) {
	                    $value = $attribute->getFrontend()->getValue($product);
	                    $label = $attribute->getStoreLabel();
	                    if (!$label) {
	                    	$labels = $attribute->getStoreLabels();
	                    	if (isset($labels[current($stores)->getId()])) {
	                    		$label = $labels[current($stores)->getId()];
	                    	}
	                    }                    
	                    
	                    $labels = array();
	                    switch($attribute->getfrontend_input())
	                    {
	                        case 'multiselect':
	                            $values = explode(',', $value);
	                            foreach($values as $value)
	                            {
	                                $value = trim($value);
	                                $properties[] = array(
	                                    'name' => $property['value'],
	                                    'display_name' => substr($label, 0, 79),
	                                    'label' => substr($value, 0, 79),
	                                    'autocomplete' => ($property['autocomplete'] == 1 ? 'on' : 'off'));
	                            }
	                            break;
	                        default:
	                            $optionName = $value;
	                            if(!empty($this->propertyLabel[$property['value']]['options'][$id]['per_store'][current($stores)->getId()])) {
	                                $optionName = $this->propertyLabel[$property['value']]['options'][$id]['per_store'][current($stores)->getId()];
	                            }
	                            $value = is_bool($value) ? (int)$value : $value;
	                            $properties[] = array(
	                                'name' => $property['value'],
	                                'display_name' => substr($label, 0, 79),
	                                'label' => substr($optionName, 0, 79),
	                                'autocomplete' => ($property['autocomplete'] == 1 ? 'on' : 'off'));
	                            break;
	                    }
                    } else {
                        Mage::log('Attribute with code : '.$property['value'].' not present in product', null, 'antidot.log');
                    }

                }
            }
        }
        
        if(!empty($properties)) {
            $this->xml->push('properties');
            foreach($properties as $property) {
                $this->xml->emptyelement('property', $property);
            }
            $this->xml->pop();
        }
    }
    
    /**
     * Write the product prices
     * 
     * @param Product $product
     */
    protected function writePrices($product, $parentProduct, $context, $store, $operations)
    {
        $prices = ($this->getPrices($parentProduct->getId(), $store->getWebsiteId()));

        $price = Mage::helper('tax')->getPrice($product, $prices['price'], true);

        //try to get price & pricecut
        if ($prices['final_price'] < $prices['price'])
        {
            $priceCut = Mage::helper('tax')->getPrice($product, $prices['price'], true);
            $price = Mage::helper('tax')->getPrice($product, $prices['final_price'], true);
        }

        $price = Mage::helper('directory')->currencyConvert($price, Mage::app()->getStore()->getCurrentCurrencyCode(), $store->getCurrentCurrencyCode()); 
        
        $this->xml->push('prices');
        $attributes = array('currency' => $context['currency'], 'type' => 'PRICE_FINAL', 'vat_included' => 'true', 'country' => strtoupper($context['country']));
        if (isset($priceCut))
        {
            $off = (int)(($priceCut - $price) / $priceCut * 100);
            $attributes['off'] = $off;
        }
        $this->xml->element(
                'price', 
                Mage::helper('Antidot')->round($price),
                $attributes
        );
        
        
        if(isset($priceCut)) {
            $priceCut = Mage::helper('directory')->currencyConvert($priceCut, Mage::app()->getStore()->getCurrentCurrencyCode(), $store->getCurrentCurrencyCode());
            $this->xml->element(
                    'price',
                    Mage::helper('Antidot')->round($priceCut, 2),
                    array('currency' => $context['currency'], 'type' => 'PRICE_CUT', 'vat_included' => 'true', 'country' => strtoupper($context['country']))
            );
            
        }
        
        $this->xml->pop();
    }

    /**
     * Return product special price (if exists)
     *
     * @param $product
     */
    protected function getSpecialPrice($product)
    {
        $specialprice = $product->getSpecialPrice();
        $specialPriceFromDate = $product->getSpecialFromDate();
        $specialPriceToDate = $product->getSpecialToDate();
        $today =  Mage::getModel('core/date')->timestamp(time());

        if ($specialprice)
        {
            if($today >= strtotime( $specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime( $specialPriceFromDate) && is_null($specialPriceToDate))
            {
                return $specialprice;
            }
        }

        return false;
    }

    /**
     * Get product's prices
     * 
     * @param int $productId
     * @param int $websiteId
     * @return array
     */
    protected function getPrices($productId, $websiteId)
    {
    	$mainTable = Mage::getSingleton('core/resource')->getTableName("catalog_product_index_price");
    	 
        $query = "SELECT price, final_price, min_price "
               . "FROM " . $mainTable . " "
               . "WHERE entity_id = ".(int)$productId." "
               . "AND website_id = ".(int)$websiteId." "
               . "AND customer_group_id = 0 "
        ;

        $result = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchRow($query);

        if (($result['min_price']))
        {
        	//on bundle product, only min_price is set
            if ($result['price'] == 0) {
        		$result['price'] = $result['min_price'];
        	}
        	if ($result['final_price'] == 0) {
                $result['final_price'] = $result['min_price'];
        	}
        }

        if (($result['min_price'] == 0) && ($result['price'] == 0) && ($result['final_price'] == 0))
        {
            $product = Mage::getModel('catalog/product')->load($productId);
            $result['min_price'] = $product->getPrice();
            $result['price'] = $product->getPrice();
            $result['final_price'] = $product->getPrice();
            $product->clearInstance();
        }
        
        return $result;
    }
    
    /**
     * Write the marketing elements
     * 
     * @param Product $product
     * @param array $operations
     */
    protected function writeMarketing($product, $operations)
    {
        $this->xml->push('marketing');
        $this->xml->element('is_new', ($this->getField($product, 'is_new') ? 1 : 0));
        $this->xml->element('is_best_sale', ($this->getField($product, 'is_best_sale') ? 1 : 0));
        $this->xml->element('is_featured', ($this->getField($product, 'is_featured') ? 1 : 0));

        $isPromotional = false;
        if (is_array($operations))
        {
            foreach($operations as $operation) {
                $isPromotional = true;
                $this->xml->element('operation', 1, array('display_name' => $operation['name'], 'name' => 'OPERATION_'.$operation['rule_id']));
            }
        }
        $this->xml->element('is_promotional', (int)$isPromotional);
        
        $this->xml->pop();
    }

    /**
     * init products with operations array to prevent query for products withtout operation
     */
    protected function initProductsWithOperations()
    {
    	$mainTable = Mage::getSingleton('core/resource')->getTableName("catalogrule_product");
    	$this->productsWithOperation = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchCol('select distinct product_id from ' . $mainTable);
        Mage::log('Products with operation : '.count($this->productsWithOperation), null, 'antidot.log');
    }

    /**
     * Get operations from $product
     * 
     * @param Product $product
     * @param Store $store
     * @return array
     */
    protected function getOperations($product, $store)
    {

        //if product has no operation
        if (!isset($this->productsWithOperation[$product->getId()]))
            return false;

        $catalogruleProductTable = Mage::getSingleton('core/resource')->getTableName("catalogrule_product");
        $catalogruleTable = Mage::getSingleton('core/resource')->getTableName("catalogrule");
        
        $date = date('Y-m-d');
        $query = "SELECT " . $catalogruleTable . ".name, " . $catalogruleTable . ".rule_id, action_operator, action_amount "
               . "FROM " . $catalogruleProductTable . " "
               . "JOIN " . $catalogruleTable . " ON " . $catalogruleProductTable . ".rule_id = " . $catalogruleTable . ".rule_id "
               . "WHERE product_id = ".(int)$product->getId()." "
               . "AND website_id = ".$store->getWebSiteId()." "
               . "AND from_date < '".$date."' "
               . "AND to_date > '".$date."' "
               . "AND customer_group_id = 0 "
        ;

        return Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);
    }
    
    /**
     * Write the dynamic elements
     * 
     * @param Product $product
     */
    protected function writeMisc($product)
    {
        $this->xml->push('misc');
        $this->xml->element('product_type', $this->xml->encloseCData($product->getTypeID()));
        if(!empty($this->fields['misc'])) {
            foreach($this->fields['misc'] as $misc) {
                $this->xml->element($misc, $this->xml->encloseCData($this->getField($product, $misc)));
            }
        }
        $this->xml->pop();
    }
    
    /**
     * Write variants produt
     * 
     * @param Product $product
     * @param array $stores
     */
    protected function writeVariants($product, $stores)
    {
        $this->xml->push('variants');
        
        $this->xml->push('variant', array('id' => 'fake'));
        $this->writeVariant($product, $product, $stores);
        $this->xml->pop();

        $variantProducts = array();
        switch($product->getTypeID())
        {
            case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE:
                $variantProducts = $product->getTypeInstance(true)->getUsedProducts(null, $product);
                break;
            case Mage_Catalog_Model_Product_Type::TYPE_GROUPED:
                $variantProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
                break;
        }

        if(count($variantProducts) > 0) {
            foreach($variantProducts as $variantProduct) {

                //Do not include product if status is not enabled
                if (!$variantProduct->getstatus() == 1)
                    continue;

                $this->xml->push('variant', array('id' => $variantProduct->getId()));
                $this->writeVariant($variantProduct, $product, $stores);
                $this->xml->pop();
            }
        }

        $this->xml->pop();
    }
    
    /**
     * Write variant
     * 
     * @param Product $variantProduct
     * @param Product $product
     * @param array $stores
     */
    protected function writeVariant($variantProduct, $product, $stores)
    {
        $this->xml->element('name', $this->xml->encloseCData($this->utf8CharacterValidation($variantProduct->getName())));
        $this->writeDescriptions($variantProduct);
        $this->writeStore($product, $stores, $variantProduct);
        $this->writeIdentifiers($variantProduct);
        $this->writeProperties($variantProduct, $stores);
        $this->writeMaterials($variantProduct);
        $this->writeColors($variantProduct);
        $this->writeModels($variantProduct);
        $this->writeSizes($variantProduct);
        $this->writeGenders($product);
        $this->writeImageUrl($variantProduct);
        $this->writeMisc($variantProduct);
    }
    
    /**
     * Write a part xml to file
     * 
     * @param string $xml
     * @param boolean $close
     */
    protected function writePart($xml, $close = false) 
    {
        $filename = $this->getFilename();
        if ($this->file === null) {
            $this->file = fopen($filename, 'a+');
            if (!$this->file)
                throw new Exception('Unable to open file for writing : '.$filename);
        }
        
        $res = fwrite($this->file, $xml);
        if ($res === false)
            throw new Exception('Can not write in : '.$filename);

        if ($close) {
            fclose($this->file);
            $this->file = null;
        }
    }
    
    /**
     * Set the filename
     * 
     * @param string $filename
     */
    protected function setFilename($filename) 
    {
        if(file_exists($filename)) {
            unlink($filename);
        }
        $this->filename = $filename;
    }
    
    /**
     * Return the filename
     * 
     * @return string Return the filename
     */
    protected function getFilename() 
    {
        return $this->filename;
    }
    
    /**
     * Get products to generate
     * 
     * @param array $storeIds
     * @param int $page
     * @param int $limit
     * @param string $type
     * @return array
     */
    protected function getProductIds($storeIds, $websiteIds, $type) 
    {
        $productsInStock = $this->onlyProductsWithStock ? ' AND is_in_stock = 1' : '';
        $collection = Mage::getModel('catalog/product')
            ->getCollection()
            ->setStoreId($storeIds)
            ->addWebsiteFilter($websiteIds)
            ->addAttributeToFilter('visibility', $this->productVisible)
            ->addAttributeToFilter('status', 1)
            ->joinField('qty',
                        'cataloginventory/stock_item',
                        'qty',
                        'product_id = entity_id',
                        '{{table}}.stock_id = 1'.$productsInStock)
        ;
        
        if ($type === MDN_Antidot_Model_Observer::GENERATE_INC) {
            if($this->lastGeneration === null) {
                $this->lastGeneration = Mage::helper('Antidot/LogExport')->getLastGeneration(self::TYPE);
            }
            $collection->addAttributeToFilter('updated_at', array('gteq' => $this->lastGeneration));
        }
        
        return $collection->getAllIds();
    }
}
