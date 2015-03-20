<?php

class Magestore_Giftvoucher_Helper_Drawgiftcard extends Mage_Core_Helper_Data {
    /* Vick New Update 4.3 */

    public function getImgDir($giftcode = null) {
        $gcTemplateDir = Mage::getBaseDir('media') . DS . 'giftvoucher' . DS . 'draw' . DS . $giftcode . DS;
        $io = new Varien_Io_File();
        $io->checkAndCreateFolder($gcTemplateDir, 0755);
        return $gcTemplateDir;
    }

    public function draw($giftcode) {

        if (isset($giftcode['giftcard_template_id']) && $giftcode['giftcard_template_id'] != null)
            $giftcardTemplate = Mage::getModel('giftvoucher/gifttemplate')->load($giftcode['giftcard_template_id']);

        switch ($giftcardTemplate['design_pattern']) {
            case '2':
                $this->generateTopImage($giftcode, $giftcardTemplate);
                break;
            case '3':
                $this->generateCenterImage($giftcode, $giftcardTemplate);
                break;
            default:
                $this->generateLeftImage($giftcode, $giftcardTemplate);
                break;
        }
    }

    public function generateLeftImage($giftcode, $giftcardTemplate) {

        $storeId = Mage::app()->getStore()->getId();
        $images = $this->getImagesInFolder($giftcode['gift_code']);
        if (isset($images[0]) && file_exists($images[0]))
            unlink($images[0]);

        $image_suffix = Mage::getModel('core/date')->timestamp(now());

        $img_file = $this->getImgDir($giftcode['gift_code']) . $giftcode['gift_code'] . '-' . $image_suffix . '.png';
        $w = 600;
        $h = 365;

        $img = imagecreatetruecolor($w, $h);
        $text_color = $this->hexColorAllocate($img, $giftcardTemplate['text_color']);
        $style_color = $this->hexColorAllocate($img, $giftcardTemplate['style_color']);
        $bg_color = imagecolorallocate($img, 255, 255, 255);
        imagefilledrectangle($img, 0, 0, $w - 1, $h - 1, $bg_color);


        $img2 = $this->createGCImage($giftcode['giftcard_template_image'], 'left');

        $img1 = $this->createGCBackground($giftcardTemplate['background_img'], 'left');

        $img3 = $this->createGCLogo();

        $img4 = $this->createGMessageBox('left');

        $x = 10;
        $y = 30;
        $fsize = 15;
        $font = Mage::getBaseDir('lib') . DS . 'Magestore' . DS . 'fonts' . DS . 'OpenSans-Semibold.ttf';

        /* Insert Logo to Image */
        if ($img3) {
            $width_logo = imagesx($img3);
            $height_logo = imagesy($img3);
            imagecopyresampled($img2, $img3, (250 - $width_logo) / 2, 265, 0, 0, $width_logo, $height_logo, $width_logo, $height_logo);
        }

        /* Draw Expiry Date */
        if (Mage::helper('giftvoucher')->getGeneralConfig('show_expiry_date')) {
            if ($giftcode['expired_at']) {
                $expiry_date = Mage::helper('giftvoucher')->__('Expired: ') . date('m/d/Y', strtotime($giftcode['expired_at']));
                $textbox = imageftbbox(9, 0, $font, $expiry_date);
                imagefttext($img2, 9, 0, (250 - ($textbox[2] - $textbox[0])) / 2, 350, imagecolorallocate($img, 255, 255, 255), $font, $expiry_date);
            }
        }

        /* Draw Text */
        $word = $giftcardTemplate['caption'];
        $textbox = imageftbbox($fsize, 0, $font, $word);

        $stringArray = $this->processString($word, $font, $fsize, 350);
        // The width of textbox: $textbox[2] - $textbox[0]
        // The height of textbox: $textbox[7] - $textbox[1]
        // $x = ($w - ($textbox[2] - $textbox[0])) / 2;
        // $y = ($h - ($textbox[7] - $textbox[1])) / 2;

        for ($i = 0; $i < count($stringArray); $i++) {
            imagefttext($img1, $fsize, 0, $x, $y, $style_color, $font, $stringArray[$i]);
            $y -= 1.4 * ($textbox[7] - $textbox[1]);
        }

        // $font = Mage::getBaseDir('lib') . DS . 'Magestore' . DS . 'fonts' . DS . 'OpenSans-Light.ttf';
        /* Print "From:" and "To: " */

        $textbox = imageftbbox($fsize, 0, $font, Mage::helper('giftvoucher')->__('From: '));
        imagefttext($img1, 13, 0, 15, $y, $text_color, $font, Mage::helper('giftvoucher')->__('From: '));
        imagefttext($img1, 13, 0, $x + ($textbox[2] - $textbox[0]), $y, $style_color, $font, $giftcode['customer_name']);
        $y -= 1.55 * ($textbox[7] - $textbox[1]);

        $textbox = imageftbbox($fsize, 0, $font, Mage::helper('giftvoucher')->__('To: '));
        imagefttext($img1, 13, 0, 15, $y + 5, $text_color, $font, Mage::helper('giftvoucher')->__('To: '));
        imagefttext($img1, 13, 0, $x + ($textbox[2] - $textbox[0]), $y + 5, $style_color, $font, $giftcode['recipient_name']);
        $y -= 1.55 * ($textbox[7] - $textbox[1]);

        /* Print Customers' s messages */

        $x_message = 5;
        $y_message = 15;

        if (isset($giftcode['message']) && $giftcode['message'] != null)
            $message = $giftcode['message'];
        else
            $message = '';

        $stringArray = $this->processString($message, $font, 9, 322);

        for ($i = 0; $i < count($stringArray); $i++) {
            imagefttext($img4, 9, 0, $x_message, $y_message, $text_color, $font, $stringArray[$i]);
            $y_message -= 1.25 * ($textbox[7] - $textbox[1]);
        }

        imagecopyresampled($img1, $img4, 14, $y - 10, 0, 0, 322, 90, 322, 90);

        /* Print Value */

        $value_y = $y + 100;
        $fsizePrice = 13;

        $textbox = imageftbbox($fsize, 0, $font, Mage::helper('giftvoucher')->__('Value '));
        imagefttext($img1, 10, 0, 15, $value_y, $text_color, $font, Mage::helper('giftvoucher')->__('Value '));
        $value_y -= 1.55 * ($textbox[7] - $textbox[1]);

        $price = Mage::getModel('directory/currency')->setData('currency_code', $giftcode['currency'])
                ->format($giftcode['balance'], array('display' => Zend_Currency::USE_SYMBOL), false);

        $textbox = imageftbbox($fsizePrice, 0, $font, $price);
        imagefttext($img1, $fsizePrice, 0, 15, $value_y + 5, $style_color, $font, $price);
        $value_y -= 1.55 * ($textbox[7] - $textbox[1]);

        /* Print Gift Code */

        $font_code = Mage::getBaseDir('lib') . DS . 'Magestore' . DS . 'fonts' . DS . 'OpenSans-SemiboldItalic.ttf';
        $code_y = $y + 105;
        $textbox = imageftbbox(13, 0, $font_code, $giftcode['gift_code']);
        imagefttext($img1, 13, 0, 335 - ($textbox[2] - $textbox[0]), $code_y, $style_color, $font_code, $giftcode['gift_code']);
        $code_y -= ($textbox[7] - $textbox[1]);

        /* Print Barcode */
        $barcode = Mage::helper('giftvoucher')->getGeneralConfig('barcode_enable');
        if ($barcode) {
            $new_img_barcode = $this->resizeBarcodeImage($giftcode['gift_code']);
            $new_img_barcode_x = imagesx($new_img_barcode);
            $new_img_barcode_y = imagesy($new_img_barcode);
            imagecopyresampled($img1, $new_img_barcode, 335 - $new_img_barcode_x, $code_y - 5, 0, 0, $new_img_barcode_x, $new_img_barcode_y, $new_img_barcode_x, $new_img_barcode_y);
        }

        /* Print Notes */

        if (isset($giftcardTemplate['notes']) && $giftcardTemplate['notes'] != null) {
            $notes = $giftcardTemplate['notes'];
        } else {
            $notes = Mage::getStoreConfig('giftvoucher/print_voucher/note', $storeId);
            $notes = str_replace(array(
                '{store_url}',
                '{store_name}',
                '{store_address}'
                    ), array(
                '<span class="print-notes">' . Mage::app()->getStore($storeId)->getBaseUrl() . '</span>',
                '<span class="print-notes">' . Mage::app()->getStore($storeId)->getFrontendName() . '</span>',
                '<span class="print-notes">' . Mage::getStoreConfig('general/store_information/address', $storeId) . '</span>'
                    ), $notes);
            $notes = strip_tags($notes);
        }

        $stringArray = $this->processString($notes, $font, 9, 350);
        for ($i = 0; $i < count($stringArray); $i++) {
            imagefttext($img1, 9, 0, $x, $code_y + 58, $text_color, $font, $stringArray[$i]);
            $code_y -= 1.3 * ($textbox[7] - $textbox[1]);
        }

        /* End */


        /* Draw Images */
        imagecopyresampled($img, $img2, 0, 0, 0, 0, 250, 365, 250, 365);


        /* Draw Background */
        imagecopyresampled($img, $img1, 250, 0, 0, 0, 350, 365, 350, 365);

        imagepng($img, $img_file);
        imagedestroy($img);
    }

