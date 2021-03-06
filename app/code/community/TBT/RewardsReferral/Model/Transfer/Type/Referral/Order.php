<?php

/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 *      https://www.sweettoothrewards.com/terms-of-service
 * The Open Software License is available at this URL: 
 *      http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
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
 * @copyright  Copyright (c) 2014 Sweet Tooth Inc. (http://www.sweettoothrewards.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Transfer Reference
 *
 * @category   TBT
 * @package    TBT_Rewards
 * * @author     Sweet Tooth Inc. <support@sweettoothrewards.com>
 */
class TBT_RewardsReferral_Model_Transfer_Type_Referral_Order extends TBT_Rewards_Model_Transfer_Type_Abstract {
    const REFERENCE_REFERRAL = 10;

    public function transferBeforeSave(&$transfer) {
        return $this;
    }

    public function transferAfterSave(&$transfer) {
        return $this;
    }

    public function loadReferenceInformation(&$transfer) {
        return $this;
    }

    public function clearReferences(&$transfer) {
        return $this;
    }

    public function getOtherReasons() {
        return array();
    }

    public function getAvailReasons($current_reason, &$availR) {
        return $availR;
    }

    public function getManualReasons() {
        return array();
    }

    public function getReferenceOptions() {
        return array();
    }

    public function getDistributionReasons() {
        return array();
    }

    public function getRedemptionReasons() {
        return array();
    }

    public function getCustomerTransferRowRenderers() {
        return array();
    }

}