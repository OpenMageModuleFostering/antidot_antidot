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
class MDN_Antidot_Model_Transport_Http extends MDN_Antidot_Model_Transport_Abstract implements MDN_Antidot_Model_Transport_Interface
{

    protected $afsHost;
    protected $afsService;
    protected $afsStatus;
    protected $afsUser;
    protected $afsPasswd;

    /**
     * Init Antidot API
     */
    public function _construct($host = 'bo-store.afs-antidot.net')
    {
        parent::_construct();

        $this->afsHost = $host;

        require_once "antidot/afs_lib.php";

        if ($config = Mage::getStoreConfig('antidot/web_service')) {

            if (isset($config['service'])) {
                $this->afsService = (int)$config['service'];
            }
            if (isset($config['status'])) {
                $this->afsStatus = $config['status'];
            }
        }
        if ($config = Mage::getStoreConfig('antidot/ftp')) {

            if (isset($config['username'])) {
                $this->afsUser = $config['username'];
            }
            if (isset($config['passwd'])) {
                $this->afsPasswd = $config['passwd'];
            }

        }
    }


    /**
     * {@inherit}
     */
    public function send($file, $exportModel, SAI_CurlInterface $curlConnector=null)
    {

        /**
         * see http://antidot.github.io/PHP_API/doc/html/classAfsPafConnector.html
         */
        $auth = new AfsUserAuthentication($this->afsUser, $this->afsPasswd, null);

        $service = new AfsService($this->afsService, $this->afsStatus);

        $doc = new AfsDocument(file_get_contents($file));

        $connector = new AfsPafConnector($this->afsHost, $service, $exportModel->getPafName(), $auth, AFS_SCHEME_HTTPS, $curlConnector);

        /** @var AfsPafUploadReply $result */
        $result = $connector->upload_doc($doc);

        if ($result->in_error()) {
            throw new Exception("Can't send the file (".$result->get_error().")");
        }


    }
}
