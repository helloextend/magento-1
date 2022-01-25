<?php

class Extend_Warranty_Adminhtml_WarrantyController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @return void
     */
    public function addAction()
    {
        try {
            $warranty = Mage::helper('warranty')->getWarrantyProduct();
            $warrantyData = new Varien_Object($this->getRequest()->getParam('warranty'));
            $quote = $this->_getSession()->getQuote();
            if (!$warranty) {
                $data = ["status"=>"fail"];
            }
            $warrantyHelper = Mage::helper('warranty');
            $price = $warrantyHelper->removeFormatPrice($warrantyData->getPrice());
            $warranty = Mage::getModel('catalog/product')->load($warranty->getId());
            $quote->addProduct($warranty, $warrantyData);
            $item = $quote->getItemByProduct($warranty);
            $item->setCustomPrice($price);
            $item->setOriginalCustomPrice($price);
            $item->getProduct()->setIsSuperMode(true);
            $quote->collectTotals()->save();
            $data = ["status"=>"success"];
        } catch (\Exception $e) {
            $data = ["status"=>"fail"];
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($data));
    }

    /**
     * Check is allowed access to action
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }

    /**
     * @return Mage_Adminhtml_Model_Session|Mage_Core_Model_Abstract|null
     */
    protected function _getSession()
    {
        return Mage::getSingleton('adminhtml/session_quote');
    }
}
