<?php

class Extend_Warranty_Model_Normalizer
{
    /**
     * @param Mage_Checkout_Model_Cart|Mage_Sales_Model_Quote $cart
     */
    public function normalize($cart)
    {
        $items = is_a($cart, Mage_Checkout_Model_Cart::class)
            ? $cart->getItems()
            : $cart->getAllItems();

        $warranties = [];
        $products = [];
        foreach ($items as $item) {
            if ($item->getProductType() === Extend_Warranty_Model_Product_Type::TYPE_CODE) {
                $warranties[$item->getItemId()] = $item;
            } else if ($item->getProductType() == 'bundle') {
                $products[] = $item;
                foreach ($item->getChildren() as $bundleChild) {
                    $products[] = $bundleChild;
                }
            } else {
                $products[] = $item;
            }
        }


        //Loop products to see if their qty is different from the warranty qty and adjust both to max
        foreach ($products as $item) {
            $itemQty = $item->getTotalQty();
            $warrantiesQty = 0;
            $productWarranties = [];

            foreach ($warranties as $warrantyitem) {
                if ($this->isWarrantyQuoteItemMatch($warrantyitem, $item)) {
                    $warrantiesQty += $warrantyitem->getQty();
                    $productWarranties[$warrantyitem->getId()] = $warrantyitem;
                }
            }

            $this->sortWarrantiesItems($productWarranties);

            $warrantyQtyDelta = $itemQty - $warrantiesQty;
            if ($itemQty > $warrantiesQty) {
                foreach ($productWarranties as $warrantyitem) {
                    if ($itemQty > $warrantiesQty) {
                        //Update Warranty QTY
                        $warrantyitem->setQty($warrantyitem->getQty() + $warrantyQtyDelta);
                        break;
                    }
                }
            } elseif ($itemQty > 0) {
                $warrantyQtyDelta = abs($warrantyQtyDelta);
                $productWarranties = array_reverse($productWarranties, true);
                foreach ($productWarranties as $warrantyitem) {
                    if ($warrantyitem->getQty() > $warrantyQtyDelta) {
                        $warrantyitem->setQty($warrantyitem->getQty() - $warrantyQtyDelta);
                        break;
                    } elseif ($warrantyitem->getQty() <= $warrantyQtyDelta) {
                        $cart->removeItem($warrantyitem->getItemId());
                        $warrantyQtyDelta = $warrantyQtyDelta - $warrantyitem->getQty();
                    }
                }
            } elseif ($itemQty <= 0) {
                foreach ($productWarranties as $warrantyitem) {
                    $cart->removeItem($warrantyitem->getItemId());
                    $cart->removeItem($item->getItemId());
                }
            }
        }
    }

    /**
     * @param $warranties
     * @return mixed
     */
    private function sortWarrantiesItems(&$warranties)
    {
        uasort($warranties, function ($warrantyItemA, $warrantyItemB) {
            return $warrantyItemA->getOptionByCode(Extend_Warranty_Model_Product_Type::TERM)->getValue() <= $warrantyItemB->getOptionByCode(Extend_Warranty_Model_Product_Type::TERM)->getValue()
                ? 1
                : -1;
        });
        return $warranties;
    }

    /**
     * @param $warranty
     * @param $quoteItem
     * @return bool
     */
    private function isWarrantyQuoteItemMatch($warranty, $quoteItem)
    {
        $associatedSku = [$warranty->getOptionByCode(Extend_Warranty_Model_Product_Type::ASSOCIATED_PRODUCT)->getValue()];

        if ($warranty->getOptionByCode(Extend_Warranty_Model_Product_Type::DYNAMIC_SKU)) {
            // case when product is bundle with dynamic sku
            $associatedSku[] = $warranty->getOptionByCode(Extend_Warranty_Model_Product_Type::DYNAMIC_SKU)->getValue();
        }

        $warrantyHelper = Mage::helper('warranty');

        if ($warranty->getOptionByCode(Extend_Warranty_Model_Product_Type::RELATED_ITEM_ID)) {
            $warrantRelatedId = $warranty->getOptionByCode(Extend_Warranty_Model_Product_Type::RELATED_ITEM_ID)->getValue();
            $bundleCheck = in_array($warrantRelatedId, array($quoteItem->getParentItemId(), $quoteItem->getId()));
        } else {
            $bundleCheck = true;
        }

        return
            in_array($warrantyHelper->getComplexQuoteItemSku($quoteItem), $associatedSku)
            && ($quoteItem->getProductType() == 'configurable' || is_null($quoteItem->getOptionByCode('parent_product_id')))
            && $bundleCheck
            && !$warranty->getOptionByCode(Extend_Warranty_Model_Product_Type::LEAD_TOKEN);
    }
}
