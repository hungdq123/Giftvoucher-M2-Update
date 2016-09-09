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

namespace Magestore\Giftvoucher\Controller\Index;

use Magento\Customer\Model\Session;

/**
 * Giftvoucher Index CustomUpload Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class CustomUpload extends \Magestore\Giftvoucher\Controller\Action
{
   
    public function execute()
    {
        try {
            $customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');
            if ($customerSession->getGiftcardCustomUploadImage()) {
                $this->getHelperData()->deleteImageFile($customerSession->getGiftcardCustomUploadImage());
            }
            $uploader = $this->_objectManager->create('Magento\Framework\File\Uploader', array('fileId' => 'image'));
            $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);
            $this->getHelperData()->createImageFolderHaitv('', '', true);
            $result = $uploader->save(
                $this->getFileSystem()->getDirectoryRead('media')->getAbsolutePath('tmp/giftvoucher/images')
            );
            $result['url'] = $this->_storeManager->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
                . 'tmp/giftvoucher/images/' . $result['file'];
            $customerSession->setGiftcardCustomUploadImage($result['url']);
            $customerSession->setGiftcardCustomUploadImageName($result['file']);
            $this->getHelperData()->resizeImage($result['url']);
        } catch (\Exception $e) {
            $result = array('error' => $e->getMessage(), 'errorcode' => $e->getCode());
        }
        $this->getResponse()->setBody(
            $this->_objectManager->create('\Magento\Framework\Json\Helper\Data')->jsonEncode($result)
        );
        
    }
}
