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
if ((string)Mage::getConfig()->getModuleConfig('Enterprise_Search')->active != 'true') {
    class Enterprise_Search_Block_Catalogsearch_Layer extends Mage_CatalogSearch_Block_Layer {}
}
class MDN_Antidot_Block_Catalogsearch_Layer extends Enterprise_Search_Block_Catalogsearch_Layer
{
    /**
     * Boolean block name.
     *
     * @var string
     */
    protected $_booleanFilterBlockName;

    /**
     * Modifies default block names to specific ones if engine is active.
     */
    protected function _initBlocks()
    {
        parent::_initBlocks();

        if (Mage::helper('Antidot')->isActiveEngine()) {
            $this->_categoryBlockName = 'Antidot/catalog_layer_filter_category';
            $this->_attributeFilterBlockName = 'Antidot/catalogsearch_layer_filter_attribute';
            $this->_priceFilterBlockName = 'Antidot/catalog_layer_filter_price';
            $this->_decimalFilterBlockName = 'Antidot/catalog_layer_filter_decimal';
            $this->_booleanFilterBlockName   = 'Antidot/catalog_layer_filter_boolean';
        }
    }

    /**
     * Prepares layout if engine is active.
     * Difference between parent method is addFacetCondition() call on each created block.
     *
     * @return MDN_Antidot_Block_Catalogsearch_Layer
     */
    protected function _prepareLayout()
    {
        $helper = Mage::helper('Antidot');
        if (!$helper->isActiveEngine()) {
            parent::_prepareLayout();
        } else {
            $stateBlock = $this->getLayout()->createBlock($this->_stateBlockName)
                ->setLayer($this->getLayer());

            $this->setChild('layer_state', $stateBlock);
            
            $filterableAttributes = $this->_getFilterableAttributes();
            $filters = array();
            foreach ($filterableAttributes as $attribute) {
                $filters[$attribute->getAttributeCode() . '_filter'] = $this->getLayout()->createBlock($this->_attributeFilterBlockName)
                    ->setLayer($this->getLayer())
                    ->setAttributeModel($attribute)
                    ->init();
            }
            
            foreach ($filters as $filterName => $block) {
                $this->setChild($filterName, $block->addFacetCondition());
            }
            
            $this->getLayer()->apply();
        }
        
        return $this;
    }
    
    /**
     * Checks display availability of layer block.
     *
     * @return bool
     */
    public function canShowBlock()
    {
    	$helper = Mage::helper('Antidot');
        if ($helper->isActiveEngine()) {
        	return ($this->canShowOptions() || count($this->getLayer()->getState()->getFilters()));
        }
        return parent::canShowBlock();
    }

    /**
     * Returns current catalog layer.
     *
     * @return MDN_Antidot_Model_Catalogsearch_Layer|Mage_Catalog_Model_Layer
     */
    public function getLayer()
    {
        $helper = Mage::helper('Antidot');
        if ($helper->isActiveEngine()) {
            return Mage::getSingleton('Antidot/catalogsearch_layer');
        }

        return parent::getLayer();
    }

    /**
     * MCNX-230 ManaDev compatibility
     *
     * Set path to template used for generating block's output.
     *
     *  OVERRIDE : for compatibility with module manadev : don't let manadev change the template
     *
     * @param string $template
     * @return Mage_Core_Block_Template
     */
    public function setTemplate($template)
    {
        if ($this->_template == null || strpos($template, 'mana') === false ) {
            $this->_template = $template;
        }
        return $this;
    }

}
