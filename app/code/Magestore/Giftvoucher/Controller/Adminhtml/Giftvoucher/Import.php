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

namespace Magestore\Giftvoucher\Controller\Adminhtml\Giftvoucher;

/**
 * Adminhtml Giftvoucher Import Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Import extends \Magestore\Giftvoucher\Controller\Adminhtml\Giftvoucher
{
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->addBreadcrumb(__('Gift Code Manager'), __('Gift Code Manager'))
            ->addBreadcrumb(__('Import Gift Code'), __('Import Gift Code'))
            ->setActiveMenu('Magestore_Giftvoucher::giftvoucher');
        $resultPage->getConfig()->getTitle()->prepend(__('Gift Code'));

        return $resultPage;
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magestore_Giftvoucher::giftvoucher');
    }
}
