<?php

class Extend_Warranty_Block_Adminhtml_Sales_Order_Create_Warranty extends Mage_Adminhtml_Block_Template
{
    protected $warrantyRenderer;

    /**
     * Get order item object from parent block
     *
     * @return Mage_Sales_Model_Order_Item
     */
    public function getItem()
    {
        return $this->getParentBlock()->getData('item');
    }

    public function setWarrantyRender($block, $template)
    {
        $this->warrantyRenderer = [
            'block' => $block,
            'template' => $template,
            'renderer' => null
        ];
    }

    public function getWarrantyRenderer()
    {
        if (is_null($this->warrantyRenderer['renderer'])) {
            $this->warrantyRenderer['renderer'] = $this->getLayout()
                ->createBlock($this->warrantyRenderer['block'])
                ->setTemplate($this->warrantyRenderer['template'])
                ->setRenderedBlock($this);
        }
        return $this->warrantyRenderer['renderer'];
    }

    /**
     * @param Mage_Sales_Model_Quote_Item $item
     * @return string
     */
    public function renderChildItems($item)
    {
        $html = '';
        $helper = Mage::helper('warranty/connector');


        if ($item->getProductType() !== 'bundle') {
            return '';
        }

        foreach ($item->getChildren() as $child) {
            if ($helper->productHasWarranty($child, true)) {
                continue;
            }
            $block = $this->getWarrantyRenderer();
            $html .= $block->setChildItem($child)->toHtml();
        }
        return $html;
    }
}
