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
class MDN_Antidot_Model_Catalogsearch_Layer_Filter_Attribute extends MDN_Antidot_Model_Catalog_Layer_Filter_Attribute
{
    protected function _getIsFilterableAttribute($attribute)
    {
        return $attribute->getIsFilterableInSearch();
    }

    /**
     * Override getName method to return facet name in the last AFS response
     *
     * @return mixed
     */
    public function getName()
    {
        $name = Mage::helper('Antidot')->translateFacetName($this->getCode(), parent::getName());


        return $name;
    }
}
