<?php


class Extend_Warranty_Model_Api_Sync_Orders_Handler
{

    const ENDPOINT_URI = 'orders';

    const BATCH_CREATE_ORDER_ENDPOINT_URI = 'orders/batch';

    const SEARCH_ORDER_ENDPOINT_URI = 'orders/search';

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

    public function sync($ordersCollection, $batch)
    {
        $data = [];
        foreach ($ordersCollection as $order) {
            if ($order->hasWasSent() && $order->getWasSent()) {
                continue;
            }
            $orderData = Mage::getModel('warranty/api_databuilder_order')->build($order);
            if ($orderData && $this->checkOrderBeforeSync($orderData)) {
                $data[] = $orderData;
            }
        }

        try {
            if ($data) {
                $response = Mage::getModel('warranty/api_connector')->call(
                    self::BATCH_CREATE_ORDER_ENDPOINT_URI,
                    \Zend_Http_Client::POST,
                    $data
                );
                $responseArray = json_decode($response, true);

                Mage::getModel('warranty/logger')->info('Synced ' . count($data) . ' orders in batch ' . $batch);

                $syncedData = array();
                foreach ($responseArray as $name => $section) {
                    $info = array_column($section, 'transactionId');
                    $syncedData[$name] = $info;
                }
                Mage::getModel('warranty/logger')->info('', $syncedData, 'Synced Data');
            }
            $this->saveSyncedOrders($ordersCollection);
        } catch (Zend_Http_Client_Exception $e) {
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            Mage::getModel('warranty/logger')->critical($e->getMessage());
            throw new Exception($e->getMessage());
        }

    }

    /**
     * Check order data if it should be synced
     * @param $orderData
     * @return bool
     */
    protected function checkOrderBeforeSync($orderData)
    {
        if (count($orderData['lineItems']) == 0) {
            return false;
        }

        if (!$orderData['customer']) {
            return false;
        }

        foreach ($orderData['lineItems'] as $lineItem) {
            if ($lineItem['plane'] || $lineItem['leadToken']) {
                return false;
            }
        }
        /** In case the order should be checked on extend side before send */
//        if ($this->loadExtendOrder($orderData['transactionId'])) {
//            return false;
//        }
        return true;
    }

    /**
     * @param $orders
     * @return $this
     * @throws Exception
     */
    protected function saveSyncedOrders($orders)
    {
        foreach ($orders as $syncedOrder) {
            $model = Mage::getModel('warranty/historicalOrder');
            $model
                ->setId($syncedOrder->getId())
                ->load($syncedOrder->getId())
                ->setData('was_sent', true)
                ->save();
        }
        return $this;
    }

    public function loadExtendOrder($transactionId)
    {
        $extendOrder = $this->getConnector()->call(
            self::SEARCH_ORDER_ENDPOINT_URI . "?transactionId=" . $transactionId,
            \Zend_Http_Client::GET
        );

        $extendOrderObj = json_decode($extendOrder);
        if ($extendOrderObj) {
            return $extendOrderObj;
        }
        return false;
    }
}