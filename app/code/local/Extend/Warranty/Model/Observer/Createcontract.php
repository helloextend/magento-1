<?php

class Extend_Warranty_Model_Observer_Createcontract
{
    /**
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function execute(Varien_Event_Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        $order = $invoice->getOrder();
        $warranties = [];
        $flag = 0;

        foreach ($order->getAllItems() as $key => $item) {
            if ($item->getProductType() === Extend_Warranty_Model_Product_Type::TYPE_CODE) {
                if (!$flag) {
                    $flag = 1;
                }
                $warranties[$key] = $item;
            }
        }

        if ($flag) {
            Mage::getModel('warranty/contract')->createContract($order, $warranties);
        }
    }
}
