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

        Mage::getModel('warranty/logger')->info([], Mage::helper('warranty')->__('Contract #%s request successful', $contractId));
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

            $response = $this->connector
                ->call(
                    $endpoint,
                    \Zend_Http_Client::POST
                );

            return $this->processRefundResponse($response);

        } catch (\Zend_Http_Client_Exception $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return false;
        }
    }

    /**
     * @param Zend_Http_Response $response
     * @return bool
     */
    private function processRefundResponse(\Zend_Http_Response $response)
    {
        if ($response->getStatus() === 201 || $response->getStatus() === 202) {
            $this->logger->info('Refund Request Success');
            return true;
        }

        //Refund Already Accepted
        try {
            $body = json_decode($response->getBody(), true);
            if ($body["message"] == "The contract has already been refunded") {
                $this->logger->info('Refund Request already processed');
                return true;
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return false;
        }

        $res = $this->jsonSerializer->unserialize($response->getBody());
        $this->logger->error('Refund Request Fail', $res);
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

            $response = $this->connector
                ->call(
                    $endpoint,
                    \Zend_Http_Client::POST
                );

            $body = json_decode($response->getBody(), true);
            if (!empty($body)) {
                return $body;
            } else {
                return false;
            }


        } catch (\Zend_Http_Client_Exception $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return false;
        }
    }
}
