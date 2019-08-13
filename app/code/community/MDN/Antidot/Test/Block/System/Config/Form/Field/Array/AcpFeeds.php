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
class MDN_Antidot_Test_Block_System_Config_Form_Field_Array_AcpFeeds extends EcomDev_PHPUnit_Test_Case
{

    /**
     *
     */
    public function testGetArrayRows()
    {
        /* @var $block MDN_Antidot_Block_System_Config_Form_Field_Array_AcpFeeds */
        $block = Mage::app()->getLayout()->createBlock('Antidot/system_config_form_field_array_acpFeeds');


        $element = new Varien_Object();
        $element->setValue(array('1' => array('feed'=>'products'),
            '2' => array('feed'=>'categories'),
            '3' => array('feed'=>'brands'),
            '4' => array('feed'=>'articles'),
            '5' => array('feed'=>'stores'),
        ));
        $block->setElement($element);

        $rows = $block->getArrayRows();

        $this->assertEquals(
            5,
            count($rows)
        );
        $this->assertTrue(isset($rows['_feeds_products']));
        $this->assertTrue(isset($rows['_feeds_categories']));
        $this->assertTrue(isset($rows['_feeds_brands']));
        $this->assertTrue(isset($rows['_feeds_articles']));
        $this->assertTrue(isset($rows['_feeds_stores']));

    }


}