    public function generateTopImage($giftcode, $giftcardTemplate) {

        $storeId = Mage::app()->getStore()->getId();
        $images = $this->getImagesInFolder($giftcode['gift_code']);
        if (isset($images[0]) && file_exists($images[0]))
            unlink($images[0]);

        $image_suffix = Mage::getModel('core/date')->timestamp(now());

        $img_file = $this->getImgDir($giftcode['gift_code']) . $giftcode['gift_code'] . '-' . $image_suffix . '.png';
        $w = 600;
        $h = 365;

        $img = imagecreatetruecolor($w, $h);
        $text_color = $this->hexColorAllocate($img, $giftcardTemplate['text_color']);
        $style_color = $this->hexColorAllocate($img, $giftcardTemplate['style_color']);
        $bg_color = imagecolorallocate($img, 255, 255, 255);
        imagefilledrectangle($img, 0, 0, $w - 1, $h - 1, $bg_color);

        // if (isset($giftcode['giftcard_template_image']) && $giftcode['giftcard_template_image'] != null)
        $img2 = $this->createGCImage($giftcode['giftcard_template_image'], 'top');

        $img1 = $this->createGCBackground($giftcardTemplate['background_img'], 'top');

        $img3 = $this->createGCLogo();

        $img4 = $this->createGMessageBox('top');

        $img5 = $this->createGCBackground('bkg-title.png');

        $img6 = $this->createGCBackground('bkg-value.png');

        $x = 10;
        $y = 33;
        $fsize = 15;
        $font = Mage::getBaseDir('lib') . DS . 'Magestore' . DS . 'fonts' . DS . 'OpenSans-Semibold.ttf';

        /* Draw Expiry Date */
        if (Mage::helper('giftvoucher')->getGeneralConfig('show_expiry_date')) {
            if ($giftcode['expired_at']) {
                $expiry_date = Mage::helper('giftvoucher')->__('Expired: ') . date('m/d/Y', strtotime($giftcode['expired_at']));
                $textbox = imageftbbox(9, 0, $font, $expiry_date);
                imagefttext($img2, 9, 0, (580 - ($textbox[2] - $textbox[0])), 25, imagecolorallocate($img, 255, 255, 255), $font, $expiry_date);
            }
        }


        /* Draw Text */

        $word = $giftcardTemplate['caption'];
        $textbox = imageftbbox($fsize, 0, $font, $word);

        $word = $this->processTitle($word, $font, $fsize, 370);
        imagefttext($img5, $fsize, 0, $x, $y, $style_color, $font, $word);


        /* Print Value */
        $fontPrice = Mage::getBaseDir('lib') . DS . 'Magestore' . DS . 'fonts' . DS . 'OpenSans-ExtraBold.ttf';
        $fsizePrice = 14;

        $price = Mage::getModel('directory/currency')->setData('currency_code', $giftcode['currency'])
                ->format($giftcode['balance'], array('display' => Zend_Currency::USE_SYMBOL), false);

        $textbox = imageftbbox($fsizePrice, 0, $fontPrice, $price);
        $value_x = $textbox[2] - $textbox[0];
        imagefttext($img6, $fsizePrice, 0, 210 - $value_x, $y + 3, $style_color, $font, $price);

        $fsizeValue = 9;
        $textbox = imageftbbox($fsizeValue, 0, $fontPrice, Mage::helper('giftvoucher')->__('Value '));
        $value_x += $textbox[2] - $textbox[0] + 15;
        imagefttext($img6, $fsizeValue, 0, 210 - $value_x, $y, $style_color, $font, Mage::helper('giftvoucher')->__('Value '));

        /* Print "From:" and "To: " */

        $textbox = imageftbbox($fsize, 0, $font, Mage::helper('giftvoucher')->__('From: '));
        imagefttext($img1, 13, 0, 15, $y - 5, $text_color, $font, Mage::helper('giftvoucher')->__('From: '));
        imagefttext($img1, 13, 0, $x + ($textbox[2] - $textbox[0]), $y - 5, $text_color, $font, $giftcode['customer_name']);
        $y -= 1.55 * ($textbox[7] - $textbox[1]);

        $textbox = imageftbbox($fsize, 0, $font, Mage::helper('giftvoucher')->__('To: '));
        imagefttext($img1, 13, 0, 15, $y, $text_color, $font, Mage::helper('giftvoucher')->__('To: '));
        imagefttext($img1, 13, 0, $x + ($textbox[2] - $textbox[0]), $y, $text_color, $font, $giftcode['recipient_name']);
        $y -= 1.55 * ($textbox[7] - $textbox[1]);

        /* Print Customers' s messages */

        $x_message = 5;
        $y_message = 15;

        if (isset($giftcode['message']) && $giftcode['message'] != null)
            $message = $giftcode['message'];
        else
            $message = '';

        $stringArray = $this->processString($message, $font, 9, 340);

        for ($i = 0; $i < count($stringArray); $i++) {
            imagefttext($img4, 9, 0, $x_message, $y_message, $text_color, $font, $stringArray[$i]);
            $y_message -= 1.25 * ($textbox[7] - $textbox[1]);
        }

        imagecopyresampled($img1, $img4, 14, $y - 7, 0, 0, 343, 97, 343, 97);

        /* Print Gift Code */

        $font_code = Mage::getBaseDir('lib') . DS . 'Magestore' . DS . 'fonts' . DS . 'OpenSans-SemiboldItalic.ttf';
        $code_y = 20;
        $textbox = imageftbbox(11, 0, $font_code, $giftcode['gift_code']);
        imagefttext($img1, 11, 0, 590 - ($textbox[2] - $textbox[0]), $code_y, $text_color, $font_code, $giftcode['gift_code']);
        $code_y -= ($textbox[7] - $textbox[1]);

        /* Print Barcode */

        $barcode = Mage::helper('giftvoucher')->getGeneralConfig('barcode_enable');
        if ($barcode) {
            $new_img_barcode = $this->resizeBarcodeImage($giftcode['gift_code']);
            $new_img_barcode_x = imagesx($new_img_barcode);
            $new_img_barcode_y = imagesy($new_img_barcode);
            imagecopyresampled($img1, $new_img_barcode, 590 - $new_img_barcode_x, $code_y - 5, 0, 0, $new_img_barcode_x, $new_img_barcode_y, $new_img_barcode_x, $new_img_barcode_y);
        }

        /* Print Notes */

        if (isset($giftcardTemplate['notes']) && $giftcardTemplate['notes'] != null) {
            $notes = $giftcardTemplate['notes'];
        } else {
            $notes = Mage::getStoreConfig('giftvoucher/print_voucher/note', $storeId);
            $notes = str_replace(array(
                '{store_url}',
                '{store_name}',
                '{store_address}'
                    ), array(
                '<span class="print-notes">' . Mage::app()->getStore($storeId)->getBaseUrl() . '</span>',
                '<span class="print-notes">' . Mage::app()->getStore($storeId)->getFrontendName() . '</span>',
                '<span class="print-notes">' . Mage::getStoreConfig('general/store_information/address', $storeId) . '</span>'
                    ), $notes);
            $notes = strip_tags($notes);
        }

        $stringArray = $this->processString($notes, $font, 8, 240);
        for ($i = 0; $i < count($stringArray); $i++) {
            $textbox = imageftbbox(8, 0, $font, $stringArray[$i]);
            imagefttext($img1, 8, 0, 590 - ($textbox[2] - $textbox[0]), $code_y + 58, $text_color, $font, $stringArray[$i]);
            $code_y += 18.5;
        }
        /* End */

        /* Insert Logo to Image */
        if ($img3) {
            $width_logo = imagesx($img3);
            $height_logo = imagesy($img3);
            imagecopyresampled($img2, $img3, 13, 0, 0, 0, $width_logo, $height_logo, $width_logo, $height_logo);
        }


        /* Insert Backgound Value Image */
        imagecopyresampled($img5, $img6, 381, 0, 0, 0, 219, 52, 219, 52);

        /* Insert Background Title Image */
        imagecopyresampled($img2, $img5, 0, 138, 0, 0, 600, 52, 600, 52);

        /* Draw Images */
        imagecopyresampled($img, $img2, 0, 0, 0, 0, 600, 190, 600, 190);


        /* Draw Background */
        imagecopyresampled($img, $img1, 0, 190, 0, 0, 600, 175, 600, 175);

        imagepng($img, $img_file);
        imagedestroy($img);
    }

