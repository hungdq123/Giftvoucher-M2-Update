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
 * Giftvoucher Index PrintAction
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class PrintAction extends \Magestore\Giftvoucher\Controller\Action
{
    public function execute()
    {
        $resultPage = $this->getPageFactory();
        $linked = $this->getModel('Magestore\Giftvoucher\Model\Customervoucher')
            ->load($this->getRequest()->getParam('id'));
        if ($linked->getCustomerId() != $this->getSingleton('Magento\Customer\Model\Session')->getCustomerId()) {
            return $this->_redirect('*/*/index');
        }
        return $resultPage;
    }
}
