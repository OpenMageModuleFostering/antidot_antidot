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
class MDN_Antidot_Model_Observer extends Mage_Core_Model_Abstract
{
    const GENERATE_FULL = 'FULL';
    const GENERATE_INC = 'INC';

    /**
     * @var string @tmpDirectory
     */
    private $tmpDirectory;
    private $tmpErrorDirectory;
    
    /**
     * @var string Uniq request Id
     */
    private $request;
    
    /**
     * @var int Begin timestamp
     */
    private $begin;
    
    /**
     * @var string Current generation type
     */
    private $type;
    
    /**
     * Init the controller
     */
    protected function _construct()
    {
        $this->request      = uniqid();
        $this->begin        = microtime(true);
        $this->initTmpDirectory();

        ini_set('memory_limit', '1024M');
    }

    /**
     * Init the tmp directory
     */
    protected function initTmpDirectory()
    {
        $baseDirectory = sys_get_temp_dir().DS;

        if (Mage::getStoreConfig('antidot/ftp/working_directory'))
            $baseDirectory = Mage::getStoreConfig('antidot/ftp/working_directory').DS;

        $this->tmpDirectory = $baseDirectory.'antidot'.DS;
        Mage::log('Working directory is '.$this->tmpDirectory, null, 'antidot.log');

        $this->tmpErrorDirectory = $this->tmpDirectory.'error'.DS;
        Mage::log('Error directory is '.$this->tmpErrorDirectory, null, 'antidot.log');

        if(!is_dir($this->tmpDirectory)) {
            $r1 = mkdir($this->tmpDirectory, 0775, true);
            $r2 = mkdir($this->tmpErrorDirectory, 0775, true);
            Mage::log('Directories do not exist, create them : '.(($r1 && $r2) ? ' OK' : ' NOK '), null, 'antidot.log');
        }
    }
    
    /**
     * Generate the full catalog file
     */
    public function catalogFullExport() 
    {
        $this->log('FULL EXPORT');
        $this->generate(Mage::getModel('Antidot/Export_Product'), self::GENERATE_FULL);
    }
    
    /**
     * Generate the  inc catalog file
     */
    public function catalogIncExport() 
    {
        $this->generate(Mage::getModel('Antidot/Export_Product'), self::GENERATE_INC);
    }
    
    /**
     * Generate the category file
     */
    public function categoriesFullExport() 
    {
        $this->generate(Mage::getModel('Antidot/Export_Category'), self::GENERATE_FULL);
    }
    
    /**
     * Generate files
     * 
     * @param Antidot/Export_* $exportModel
     * @param string $type
     * @throws Exception
     */
    protected function generate($exportModel, $type)
    {
        $this->type = $exportModel::TYPE;
        $this->log('start');
        $log['begin'] = time();
        $log['items'] = 0;
        $log['error'] = array();
        $log['reference'] = '';
        
        try
        {
        
            $files = array();
            foreach($this->getDefaultContext() as $context) {
                $this->log('generate '.$exportModel::TYPE.' '.$context['owner']);
                $context['store_id'] = array_keys($context['stores']);

                $filename = $this->tmpDirectory.sprintf($exportModel::FILENAME_XML, $type, $context['lang']);
                $items    = $exportModel->writeXml($context, $filename, $type);
                if($items === 0) {
                    $this->log('No items to export');
                    continue;
                }

                $log['items']+= $items;
                if ($this->schemaValidate($filename, $exportModel::XSD)) {
                    $this->log('Schema validated');
                    $files[] = $filename;
                } else {
                    $this->fileError($filename);

                    $errors = Mage::helper('Antidot/XmlWriter')->getErrors();
                    $this->log('xml schema not valid '.print_r($errors, true));
                    Mage::helper('Antidot')->sendMail('Export failed', print_r($errors, true));
                    foreach($errors as $error)
                        $log['error'][] = $error;
                    continue;
                }
            }

            if($log['items'] === 0) {
                $this->log('No items available, cancel export');
                return;
            }

            $log['reference'] = 'unknown';
            if(!empty($files)) {
                $this->log('Compress files');
                $filenameZip = $type === self::GENERATE_INC ? $exportModel::FILENAME_ZIP_INC : $exportModel::FILENAME_ZIP;
                $filename = $this->compress($files, $filenameZip);
                $log['reference'] = md5($filename);
                $this->send($filename);

                $log['status'] = 'SUCCESS';
            } else {
                $log['status'] = 'FAILED';
                $lastError = current(Mage::helper('Antidot/XmlWriter')->getErrors());
                if ($lastError)
                   $log['error'][] = $lastError;
            }

            if(file_exists($filename)) {
                $this->log('Unlink file');
                unlink($filename);
            }
        }
        catch(Exception $ex)
        {
            $log['error'][] = $ex->getMessage();
            $log['status'] = 'FAILED';
        }
        
        $log['end'] = time();
        $this->log('generate '.$exportModel::TYPE.' '.$context['owner']);
        $this->log('end');

        Mage::helper('Antidot/LogExport')->add($log['reference'], $type, $exportModel::TYPE, $log['begin'], $log['end'], $log['items'], $log['status'], implode(',', $log['error']));
    }

