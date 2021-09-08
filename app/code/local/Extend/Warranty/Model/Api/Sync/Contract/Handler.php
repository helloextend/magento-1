<?php

class Extend_Warranty_Model_Api_Sync_Contract_Handler
{
    const ENDPOINT_URI = 'contracts';

    public function create($contract): string
    {
        return $this->createRequest($contract);
    }

    private function createRequest($contract): string
    {
        try {
            $response = $this->connector
                ->call(
                    self::ENDPOINT_URI,
                    \Zend_Http_Client::POST,
                    $contract
                );

            return $this->processCreateResponse($response);

        } catch (\Zend_Http_Client_Exception $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return '';
        }
    }

    private function processCreateResponse(\Zend_Http_Response $response): string
    {
        if ($response->isError()) {
            $res = $this->jsonSerializer->unserialize($response->getBody());
            $this->logger->error('Contract Request Fail', $res);

        } elseif ($response->getStatus() === 201 || $response->getStatus() === 202) {
            $res = $this->jsonSerializer->unserialize($response->getBody());
            $contractId = $res['id'];
            $this->logger->info(__('Contract #%1 request successful', $contractId));
            return $contractId;
        }

        return '';
    }


    public function refund($contractId): bool
    {
        return $this->refundRequest($contractId);
    }

    private function refundRequest($contractId): bool
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

    private function processRefundResponse(\Zend_Http_Response $response): bool
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
        } catch(\Exception $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return false;
        }

        $res = $this->jsonSerializer->unserialize($response->getBody());
        $this->logger->error('Refund Request Fail', $res);
        return false;
    }

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

    public function validateRefund($contractId)
    {
        return $this->validateRefundRequest($contractId);
    }
}
