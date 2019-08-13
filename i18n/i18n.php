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

$ref = __DIR__."/en_US";
$toTranslate = array(
    'es_ES' => array('es_ES', 'es_AR', 'es_CL', 'es_CO', 'es_CR', 'es_MX', 'es_PA', 'es_PE', 'es_VE'),
    'fr_FR' => array('fr_FR', 'fr_CA'),
    'de_DE' => array('de_DE', 'de_CH', 'de_AT')
);

foreach($toTranslate as $lang => $countries) {
    foreach($countries as $country) {
        if(!is_dir(__DIR__.'/../app/locale/'.$country)) {
            mkdir(__DIR__.'/../app/locale/'.$country);
        }

        $refLines       = file($ref);
        $translateLines = file(__DIR__.'/'.$lang);

        echo __DIR__.'/'.$lang."\n";
        echo count($translateLines)."\n";

        $file = fopen(__DIR__.'/../app/locale/'.$country.'/MDN_Antidot.csv', 'w');
        foreach($refLines as $line => $expr) {
            $translate = array(trim($expr), trim($translateLines[$line]));
            fputcsv($file, $translate, ",");
        }
    }
}

