<?php

class Extend_Warranty_Block_Adminhtml_Sales_Items_Column_Name extends Mage_Adminhtml_Block_Sales_Items_Column_Name
{
    /**
     * @param $item
     * @param false $isPartial
     * @return string
     */
    public function getDataInit($item)
    {
        $contractID = json_decode($item->getContractId()) === NULL ? [$item->getContractId()] : json_decode($item->getContractId(), true);

        $_elements = count($contractID);

        $isPartial = $this->canShowPartial($item);

        $config = [
            "url" => $this->getUrl('adminhtml/extend_order/refund'),
            "contractId" => $contractID,
            "isPartial" => $isPartial,
            "validate" => !Mage::helper('warranty/connector')->isOrdersApiEnabled(),
            "maxRefunds" => $_elements,
            "itemId" => $item->getId()
        ];
        return json_encode($config);
    }

    /**
     * @param $item
     * @return bool
     */
    public function canShowPartial($item)
    {
        $contractID = json_decode($item->getContractId()) === NULL ? json_encode([$item->getContractId()]) : $item->getContractId();
        return count(json_decode($contractID, true)) > 1;
    }

    /**
     * @return string
     */
    public function getHtmlId()
    {
        return 'return_order_item_' . $this->getItem()->getId();
    }
}
