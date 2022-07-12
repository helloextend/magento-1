<?php

class Extend_Warranty_Model_Observer_Syncorder
{
    /**
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function execute(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('warranty/connector');
        if (!$helper->isModuleEnabled()
            || !$helper->isOrdersApiEnabled()) {
            return;
        }

        $invoice = $observer->getEvent()->getInvoice();
        $order = $invoice->getOrder();

        Mage::getModel('warranty/order')->createOrder($order);
    }
}
