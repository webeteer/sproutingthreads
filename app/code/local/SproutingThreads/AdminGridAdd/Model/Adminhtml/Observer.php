<?php


class SproutingThreads_AdminGridAdd_Model_Adminhtml_Observer
{

    public function onBlockHtmlBefore(Varien_Event_Observer $observer) {
		
		echo "IN HERE";
		
        $block = $observer->getBlock();
        if (!isset($block)) return;

        switch ($block->getType()) {
            case 'adminhtml/catalog_product_grid':
                $block->addColumn('gender', array(
                    'header' => Mage::helper('mymodule')->__('Gender'),
                    'index'  => 'gender',
                ));
                break;
        }
    }

    public function onEavLoadBefore(Varien_Event_Observer $observer) {
        $collection = $observer->getCollection();
        if (!isset($collection)) return;

        if (is_a($collection, 'Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection')) {
            // Manipulate $collection here to add a COLUMN_ID column
            $collection->addExpressionAttributeToSelect('gender', '...Some SQL goes here...');
        }
    }

}
