<?php


class Extend_Warranty_Model_Api_Sync_Orders_Handler
{

    const ENDPOINT_URI = 'orders';


    /**
     * @return Extend_Warranty_Model_Api_Connector
     */
    protected function getConnector()
    {
        return Mage::getModel('warranty/api_connector');
    }

    /**
     * Response status codes
     */
    const STATUS_CODE_SUCCESS = 200;

    /**
     * Create an order
     *
     * @param array $orderData
     * @return array
     */
    public function create(array $orderData)
    {
        $url = self::ENDPOINT_URI;
        try {
            $response = $this->getConnector()->call(
                $url,
                \Zend_Http_Client::POST,
                $orderData
            );
            $responseBody = $this->processResponse($response);
            foreach ($responseBody['lineItems'] as $lineItem) {
                $contractIds[] = $lineItem['contractId'];
            }

            $orderId = isset($responseBody['id']) && $responseBody['id'] ? $responseBody['id'] : '';
            if ($orderId) {
                Mage::getModel('warranty/logger')->info('Order is created successfully. OrderID: ' . $orderId);
            } else {
                Mage::getModel('warranty/logger')->error('Order creation is failed.');
            }
        } catch (Exception $exception) {
            Mage::getModel('warranty/logger')->error($exception->getMessage());
        }

        return $responseBody;
    }

    protected function getUuid4()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Process response
     *
     * @param string $response
     * @return array
     */
    protected function processResponse($responseBodyJson)
    {
        $responseBody = [];

        if ($responseBodyJson) {
            $responseBody = json_decode($responseBodyJson, true);

            if (isset($responseBody['customer'])) {
                $depersonalizedBody = $responseBody;
                $depersonalizedBody['customer'] = [];
                $rawBody = json_encode($depersonalizedBody);
            } else {
                $rawBody = $responseBodyJson;
            }

            Mage::getModel('warranty/logger')->info('Response: ' . PHP_EOL . $rawBody);
        } else {
            Mage::getModel('warranty/logger')->error('Response body is empty.');
        }

        return $responseBody;
    }
}