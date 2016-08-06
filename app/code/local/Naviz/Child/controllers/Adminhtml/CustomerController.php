<?php
require_once "Mage/Adminhtml/controllers/CustomerController.php";  
class Naviz_Child_Adminhtml_CustomerController extends Mage_Adminhtml_CustomerController{

    public function postDispatch()
    {
        parent::postDispatch();
        Mage::dispatchEvent('controller_action_postdispatch_adminhtml', array('controller_action' => $this));
    }


}
				