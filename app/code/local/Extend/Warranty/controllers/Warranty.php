<?php

class Extend_Warranty_Warranty extends Mage_Adminhtml_Controller_Action
{

    /**
     *
     */
    public function addAction()
    {
        try {
            $warranty = Mage::helper('warranty')->getWarrantyProduct();
            $warrantyData = $this->getRequest()->getPost('warranty');
            $quote = $this->_getSession()->getQuote();

            if (!$warranty) {
                $data = ["status"=>"fail"];
            }
            $warranty = Mage::getModel('catalog/product')->load($warranty->getId());
            $quote->addProduct($warranty->getId(), $warrantyData);
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
