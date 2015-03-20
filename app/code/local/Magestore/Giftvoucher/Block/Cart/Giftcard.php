<?php
class Magestore_Giftvoucher_Block_Cart_Giftcard extends Magestore_Giftvoucher_Block_Payment_Form
{
    public function _construct(){
        parent::_construct();
        $this->setTemplate('giftvoucher/giftcard/coupon.phtml');
    }
}
