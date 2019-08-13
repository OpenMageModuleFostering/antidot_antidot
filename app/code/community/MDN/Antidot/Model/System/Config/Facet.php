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
class MDN_Antidot_Model_System_Config_Facet
{

    protected $options = false;

    /**
     * {@inherit}
     */
    public function toOptionArray($typeExclude = null) 
    {
        if (!$this->options) {
            try {
                $search = Mage::getModel('Antidot/Search_Search');

                $this->options = array();
                foreach($search->getFacets() as $facetId => $facet) {
                    if($typeExclude === null || $facet->get_type() !== $typeExclude) {
                        $this->options[] = array('value' => $facetId.'|'.$facet->label, 'label' => $facetId.' ('.$facet->get_type().')');
                    }
                }

                //sort facets
                usort($this->options, array("MDN_Antidot_Model_System_Config_Facet", "sortFacetPerLabel"));

                return $this->options;
            } catch(Exception $e) {
                $this->options = array();
            }
        }
        
        return $this->options;
    }

    public static function sortFacetPerLabel($a, $b)
    {
        $al = $a['label'];
        $bl = $b['label'];
        if ($al == $bl) {
            return 0;
        }
        return ($al > $bl) ? +1 : -1;
    }
}
