<?php

class TBT_Milestone_Model_Rule_Observer extends Varien_Object
{
    /**
     * Observes the sales_order_place_after event.
     * Triggers any 'orders' milestones if they are set to be triggered upon order creation.
     * @param Varien_Event_Observer $observer
     * @return self
     */
    public function orderPlaceAfter($observer)
    {
        $conditionsToTest = array('orders');                     
        
        $event = $observer->getEvent();
        if (!$event) {
            return $this;
        }
        
        $order = $event->getOrder();
        if (!$order) {
            return $this;
        }        
        
        $customerId = $order->getCustomerId();
        if (!$customerId) {
            return $this;
        }        
        
        $store = $order->getStore();
        if (!$store) {
            return $this;
        }
        
        $storeId = $store->getId();
        $customerGroupId = $order->getCustomerGroupId();
        $websiteId = $store->getWebsiteId();
        
        foreach ($conditionsToTest as $conditionType) {           
            $doTrigger = Mage::helper('tbtmilestone/config')->isTriggerOnOrderCreate($conditionType, $storeId);            
            if (!$doTrigger) {
                continue;
            }
            
            $this->_testRules($conditionType, $customerId, $customerGroupId);
        }
        
        return $this;
    }
    

    /**
     * Observes the sales_order_invoice_save_commit_after event.
     * Triggers any 'orders' or 'revenue' milestones if they are set to be triggered upon order payment.
     * @param Varien_Event_Observer $observer
     * @return self
     */
    public function invoiceSaveCommitAfter($observer)
    {
        $conditionsToTest = array('orders', 'revenue');
        
        $event = $observer->getEvent();
        if (!$event) {
            return $this;
        }

        $invoice = $event->getInvoice();
        if (!$invoice) {
            return $this;
        }

        $order = $invoice->getOrder();
        if (!$order) {
            return $this;
        }

        $customerId = $order->getCustomerId();
        if (!$customerId) {
            return $this;
        }

        $store = $order->getStore();
        if (!$store) {
            return $this;
        }
        
        $storeId = $store->getId();
        $customerGroupId = $order->getCustomerGroupId();
        $websiteId = $store->getWebsiteId();

        foreach ($conditionsToTest as $conditionType) {
            $doTrigger = Mage::helper('tbtmilestone/config')->isTriggerOnOrderPayment($conditionType, $storeId);
            if (!$doTrigger) {
                continue;
            }
            
            $this->_testRules($conditionType, $customerId, $customerGroupId);
        }
    }

    /**
     * Observes the sales_order_shipment_save_commit_after event.
     * Triggers any 'orders' milestones if they are set to be triggered upon order shipment.
     * @param Varien_Event_Observer $observer
     * @return self
     */
    public function shipmentSaveCommitAfter($observer)
    {
        $conditionsToTest = array('orders');
        
        $event = $observer->getEvent();
        if (!$event) {
            return $this;
        }

        $shipment = $observer->getShipment();
        if (!$shipment || !($shipment instanceof Mage_Sales_Model_Order_Shipment)) {
            return $this;
        }

        $order = $shipment->getOrder();
        if (!$order) {
            return $this;
        }

        $customerId = $order->getCustomerId();
        if (!$customerId) {
            return $this;
        }

        $store = $order->getStore();
        if (!$store) {
            return $this;
        }
        
        $storeId = $store->getId();
        $customerGroupId = $order->getCustomerGroupId();
        $websiteId = $store->getWebsiteId();
        
        foreach ($conditionsToTest as $conditionType){
            $doTrigger = Mage::helper('tbtmilestone/config')->isTriggerOnOrderShipment($conditionType, $store->getId());
            if (!$doTrigger) {
                continue;
            }
            
            $this->_testRules($conditionType, $customerId, $customerGroupId);
        }

        return $this;
    }
    
    /**
     * Observes the rewards_referral_save_commit_after event.
     * Triggers any 'referrals' milestones if they are set to be triggered upon referral registration.
     * Referral registration happens when an entry is added to the rewardsref_referral table.
     * @param Varien_Event_Observer $observer
     * @return self
     */    
    public function referralSaveAfter($observer)
    {        
        $conditionsToTest = array('referrals');
        
        $event = $observer->getEvent();
        if (!$event) {
            return $this;
        }
        
        $referral = $event->getReferral();
        if (!$referral) {
            return $this;
        }
                
        $referrerCustomerId = $referral->getReferralParentId();
        if (!$referrerCustomerId) {
            return $this;
        }

        $referrerCustomer = Mage::getModel('customer/customer')->load($referrerCustomerId);
        if (!$referrerCustomer->getId()){
            return $this;
        } 
        
        $customerGroupId = $referrerCustomer->getGroupId();
        $websiteIds = $referrerCustomer->getSharedWebsiteIds();        
        
        foreach ($conditionsToTest as $conditionType) {
            $this->_testRules($conditionType, $referrerCustomerId, $customerGroupId, $websiteIds);
        } 

        return $this;
    }
    
