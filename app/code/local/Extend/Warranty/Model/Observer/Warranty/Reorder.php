<?php

class Extend_Warranty_Model_Observer_Warranty_Reorder
{
    /**
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function execute(Varien_Event_Observer $observer)
    {
        $items = $observer->getItems();
        $item = reset($items);
        if (
            $item->getProductType() === Extend_Warranty_Model_Product_Type::TYPE_CODE
            && $item->getProduct()->getCustomOption('info_buyRequest')
        ) {
            try {
                $buyRequest = unserialize($item->getProduct()->getCustomOption('info_buyRequest')->getValue());
                $warrantyHelper = Mage::helper('warranty');
                $price = !empty($buyRequest['price']) ? $buyRequest['price'] : 0;
                $price = $warrantyHelper->removeFormatPrice($price);
                $item->setCustomPrice($price);
                $item->setOriginalCustomPrice($price);
                $item->getProduct()->setIsSuperMode(true);
                $quote = Mage::getModel('checkout/session')->getQuote();
                $quote->collectTotals()->save();
            } catch (Exception $e) {
                Mage::getSingleton('core/session')->addError('Oops! There was an error adding the protection plan product.');
            }
        }
    }
}
