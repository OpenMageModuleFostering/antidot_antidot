<?php


class MDN_Antidot_Test_Model_Transport_Http extends EcomDev_PHPUnit_Test_Case
{

    public static function setUpBeforeClass()
    {
       //avoid errors when session_start is called during the test
        @session_start();
    }

	/**
     * MCNX-68 : Replace sftp upload by PaF WS Upload
     * 
     * @test
     * @loadFixture
     */
    public function testSend() {

        require_once("antidot/COMMON/php-SAI/lib/CurlStub.php");

    	/** @var $observer MDN_Antidot_Model_Transport_Http */
    	$transport = Mage::getModel('Antidot/transport_http');

        $baseDirectory = sys_get_temp_dir().DS;
        if (Mage::getStoreConfig('antidot/ftp/working_directory'))
            $baseDirectory = Mage::getStoreConfig('antidot/ftp/working_directory').DS;

        $tmpDirectory = $baseDirectory.'antidot'.DS;

        if(!is_dir($tmpDirectory)) {
            mkdir($tmpDirectory, 0775, true);
        }

        $file = $tmpDirectory . DS . "unittest_send.xml";
        file_put_contents($file,"<categories xmlns=\"http://ref.antidot.net/store/afs#\"></categories>");

        $exportModel = Mage::getModel('Antidot/export_product');
        $curlConnector = new SAI_CurlStub();

        //Set BO response for AboutConnector
        $aboutRequestOpts = array(CURLOPT_URL => "https://bo-store.afs-antidot.net/bo-ws/about");
        $aboutResponse = <<<JSON
{
  "x:type":"ws.response",
  "query":{
    "x:type":"ws.response.query",
    "parameters":{
      "x:type":"collection",
      "x:values":[

      ]
    },
    "properties":{
      "x:type":"x:dynamic"
    }
  },
  "result":{
    "x:type":"bows.about",
    "boWsVersion":{
      "x:type":"AfsVersion",
      "build":"3eaebfd1f1fe261780347cbc35bfbd65d613575e",
      "gen":"7.7",
      "major":"4",
      "minor":"0",
      "motto":"Pink Dolphin"
    },
    "copyright":"Copyright (C) 1999-2013 Antidot"
  }
}
JSON;
        $curlConnector->setResponse($aboutResponse, $aboutRequestOpts);

        //Set response for upload
        $pafLiveResponse = <<<JSON
{"x:type":"ws.response","query":{"x:type":"ws.response.query","locale":"*","parameters":{"x:type":"collection","x:values":[]},"properties":{"x:type":"x:dynamic"}},"result":{"x:type":"PushPafContentReply","jobId":3641,"started":true,"uuid":"e4fe5bfa-dcc7-409d-b688-288cc62e314e"}}
JSON;
        $curlConnector->setResponse($pafLiveResponse);

        $result = $transport->send($file, $exportModel, $curlConnector);

        $this->assertNull($result);

        unlink($file);

    }

}
