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
class MDN_Antidot_Model_Catalogsearch_Resource_Attribute
{
    
    public function __construct($facet)
    {
        $this->name = $facet['label'];
        $this->id   = $facet['id'];
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getAttributeCode()
    {
        return $this->id;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getStoreLabel()
    {
        return $this->name;
    }
    
    public function getSourceModel()
    {
        return 'text';
    }
    
    public function getBackendType()
    {
        return '';
    }
    
    public function getFrontendInput()
    {
        return 'text';
    }
    
    public function usesSource()
    {
        return false;
    }
}
