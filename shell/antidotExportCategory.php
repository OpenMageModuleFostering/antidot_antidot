<?php

require_once 'abstract.php';

class MDN_Shell_AntidotExportCategory extends Mage_Shell_Abstract
{

    /**
     * Run script
     *
     */
    public function run()
    {
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        $start = time();

        Mage::getModel('Antidot/Observer')->categoriesFullExport();

        return true;
    }
}

$shell = new MDN_Shell_AntidotExportCategory();
$shell->run();