    public function generateCenterImage($giftcode, $giftcardTemplate) {

        $storeId = Mage::app()->getStore()->getId();
        $images = $this->getImagesInFolder($giftcode['gift_code']);
        if (isset($images[0]) && file_exists($images[0]))
            unlink($images[0]);

        $image_suffix = Mage::getModel('core/date')->timestamp(now());

        $img_file = $this->getImgDir($giftcode['gift_code']) . $giftcode['gift_code'] . '-' . $image_suffix . '.png';
        $w = 600;
        $h = 365;

        $img = imagecreatetruecolor($w, $h);
        $text_color = $this->hexColorAllocate($img, $giftcardTemplate['text_color']);
        $style_color = $this->hexColorAllocate($img, $giftcardTemplate['style_color']);
        $bg_color = imagecolorallocate($img, 255, 255, 255);
        imagefilledrectangle($img, 0, 0, $w - 1, $h - 1, $bg_color);

        // if (isset($giftcode['giftcard_template_image']) && $giftcode['giftcard_template_image'] != null)
        $img2 = $this->createGCImage($giftcode['giftcard_template_image']);

        $img3 = $this->createGCLogo();

        $img4 = $this->createGMessageBox();

        $img5 = $this->createGCBackground('bkg-title.png');

        $img6 = $this->createGCBackground('bkg-value.png');

        $x = 10;
        $y = 33;
        $fsize = 15;
        $font = Mage::getBaseDir('lib') . DS . 'Magestore' . DS . 'fonts' . DS . 'OpenSans-Semibold.ttf';

        /* Draw Expiry Date */
        if (Mage::helper('giftvoucher')->getGeneralConfig('show_expiry_date')) {
            if ($giftcode['expired_at']) {
                $expiry_date = Mage::helper('giftvoucher')->__('Expired: ') . date('m/d/Y', strtotime($giftcode['expired_at']));
                $textbox = imageftbbox(9, 0, $font, $expiry_date);
                imagefttext($img2, 9, 0, (580 - ($textbox[2] - $textbox[0])), 25, imagecolorallocate($img, 255, 255, 255), $font, $expiry_date);
            }
        }


        /* Draw Text */
        $fsize = 15;
        $font = Mage::getBaseDir('lib') . DS . 'Magestore' . DS . 'fonts' . DS . 'OpenSans-Semibold.ttf';
        $word = $giftcardTemplate['caption'];
        $textbox = imageftbbox($fsize, 0, $font, $word);

        $word = $this->processTitle($word, $font, $fsize, 370);
        // Chieu dai cua textbox: $textbox[2] - $textbox[0]
        // Chieu rong cua textbox: $textbox[7] - $textbox[1]

        imagefttext($img5, $fsize, 0, $x, $y, $style_color, $font, $word);

        /* Print Value */

        $fontPrice = Mage::getBaseDir('lib') . DS . 'Magestore' . DS . 'fonts' . DS . 'OpenSans-ExtraBold.ttf';
        $fsizePrice = 14;

        $price = Mage::getModel('directory/currency')->setData('currency_code', $giftcode['currency'])
                ->format($giftcode['balance'], array('display' => Zend_Currency::USE_SYMBOL), false);

        $textbox = imageftbbox($fsizePrice, 0, $fontPrice, $price);
        $value_x = $textbox[2] - $textbox[0];
        imagefttext($img6, $fsizePrice, 0, 210 - $value_x, $y + 3, $style_color, $font, $price);

        $fsizeValue = 9;
        $textbox = imageftbbox($fsizeValue, 0, $fontPrice, Mage::helper('giftvoucher')->__('Value '));
        $value_x += $textbox[2] - $textbox[0] + 15;
        imagefttext($img6, $fsizeValue, 0, 210 - $value_x, $y, $style_color, $font, Mage::helper('giftvoucher')->__('Value '));

        /* Print "From:" and "To: " */

        $y += 135;
        $textbox = imageftbbox($fsize, 0, $font, Mage::helper('giftvoucher')->__('From: '));
        imagefttext($img2, 13, 0, 15, $y - 5, $text_color, $font, Mage::helper('giftvoucher')->__('From: '));
        imagefttext($img2, 13, 0, $x + ($textbox[2] - $textbox[0]), $y - 5, $style_color, $font, $giftcode['customer_name']);
        $y -= 1.55 * ($textbox[7] - $textbox[1]);

        $textbox = imageftbbox($fsize, 0, $font, Mage::helper('giftvoucher')->__('To: '));
        imagefttext($img2, 13, 0, 15, $y, $text_color, $font, Mage::helper('giftvoucher')->__('To: '));
        imagefttext($img2, 13, 0, $x + ($textbox[2] - $textbox[0]), $y, $style_color, $font, $giftcode['recipient_name']);
        $y -= 1.55 * ($textbox[7] - $textbox[1]);

        /* Print Gift Code */

        $font_code = Mage::getBaseDir('lib') . DS . 'Magestore' . DS . 'fonts' . DS . 'OpenSans-SemiboldItalic.ttf';
        $code_y = 160;
        $textbox = imageftbbox(11, 0, $font_code, $giftcode['gift_code']);
        imagefttext($img2, 11, 0, 590 - ($textbox[2] - $textbox[0]), $code_y, $style_color, $font_code, $giftcode['gift_code']);
        $code_y -= ($textbox[7] - $textbox[1]);

        /* Print Barcode */

        $barcode = Mage::helper('giftvoucher')->getGeneralConfig('barcode_enable');
        if ($barcode) {
            $new_img_barcode = $this->resizeBarcodeImage($giftcode['gift_code']);
            $new_img_barcode_x = imagesx($new_img_barcode);
            $new_img_barcode_y = imagesy($new_img_barcode);
            imagecopyresampled($img2, $new_img_barcode, 590 - $new_img_barcode_x, $code_y - 5, 0, 0, $new_img_barcode_x, $new_img_barcode_y, $new_img_barcode_x, $new_img_barcode_y);
        }

        /* Print Customers' s messages */

        $x_message = 5;
        $y_message = 15;

        if (isset($giftcode['message']) && $giftcode['message'] != null)
            $message = $giftcode['message'];
        else
            $message = '';

        $stringArray = $this->processString($message, $font, 9, 568);

        for ($i = 0; $i < count($stringArray); $i++) {
            imagefttext($img4, 9, 0, $x_message, $y_message, $text_color, $font, $stringArray[$i]);
            $y_message -= 1.25 * ($textbox[7] - $textbox[1]);
        }

        imagecopyresampled($img2, $img4, 16, $y + 5, 0, 0, 568, 97, 568, 97);

        /* Print Notes */

        $y += 110;

        if (isset($giftcardTemplate['notes']) && $giftcardTemplate['notes'] != null) {
            $notes = $giftcardTemplate['notes'];
        } else {
            $notes = Mage::getStoreConfig('giftvoucher/print_voucher/note', $storeId);
            $notes = str_replace(array(
                '{store_url}',
                '{store_name}',
                '{store_address}'
                    ), array(
                '<span class="print-notes">' . Mage::app()->getStore($storeId)->getBaseUrl() . '</span>',
                '<span class="print-notes">' . Mage::app()->getStore($storeId)->getFrontendName() . '</span>',
                '<span class="print-notes">' . Mage::getStoreConfig('general/store_information/address', $storeId) . '</span>'
                    ), $notes);
            $notes = strip_tags($notes);
        }

        $stringArray = $this->processString($notes, $font, 9, 570);
        for ($i = 0; $i < count($stringArray); $i++) {
            imagefttext($img2, 9, 0, 16, $y + 10, $text_color, $font, $stringArray[$i]);
            $y -= 1.55 * ($textbox[7] - $textbox[1]);
        }

        /* End */

        /* Insert Logo to Image */
        if ($img3) {
            $width_logo = imagesx($img3);
            $height_logo = imagesy($img3);
            imagecopyresampled($img2, $img3, 13, 0, 0, 0, $width_logo, $height_logo, $width_logo, $height_logo);
        }


        /* Insert Backgound Value Image */
        imagecopyresampled($img5, $img6, 381, 0, 0, 0, 219, 52, 219, 52);

        /* Insert Background Title Image */
        imagecopyresampled($img2, $img5, 0, 85, 0, 0, 600, 52, 600, 52);

        /* Draw Images */
        imagecopyresampled($img, $img2, 0, 0, 0, 0, 600, 365, 600, 365);

        imagepng($img, $img_file);
        imagedestroy($img);
    }

