<?php

class Magestore_Giftvoucher_Block_Product_Upload extends Mage_Adminhtml_Block_Media_Uploader {
    public function __construct()
    {
        parent::__construct();
        $this->setId($this->getId() . '_Uploader');
        $this->setTemplate('');
        $this->getConfig()->setUrl($this->getUrl('giftvoucher/index/customUpload'));
        $this->getConfig()->setParams();
        $this->getConfig()->setFileField('image');
        $this->getConfig()->setFilters(array(
            'images' => array(
                'label' => Mage::helper('adminhtml')->__('Images (.gif, .jpg, .png)'),
                'files' => array('*.gif', '*.jpg', '*.png')
            )
        ));
        $this->getConfig()->setWidth(32);
    }
    public function getDeleteButtonHtml()
    {
        $this->setChild(
            'delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->addData(array(
                    'id'      => '{{id}}-delete',
                    'class'   => 'delete',
                    'type'    => 'button',
                    'label'   => Mage::helper('adminhtml')->__(''),
                    'onclick' => $this->getJsObjectName() . '.removeFile(\'{{fileId}}\')',
                    'style'     => 'display:none'
                ))
        );
        return $this->getChildHtml('delete_button');
    }
    public function getDataMaxSize()
    {
        $data_size = Mage::helper('giftvoucher')->getInterfaceConfig('upload_max_size');
        if(is_nan($data_size) || $data_size <=0) $data_size = 500;
        return $data_size.'K';
    }
}
