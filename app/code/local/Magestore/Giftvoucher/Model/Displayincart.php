<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Order Statuses source model
 */
class Magestore_Giftvoucher_Model_Displayincart
{
    public function toOptionArray()
    {
        $positions = array(
            'amount'    => Mage::helper('giftvoucher')->__('Gift Card value'),
            'giftcard_template_id' => Mage::helper('giftvoucher')->__('Gift Card template'),
            'customer_name'   => Mage::helper('giftvoucher')->__('Sender name'),
            'recipient_name' => Mage::helper('giftvoucher')->__('Recipient name'),
            'recipient_email' => Mage::helper('giftvoucher')->__('Recipient email address'),
            'recipient_ship' => Mage::helper('giftvoucher')->__('Ship to recipient'),
            'recipient_address' => Mage::helper('giftvoucher')->__('Recipient address'),
            'message' => Mage::helper('giftvoucher')->__('Custom message'),
            'day_to_send' => Mage::helper('giftvoucher')->__('Day to send'),
            'timezone_to_send' => Mage::helper('giftvoucher')->__('Time zone'),
        );
        $options = array();
            
        foreach ($positions as $code=>$label) {
            $options[] = array(
               'value' => $code,
               'label' => $label
            );
        }
        return $options;
    }
}

