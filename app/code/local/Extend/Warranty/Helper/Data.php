<?php

class Extend_Warranty_Helper_Data extends Mage_Core_Helper_Abstract
{
    const NOT_ALLOWED_TYPES = [Extend_Warranty_Model_Product_Type::TYPE_CODE];

    /**
     * @param $price
     * @return float|int
     */
    public function formatPrice($price)
    {
        if (empty($price)) {
            return 0;
        }

        $floatPrice = (float)$price;
        $formattedPrice = number_format(
            $floatPrice,
            2,
            '',
            ''
        );

        return (float)$formattedPrice;
    }

    /**
     * @param $price
     * @return float
     */
    public function removeFormatPrice($price)
    {
        $price = (string)$price;
        $price = substr_replace(
            $price,
            '.',
            strlen($price) - 2,
            0
        );

        return (float)$price;
    }
}
