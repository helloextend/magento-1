<?php

class Extend_Warranty_Model_Api_Connector
{
    const API_VERSION = '2021-07-01';

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

    /**
     * @return mixed
     * @throws Zend_Http_Client_Exception
     */
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
    )
    {
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

        Mage::getModel('warranty/logger')->info('Request Data, Method: ' . $method . ', Endpoint URI: ' . $apiUri, $data, 'Request Body');
        $response = $client->request();

        if ($response->isError()) {
            $responseBody = json_decode($response->getBody());
            $message = !empty($responseBody->message) ? $responseBody->message : $response->getMessage();
            Mage::getModel('warranty/logger')->critical($message);
            throw new Zend_Http_Client_Exception($message);
        }

        Mage::getModel('warranty/logger')->info('Response Data, Method: ' . $method . ', Endpoint URI: ' . $apiUri, $response, 'Response');
        return $response->getBody();
    }

    /**
     * @return Zend_Http_Client
     * @throws Zend_Http_Client_Exception
     */
    protected function initClient()
    {
        $client = new Zend_Http_Client();
        $client->setHeaders(
            array(
                'Accept' => ' application/json; version=' . self::API_VERSION,
                'Content-Type' => ' application/json',
                'X-Extend-Access-Token' => Mage::helper('warranty/connector')->getApiKey(),
                'X-Idempotency-Key' => Mage::helper('warranty/connector')->getUuid4()
            )
        );
        $client->setConfig(array('timeout' => 20));

        return $client;
    }
}