    public function processString($txt, $font, $fsize, $widthBackground) {

        $box = imageftbbox($fsize, 0, $font, $txt);
        $txtLength = $box[2] - $box[0];

        if ($txtLength < $widthBackground) {
            $result[0] = $txt;
        } else {
            $result = array();
            $strArr = explode(' ', $txt);
            $length = 0;
            $count = 0;
            $string = imageftbbox($fsize, 0, $font, ' ');
            $inc = $string[2] - $string[0];

            for ($i = 0; $i < count($strArr); $i++) {
                if ($strArr[$i] == '') {
                    $strLength = 1;
                } else {
                    $textbox = imageftbbox($fsize, 0, $font, $strArr[$i]);
                    $strLength = $textbox[2] - $textbox[0] + $inc;
                }

                if ($strLength > ($widthBackground - 6 * $inc)) {
                    $count ++;
                    $length = $strLength;
                    $strArr[$i] = $this->processTitle($strArr[$i], $font, $fsize, $widthBackground);
                } else {
                    $length += $strLength;

                    if ($length > ($widthBackground - 6 * $inc)) {
                        $count ++;
                        $length = $strLength;
                    }
                }
                if (!isset($result[$count]))
                    $result[$count] = '';

                $result[$count] .= $strArr[$i] . ' ';
            }
        }

        return $result;
    }

