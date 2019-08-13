<?php

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

