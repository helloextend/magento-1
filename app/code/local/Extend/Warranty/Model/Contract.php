<?php

class Extend_Warranty_Model_Contract
{
    /**
     * @param Mage_Sales_Model_Order $order
     * @param $warranties
     */
    public function createContract($order, $warranties)
    {
        try {
            $contracts = Mage::getModel('warranty/api_databuilder_contract')->prepareInfo($order, $warranties);
            foreach ($contracts as $key => $contract) {
                //validate qty of contracts required
                if ($contract['product']['qty'] > 1) {
                    $contractIds = [];
                    $tempcontract = $contract;
                    unset($tempcontract['product']['qty']);
                    for ($x = 1; $x <= $contract['product']['qty']; $x++) {
                        $contractIds[$x] = Mage::getModel('warranty/api_sync_contract_handler')->create($contract);
                        //array_push($contractIds,$this->contractsRequest->create($contract));
                    }
                    unset($tempcontract);
                    $contractId = json_encode($contractIds);
                    unset($contractIds);
                } else {
                    $contractId = json_encode(array('1' => Mage::getModel('warranty/api_sync_contract_handler')->create($contract)));
                }

                if (!empty($contractId)) {
                    $items = $order->getAllItems();
                    if (isset   ($items[$key]) && empty($items[$key]->getContractId())) {
                        $items[$key]->setContractId($contractId);

                        $options = $items[$key]->getProductOptions();

                        $options = array_merge($options, ['refund' => false]);

                        $items[$key]->setProductOptions($options);

                        if ($order->getId()) {
                            $items[$key]->save();
                        }
                    }
                }
            }
        } catch (Exception $e) {
            Mage::getModel('warranty/logger')->error('Error while creating warranty contract', ['message' => $e->getMessage()]);
        }
    }

    public function refundContract($item, $contractIds)
    {
        if (!is_array($contractIds)) {
            $contractIds = [$contractIds];
        }

        $currentContracts = json_decode($item->getContractId()) === NULL ?
            [$item->getContractId()] : json_decode($item->getContractId(), true);

        $success = true;

        foreach ($contractIds as $_contractId) {
            $refundResponse = Mage::getModel('warranty/api_sync_contract_handler')->refund($_contractId);

            // Refunds log
            $response_log[] = [
                "contract_id" => $_contractId,
                "response" => $refundResponse
            ];

            if ($refundResponse == true) {
                if (($key = array_search($_contractId, $currentContracts)) !== false) {
                    unset($currentContracts[$key]);
                }
            } else {
                $success = false;
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
        $item->setProductOptions($options);
        $item->setContractId(json_encode($currentContracts));
        $item->save();
        return $success;
    }

    public function validateContractRefund($contractIds)
    {
        if (!is_array($contractIds)) {
            $contractIds = [$contractIds];
        }

        $amountValidated = 0;
        if (!Mage::helper('warranty/connector')->isOrdersApiEnabled()) {
            foreach ($contractIds as $_contractId) {
                $_response = Mage::getModel('warranty/api_sync_contract_handler')->validateRefund($_contractId);
                if (!empty($_response["refundAmount"]["amount"])) {
                    $amountValidated += $_response["refundAmount"]["amount"];
                }
            }
        }
        //Cent to dollars
        if ($amountValidated > 0) {
            $amountValidated /= 100;
        }
        return $amountValidated;
    }
}
