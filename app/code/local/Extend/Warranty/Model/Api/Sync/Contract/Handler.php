<?php

class Extend_Warranty_Model_Api_Sync_Contract_Handler
{
    const ENDPOINT_URI = 'contracts';

    /**
     * @param $contract
     * @return string
     * @throws Exception
     */
    public function create($contract)
    {
        return $this->createRequest($contract);
    }

    /**
     * @param $contract
     * @return string
     * @throws Exception
     */
    private function createRequest($contract)
    {
        try {
            $response = Mage::getModel('warranty/api_connector')
                ->call(
                    self::ENDPOINT_URI,
                    \Zend_Http_Client::POST,
                    $contract
                );

            return $this->processCreateResponse($response);
        } catch (Zend_Http_Client_Exception $e) {
            throw new Exception($e->getMessage());
        } catch (UnexpectedValueException $e) {
            //TODO: Here we can add Email notification in case empty Contract Id
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            Mage::getModel('warranty/logger')->critical($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @param string $response
     * @return string
     */
    private function processCreateResponse($response)
    {
        $responseArray = json_decode($response, true);
        $contractId = !empty($responseArray['id']) ? $responseArray['id'] : '';
        if (!$contractId) {
            throw new UnexpectedValueException('Contract ID is Empty');
        }

        Mage::getModel('warranty/logger')->info(Mage::helper('warranty')->__('Contract #%s request successful', $contractId));
        return $contractId;
    }


    /**
     * @param $contractId
     * @return bool
     */
    public function refund($contractId)
    {
        return $this->refundRequest($contractId);
    }

    /**
     * @param $contractId
     * @return bool
     */
    private function refundRequest($contractId)
    {
        try {
            $endpoint = self::ENDPOINT_URI . "/{$contractId}/refund";
            $response = Mage::getModel('warranty/api_connector')
                ->call(
                    $endpoint,
                    \Zend_Http_Client::POST
                );

            return $this->processRefundResponse($response);
        } catch (\Zend_Http_Client_Exception $e) {
            Mage::getModel('warranty/logger')->error($e->getMessage(),['exception' => $e]);
            return false;
        }
    }

    /**
     * @param Zend_Http_Response $response
     * @return bool
     */
    private function processRefundResponse($response)
    {
        $response = json_decode($response, true);
        if (!empty($response['status'])) {
            Mage::getModel('warranty/logger')->info('Refund Request Success');
            return true;
        }

        //Refund Already Accepted
        try {
            if (
                isset($response["message"])
                && $response["message"] === "The contract has already been refunded") {
                Mage::getModel('warranty/logger')->info('Refund Request already processed');
                return true;
            }
        } catch (\Exception $e) {
            Mage::getModel('warranty/logger')->error($e->getMessage(),['exception' => $e]);
            return false;
        }

        Mage::getModel('warranty/logger')->error('Refund Request Fail',$response);
        return false;
    }

    /**
     * @param $contractId
     * @return false|mixed
     */
    public function validateRefund($contractId)
    {
        return $this->validateRefundRequest($contractId);
    }

    /**
     * @param $contractId
     * @return false|mixed
     */
    private function validateRefundRequest($contractId)
    {
        try {
            $endpoint = self::ENDPOINT_URI . "/{$contractId}/refund?commit=false";

            $response = Mage::getModel('warranty/api_connector')
                ->call(
                    $endpoint,
                    \Zend_Http_Client::POST
                );

            $bodyArray = json_decode($response, true);
            if (empty($bodyArray)) {
                return false;
            }

            return $bodyArray;
        } catch (\Zend_Http_Client_Exception $e) {
            Mage::getModel('warranty/logger')->error($e->getMessage(),['exception' => $e]);
            return false;
        }
    }
}
