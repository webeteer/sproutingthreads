<?php
/**
 * NOTICE OF LICENSE
 * This source file is subject to the WDCA SWEET TOOTH (TM) POINTS AND REWARDS
 * License.
 * The Sweet Tooth License is available at this URL: https://www.sweettoothrewards.com/terms-of-service
 *
 * DISCLAIMER
 * By adding to, editing, or in any way modifying this code, WDCA is
 * not held liable for any inconsistencies or abnormalities in the
 * behaviour of this code.
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the
 * provided Sweet Tooth License.
 * Upon discovery of modified code in the process of support, the Licensee
 * is still held accountable for any and all billable time WDCA spent
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension.
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to
 * support@sweettoothrewards.com or call 1.855.699.9322, so we can send you a copy
 * immediately.
 *
 * @category   [TBT]
 * @package    [TBT_Rewards]
 * @copyright  Copyright (c) 2014 Sweet Tooth Inc. (http://www.sweettoothrewards.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Poll
 *
 * @category   TBT
 * @package    TBT_Rewards
 * * @author     Sweet Tooth Inc. <support@sweettoothrewards.com>
 */
class TBT_Rewards_Model_Sales_Invoice_Total extends TBT_Rewards_Model_Sales_Order_Total_Abstract
{
    /**
     * Collect reward total for invoice
     *
     * @deprecated  We no longer need to subtract this from the grand total as this is automatically computed by
     * Magento, since we save each items row_total + tax_amount after ST Catalog discounts
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice
     *
     * @return Rewards_Model_Sales_Invoice_Total
     */
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $order = $invoice->getOrder();

        if (!$order->getHasSpentPoints()) {
            return $this;
        }

        list ($acc_diff, $acc_diff_base) = $this->getAccumulatedDiscounts($order);

        //@nelkaake -a 12/01/11: This will help us make sure we never go below $0
        $acc_diff      = min($acc_diff, $invoice->getGrandTotal());
        $acc_diff_base = min($acc_diff_base, $invoice->getBaseGrandTotal());

        $invoice->setGrandTotal($invoice->getGrandTotal() - $acc_diff);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $acc_diff_base);

        return $this;
    }
}
