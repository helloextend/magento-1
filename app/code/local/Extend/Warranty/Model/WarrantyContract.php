<?php

class Extend_Warranty_Model_WarrantyContract
{
    /**
     * @param OrderInterface $order
     * @param $warranties
     */
    public function createContract($order, $warranties)
    {

        try {
            $contracts = $this->contractBuilder->prepareInfo($order, $warranties);

            foreach ($contracts as $key => $contract) {
                //validate qty of contracts required
                if ($contract['product']['qty']>1) {
                    $contractIds = [];
                    $tempcontract = $contract;
                    unset($tempcontract['product']['qty']);
                    for ($x=1; $x<=$contract['product']['qty']; $x++) {
                        $contractIds[$x]= $this->contractsRequest->create($contract);
                        //array_push($contractIds,$this->contractsRequest->create($contract));
                    }
                    unset($tempcontract);
                    $contractId=json_encode($contractIds);
                    unset($contractIds);
                } else {
                    $contractId = json_encode(array('1'=>$this->contractsRequest->create($contract)));
                }

                if (!empty($contractId)) {
                    $items = $order->getAllItems();
                    if (isset($items[$key]) && empty($items[$key]->getContractId())) {
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

        } catch (NoSuchEntityException $exception) {
            $this->logger->error('Error while creating warranty contract');
        }
    }
}
