<?php

class Extend_Warranty_Block_Adminhtml_Sales_Order_View_Items_Renderer_WarrantyRenderer extends Mage_Adminhtml_Block_Sales_Items_Column_Default
{
    public function getColumnHtml(\Magento\Framework\DataObject $item, $column, $field = null)
    {
        if (!$this->extendHelper->isExtendEnabled() || !$this->extendHelper->isRefundEnabled()) {
            return parent::getColumnHtml($item, $column, $field);
        }
        $html = '';
        switch ($column) {
            case 'refund':
                if ($item->getStatusId() == Item::STATUS_INVOICED) {
                    $options = $item->getProductOptions();
                    if (isset($options['refund']) && $options['refund'] === false) {
                        if ($this->canDisplayContainer()) {
                            $html .= '<div id="' . $this->getHtmlId() . '">';
                        }
                        $html .= '<button type="button" class="action action-extend-refund"' . " data-mage-init='{$this->getDataInit($item, $this->canShowPartial($item))}' >Request Refund</button>";
                        if ($this->canDisplayContainer()) {
                            $html .= '</div>';
                        }
                    } else if (isset($options['refund']) && $options['refund'] === true) {
                        if ($this->canDisplayContainer()) {
                            $html .= '<div id="' . $this->getHtmlId() . '">';
                        }
                        $html .= '<button type="button" class="action action-extend-refund" disabled>Refunded</button>';
                        if ($this->canDisplayContainer()) {
                            $html .= '</div>';
                        }
                    } else {
                        $html .= '&nbsp;';
                    }
                } else {
                    $html .= '&nbsp;';
                }
                break;
            default:
                $html = parent::getColumnHtml($item, $column, $field);
        }
        return $html;
    }

    private function getDataInit($item, $isPartial = false)
    {
        $contractID = json_decode($item->getContractId()) === NULL ? json_encode([$item->getContractId()]) : $item->getContractId();
        $_elements = count(json_decode($contractID, true));

        return '{"refundWarranty": {"url": "' . $this->getUrl('extend/refund') .
            '", "contractId": ' . $contractID .
            ', "isPartial": "' . $isPartial . '"' .
            ', "maxRefunds": "' . $_elements . '"' .
            ', "itemId": "' . $item->getId() . '" }}';
    }

    private function canShowPartial($item)
    {
        $contractID = json_decode($item->getContractId()) === NULL ? json_encode([$item->getContractId()]) : $item->getContractId();
        return (count(json_decode($contractID, true)) > 1);
    }

    public function getHtmlId()
    {
        return 'return_order_item_' . $this->getItem()->getId();
    }

}