<?php
/**
 * Paradox Labs, Inc.
 * http://www.paradoxlabs.com
 * 717-431-3330
 * 
 * Need help? Open a ticket in our support system:
 *  http://support.paradoxlabs.com
 * 
 * Want to customize or need help with your store?
 *  Phone: 717-431-3330
 *  Email: sales@paradoxlabs.com
 *
 * @category	ParadoxLabs
 * @package		TokenBase
 * @author		Ryan Hoerr <magento@paradoxlabs.com>
 * @license		http://store.paradoxlabs.com/license.html
 */

class ParadoxLabs_TokenBase_Model_Override_Sales_Recurring_Profile extends Mage_Sales_Model_Recurring_Profile
{
	/**
	 * The core class doesn't define these for specific event handling...
	 * all we want is proper observers. Is that too much to ask?
	 */
    protected $_eventPrefix = 'sales_recurring_profile';
    protected $_eventObject = 'profile';
    
    /**
     * Submit a recurring profile right after an order is placed
     */
    public function submit()
    {
        $this->_getResource()->beginTransaction();
        try {
            $this->setInternalReferenceId(Mage::helper('core')->uniqHash('temporary-'));
            $this->save();
            $this->setInternalReferenceId(Mage::helper('core')->uniqHash($this->getId() . '-'));
            $this->getMethodInstance()->submitRecurringProfile($this, $this->getQuote()->getPayment());
            $this->save();
            $this->_getResource()->commit();
        } catch (Exception $e) {
            $this->_getResource()->rollBack();
            
            // Add a custom rollback event...so we have it.
        	Mage::dispatchEvent( $this->_eventPrefix . '_rollback', $this->_getEventData() );
            
            throw $e;
        }
    }
}
