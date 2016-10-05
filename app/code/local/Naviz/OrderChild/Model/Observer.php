<?php
class Naviz_OrderChild_Model_Observer
{

			public function saveChildData(Varien_Event_Observer $observer)
			{
				$event=$observer->getEvent(); 
				$order = $observer->getEvent()->getOrder();  
				$order->setData('child', $_POST['order']['child']);
 
				return $this;
			}
		
}
