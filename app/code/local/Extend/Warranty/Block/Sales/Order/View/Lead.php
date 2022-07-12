<?php

class Extend_Warranty_Block_Sales_Order_View_Lead extends Mage_Core_Block_Template
{
    /**
     * @param $orderItem
     * @return false|mixed
     */
    public function getLeadToken($orderItem)
    {
        if ($orderItem->getProductType() === Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE) {
            foreach ($orderItem->getChildrenItems() as $child) {
                if ($child->getLeadToken()) {
                    $_itemID = $child->getId();
                    $leadTokenJson = $child->getLeadToken();
                }
            }
        } elseif ($orderItem->getLeadToken()) {
            $leadTokenJson = $orderItem->getLeadToken();
            $_itemID = $orderItem->getId();
        }

        $leadTokens = json_decode($leadTokenJson, 1);
        $leadToken = is_array($leadTokens) ? reset($leadTokens) : $leadTokens;
        return $leadToken;
    }

    /**
     * @param $orderItem
     * @return string
     */
    public function getLeadItemId($orderItem)
    {
        $itemID = '';
        if ($orderItem->getProductType() === Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE) {
            foreach ($orderItem->getChildrenItems() as $child) {
                if ($child->getLeadToken()) {
                    $itemID = $child->getId();
                }
            }
        } elseif ($orderItem->getLeadToken()) {
            $itemID = $orderItem->getId();
        }
        return $itemID;
    }
}