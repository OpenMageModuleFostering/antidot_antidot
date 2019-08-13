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
class MDN_Antidot_Test_Model_Resource_Catalog_Product_Collection extends EcomDev_PHPUnit_Test_Case
{

	/**
     * MCNX-246 magento 1.5 compatibility
     * 
     * @test
     */
    public function testInheritance() {

        /** @var  $collection  MDN_Antidot_Model_Resource_Catalog_Product_Collection */
        $collection = Mage::getResourceModel('Antidot/catalog_product_collection');

        /**
         * test if this class inherits from Mage_Catalog_Model_Resource_Product_Collection
         * and Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
         */
    	$this->assertTrue(is_subclass_of($collection, 'Mage_Catalog_Model_Resource_Product_Collection'));
        $this->assertTrue(is_subclass_of($collection, 'Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection'));


    }
}