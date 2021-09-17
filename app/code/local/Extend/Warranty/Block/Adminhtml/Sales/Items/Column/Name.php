<?php

class Extend_Warranty_Block_Adminhtml_Sales_Items_Column_Name extends Mage_Adminhtml_Block_Sales_Items_Column_Name
{
    /**
     * @param $item
     * @param false $isPartial
     * @return string
     */
    public function getDataInit($item, $isPartial = false)
    {
        $contractID = json_decode($item->getContractId()) === NULL ? json_encode([$item->getContractId()]) : $item->getContractId();
        $_elements = count(json_decode($contractID, true));

        return '{"url": "' . $this->getUrl('adminhtml/contract/refund') .
            '", "contractId": ' . $contractID .
            ', "isPartial": "' . $isPartial . '"' .
            ', "maxRefunds": "' . $_elements . '"' .
            ', "itemId": "' . $item->getId() . '" }';
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
