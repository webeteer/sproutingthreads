<?php

class Magestore_Giftvoucher_Adminhtml_GifthistoryController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('giftvoucher/gifthistory')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Gift History'), Mage::helper('adminhtml')->__('Gift  History'));

        return $this;
    }

    public function indexAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $this->_title($this->__('Gift History'))
                ->_title($this->__('Gift History'));
        $this->_initAction()
                ->renderLayout();
    }

    public function newAction() {
        $this->_forward('edit');
    }

    /**
     * export grid item to CSV type
     */
    public function exportCsvAction() {
        $fileName = 'gifthistory.csv';
        $content = $this->getLayout()
                ->createBlock('giftvoucher/adminhtml_gifthistory_grid')
                ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export grid item to XML type
     */
    public function exportXmlAction() {
        $fileName = 'gifthistory.xml';
        $content = $this->getLayout()
                ->createBlock('giftvoucher/adminhtml_gifthistory_grid')
                ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('giftvoucher/gifthistory');
    }

}