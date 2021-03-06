<?php

class Magestore_Giftvoucher_Model_Pdf_Giftcard extends Varien_Object {

    public function getPdf($giftvoucherIds) {
        if ($giftvoucherIds) {
            $pdf = new Zend_Pdf();
            $this->_setPdf($pdf);
            $style = new Zend_Pdf_Style();
            $this->_setFontBold($style, 10);
  
          $giftvoucherIds = array_chunk($giftvoucherIds, 3);
   

         foreach ($giftvoucherIds as $giftvouchers) {
                $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
                $pdf->pages[] = $page;
                $this->y = 790;
                $i = 0;
                foreach ($giftvouchers as $giftvoucherId) {
                    $giftvoucher = Mage::getModel('giftvoucher/giftvoucher')->load($giftvoucherId);

                    if ($giftvoucher->getId()) {

                   $new_img_width = ($page->getWidth() - 300)/2;
                        $new_img_height = 183;
   
                        /* Insert Email Logo */
                        $images = Mage::helper('giftvoucher/drawgiftcard')->getImagesInFolder($giftvoucher['gift_code']);
                        if (isset($images[0]) && is_file($images[0])) {
                            $image = Zend_Pdf_Image::imageWithPath($images[0]);
                            $page->drawImage($image, $new_img_width , $this->y - 183, $new_img_width + 300 , $this->y);
                        }
                     
                    }
                    $temp = $this->y - 200;
                }
            }
        }
        return $pdf;
    }


    protected function _beforeGetPdf() {
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);
    }

    protected function _afterGetPdf() {
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(true);
    }

    protected function _setPdf(Zend_Pdf $pdf) {
        $this->_pdf = $pdf;
        return $this;
    }

    protected function _setFontRegular($object, $size = 7) {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertineC_Re-2.8.0.ttf');
        $object->setFont($font, $size);
        return $font;
    }

    protected function _setFontBold($object, $size = 7) {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf');
        $object->setFont($font, $size);
        return $font;
    }

    protected function _setFontItalic($object, $size = 7) {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_It-2.8.2.ttf');
        $object->setFont($font, $size);
        return $font;
    }

}
