<?php

class Extend_Warranty_Adminhtml_ContractController extends Mage_Adminhtml_Controller_Action
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
            $amountValidated = 0;
            foreach ($contractId as $_contractId) {
                $_response = Mage::getModel('warranty/api_sync_contract_handler')->validateRefund($_contractId);
                if (!empty($_response["refundAmount"]["amount"])) {
                    $amountValidated += $_response["refundAmount"]["amount"];
                }
            }

            //Cent to dollars
            if ($amountValidated > 0) {
                $amountValidated /= 100;
            }

            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(
                Mage::helper('core')->jsonEncode(["amountValidated" => $amountValidated])
            );
            $this->getResponse()->setHttpResponseCode(200);
            return;
        }

        $itemId = (string)$this->getRequest()->getParam('itemId');
        $item = Mage::getModel('sales/order_item')->load($itemId);
        $options = $item->getProductOptions();
        $response_log = empty($options['refund_responses_log']) ? [] : $options['refund_responses_log'];

        $currentContracts = json_decode($item->getContractId()) === NULL ?
            [$item->getContractId()] : json_decode($item->getContractId(), true);

        $refundHadErrors = false;

        foreach ($contractId as $_contractId) {
            $refundResponse = Mage::getModel('warranty/api_sync_contract_handler')->refund($_contractId);

            // Refunds log
            $response_log[] = [
                "contract_id" => $_contractId,
                "response"    => $refundResponse
            ];

            if ($refundResponse == true) {
                if (($key = array_search($_contractId, $currentContracts)) !== false) {
                    unset($currentContracts[$key]);
                }
            } else {
                $refundHadErrors = true;
            }
        }

        //All contracts are refunded
        $options['refund'] = false;
        if (empty($currentContracts)) {
            $options['refund'] = true;
        }

        //At least one error return 500 error code
        $this->getResponse()->setHttpResponseCode(200);
        if ($refundHadErrors) {
            $this->getResponse()->setHttpResponseCode(500);
        }

        $options['refund_responses_log'] = $response_log;
        $item->setProductOptions($options);
        $item->setContractId(json_encode($currentContracts));
        $item->save();
    }
}
