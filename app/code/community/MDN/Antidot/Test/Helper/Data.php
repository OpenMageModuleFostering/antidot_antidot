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
class MDN_Antidot_Test_Helper_Data extends EcomDev_PHPUnit_Test_Case
{

   /**
     * Test method MDN_Antidot_Model_Observer returnBytes
     */
    function testReturnBytes()
    {
        /** @var $observer MDN_Antidot_Model_Observer */
        $helper = Mage::helper('Antidot');

        foreach (array("2048M" => "2147483648", "512M" => "536870912", "4G" => "4294967296") as $value => $expected) {
            $result = $helper->returnBytes($value);
            $this->assertEquals(
                $result,
                $expected
            );
        }

    }

}