    /**
     * Observes the rewards_transfer_save_commit_after event.
     * Triggers any 'points_earned' milestones if they are set to be triggered upon transfer creation.
     * @param Varien_Event_Observer $observer
     * @return self
     */
    public function transferSaveCommitAfter($observer)
    {
    	$conditionsToTest = array('points_earned');
    
    	$event = $observer->getEvent();
    	if (!$event) {
    		return $this;
    	}
    
    	$transfer = $event->getRewardsTransfer();
    	if (!$transfer) {
    		return $this;
    	}
    
    	$customerId = $transfer->getCustomerId();
    	if (!$customerId) {
    		return $this;
    	}
    
    	foreach ($conditionsToTest as $conditionType) {
    		    		    		
    		if ($conditionType == 'points_earned') {    			
    			 // Skip this rule type of there's already another partner module watching for it.
    			if (Mage::helper('core')->isModuleEnabled("JMT_PointsMilestone")) continue;
    			
    			// Skip this rule if the transfer is coming from execution of a similar kind of rule
    			if ($transfer->getReferenceType() === TBT_Milestone_Model_Rule_Condition_Earned::POINTS_REFERENCE_TYPE_ID) continue;
    			
    			// Only process for eligable transfer status. Skip otherwise.
    			if ($transfer->getStatus() != TBT_Rewards_Model_Transfer_Status::STATUS_APPROVED) {
    				if (!Mage::helper('tbtmilestone/config')->doIncludePendingTransfers()) continue;
    				if (
    					$transfer->getStatus() != TBT_Rewards_Model_Transfer_Status::STATUS_PENDING_EVENT &&
                		$transfer->getStatus() != TBT_Rewards_Model_Transfer_Status::STATUS_PENDING_APPROVAL &&
                		$transfer->getStatus() != TBT_Rewards_Model_Transfer_Status::STATUS_PENDING_TIME 
    				) continue;
    			}
    		}

    		$this->_testRules($conditionType, $customerId);
    	}
    
    	return $this;
    }
        
    /**
     * Entry point for daily cron events. Magento cron must be working for this.
     * Ideally this will be called at 12am every day but there's no guarantee about that.
     * All conditions here will need a prequalifier to generate a list of eligible customers.
     *
     * @param $observer
     * @return TBT_Milestone_Model_Rule_Observer
     */
    public function dailyCronTrigger($observer = null)
    {
        $conditionsToTest = array('inactivity', 'membership');

        foreach ($conditionsToTest as $conditionType) {
            $this->_testRules($conditionType);
        }

        return $this;
    }

    /**
     * For the specified condition type, will retrieve all active rules of the type,
     * and trigger any which are appropriate.
     *
     * @param string $conditionType
     * @param int $customerId
     * @param int|null $customerGroupId. If not specified, will skip this check.
     * @param int|array|null $websiteId. If not specified, will skip this check.
     * @return self
     */
    protected function _testRules($conditionType, $customerId = null, $customerGroupId = null, $websiteId = null)
    {    
        try {

            $milestoneRules = Mage::getSingleton('tbtmilestone/rule')->getMatchingRules($conditionType);
            foreach ($milestoneRules as $rule) {
                if (!empty($customerId)){
                    /*
                     * If we have a customerId,
                     * we'll just check to make sure they qualify for this rule.
                     */
                    if (!is_null($customerGroupId) && !in_array($customerGroupId, $rule->getCustomerGroupIds())) {
                        continue;
                    }

                    if (!is_null($websiteId)){
                        $websiteIds = is_array($websiteId) ? $websiteId : array($websiteId);
                        $commonMemberships = array_intersect($websiteIds, $rule->getWebsiteIds());
                        if (empty($commonMemberships)){
                            continue;
                        }
                    }

                    if ($rule->wasEverExecuted($customerId)){
                        continue;
                    }

                    $rule->trigger($customerId);

                } else {
                    /*
                     * If we don't have a customerId,
                     * we'll need to get a prequalified list of customers this rule applies to.
                     */
                    $prequalifierClass = "tbtmilestone/rule_condition_{$conditionType}_prequalifier";
                    $prequalifier = Mage::getModel($prequalifierClass)
                                        ->setRule($rule);
                                        
                    /*
                     * Paginating in an effor to be memory friendly.
                     * Also only load ids in the collection
                     */
                    $customerCollection = $prequalifier->getCollection();
                    $customerCollectionSize = $customerCollection->getSize();
                      
                    $pageSize = 100;                 
                    $pages = max(1, ceil($customerCollectionSize/$pageSize));
                    $currentPageIndex = 0;
                    do {
                    	$customerIds = $customerCollection->getAllIds($pageSize, $currentPageIndex * $pageSize);                 	
                    	foreach ($customerIds as $customerId) {
                    		if (!$rule->wasEverExecuted($customerId)){
                    			$processed++;
                    			$rule->trigger($customerId);                    			 
                    		}
                    	}                    	
                    	$currentPageIndex++;
                    } while ($currentPageIndex < $pages);
                }
            }

        } catch (Exception $e){
            Mage::log("Failure in triggering a rule. Check the rewards exception log.");
            Mage::helper('rewards')->logException($e);
        }

    
        return $this;
    }
}
