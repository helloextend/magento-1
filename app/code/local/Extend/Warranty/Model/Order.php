<?php

class Extend_Warranty_Model_Order
{
    /**
     * @param Mage_Sales_Model_Order $orderPayload
     * @param $warranties
     */
    public function createOrder($order)
    {
        try {
            /**
             * @var Extend_Warranty_Model_Api_Databuilder_Order $orderDataBuilder
             */
            $orderDataBuilder = Mage::getModel('warranty/api_databuilder_order');

            $orderPayload = $orderDataBuilder->prepareInfo($order);

            $result = Mage::getModel('warranty/api_sync_orders_handler')->create($orderPayload);

            $lineItems = [];
            foreach ($result['lineItems'] as $lineItem) {
                if (isset($lineItem['contractId']) && $lineItem['contractId']) {
                    $lineItems[$lineItem['lineItemTransactionId']]['contractId'][] = $lineItem['contractId'];
                }
            }

            foreach ($order->getAllItems() as $item) {
                $orderTransactionId = $orderDataBuilder->encodeId($item->getId());
                if (isset($lineItems[$orderTransactionId]) && $lineItems[$orderTransactionId]) {
                    if ($item->getProductType() == Extend_Warranty_Model_Product_Type::TYPE_CODE) {
                        $options = $item->getProductOptions();
                        $options = array_merge($options, ['refund' => false]);
                    }

                    $item->setProductOptions($options);

                    if (isset($lineItems[$orderTransactionId]['contractId'])) {
                        $item->setContractId(json_encode($lineItems[$orderTransactionId]['contractId']));
                    }
                    $item->save();
                }
            }
            return $result;
        } catch (Exception $e) {
            Mage::getModel('warranty/logger')->error('Error while creating warranty contract', ['message' => $e->getMessage()]);
        }
    }


    /**
     * @param Mage_Sales_Model_Order_Item $item
     * @return void
     */
    public function refundContract($item, $contractIds)
    {
        if (!is_array($contractIds)) {
            $contractIds = [$contractIds];
        }

        $refundsHandler = Mage::getModel('warranty/api_sync_refund_handler');

        $refundResult = [];
        foreach ($contractIds as $contractId) {
            $refundResult[$contractId] = $refundsHandler->refundContract($contractId);
        }

        return $this->processRefundResult($item, $refundResult);
    }

    /**
     * @param $item
     * @param $refundResult
     * @return bool
     */
    protected function processRefundResult($item, $refundResult)
    {
        $hasErrors = false;
        $currentContracts = json_decode($item->getContractId()) === NULL ?
            [$item->getContractId()] : json_decode($item->getContractId(), true);

        foreach ($refundResult as $result) {
            if ($result === false) {
                $hasErrors = true;
                continue;
            }

            if (isset($result['contractId'])) {
                if (($key = array_search($result['contractId'], $currentContracts)) !== false) {
                    unset($currentContracts[$key]);
                }
            }
        }
        $options = $item->getProductOptions();

        $response_log = empty($options['refund_responses_log']) ? [] : $options['refund_responses_log'];

        //All contracts are refunded
        $options['refund'] = false;
        if (empty($currentContracts)) {
            $options['refund'] = true;
        }

        $options['refund_responses_log'] = $response_log;

        $item->setContractId(json_encode($currentContracts));
        $item->setProductOptions($options);
        $item->save();

        return !$hasErrors;
    }
}
