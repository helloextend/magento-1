<?php

class Extend_Warranty_Model_Api_Sync_Refund_Handler
{

    const ENDPOINT_URI = 'refunds';


    public function refundLineItem($lineItemId)
    {
        $payload = ['lineItemId' => $lineItemId];

        return $this->refund($payload);
    }

    public function refundContract($contractId)
    {
        $payload = ['contractId' => $contractId];

        return $this->refund($payload);
    }

    public function refundOrder($orderId)
    {
        $payload = ['orderId' => $orderId];

        return $this->refund($payload);
    }

    /**
     * @param $payload
     * @return false|mixed
     */
    protected function refund($payload)
    {
        try {
            $endpoint = self::ENDPOINT_URI;
            $response = Mage::getModel('warranty/api_connector')
                ->call(
                    $endpoint,
                    \Zend_Http_Client::POST,
                    $payload
                );

            $result = json_decode($response, true);
            return $result;
        } catch (\Zend_Http_Client_Exception $e) {
            Mage::getModel('warranty/logger')->error($e->getMessage(), ['exception' => $e]);
            return false;
        }
    }
}