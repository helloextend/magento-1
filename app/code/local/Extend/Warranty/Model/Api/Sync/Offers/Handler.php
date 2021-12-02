<?php


class Extend_Warranty_Model_Api_Sync_Offers_Handler
{
    const ENDPOINT_URI = 'offers?storeId=%s&productId=%s';

    /**
     * @param $contract
     * @return string
     * @throws Exception
     */
    public function getOffers($productId)
    {
        try {
            $storeId = Mage::helper('warranty/connector')->getApiStoreId();
            $endpoint = sprintf(self::ENDPOINT_URI, $storeId, $productId);

            $response = Mage::getModel('warranty/api_connector')
                ->call(
                    $endpoint
                );

            return $this->processResponse($response);
        } catch (Zend_Http_Client_Exception $e) {
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            Mage::getModel('warranty/logger')->critical($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @param string $response
     * @return string
     */
    private function processResponse($response)
    {

        $responseBody = [];
        $responseBodyJson = $response;

        if ($responseBodyJson) {
            $responseBody = json_decode($responseBodyJson, true);
        }

        return $responseBody;
    }
}