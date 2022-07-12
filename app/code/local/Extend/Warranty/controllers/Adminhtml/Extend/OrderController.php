<?php

class Extend_Warranty_Adminhtml_Extend_OrderController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @throws Zend_Controller_Response_Exception
     */
    public function refundAction()
    {
        if (!Mage::helper('warranty/connector')->isExtendEnabled() || !Mage::helper('warranty/connector')->isRefundEnabled()) {
            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(
                Mage::helper('core')->jsonEncode(
                    [
                        'error' => 'Extend module or refunds are not enabled'
                    ]
                ));

            $this->getResponse()->setHttpResponseCode(403); //Forbidden error
        }

        $contractId = $this->getRequest()->getParam('contractId');

        $isValidationRequest = $this->getRequest()->getParam('validation');

        /* Validation Request */
        if ($isValidationRequest) {
            $this->validateContractsRefund($contractId);
            return;
        }

        $itemId = (string)$this->getRequest()->getParam('itemId');
        $item = Mage::getModel('sales/order_item')->load($itemId);

        if (Mage::helper('warranty/connector')->isOrdersApiEnabled()) {
            $result = Mage::getModel('warranty/order')->refundContract($item, $contractId);
        } else {
            $result = Mage::getModel('warranty/contract')->refundContract($item, $contractId);
        }

        //At least one error return 500 error code
        $this->getResponse()->setHttpResponseCode(200);
        if ($result !== true) {
            $this->getResponse()->setHttpResponseCode(500);
        }
    }

    protected function validateContractsRefund($contractId)
    {
        $amountValidated = Mage::getModel('warranty/contract')->validateContractRefund($contractId);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(
            Mage::helper('core')->jsonEncode(["amountValidated" => $amountValidated])
        );
        $this->getResponse()->setHttpResponseCode(200);
    }
}
