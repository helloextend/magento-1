<?php


class Extend_Warranty_Helper_Api extends Mage_Core_Helper_Abstract
{
    /**
     * @param $warrantyData
     * @return array
     */
    public function validateWarranty($warrantyData)
    {
        $errors = [];

        if (empty($warrantyData['planId'])) {
            $errors[] = 'Plan ID doesn\'t set.';
        }

        if (!isset($warrantyData['price'])) {
            $errors[] = 'Warranty plan price doesn\'t set.';
        } elseif ((int)$warrantyData['price'] <= 0) {
            $errors[] = 'Warranty plan price must be positive.';
        }

        if (empty($warrantyData['term'])) {
            $errors[] = 'Warranty term doesn\'t set.';
        }

        if (empty($warrantyData['product'])) {
            $errors[] = 'Product reference ID doesn\'t set.';
        }

        if (empty($errors)) {
            $offerInformation = $this->getOfferInformation($warrantyData['product']);
            if (isset($offerInformation['base'])) {
                $baseOfferInformation = $offerInformation['base'];
                $offerIds = array_column($baseOfferInformation, 'id');
                if (in_array($warrantyData['planId'], $offerIds)) {
                    foreach ($baseOfferInformation as $offer) {
                        if ($warrantyData['planId'] === $offer['id']) {
                            if (isset($offer['price']) && (int)$warrantyData['price'] !== $offer['price']) {
                                $errors[] = 'Invalid price.';
                            }

                            if (isset($offer['contract']['termLength']) && (int)$warrantyData['term'] !== $offer['contract']['termLength']) {
                                $errors[] = 'Invalid warranty term.';
                            }
                        }
                    }
                } else {
                    $errors[] = 'Invalid warranty plan ID.';
                }
            }
        }

        return $errors;
    }

    /**
     * @param $product
     * @return array
     */
    protected function getOfferInformation($product)
    {
        $offers = Mage::getModel('warranty/api_sync_offers_handler')->getOffers($product);
        if (!empty($offers) && isset($offers['plans'])
            && is_array($offers['plans']) && count($offers['plans']) >= 1) {
            return $offers['plans'];
        }
        return [];
    }

    public function getWarrantyDataAsString($warrantyData)
    {
        try {
            $result = json_encode($warrantyData);
        } catch (InvalidArgumentException $exception) {
            Mage::getModel('warranty/logger')->error($exception->getMessage());
            $result = '';
        }

        return $result;
    }
}