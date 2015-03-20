<?php

class Magestore_Giftvoucher_Model_Aftertax extends Varien_Object {

    static public function getOptionArray() {
        return array(
            0 => Mage::helper('giftvoucher')->__('Before tax'),
            1 => Mage::helper('giftvoucher')->__('After tax'),
        );
    }

    public function toOptionArray() {
        $options = array();
        foreach (self::getOptionArray() as $value => $label)
            $options[] = array(
                'value' => $value,
                'label' => $label
            );
        return $options;
    }

}