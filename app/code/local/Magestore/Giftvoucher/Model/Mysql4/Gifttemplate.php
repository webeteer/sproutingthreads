<?php

class Magestore_Giftvoucher_Model_Mysql4_Gifttemplate extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct(){
        $this->_init('giftvoucher/gifttemplate', 'giftcard_template_id');
    }
}