    public function processTitle($txt, $font, $fsize, $widthBackground) {

        $box = imageftbbox($fsize, 0, $font, $txt);
        $txtLength = $box[2] - $box[0];
        $string = imageftbbox($fsize, 0, $font, ' ');
        $inc = $string[2] - $string[0];

        if ($txtLength < $widthBackground) {
            $result = $txt;
        } else {
            $length = 0;
            $result = '';

            for ($i = 0; $i < strlen($txt); $i++) {
                $textbox = imageftbbox($fsize, 0, $font, $txt[$i]);
                $strLength = $textbox[2] - $textbox[0];
                $length += $strLength;

                if ($length >= ($widthBackground - 6 * $inc))
                    break;

                $result .= $txt[$i];
            }
        }

        return $result;
    }

    public function imagecreatefromfile($filename) {
        if (!file_exists($filename)) {
            throw new Mage_Exception('File "' . $filename . '" not found.');
        }
        switch (strtolower(pathinfo($filename, PATHINFO_EXTENSION))) {
            case 'jpeg':
            case 'jpg':
                return imagecreatefromjpeg($filename);
                break;

            case 'png':
                return imagecreatefrompng($filename);
                break;

            case 'gif':
                return imagecreatefromgif($filename);
                break;

            default:
                throw new Mage_Exception('File "' . $filename . '" is not valid jpg, png or gif image.');
                break;
        }
    }

