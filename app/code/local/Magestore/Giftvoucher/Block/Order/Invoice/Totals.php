<?php

class Magestore_Giftvoucher_Block_Order_Invoice_Totals extends Mage_Core_Block_Template {

    public function initTotals() {
        $orderTotalsBlock = $this->getParentBlock();
        $order = $orderTotalsBlock->getInvoice();
        if ($order->getGiftVoucherDiscount() && $order->getGiftVoucherDiscount() > 0) {
            $orderTotalsBlock->addTotal(new Varien_Object(array(
                'code' => 'giftvoucher',
                'label' => $this->__('Gift Card (%s)', $order->getOrder()->getGiftCodes()),
                'value' => -$order->getGiftVoucherDiscount(),
                'base_value' => -$order->getBaseGiftVoucherDiscount(),
                    )), 'subtotal');
        }
    }

}
