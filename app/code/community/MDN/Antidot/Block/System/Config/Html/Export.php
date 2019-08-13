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
class MDN_Antidot_Block_System_Config_Html_Export extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /**
     * {@inherit}
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $table = '<div class="grid">' 
                    .'<table class="border" cellspacing="0" cellpadding="0">'
                        .'<thead>'
                            .'<tr class="headings">'
                                .'<th width="120px">'.Mage::helper('Antidot')->__('Date').'</th>'
                                .'<th>'.Mage::helper('Antidot')->__('Reference').'</th>'
                                .'<th>'.Mage::helper('Antidot')->__('Type').'</th>'
                                .'<th>'.Mage::helper('Antidot')->__('Element').'</th>'
                                .'<th>'.Mage::helper('Antidot')->__('Products').'</th>'
                                .'<th>'.Mage::helper('Antidot')->__('Status').'</th>'
                                .'<th>'.Mage::helper('Antidot')->__('Msg').'</th>'
                            .'</tr>'
                        .'</thead>'
                        .'<tbody>'
                            .'%s'
                        .'</tbody>'
                    .'</table>'
                .'</div>'
        ;
        
        $rows = '';
        $rowExport = '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>';
        foreach(Mage::helper('Antidot/LogExport')->getAllLastGeneration() as $export) {
            $rows.= sprintf(
                $rowExport, 
                $export['begin_at'],
                $export['reference'],
                $export['type'],
                $export['element'],
                $export['items_processed'],
                $export['status'],
                $export['error']
            );
        }
        
        return sprintf($table, $rows);
    }
}