    public function createGCImage($filename, $type = null) {
        if (isset($type) && $type != null)
            $dir = Mage::getBaseDir('media') . DS . 'giftvoucher' . DS . 'template' . DS . 'images' . DS . $type . DS . $filename;
        else
            $dir = Mage::getBaseDir('media') . DS . 'giftvoucher' . DS . 'template' . DS . 'images' . DS . $filename;

        if (($filename == null) || (!file_exists($dir)))
            $dir = Mage::getBaseDir('media') . DS . 'giftvoucher' . DS . 'draw' . DS . 'default.png';

        return $this->imagecreatefromfile($dir);
    }

    public function createGCBackground($filename, $type = null) {
        if ($filename) {
            if (isset($type) && $type != null)
                $dir = Mage::getBaseDir('media') . DS . 'giftvoucher' . DS . 'template' . DS . 'background' . DS . $type . DS . $filename;
            else
                $dir = Mage::getBaseDir('media') . DS . 'giftvoucher' . DS . 'template' . DS . 'background' . DS . $filename;
        } else {
            $dir = Mage::getBaseDir('media') . DS . 'giftvoucher' . DS . 'draw' . DS . 'default.png';
        }

        return $this->imagecreatefromfile($dir);
    }

    public function createGCLogo() {
        $storeId = Mage::app()->getStore()->getId();
        $image = Mage::getStoreConfig('giftvoucher/print_voucher/logo', $storeId);
        if ($image) {
            $dir = Mage::getBaseDir('media') . DS . 'giftvoucher' . DS . 'pdf' . DS . 'logo' . DS . str_replace("/", DS, $image);
            $img_logo = $this->imagecreatefromfile($dir);
            $new_width = round(63 * imagesx($img_logo) / imagesy($img_logo));
            $resize_logo_url = Mage::getBaseDir('media') . DS . 'giftvoucher' . DS . 'draw' . DS . 'logo' . DS . str_replace("/", DS, $image);

            if (!is_file($resize_logo_url)) {
                $resizeLogoObj = new Varien_Image($dir);
                $resizeLogoObj->constrainOnly(TRUE);
                $resizeLogoObj->keepAspectRatio(TRUE);
                $resizeLogoObj->keepFrame(false);
                $resizeLogoObj->keepTransparency(true);
                $resizeLogoObj->resize($new_width, 63);
                $resizeLogoObj->save($resize_logo_url);
            }
            return $this->imagecreatefromfile($resize_logo_url);
        } else {
            return false;
        }
    }

