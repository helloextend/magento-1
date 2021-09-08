<?php

class Extend_Warranty_Model_Api_Sync_Products_Handler
{
    const ENDPOINT_URI = 'products';

    /**
     * @param $productsCollection
     * @param $currentBatch
     * @throws Exception
     */
    public function sync($productsCollection, $currentBatch)
    {

        $data = [];
        foreach ($productsCollection as $product) {
            $data[] = Mage::getModel('warranty/api_databuilder_product')->build($product);
        }
        try {
            $response = Mage::getModel('warranty/api_connector')->call(
                self::ENDPOINT_URI . '?batch=1&upsert=1',
                \Zend_Http_Client::POST,
                $data
            );
            $responseArray = json_decode($response);
            Mage::getModel('warranty/logger')->info('Synced ' . count($data) . ' products in batch ' . $currentBatch);
            $syncedData = array();
            foreach ($responseArray as $name => $section) {
                $info = array_column($section, 'referenceId');
                $syncedData[$name] = $info;
            }
            Mage::getModel('warranty/logger')->info($syncedData, '', 'Synced Data');
        } catch (Zend_Http_Client_Exception $e){
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            Mage::getModel('warranty/logger')->critical($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }
}
