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
            if ($result['id']) {
                $order->setExtendOrderId($result['id']);
                $order->save();
            }

            $lineItems = [];
            foreach ($result['lineItems'] as $lineItem) {
                if (isset($lineItem['id']) && $lineItem['id']) {
                    $lineItems[$lineItem['lineItemTransactionId']]['lineItemId'][] = $lineItem['id'];
                }
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
                        $item->setExtendLineItemId(json_encode($lineItems[$orderTransactionId]['lineItemId']));
                    }

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


    public function validateRefundContracts($contractIds)
    {
        $this->refundItem($contractIds, true);
    }

    /**
     * @param Mage_Sales_Model_Order_Item $item
     * @return void
     */
    public function refundContracts($item, $contractIds)
    {
        if (!is_array($contractIds)) {
            $contractIds = [$contractIds];
        }

        $refundsHandler = Mage::getModel('warranty/api_sync_refunds_handler');

        $result = [];
        foreach ($contractIds as $contractId) {
            $refundResult[$contractId] = $this->processResult($refundsHandler->refundContract($contractId));
        }

        $this->processRefundResult($item, $refundResult);
        return $result;
    }

    protected function processRefundResult($item, $refundResult)
    {
        $currentContractId = $item->getContractId();
        $currentLineItemId = $item->getExtendLineItemId();

        foreach ($refundResult as $result) {
            if ($result === false) {
                continue;
            }

            if (isset($result['contractId'])) {

            }
        }
//        $item->setContractId();
//        $item->setExtendLineItemId();
//        $item->setProductOptions($options);
    }
}
