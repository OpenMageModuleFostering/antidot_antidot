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

        $file = "/tmp/antidot/unittest_send.xml";
        file_put_contents($file,"<categories xmlns=\"http://ref.antidot.net/store/afs#\"></categories>");

        $exportModel = Mage::getModel('Antidot/export_product');
        $curlConnector = new SAI_CurlStub();
        //Set response for upload
        $pafLiveResponse = <<<JSON
{"x:type":"ws.response","query":{"x:type":"ws.response.query","locale":"*","parameters":{"x:type":"collection","x:values":[]},"properties":{"x:type":"x:dynamic"}},"result":{"x:type":"PushPafContentReply","jobId":3641,"started":true,"uuid":"e4fe5bfa-dcc7-409d-b688-288cc62e314e"}}
JSON;
        $curlConnector->setResponse($pafLiveResponse);

        $result = $transport->send($file, $exportModel, $curlConnector);

        $this->assertNull($result);

    }

}
