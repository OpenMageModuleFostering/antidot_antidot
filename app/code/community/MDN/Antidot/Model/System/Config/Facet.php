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
                if (count($search->getFacets()) > 0) {

                    /*
                     * MCNX-217 : we store all the labels returned by the afsstore WS in the magento core_translate table
                     */
                    $locales = array();
                    foreach (Mage::app()->getStores() as $store) {
                        $locale = Mage::getStoreConfig('general/locale/code', $store->getId());
                        list($lang) = explode('_',$locale);
                        if (!isset($locales[$lang])) {
                            $locales[$lang] = $locale;
                        }
                    }
                    /* @var $resource Mage_Core_Model_Mysql4_Translate_String */
                    $resource = Mage::getResourceModel('core/translate_string');

                    foreach ($search->getFacets() as $facetId => $facet) {
                        if ($typeExclude === null || $facet->get_type() !== $typeExclude) {
                            $this->options[] = array(
                                'value' => $facetId.'|'.$facet->get_label(),
                                'label' => $facetId.' ('.$facet->get_type().')'
                            );

                            //MCNX-217 : Store translated labels in magento core_translate table
                            // use the original label as the key string in order to let module upgrade ok
                            if ($typeExclude != null) {
                                $originLabel = $facet->get_label();
                                $translatedlabels = $facet->get_labels();
                                foreach ($translatedlabels as $lang => $translatedLabel) {
                                    if (isset($locales[$lang])) {
                                        $locale = $locales[$lang];
                                        $resource->saveTranslate($originLabel, $translatedLabel, $locale, 0);
                                    }
                                }
                            }
                        }
                    }

                    //MCNX-217 : flush the translations cache in order to make this nes translation immediatly availables
                    Mage::app()->getCacheInstance()->cleanType('translate');

                    //sort facets
                    usort($this->options, array("MDN_Antidot_Model_System_Config_Facet", "sortFacetPerLabel"));
                }

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
