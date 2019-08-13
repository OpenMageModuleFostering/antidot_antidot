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
class MDN_Antidot_Model_Resource_Export_Product extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product
// instead of Mage_Catalog_Model_Resource_Product for compatibility with magento < 1.6
{

    /**
     * Reset firstly loaded attributes
     *
     * @param Varien_Object $object
     * @param integer $entityId
     * @param array|null $attributes
     * @return Mage_Catalog_Model_Resource_Abstract
     */
    public function load($object, $entityId, $attributes = array())
    {


        $this->_attributes = array();

        Varien_Profiler::start('__EXPORT_PRODUCT_LOAD_MODEL__');
        /**
         * Load object base row data
         */
        $select  = $this->_getLoadRowSelect($object, $entityId);
        $row     = $this->_getReadAdapter()->fetchRow($select);

        if (is_array($row)) {
            $object->addData($row);
        } else {
            $object->isObjectNew(true);
        }

        $attributesIds = array();
        if (empty($attributes)) {
            $this->loadAllAttributes($object);
        } else {
            foreach ($attributes as $attrCode) {
                $attribute = $this->getAttribute($attrCode);
                $attributesIds[] = $attribute->getId();
            }
        }

        //ADD : load only attributes needed
        $this->loadModelAttributes($object, $attributesIds);

        //$object->setOrigData();
        Varien_Profiler::start('__EXPORT_PRODUCT_LOAD_MODEL_AFTER_LOAD__');

        $this->_afterLoad($object);
        Varien_Profiler::stop('__EXPORT_PRODUCT_LOAD_MODEL_AFTER_LOAD__');

        Varien_Profiler::stop('__EXPORT_PRODUCT_LOAD_MODEL__');
        return $this;
    }

    /**
     * Load model attributes data
     *
     * Only attributes passed by parameters
     *
     * @param Mage_Core_Model_Abstract $object
     * @param array $attributesIds
     * @return Mage_Eav_Model_Entity_Abstract
     */
    public function loadModelAttributes($object, $attributesIds = array())
    {
        if (!$object->getId()) {
            return $this;
        }

        Varien_Profiler::start('__EXPORT_PRODUCT_LOAD_MODEL_ATTRIBUTES__');

        if (method_exists($this, '_addLoadAttributesSelectFields')) {  // magento > 1.5

            $selects = array();
            foreach (array_keys($this->getAttributesByTable()) as $table) {
                $attribute = current($this->_attributesByTable[$table]);
                $eavType = $attribute->getBackendType();
                $select = $this->_getLoadAttributesSelect($object, $table);
                //ADD : load only needed attributes
                if (count($attributesIds)) {
                    $select->where("attr_table.attribute_id IN (?)", $attributesIds);
                }
                //ADD : load only needed attributes
                $selects[$eavType][] = $this->_addLoadAttributesSelectFields($select, $table, $eavType);
            }
            $selectGroups = Mage::getResourceHelper('eav')->getLoadAttributesSelectGroups($selects);
            foreach ($selectGroups as $selects) {
                if (!empty($selects)) {
                    $select = $this->_prepareLoadSelect($selects);
                    $values = $this->_getReadAdapter()->fetchAll($select);
                    foreach ($values as $valueRow) {
                        $this->_setAttributeValue($object, $valueRow);
                    }
                }
            }

        } else {  // magento 1.5
            $selects = array();
            foreach ($this->getAttributesByTable() as $table => $attributes) {
                $selects[] = $this->_getLoadAttributesSelect($object, $table);
                //ADD : load only needed attributes
                foreach ($selects as $select) {
                    if (count($attributesIds)) {
                        $select->where("attr_table.attribute_id IN (?)", $attributesIds);
                    }
                }
                //ADD : load only needed attributes
            }
            if (!empty($selects)) {
                $select = $this->_prepareLoadSelect($selects);
                $values = $this->_getReadAdapter()->fetchAll($select);
                foreach ($values as $valueRow) {
                    $this->_setAttribteValue($object, $valueRow);
                }
            }
        }

        Varien_Profiler::stop('__EXPORT_PRODUCT_LOAD_MODEL_ATTRIBUTES__');

        return $this;
    }

}