    /**
     * Move file with error to another directory $tmp/antidot/error
     *
     * @param string $file
     */
    protected function fileError($file)
    {
        $files = array();
        if ($handle = opendir($this->tmpErrorDirectory)) {
            while ($fileError = readdir($handle)) {
                if ($fileError != "." && $fileError != "..") {
                    $files[] = $fileError;
                }
            }
            closedir($handle);

            if(count($files) >= 5) {
                sort($files);
                unlink($this->tmpErrorDirectory.current($files));
            }

            $fileError  = $this->tmpErrorDirectory.time().'-'.basename($file);
            rename($file, $fileError);
        }
    }
   
   /**
    * Compress xml file
    * 
    * @param array $files
    * @param string $compressFile filename
    * @return path to file compressed
    */
   protected function compress($files, $compressFile)
    {
        $this->log('compress the file');
       
        $compressFile = dirname(current($files)).'/'.sprintf($compressFile, date('YmdHis'));
        Mage::helper('Antidot/Compress')->zip($files, $compressFile);
       
        return $compressFile;
   }
   
   /**
    * Send the file to antidot
    * 
    * @param string $filename
    * @return boolean
    */
   protected function send($filename)
    {
       $this->log('send the file');
       
       $transport = Mage::getModel('Antidot/Transport');
       
       return $transport->send($filename, $transport::TRANS_FTP);
   }
   
   /**
    * Check if the xml file is valid
    * 
    * @param string $filename xml file
    * @param string $xsd xsd file
    * @return boolean
    */
   protected function schemaValidate($filename, $xsd)
    {
        //disable schema validation
        if (Mage::getStoreConfig('antidot/xsd_verification/disable') == 1)
        {
            $this->log('schema validation is DISABLED');
            return true;
        }

       libxml_use_internal_errors(true);
       $this->log('schema validate');
       
       $xml = new DOMDocument();
       $xml->load($filename);
       
       return $xml->schemaValidate($xsd);
   }
   
   /**
    * Return the context default values
    * 
    * @todo retrieve these data from the db
    * @return array
    */
    private function getDefaultContext() 
    {
        $listStore = array();
        foreach (Mage::app()->getStores() as $store) {
            list($lang) = explode('_', Mage::getStoreConfig('general/locale/code', $store->getId()));
            $listStore[$lang][$store->getId()] = $store;
        }
        
        $listContext = array();
        foreach($listStore as $lang => $stores) {
            $defaultOwner      = 'AFS@Store for Magento v'.Mage::getConfig()->getNode()->modules->MDN_Antidot->version;
            $context['owner']  = Mage::getStoreConfig('antidot/general/owner') === '' ? $defaultOwner : Mage::getStoreConfig('antidot/general/owner');
            $context['lang']   = $lang;
            $context['stores'] = $stores;
            $context['langs']  = count($listStore);
            
            $listContext[] = $context;
        }
        
        return $listContext;
   }
   
    /**
     * Write message to log
     * 
     * @param string $action
     */
    private function log($action)
    {
        $message = '[antidot] ['.$this->type.'] ['.$this->request.'] '
                 .  memory_get_usage(true).' '
                 . $action.' ('.round(microtime(true)-$this->begin, 2)."sec)";

        Mage::log($message, null, 'antidot.log');
    }
}