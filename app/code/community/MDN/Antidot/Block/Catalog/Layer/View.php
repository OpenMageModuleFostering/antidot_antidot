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
class MDN_Antidot_Block_Catalog_Layer_View extends Mage_Catalog_Block_Layer_View
{
    /**
     * Boolean block name.
     *
     * @var string
     */
    protected $_booleanFilterBlockName;

    /**
     * Registers current layer in registry.
     *
     * @see Mage_Catalog_Block_Product_List::getLayer()
     */
    protected function _construct()
    {
        parent::_construct();
        Mage::register('current_layer', $this->getLayer());
    }

    /**
     * Modifies default block names to specific ones if engine is active.
     */
    protected function _initBlocks()
    {
        parent::_initBlocks();

        if (Mage::helper('Antidot')->isActiveEngine()) {
            $this->_categoryBlockName        = 'Antidot/catalog_layer_filter_category';
            $this->_attributeFilterBlockName = 'Antidot/catalog_layer_filter_attribute';
            $this->_priceFilterBlockName     = 'Antidot/catalog_layer_filter_price';
            $this->_decimalFilterBlockName   = 'Antidot/catalog_layer_filter_decimal';
            $this->_booleanFilterBlockName   = 'Antidot/catalog_layer_filter_boolean';
        }
    }

    /**
     * Prepares layout if engine is active.
     * Difference between parent method is addFacetCondition() call on each created block.
     *
     * @return MDN_Antidot_Block_Catalog_Layer_View
     */
    protected function _prepareLayout()
    {
        $helper = Mage::helper('Antidot');
        if (!$helper->isActiveEngine()) {
            parent::_prepareLayout();
        } else {
            $stateBlock = $this->getLayout()->createBlock($this->_stateBlockName)
                ->setLayer($this->getLayer());

            $categoryBlock = $this->getLayout()->createBlock($this->_categoryBlockName)
                ->setLayer($this->getLayer())
                ->init();

            $this->setChild('layer_state', $stateBlock);
            $this->setChild('category_filter', $categoryBlock->addFacetCondition());

            $filterableAttributes = $this->_getFilterableAttributes();
            $filters = array();
            foreach ($filterableAttributes as $attribute) {
                if ($attribute->getAttributeCode() == 'price') {
                    $filterBlockName = $this->_priceFilterBlockName;
                } elseif ($attribute->getBackendType() == 'decimal') {
                    $filterBlockName = $this->_decimalFilterBlockName;
                } elseif ($attribute->getSourceModel() == 'eav/entity_attribute_source_boolean') {
                    $filterBlockName = $this->_booleanFilterBlockName;
                } else {
                    $filterBlockName = $this->_attributeFilterBlockName;
                }

                $filters[$attribute->getAttributeCode() . '_filter'] = $this->getLayout()->createBlock($filterBlockName)
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
     * Returns current catalog layer.
     *
     * @return MDN_Antidot_Model_Catalog_Layer|Mage_Catalog_Model_Layer
     */
    public function getLayer()
    {
        $helper = Mage::helper('Antidot');
        if ($helper->isActiveEngine()) {
            return Mage::getSingleton('Antidot/catalog_layer');
        }

        return parent::getLayer();
    }
}