    public function createGMessageBox($type = null) {
        if (isset($type) && $type != null)
            $dir = Mage::getBaseDir('media') . DS . 'giftvoucher' . DS . 'template' . DS . 'messagebox' . DS . $type . DS . 'default.png';
        else
            $dir = Mage::getBaseDir('media') . DS . 'giftvoucher' . DS . 'template' . DS . 'messagebox' . DS . 'default.png';
        return $this->imagecreatefromfile($dir);
    }

    public function resizeBarcodeImage($code) {
        $barcode = Mage::helper('giftvoucher')->getGeneralConfig('barcode_enable');
        $barcode_type = Mage::helper('giftvoucher')->getGeneralConfig('barcode_type');

        if ($barcode_type == 'code128') {
            $barcode_url = Mage::getBaseDir('media') . DS . 'giftvoucher' . DS . 'template' . DS . 'barcode' . DS . $code . '.png';

            $resize_barcode_url = Mage::getBaseDir('media') . DS . 'giftvoucher' . DS . 'draw' . DS . $code . DS . 'barcode.png';
            $resizeBarcodeObj = new Varien_Image($barcode_url);
            $resizeBarcodeObj->constrainOnly(TRUE);
            $resizeBarcodeObj->keepAspectRatio(TRUE);
            $resizeBarcodeObj->keepFrame(false);
            $resizeBarcodeObj->resize(180, 40);
            $resizeBarcodeObj->save($resize_barcode_url);

            return imagecreatefrompng($resize_barcode_url);
        } else {
            $qr = new Magestore_Giftvoucher_QRCode($code);
            $content = file_get_contents($qr->getResult());
            $file_name = Mage::getBaseDir('media') . DS . 'giftvoucher' . DS . 'draw' . DS . $code . DS . 'qrcode.png';
            file_put_contents($file_name, $content);

            $resizeBarcodeObj = new Varien_Image($file_name);
            $resizeBarcodeObj->constrainOnly(TRUE);
            $resizeBarcodeObj->keepAspectRatio(TRUE);
            $resizeBarcodeObj->keepFrame(false);
            $resizeBarcodeObj->resize(180, 40);
            $resizeBarcodeObj->save($file_name);

            return imagecreatefrompng($file_name);
        }
    }

    public function hexColorAllocate($img, $hex) {
        $hex = ltrim($hex, '#');
        $a = hexdec(substr($hex, 0, 2));
        $b = hexdec(substr($hex, 2, 2));
        $c = hexdec(substr($hex, 4, 2));
        return imagecolorallocate($img, $a, $b, $c);
    }

    public function getImagesInFolder($code) {
        $directory = Mage::getBaseDir('media') . DS . 'giftvoucher' . DS . 'draw' . DS . $code . DS;
        return glob($directory . $code . "*.png");
    }

}
