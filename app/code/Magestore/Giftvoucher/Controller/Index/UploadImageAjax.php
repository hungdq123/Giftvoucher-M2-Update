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
 * Giftvoucher Index UploadImageAjax Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class UploadImageAjax extends \Magestore\Giftvoucher\Controller\Action
{
   
    /**
     * Upload images action
     */
    public function execute()
    {
        $result = array();
        if (isset($_FILES['templateimage'])) {
            $error = $_FILES["templateimage"]["error"];

            try {
                $uploader = $this->_objectManager->create(
                    'Magento\Framework\File\Uploader',
                    array('fileId' => 'templateimage')
                );
                $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);
                $this->getHelper()->createImageFolderHaitv('', '', true);
                $fileName = $_FILES['templateimage']['name'];
                $result = $uploader->save(
                    $this->getFileSystem()->getDirectoryRead('media')->getAbsolutePath('tmp/giftvoucher/images')
                );
                $result['tmp_name'] = $result['tmp_name'];
                $result['path'] = $result['path'];
                $result['url'] = $this->_storeManager->getStore()
                    ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
                    . 'tmp/giftvoucher/images/' . $result['file'];

                $result['filename']= $fileName;
                $result['sucess'] = true;
            } catch (\Exception $e) {
                $result['sucess'] = false;
                $result = array('error' => $e->getMessage(), 'errorcode' => $e->getCode());
            }
        } else {
            $this->messageManager->addError(__('Image Saving Error!'));
            $result['sucess'] = false;
            $result = array('error' => __('Image Saving Error!'));
        }
        $this->getResponse()->setBody(
            $this->_objectManager->create('\Magento\Framework\Json\Helper\Data')->jsonEncode($result)
        );
        
        
    }
}
