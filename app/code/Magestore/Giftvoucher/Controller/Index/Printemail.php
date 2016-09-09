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
 * Giftvoucher Index Printemail Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Printemail extends \Magestore\Giftvoucher\Controller\Action
{
    public function execute()
    {
        $resultPage = $this->getPageFactory();
        if ($key = $this->getRequest()->getParam('k')) {
            $keyDecode = explode('$', base64_decode($key));
            $giftvoucher = $this->getModel('Magestore\Giftvoucher\Model\Giftvoucher')->load($keyDecode[1]);
            if ($giftvoucher && $giftvoucher->getId() && $giftvoucher->getGiftCode() == $keyDecode[0]) {
                $this->getRequest()->setParam('id', $giftvoucher->getId());
                return $resultPage;
            }
        } else {
            return $this->_redirect('*/*/index');
        }
    }
}
