<?php

class Magestore_Giftvoucher_Helper_Giftproduct extends Mage_Core_Helper_Data {

    /**

     */
    public function getGiftValue($product) {
        $gift_type = $product->getGiftType();
        switch ($gift_type) {
            case Magestore_Giftvoucher_Model_Gifttype::GIFT_TYPE_FIX:
                return array('type' => 'static', 'gift_price' => $this->getGiftPriceByStatic($product), 'value' => $product->getGiftValue());

            case Magestore_Giftvoucher_Model_Gifttype::GIFT_TYPE_RANGE:
                $data = array('type' => 'range', 'from' => $product->getGiftFrom(), 'to' => $product->getGiftTo());
                $price_type = $product->getGiftPriceType();
                if ($price_type == Magestore_Giftvoucher_Model_Giftpricetype::GIFT_PRICE_TYPE_DEFAULT)
                    $data['gift_price_type'] = 'default';
                else {
                    $data['gift_price_type'] = 'percent';
                    $data['gift_price_options'] = $product->getGiftPrice();
                }
                return $data;

            case Magestore_Giftvoucher_Model_Gifttype::GIFT_TYPE_DROPDOWN:
                $options = explode(',', $product->getGiftDropdown());
                $giftPrices = explode(',', $product->getGiftPrice());

                foreach ($options as $key => $option) {
                    if (!is_numeric($option) || $option <= 0) {
                        unset($options[$key]);
                    }
                }

                $data = array('type' => 'dropdown', 'options' => $options);
                $price_type = $product->getGiftPriceType();
                if ($price_type == Magestore_Giftvoucher_Model_Giftpricetype::GIFT_PRICE_TYPE_DEFAULT) {
                    $data['prices'] = $options;
                } else if ($price_type == Magestore_Giftvoucher_Model_Giftpricetype::GIFT_PRICE_TYPE_FIX) {
                    $options_price = explode(',', $product->getGiftPrice());
                    $data['prices'] = $options_price;
                } else {
                    if (count($giftPrices) == count($options)) {
                        for ($i = 0; $i < count($giftPrices); $i++) {
                            $data['prices'][] = $giftPrices[$i] * $options[$i] / 100;
                        }
                    } else {
                        foreach ($options as $value) {
                            $data['prices'][] = $value * $product->getGiftPrice() / 100;
                        }
                    }
                }

                return $data;
            default:
                $giftValue = Mage::helper('giftvoucher')->getInterfaceConfig('amount');
                $options = explode(',', $giftValue);
                return array('type' => 'dropdown', 'options' => $options, 'prices' => $options);
        }
    }

    public function getGiftPriceByStatic($product) {
        $gift_value = $product->getGiftValue();
        $gift_price = $product->getGiftPrice();
        if ($product->getGiftPriceType() == Magestore_Giftvoucher_Model_Giftpricetype::GIFT_PRICE_TYPE_DEFAULT) {
            return $gift_value;
        } else if ($product->getGiftPriceType() == Magestore_Giftvoucher_Model_Giftpricetype::GIFT_PRICE_TYPE_FIX) {
            return $gift_price;
        } else
            return $gift_value * $gift_price / 100;
    }

}
