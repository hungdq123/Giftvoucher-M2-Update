<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Giftvoucher
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Giftvoucher\Helper;

/**
 * Giftvoucher draw helper
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Drawgiftcard extends \Magestore\Giftvoucher\Helper\Data
{

    /**
     * Get the drawing directory of Gift Card
     *
     * @param null|string $giftcode
     * @return string
     */
    public function getImgDir($giftcode = null)
    {
        $gcTemplateDir = $this->getBaseDirMedia()->getAbsolutePath('giftvoucher/draw/' . $giftcode . '/');
        $io = $this->_objectManager->create('Magento\Framework\Filesystem\Io\File');
        $io->checkAndCreateFolder($gcTemplateDir, 0755);
        return $gcTemplateDir;
    }

    /**
     * Draw Gift Card templates
     */
    public function draw($giftcode)
    {
        if (isset($giftcode['giftcard_template_id']) && $giftcode['giftcard_template_id'] != null) {
            $giftcardTemplate = $this->_objectManager->get('Magestore\Giftvoucher\Model\Gifttemplate')
                ->load($giftcode['giftcard_template_id']);
        
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
    }

    /**
     * Draw the left template of Gift Card
     */
    public function generateLeftImage($giftcode, $giftcardTemplate)
    {

        $storeId = $this->_storeManager->getStore()->getId();
        $images = $this->getImagesInFolder($giftcode['gift_code']);
        if (isset($images[0]) && file_exists($images[0])) {
            unlink($images[0]);
        }

        $imageSuffix = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime')->timestamp(time());

        $imgFile = $this->getImgDir($giftcode['gift_code']) . $giftcode['gift_code'] . '-' . $imageSuffix . '.png';
        $w = 600;
        $h = 365;

        $img = imagecreatetruecolor($w, $h);
        $textColor = $this->hexColorAllocate($img, $giftcardTemplate['text_color']);
        $styleColor = $this->hexColorAllocate($img, $giftcardTemplate['style_color']);
        $bgColor = imagecolorallocate($img, 255, 255, 255);
        imagefilledrectangle($img, 0, 0, $w - 1, $h - 1, $bgColor);


        $img2 = $this->createGCImage($giftcode['giftcard_template_image'], 'left');
        $img1 = $this->createGCBackground($giftcardTemplate['background_img'], 'left');

        $img3 = $this->createGCLogo();

        $img4 = $this->createGMessageBox('left');

        $x = 10;
        $y = 30;
        $fsize = 15;
        $font = $this->_filesystem->getDirectoryRead('lib_internal')
            ->getAbsolutePath('Magestore/fonts/OpenSans-Semibold.ttf');

        /* Insert Logo to Image */
        if ($img3) {
            $widthLogo = imagesx($img3);
            $heightLogo = imagesy($img3);
            imagecopyresampled(
                $img2,
                $img3,
                (250 - $widthLogo) / 2,
                265,
                0,
                0,
                $widthLogo,
                $heightLogo,
                $widthLogo,
                $heightLogo
            );
        }

        /* Draw Expiry Date */
        if ($this->getGeneralConfig('show_expiry_date')) {
            if ($giftcode['expired_at']) {
                $expiryDate = __('Expired: ') . date('m/d/Y', strtotime($giftcode['expired_at']));
                $textbox = imageftbbox(9, 0, $font, $expiryDate);
                imagefttext(
                    $img2,
                    9,
                    0,
                    (250 - ($textbox[2] - $textbox[0])) / 2,
                    350,
                    imagecolorallocate($img, 255, 255, 255),
                    $font,
                    $expiryDate
                );
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
            imagefttext($img1, $fsize, 0, $x, $y, $styleColor, $font, $stringArray[$i]);
            $y -= 1.4 * ($textbox[7] - $textbox[1]);
        }

        // $font = Mage::getBaseDir('lib') . DS . 'Magestore' . DS . 'fonts' . DS . 'OpenSans-Light.ttf';
        /* Print "From:" and "To: " */

        $textbox = imageftbbox($fsize, 0, $font, __('From: '));
        imagefttext($img1, 13, 0, 15, $y, $textColor, $font, __('From: '));
        imagefttext($img1, 13, 0, $x + ($textbox[2] - $textbox[0]), $y, $styleColor, $font, $giftcode['customer_name']);
        $y -= 1.55 * ($textbox[7] - $textbox[1]);

        $textbox = imageftbbox($fsize, 0, $font, __('To: '));
        imagefttext($img1, 13, 0, 15, $y + 5, $textColor, $font, __('To: '));
        imagefttext($img1, 13, 0, $x+($textbox[2]-$textbox[0]), $y+5, $styleColor, $font, $giftcode['recipient_name']);
        $y -= 1.55 * ($textbox[7] - $textbox[1]);

        /* Print Customers' s messages */

        $xMessage = 5;
        $yMessage = 15;

        if (isset($giftcode['message']) && $giftcode['message'] != null) {
            $message = $giftcode['message'];
        } else {
            $message = '';
        }

        $stringArray = $this->processString($message, $font, 9, 322);

        for ($i = 0; $i < count($stringArray); $i++) {
            imagefttext($img4, 9, 0, $xMessage, $yMessage, $textColor, $font, $stringArray[$i]);
            $yMessage -= 1.25 * ($textbox[7] - $textbox[1]);
        }

        imagecopyresampled($img1, $img4, 14, $y - 10, 0, 0, 322, 90, 322, 90);

        /* Print Value */

        $valueY = $y + 100;
        $fsizePrice = 13;

        $textbox = imageftbbox($fsize, 0, $font, __('Value '));
        imagefttext($img1, 10, 0, 15, $valueY, $textColor, $font, __('Value '));
        $valueY -= 1.55 * ($textbox[7] - $textbox[1]);
        
        $price = $this->_objectManager->get('Magento\Directory\Model\Currency')
                    ->setData('currency_code', $giftcode['currency'])
                    ->format($giftcode['balance'], array('display' => 2), false);

        $textbox = imageftbbox($fsizePrice, 0, $font, $price);
        imagefttext($img1, $fsizePrice, 0, 15, $valueY + 5, $styleColor, $font, $price);
        $valueY -= 1.55 * ($textbox[7] - $textbox[1]);

        /* Print Gift Code */

        $fontCode = $this->_filesystem->getDirectoryRead('lib_internal')
            ->getAbsolutePath('Magestore/fonts/OpenSans-SemiboldItalic.ttf');
        $codeY = $y + 105;
        $textbox = imageftbbox(13, 0, $fontCode, $giftcode['gift_code']);
        imagefttext(
            $img1,
            13,
            0,
            335 - ($textbox[2] - $textbox[0]),
            $codeY,
            $styleColor,
            $fontCode,
            $giftcode['gift_code']
        );
        $codeY -= ($textbox[7] - $textbox[1]);

        /* Print Barcode */
        $barcode = $this->getGeneralConfig('barcode_enable');
        if ($barcode) {
            $newImgBarcode = $this->resizeBarcodeImage($giftcode['gift_code']);
            $newImgBarcodeX = imagesx($newImgBarcode);
            $newImgBarcodeY = imagesy($newImgBarcode);
            imagecopyresampled(
                $img1,
                $newImgBarcode,
                335 - $newImgBarcodeX,
                $codeY - 5,
                0,
                0,
                $newImgBarcodeX,
                $newImgBarcodeY,
                $newImgBarcodeX,
                $newImgBarcodeY
            );
        }

        /* Print Notes */

        if (isset($giftcardTemplate['notes']) && $giftcardTemplate['notes'] != null) {
            $notes = $giftcardTemplate['notes'];
        } else {
            $notes = $this->getStoreConfig('giftvoucher/print_voucher/note', $storeId);
            $notes = str_replace(array(
                '{store_url}',
                '{store_name}',
                '{store_address}'
                    ), array(
                '<span class="print-notes">' . $this->_storeManager->getStore($storeId)->getBaseUrl() . '</span>',
                '<span class="print-notes">' . $this->_storeManager->getStore($storeId)->getFrontendName() . '</span>',
                '<span class="print-notes">' . $this->getStoreConfig('general/store_information/address', $storeId)
                . '</span>'
                    ), $notes);
            $notes = strip_tags($notes);
        }

        $stringArray = $this->processString($notes, $font, 9, 350);
        for ($i = 0; $i < count($stringArray); $i++) {
            imagefttext($img1, 9, 0, $x, $codeY + 58, $textColor, $font, $stringArray[$i]);
            $codeY -= 1.3 * ($textbox[7] - $textbox[1]);
        }

        /* End */


        /* Draw Images */
        imagecopyresampled($img, $img2, 0, 0, 0, 0, 250, 365, 250, 365);


        /* Draw Background */
        imagecopyresampled($img, $img1, 250, 0, 0, 0, 350, 365, 350, 365);

        imagepng($img, $imgFile);
        imagedestroy($img);
    }

    /**
     * Draw the top template of Gift Card
     */
    public function generateTopImage($giftcode, $giftcardTemplate)
    {

        $storeId = $this->_storeManager->getStore()->getId();
        $images = $this->getImagesInFolder($giftcode['gift_code']);
        if (isset($images[0]) && file_exists($images[0])) {
            unlink($images[0]);
        }

        $imageSuffix = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime')->timestamp(time());

        $imgFile = $this->getImgDir($giftcode['gift_code']) . $giftcode['gift_code'] . '-' . $imageSuffix . '.png';
        $w = 600;
        $h = 365;

        $img = imagecreatetruecolor($w, $h);
        $textColor = $this->hexColorAllocate($img, $giftcardTemplate['text_color']);
        $styleColor = $this->hexColorAllocate($img, $giftcardTemplate['style_color']);
        $bgColor = imagecolorallocate($img, 255, 255, 255);
        imagefilledrectangle($img, 0, 0, $w - 1, $h - 1, $bgColor);

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
        $font = $this->_filesystem->getDirectoryRead('lib_internal')
            ->getAbsolutePath('Magestore/fonts/OpenSans-Semibold.ttf');

        /* Draw Expiry Date */
        if ($this->getGeneralConfig('show_expiry_date')) {
            if ($giftcode['expired_at']) {
                $expiryDate = __('Expired: ') . date('m/d/Y', strtotime($giftcode['expired_at']));
                $textbox = imageftbbox(9, 0, $font, $expiryDate);
                imagefttext(
                    $img2,
                    9,
                    0,
                    (580 - ($textbox[2] - $textbox[0])),
                    25,
                    imagecolorallocate($img, 255, 255, 255),
                    $font,
                    $expiryDate
                );
            }
        }

        /* Draw Text */
        $word = $giftcardTemplate['caption'];
        $textbox = imageftbbox($fsize, 0, $font, $word);

        $word = $this->processTitle($word, $font, $fsize, 370);
        imagefttext($img5, $fsize, 0, $x, $y, $styleColor, $font, $word);


        /* Print Value */
        $fontPrice = $this->_filesystem->getDirectoryRead('lib_internal')
            ->getAbsolutePath('Magestore/fonts/OpenSans-ExtraBold.ttf');
        $fsizePrice = 14;

        $price = $this->_objectManager->get('Magento\Directory\Model\Currency')
                    ->setData('currency_code', $giftcode['currency'])
                    ->format($giftcode['balance'], array('display' => 2), false);

        $textbox = imageftbbox($fsizePrice, 0, $fontPrice, $price);
        $valueX = $textbox[2] - $textbox[0];
        imagefttext($img6, $fsizePrice, 0, 210 - $valueX, $y + 3, $styleColor, $font, $price);

        $fsizeValue = 9;
        $textbox = imageftbbox($fsizeValue, 0, $fontPrice, __('Value '));
        $valueX += $textbox[2] - $textbox[0] + 15;
        imagefttext($img6, $fsizeValue, 0, 210 - $valueX, $y, $styleColor, $font, __('Value '));

        /* Print "From:" and "To: " */

        $textbox = imageftbbox($fsize, 0, $font, __('From: '));
        imagefttext($img1, 13, 0, 15, $y - 5, $textColor, $font, __('From: '));
        imagefttext(
            $img1,
            13,
            0,
            $x + ($textbox[2] - $textbox[0]),
            $y - 5,
            $styleColor,
            $font,
            $giftcode['customer_name']
        );
        $y -= 1.55 * ($textbox[7] - $textbox[1]);

        $textbox = imageftbbox($fsize, 0, $font, __('To: '));
        imagefttext($img1, 13, 0, 15, $y, $textColor, $font, __('To: '));
        imagefttext(
            $img1,
            13,
            0,
            $x + ($textbox[2] - $textbox[0]),
            $y,
            $styleColor,
            $font,
            $giftcode['recipient_name']
        );
        $y -= 1.55 * ($textbox[7] - $textbox[1]);

        /* Print Customers' s messages */

        $xMessage = 5;
        $yMessage = 15;

        if (isset($giftcode['message']) && $giftcode['message'] != null) {
            $message = $giftcode['message'];
        } else {
            $message = '';
        }

        $stringArray = $this->processString($message, $font, 9, 340);

        for ($i = 0; $i < count($stringArray); $i++) {
            imagefttext($img4, 9, 0, $xMessage, $yMessage, $textColor, $font, $stringArray[$i]);
            $yMessage -= 1.25 * ($textbox[7] - $textbox[1]);
        }

        imagecopyresampled($img1, $img4, 14, $y - 7, 0, 0, 343, 97, 343, 97);

        /* Print Gift Code */

        $fontCode = $this->_filesystem->getDirectoryRead('lib_internal')
            ->getAbsolutePath('Magestore/fonts/OpenSans-SemiboldItalic.ttf');
        $codeY = 20;
        $textbox = imageftbbox(11, 0, $fontCode, $giftcode['gift_code']);
        imagefttext(
            $img1,
            11,
            0,
            590 - ($textbox[2] - $textbox[0]),
            $codeY,
            $styleColor,
            $fontCode,
            $giftcode['gift_code']
        );
        $codeY -= ($textbox[7] - $textbox[1]);

        /* Print Barcode */

        $barcode = $this->getGeneralConfig('barcode_enable');
        if ($barcode) {
            $newImgBarcode = $this->resizeBarcodeImage($giftcode['gift_code']);
            $newImgBarcodeX = imagesx($newImgBarcode);
            $newImgBarcodeY = imagesy($newImgBarcode);
            imagecopyresampled(
                $img1,
                $newImgBarcode,
                590 - $newImgBarcodeX,
                $codeY - 5,
                0,
                0,
                $newImgBarcodeX,
                $newImgBarcodeY,
                $newImgBarcodeX,
                $newImgBarcodeY
            );
        }

        /* Print Notes */

        if (isset($giftcardTemplate['notes']) && $giftcardTemplate['notes'] != null) {
            $notes = $giftcardTemplate['notes'];
        } else {
            $notes = $this->getStoreConfig('giftvoucher/print_voucher/note', $storeId);
            $store = $this->_storeManager->getStore($storeId);
            $notes = str_replace(array(
                '{store_url}',
                '{store_name}',
                '{store_address}'
                    ), array(
                '<span class="print-notes">' . $store->getBaseUrl() . '</span>',
                '<span class="print-notes">' . $store->getFrontendName() . '</span>',
                '<span class="print-notes">' . $this->getStoreConfig('general/store_information/address', $storeId)
                . '</span>'
                    ), $notes);
            $notes = strip_tags($notes);
        }

        $stringArray = $this->processString($notes, $font, 8, 240);
        for ($i = 0; $i < count($stringArray); $i++) {
            $textbox = imageftbbox(8, 0, $font, $stringArray[$i]);
            imagefttext(
                $img1,
                8,
                0,
                590 - ($textbox[2] - $textbox[0]),
                $codeY + 58,
                $textColor,
                $font,
                $stringArray[$i]
            );
            $codeY += 18.5;
        }
        /* End */

        /* Insert Logo to Image */
        if ($img3) {
            $widthLogo = imagesx($img3);
            $heightLogo = imagesy($img3);
            imagecopyresampled($img2, $img3, 13, 0, 0, 0, $widthLogo, $heightLogo, $widthLogo, $heightLogo);
        }


        /* Insert Backgound Value Image */
        imagecopyresampled($img5, $img6, 381, 0, 0, 0, 219, 52, 219, 52);

        /* Insert Background Title Image */
        imagecopyresampled($img2, $img5, 0, 138, 0, 0, 600, 52, 600, 52);

        /* Draw Images */
        imagecopyresampled($img, $img2, 0, 0, 0, 0, 600, 190, 600, 190);


        /* Draw Background */
        imagecopyresampled($img, $img1, 0, 190, 0, 0, 600, 175, 600, 175);

        imagepng($img, $imgFile);
        imagedestroy($img);
    }

    /**
     * Draw the center template of Gift Card
     */
    public function generateCenterImage($giftcode, $giftcardTemplate)
    {

        $storeId = $this->_storeManager->getStore()->getId();
        $images = $this->getImagesInFolder($giftcode['gift_code']);
        if (isset($images[0]) && file_exists($images[0])) {
            unlink($images[0]);
        }

        $imageSuffix = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime')->timestamp(time());

        $imgFile = $this->getImgDir($giftcode['gift_code']) . $giftcode['gift_code'] . '-' . $imageSuffix . '.png';
        $w = 600;
        $h = 365;

        $img = imagecreatetruecolor($w, $h);
        $textColor = $this->hexColorAllocate($img, $giftcardTemplate['text_color']);
        $styleColor = $this->hexColorAllocate($img, $giftcardTemplate['style_color']);
        $bgColor = imagecolorallocate($img, 255, 255, 255);
        imagefilledrectangle($img, 0, 0, $w - 1, $h - 1, $bgColor);

        // if (isset($giftcode['giftcard_template_image']) && $giftcode['giftcard_template_image'] != null)
        $img2 = $this->createGCImage($giftcode['giftcard_template_image']);

        $img3 = $this->createGCLogo();

        $img4 = $this->createGMessageBox();

        $img5 = $this->createGCBackground('bkg-title.png');

        $img6 = $this->createGCBackground('bkg-value.png');

        $x = 10;
        $y = 33;
        $fsize = 15;
        $font = $this->_filesystem->getDirectoryRead('lib_internal')
            ->getAbsolutePath('Magestore/fonts/OpenSans-Semibold.ttf');

        /* Draw Expiry Date */
        if ($this->getGeneralConfig('show_expiry_date')) {
            if ($giftcode['expired_at']) {
                $expiryDate = __('Expired: ') . date('m/d/Y', strtotime($giftcode['expired_at']));
                $textbox = imageftbbox(9, 0, $font, $expiryDate);
                imagefttext(
                    $img2,
                    9,
                    0,
                    (580 - ($textbox[2] - $textbox[0])),
                    25,
                    imagecolorallocate($img, 255, 255, 255),
                    $font,
                    $expiryDate
                );
            }
        }


        /* Draw Text */
        $fsize = 15;
        $font = $this->_filesystem->getDirectoryRead('lib_internal')
            ->getAbsolutePath('Magestore/fonts/OpenSans-Semibold.ttf');
        $word = $giftcardTemplate['caption'];
        $textbox = imageftbbox($fsize, 0, $font, $word);

        $word = $this->processTitle($word, $font, $fsize, 370);
        // Chieu dai cua textbox: $textbox[2] - $textbox[0]
        // Chieu rong cua textbox: $textbox[7] - $textbox[1]

        imagefttext($img5, $fsize, 0, $x, $y, $styleColor, $font, $word);

        /* Print Value */

        $fontPrice = $this->_filesystem->getDirectoryRead('lib_internal')
            ->getAbsolutePath('Magestore/fonts/OpenSans-ExtraBold.ttf');
        $fsizePrice = 14;

        $price = $this->_objectManager->get('Magento\Directory\Model\Currency')
                    ->setData('currency_code', $giftcode['currency'])
                    ->format($giftcode['balance'], array('display' => 2), false);
       
        $textbox = imageftbbox($fsizePrice, 0, $fontPrice, $price);
        $valueX = $textbox[2] - $textbox[0];
        imagefttext($img6, $fsizePrice, 0, 210 - $valueX, $y + 3, $styleColor, $font, $price);

        $fsizeValue = 9;
        $textbox = imageftbbox($fsizeValue, 0, $fontPrice, __('Value '));
        $valueX += $textbox[2] - $textbox[0] + 15;
        imagefttext($img6, $fsizeValue, 0, 210 - $valueX, $y, $styleColor, $font, __('Value '));

        /* Print "From:" and "To: " */

        $y += 135;
        $textbox = imageftbbox($fsize, 0, $font, __('From: '));
        imagefttext($img2, 13, 0, 15, $y - 5, $textColor, $font, __('From: '));
        imagefttext(
            $img2,
            13,
            0,
            $x + ($textbox[2] - $textbox[0]),
            $y - 5,
            $styleColor,
            $font,
            $giftcode['customer_name']
        );
        $y -= 1.55 * ($textbox[7] - $textbox[1]);

        $textbox = imageftbbox($fsize, 0, $font, __('To: '));
        imagefttext($img2, 13, 0, 15, $y, $textColor, $font, __('To: '));
        imagefttext(
            $img2,
            13,
            0,
            $x + ($textbox[2] - $textbox[0]),
            $y,
            $styleColor,
            $font,
            $giftcode['recipient_name']
        );
        $y -= 1.55 * ($textbox[7] - $textbox[1]);

        /* Print Gift Code */

        $fontCode = $this->_filesystem->getDirectoryRead('lib_internal')
            ->getAbsolutePath('Magestore/fonts/OpenSans-SemiboldItalic.ttf');
        $codeY = 160;
        $textbox = imageftbbox(11, 0, $fontCode, $giftcode['gift_code']);
        imagefttext(
            $img2,
            11,
            0,
            590 - ($textbox[2] - $textbox[0]),
            $codeY,
            $styleColor,
            $fontCode,
            $giftcode['gift_code']
        );
        $codeY -= ($textbox[7] - $textbox[1]);

        /* Print Barcode */

        $barcode = $this->getGeneralConfig('barcode_enable');
        if ($barcode) {
            $newImgBarcode = $this->resizeBarcodeImage($giftcode['gift_code']);
            $newImgBarcodeX = imagesx($newImgBarcode);
            $newImgBarcodeY = imagesy($newImgBarcode);
            imagecopyresampled(
                $img2,
                $newImgBarcode,
                590 - $newImgBarcodeX,
                $codeY - 5,
                0,
                0,
                $newImgBarcodeX,
                $newImgBarcodeY,
                $newImgBarcodeX,
                $newImgBarcodeY
            );
        }

        /* Print Customers' s messages */

        $xMessage = 5;
        $yMessage = 15;

        if (isset($giftcode['message']) && $giftcode['message'] != null) {
            $message = $giftcode['message'];
        } else {
            $message = '';
        }

        $stringArray = $this->processString($message, $font, 9, 568);

        for ($i = 0; $i < count($stringArray); $i++) {
            imagefttext($img4, 9, 0, $xMessage, $yMessage, $textColor, $font, $stringArray[$i]);
            $yMessage -= 1.25 * ($textbox[7] - $textbox[1]);
        }

        imagecopyresampled($img2, $img4, 16, $y + 5, 0, 0, 568, 97, 568, 97);

        /* Print Notes */

        $y += 110;

        if (isset($giftcardTemplate['notes']) && $giftcardTemplate['notes'] != null) {
            $notes = $giftcardTemplate['notes'];
        } else {
            $notes = $this->getStoreConfig('giftvoucher/print_voucher/note', $storeId);
            $notes = str_replace(array(
                '{store_url}',
                '{store_name}',
                '{store_address}'
                    ), array(
                '<span class="print-notes">' . $this->_storeManager->getStore($storeId)->getBaseUrl() . '</span>',
                '<span class="print-notes">' . $this->_storeManager->getStore($storeId)->getFrontendName() . '</span>',
                '<span class="print-notes">' . $this->getStoreConfig('general/store_information/address', $storeId)
                . '</span>'
                    ), $notes);
            $notes = strip_tags($notes);
        }

        $stringArray = $this->processString($notes, $font, 9, 570);
        for ($i = 0; $i < count($stringArray); $i++) {
            imagefttext($img2, 9, 0, 16, $y + 10, $textColor, $font, $stringArray[$i]);
            $y -= 1.55 * ($textbox[7] - $textbox[1]);
        }

        /* End */

        /* Insert Logo to Image */
        if ($img3) {
            $widthLogo = imagesx($img3);
            $heightLogo = imagesy($img3);
            imagecopyresampled($img2, $img3, 13, 0, 0, 0, $widthLogo, $heightLogo, $widthLogo, $heightLogo);
        }


        /* Insert Backgound Value Image */
        imagecopyresampled($img5, $img6, 381, 0, 0, 0, 219, 52, 219, 52);

        /* Insert Background Title Image */
        imagecopyresampled($img2, $img5, 0, 85, 0, 0, 600, 52, 600, 52);

        /* Draw Images */
        imagecopyresampled($img, $img2, 0, 0, 0, 0, 600, 365, 600, 365);

        imagepng($img, $imgFile);
        imagedestroy($img);
    }

    /**
     * Draw message to Image
     *
     * @param string $txt
     * @param string $font
     * @param int $fsize
     * @param int $widthBackground
     * @return array
     */
    public function processString($txt, $font, $fsize, $widthBackground)
    {

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
                if (!isset($result[$count])) {
                    $result[$count] = '';
                }

                $result[$count] .= $strArr[$i] . ' ';
            }
        }

        return $result;
    }

    /**
     * Draw title to Image
     *
     * @param string $txt
     * @param string $font
     * @param int $fsize
     * @param int $widthBackground
     * @return array
     */
    public function processTitle($txt, $font, $fsize, $widthBackground)
    {

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

                if ($length >= ($widthBackground - 6 * $inc)) {
                    break;
                }

                $result .= $txt[$i];
            }
        }

        return $result;
    }

    /**
     * Convert Image object
     */
    public function imagecreatefromfile($filename)
    {
        if (!file_exists($filename)) {
            throw new \Exception('File "' . $filename . '" not found.');
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
                throw new \Exception('File "' . $filename . '" is not valid jpg, png or gif image.');
                break;
        }
    }

    /**
     * Create a Gift Card image object
     */
    public function createGCImage($filename, $type = null)
    {
        if (isset($type) && $type != null) {
            $dir = $this->getBaseDirMedia()->getAbsolutePath('giftvoucher/template/images/' . $type . '/' . $filename);
        } else {
            $dir = $this->getBaseDirMedia()->getAbsolutePath('giftvoucher/template/images/' . $filename);
        }

        if (($filename == null) || (!file_exists($dir))) {
            $dir = $this->getBaseDirMedia()->getAbsolutePath('giftvoucher/draw/default.png');
        }

        return $this->imagecreatefromfile($dir);
    }

    /**
     * Create a Gift Card background object
     */
    public function createGCBackground($filename, $type = null)
    {
        if ($filename) {
            if (isset($type) && $type != null) {
                $dir = $this->getBaseDirMedia()
                    ->getAbsolutePath('giftvoucher/template/background/' . $type . '/' . $filename);
            } else {
                $dir = $this->getBaseDirMedia()
                ->getAbsolutePath('giftvoucher/template/background/' . $filename);
            }
        } else {
            $dir = $this->getBaseDirMedia()->getAbsolutePath('giftvoucher/draw/default.png');
        }

        return $this->imagecreatefromfile($dir);
    }

    /**
     * Create a Gift Card logo object
     */
    public function createGCLogo()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $image = $this->getStoreConfig('giftvoucher/print_voucher/logo', $storeId);
        if ($image) {
            $dir = $this->getBaseDirMedia()->getAbsolutePath('giftvoucher/pdf/logo/' . $image);
            $imgLogo = $this->imagecreatefromfile($dir);
            $newWidth = round(63 * imagesx($imgLogo) / imagesy($imgLogo));
            $resizeLogoUrl = $this->getBaseDirMedia()->getAbsolutePath('giftvoucher/draw/logo/' . $image);

            if (!is_file($resizeLogoUrl)) {
                $resizeLogoObj = $this->_imageFactory->create();
                $resizeLogoObj->open($dir);
                $resizeLogoObj->constrainOnly(true);
                $resizeLogoObj->keepAspectRatio(true);
                $resizeLogoObj->keepFrame(false);
                $resizeLogoObj->keepTransparency(true);
                $resizeLogoObj->resize($newWidth, 63);
                $resizeLogoObj->save($resizeLogoUrl);
            }
            return $this->imagecreatefromfile($resizeLogoUrl);
        } else {
            return false;
        }
    }

    /**
     * Create a Gift Card message image object
     */
    public function createGMessageBox($type = null)
    {
        if (isset($type) && $type != null) {
            $dir = $this->getBaseDirMedia()
                ->getAbsolutePath('giftvoucher/template/messagebox/' . $type . '/' . 'default.png');
        } else {
            $dir = $this->getBaseDirMedia()->getAbsolutePath('giftvoucher/template/messagebox/default.png');
        }
        return $this->imagecreatefromfile($dir);
    }

    /**
     * Resize Barcode image
     */
    public function resizeBarcodeImage($code)
    {
        $barcode = $this->getGeneralConfig('barcode_enable');
        $barcodeType = $this->getGeneralConfig('barcode_type');

        if ($barcodeType == 'code128') {
            $barcodeUrl = $this->getBaseDirMedia()->getAbsolutePath('giftvoucher/template/barcode/' . $code . '.png');

            $resizeBarcodeUrl = $this->getBaseDirMedia()->getAbsolutePath('giftvoucher/draw/' . $code . '/barcode.png');
            $resizeBarcodeObj = $this->_imageFactory->create();
            $resizeBarcodeObj->open($barcodeUrl);
            $resizeBarcodeObj->getImage();
            $resizeBarcodeObj->constrainOnly(true);
            $resizeBarcodeObj->keepAspectRatio(true);
            $resizeBarcodeObj->keepFrame(false);
            $resizeBarcodeObj->resize(180, 40);
            $resizeBarcodeObj->save($resizeBarcodeUrl);

            return imagecreatefrompng($resizeBarcodeUrl);
        } else {
            $qr = new \Magestore_Giftvoucher_QRCode($code);
            $content = file_get_contents($qr->getResult());
            $fileName = $this->getBaseDirMedia()->getAbsolutePath('giftvoucher/draw/' . $code . '/' . 'qrcode.png');
            file_put_contents($fileName, $content);
            $resizeBarcodeObj = $this->_imageFactory->create();
            $resizeBarcodeObj->open($fileName);
            $resizeBarcodeObj->getImage();
            $resizeBarcodeObj->constrainOnly(true);
            $resizeBarcodeObj->keepAspectRatio(true);
            $resizeBarcodeObj->keepFrame(false);
            $resizeBarcodeObj->resize(180, 40);
            $resizeBarcodeObj->save($fileName);

            return imagecreatefrompng($fileName);
        }
    }

    /**
     * Allocate color for an image
     */
    public function hexColorAllocate($img, $hex)
    {
        $hex = ltrim($hex, '#');
        $a = hexdec(substr($hex, 0, 2));
        $b = hexdec(substr($hex, 2, 2));
        $c = hexdec(substr($hex, 4, 2));
        return imagecolorallocate($img, $a, $b, $c);
    }

    /**
     * Get the directory of gift code image
     *
     * @param string $code
     * @return string
     */
    public function getImagesInFolder($code)
    {
        $directory = $this->getBaseDirMedia()->getAbsolutePath('giftvoucher/draw/' . $code . '/');
        return glob($directory . $code . "*.png");
    }
}
