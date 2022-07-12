<?php

class Extend_Warranty_Model_Observer_Warranty_Refund
{

    /**
     * @return Extend_Warranty_Helper_Connector
     */
    protected function getConnectorHelper()
    {
        return Mage::helper('warranty/connector');
    }

    /**
     * @return Extend_Warranty_Helper_Contract
     */
    protected function getContractHelper()
    {
        return Mage::helper('warranty/contract');
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function execute(Varien_Event_Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $order = $creditmemo->getOrder();

        $storeId = '';

        if (!$this->getConnectorHelper()->isExtendEnabled()
            || !$this->getConnectorHelper()->isRefundEnabled()
        ) {
            return;
        }


        $creditmemoItems = $creditmemo->getAllItems();
        foreach ($creditmemoItems as $creditmemoItem) {
            $orderItem = $creditmemoItem->getOrderItem();
            if ($orderItem->getProductType() !== Extend_Warranty_Model_Product_Type::TYPE_CODE) {
                continue;
            }

            $contractIds = $this->getContractHelper()->getOrderItemContractIds($orderItem);

            if (!$contractIds) {
                continue;
            }
            $this->getRefundProvider()->refundContract($orderItem, $contractIds);
        }
    }

    /**
     * @return
     */
    public function getRefundProvider()
    {
        if ($this->getConnectorHelper()->isOrdersApiEnabled()) {
            return Mage::getModel('warranty/order');
        } else {
            return Mage::getModel('warranty/contract');
        }
    }
}