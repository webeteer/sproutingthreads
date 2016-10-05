<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
class Amasty_Rma_Model_Observer 
{
    public function handleBlockOutput($observer)
    {
        /* @var $block Mage_Core_Block_Abstract */
        $block = $observer->getBlock();
        
        $transport = $observer->getTransport();

        if ($block instanceof Mage_Sales_Block_Order_History){
            $hlr = Mage::helper('amrma');
            if ($hlr->isModuleEnabled()){

                $html = $transport->getHtml();  

                $dom = new DOMDocument();
                $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
                $domx = new DOMXPath($dom);


                $thead = $domx->evaluate("//table[@id='my-orders-table']/thead/tr");
                if ($thead && $thead->item(0)) {
                    $thead->item(0)->appendChild($dom->createElement('th', 'Selfcheckout'));


                    $entries = $domx->evaluate("//table[@id='my-orders-table']/tbody/*");

                    foreach ($entries as $entry) {
                        $incrementId = null;

                        foreach ($entry->childNodes as $node) {
                            $incrementId = $node->nodeValue;
                            break;
                        }

                        $link = '&nbsp;';

                        $td = $dom->createElement('td');

                        if ($incrementId){
                            $order = Mage::getModel('sales/order')->load($incrementId, 'increment_id');
							$Shipments=$order->getShipmentsCollection();
							if(count($Shipments->getData())>0){
                            if ($order->getId() && $hlr->canCreateRma($order->getId())){
                                $a = $dom->createElement('a', $hlr->__("Self Checkout"));
                                $a->setAttribute("href", Mage::getUrl('amrmafront/customer/new', 
                                        array(
                                            'order_id' => $order->getId()
                                        )
                                ));
								
                                $td->appendChild($a);
                            }
							else
							{
								$a = $dom->createElement('a', $hlr->__("Completed"));
                                $a->setAttribute("href", '#');
								
                                $td->appendChild($a);
							}
							}
							else
							{
								$a = $dom->createElement('a', $hlr->__("Shipment Pending"));
                                $a->setAttribute("href", '#');
								
                                $td->appendChild($a);
							}
                        }

                        $entry->appendChild($td);
                    }


                    $html = $dom->saveHTML(); 

                    $transport->setHtml($html);
                }
            }
        }
        
    }
}
?>