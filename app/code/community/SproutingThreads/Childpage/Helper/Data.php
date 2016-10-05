<?php
class SproutingThreads_Childpage_Helper_Data extends Mage_Core_Helper_Abstract
{
	
	public function getChildProfiles($customer_id,$state=false)
	{
		echo '1';
		exit;
		try
		{
			$profiles=Mage::getModel('sales/recurring_profile')->getCollection()
            ->addFieldToFilter('customer_id',  $customer_id);
			if($state){ $profiles->addFieldToFilter('state','active');}
			$profiles->addFieldToSelect('*');
            $profiles->setOrder('profile_id', 'desc');
			$profile=$profiles->getData();  
			$order_item_info=unserialize($prof[0]['order_item_info']);
			$info_buyRequest=unserialize($info['info_buyRequest']);  
			//$product=$child['product']?$child['product']:$child['product_id'];
			echo '<pre>';
			print_r($profile);
			
		}
		catch(Exception $e)
		{
			Mage::getSingleton('core/session')->addSuccess($e->getMessage());
		}
		
		
	}
	
}  