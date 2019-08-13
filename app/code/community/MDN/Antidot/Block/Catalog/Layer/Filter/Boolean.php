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
class MDN_Antidot_Block_Catalog_Layer_Filter_Boolean extends MDN_Antidot_Block_Catalog_Layer_Filter_Attribute
{
    /**
     * Defines specific filter model name.
     *
     * @see MDN_Antidot_Model_Catalog_Layer_Filter_Boolean
     */
    public function __construct()
    {
        parent::__construct();
        $this->_filterModelName = 'Antidot/catalog_layer_filter_boolean';
    }
}
