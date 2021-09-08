<?php

class Extend_Warranty_Model_Api_Connector
{
    const API_VERSION = '2020-08-01';

    /**
     * @var Zend_Http_Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $uri;

    /**
     * @var Mage_Core_Model_Store
     */
    protected $_store;

    public function testConnection()
    {
        $response = $this->call(Extend_Warranty_Model_Api_Sync_Products_Handler::ENDPOINT_URI);
        return $response->isSuccessful();
    }

    /**
     * @param $endpoint
     * @param string $method
     * @param array|null $data
     * @return string
     * @throws Zend_Http_Client_Exception
     */
    public function call(
        $endpoint,
        $method = \Zend_Http_Client::GET,
        $data = null
    ) {
        $client = $this->initClient();
        $apiUri = Mage::helper('warranty/connector')->getEndpointUri($endpoint);
        $client->setUri($apiUri);
        $client->setMethod($method);

        if (
            isset($data) &&
            $method !== \Zend_Http_Client::GET
        ) {
            $client
                ->setRawData(
                    json_encode($data),
                    'application/json'
                );
        }

        if(!$data){
            $data = 'Request Body is Empty';
        }

        Mage::getModel('warranty/logger')->info($data, 'Method: ' . $method . ', Endpoint URI: ' . $apiUri, 'Request Body');
        $responce = $client->request();

        if ($responce->isError()) {
            $responseBody = json_decode($responce->getBody());
            $message = !empty($responseBody->message) ? $responseBody->message : $responce->getMessage();
            Mage::getModel('warranty/logger')->critical($message);
            throw new Zend_Http_Client_Exception($message);
        }

        return $responce->getBody();
    }

    /**
     * @return Varien_Http_Client
     * @throws Zend_Http_Client_Exception
     */
    protected function initClient()
    {
        $client = new Varien_Http_Client();
        $client->setHeaders(
            array(
                'Accept'                => ' application/json; version=' . self::API_VERSION,
                'Content-Type'          => ' application/json',
                'X-Extend-Access-Token' => Mage::helper('warranty/connector')->getApiKey()
            )
        );
        $client->setConfig(array('timeout' => 20));

        return $client;
    }
}
