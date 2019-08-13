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

    protected $facetsOptions = false;
    protected $filtersOptions = false;

    /**
     * {@inherit}
     */
    public function toOptionArray($forSortOptions = false)
    {
        if (!$this->facetsOptions) {
            try {

                $search = Mage::getSingleton('Antidot/search_search');

                $this->facetsOptions = array();
                $this->filtersOptions = array();
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

                    /* @var $facet AfsFacetInfo */
                    foreach ($search->getFacets() as $facetId => $facet) {
                        //MCNX-217 : Store translated labels in magento core_translate table
                        $translatedlabels = $facet->get_labels();
                        $originLabel = null;
                        foreach ($translatedlabels as $lang => $translatedLabel) {
                            $lang = mb_strtolower($lang);
                            if ($originLabel == null) { //on prend le premier libellé renvoyé => rétro compatibilité avec MCNX-217, c'est ce qui était fait dans get_label() du ws de search
                                $originLabel = $translatedLabel;
                            }
                            if (isset($locales[$lang])) {
                                $locale = $locales[$lang];
                                $resource->saveTranslate($originLabel, $translatedLabel, $locale, 0);
                            }
                        }

                        //cas de afs:lang qui n'a pas de label dans le WS d'introspection ..
                        if ($originLabel == null) {
                            $originLabel=$facetId;
                            if ($originLabel=='afs:lang') {
                                $originLabel='Language';
                            }
                        }

                        //MCNX-235 : escape single quote for javascript, it cause error in javascript facet editor in BO
                        $option = array(
                            'value' => $facetId.'|'.Mage::helper('core')->jsQuoteEscape($originLabel),
                            'label' => $facetId.' ('.$facet->get_type().')'
                        );

                        if (!$facet->is_filter()) {
                            $this->facetsOptions[] = $option;
                        }
                        $this->sortOptions[] = $option;

                    }

                    //MCNX-217 : flush the translations cache in order to make this nes translation immediatly availables
                    Mage::app()->getCacheInstance()->cleanType('translate');

                    //sort facets
                    usort($this->facetsOptions, array("MDN_Antidot_Model_System_Config_Facet", "sortFacetPerLabel"));
                    usort($this->filtersOptions, array("MDN_Antidot_Model_System_Config_Facet", "sortFacetPerLabel"));
                }

            } catch(Exception $e) {
                $this->options = array();
            }
        }

        if ($forSortOptions) {
            return $this->sortOptions;
        } else {
            return $this->facetsOptions;
        }
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
