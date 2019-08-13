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
class MDN_Antidot_Test_Block_System_Config_Button_AfsStore extends EcomDev_PHPUnit_Test_Case
{


    /**
     *
     */
    public function testGetElementHtml()
    {

        /* @var $block MDN_Antidot_Block_System_Config_Button_AfsStore */
        $block = Mage::app()->getLayout()->createBlock('Antidot/system_config_button_afsStore');

        /**
         * First test : session fr => button fr
         */
        $mockSession = $this->getModelMock('adminhtml/session', array('getLocale'), false, array(), '',  false);
        //set the paramterer $callOriginalConstructor to false in order to not initialise session during unit test
        $mockSession->expects($this->any())
            ->method('getLocale')
            ->will($this->returnCallBack(function(){return 'fr_FR';}));

        $this->replaceByMock('singleton', 'adminhtml/session', $mockSession);

        $html= MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($block, '_getElementHtml', array(new Varien_Data_Form_Element_Button()));

        $expectedPattern = '#https://bo-store.afs-antidot.net/fr#';

        $this->assertRegExp(
            $expectedPattern,
            $html
        );

        /**
         * Second test : session en => button en
         */
        $mockSession = $this->getModelMock('adminhtml/session', array('getLocale'), false, array(), '',  false);
        //set the paramterer $callOriginalConstructor to false in order to not initialise session during unit test
        $mockSession->expects($this->any())
            ->method('getLocale')
            ->will($this->returnCallBack(function(){return 'en_GB';}));

        $this->replaceByMock('singleton', 'adminhtml/session', $mockSession);

        $html= MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($block, '_getElementHtml', array(new Varien_Data_Form_Element_Button()));

        $expectedPattern = '#https://bo-store.afs-antidot.net/en#';

        $this->assertRegExp(
            $expectedPattern,
            $html
        );

        /**
         * Second test : session en => button en
         */
        $mockSession = $this->getModelMock('adminhtml/session', array('getLocale'), false, array(), '',  false);
        //set the paramterer $callOriginalConstructor to false in order to not initialise session during unit test
        $mockSession->expects($this->any())
            ->method('getLocale')
            ->will($this->returnCallBack(function(){return 'es_ES';}));

        $this->replaceByMock('singleton', 'adminhtml/session', $mockSession);

        $html= MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($block, '_getElementHtml', array(new Varien_Data_Form_Element_Button()));

        $expectedPattern = '#https://bo-store.afs-antidot.net/en#';

        $this->assertRegExp(
            $expectedPattern,
            $html
        );

    }
}