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
class MDN_Antidot_Model_Catalog_Layer_Filter_Boolean extends MDN_Antidot_Model_Catalog_Layer_Filter_Attribute
{
    /**
     * Returns facets data of current attribute.
     *
     * @return array
     */
    protected function _getFacets()
    {
        $facets = parent::_getFacets();
        $result = array();
        foreach ($facets as $value => $count) {
            $key = 0; // false by default
            if ($value === 'true' || $value === 'T' || $value === '1' || $value === 1 || $value === true) {
                $key = 1;
            }
            $result[$key] = $count;
        }

        return $result;
    }

    /**
     * Checks if given filter is valid before being applied to product collection.
     *
     * @param string $filter
     * @return bool
     */
    protected function _isValidFilter($filter)
    {
        return $filter === '0' || $filter === '1' || false === $filter || true === $filter;
    }
}
