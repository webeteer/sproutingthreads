<?php

class Magestore_Giftvoucher_Model_Designpattern extends Varien_Object {

    const PATTERN_LEFT = 1;
    const PATTERN_TOP = 2;
    const PATTERN_CENTER = 3;

    static public function getOptionArray() {
        return array(
            self::PATTERN_LEFT => Mage::helper('giftvoucher')->__('Left'),
            self::PATTERN_TOP => Mage::helper('giftvoucher')->__('Top'),
            self::PATTERN_CENTER => Mage::helper('giftvoucher')->__('Center'),
        );
    }

    static public function getOptions() {
        $options = array();
        foreach (self::getOptionArray() as $value => $label)
            $options[] = array(
                'value' => $value,
                'label' => $label
            );
        return $options;
    }

    public function toOptionArray() {
        return self::getOptions();
    }

}