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
 * Giftvoucher Index View controller
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class View extends \Magestore\Giftvoucher\Controller\Action
{
    /**
     * Giftvoucher View action
     */
    public function execute()
    {
        if (!$this->customerLoggedIn()) {
            $resultRedirectFactory = $this->getRedirectFactory()
                ->setPath('customer/account/login', array('_secure' => true));
            return $resultRedirectFactory;
        }
        $resultPageFactory = $this->getPageFactory();
        $resultPageFactory->getConfig()->getTitle()->set('Gift Card Detail');
        /** @var \Magento\Framework\View\Element\Html\Links $navigationBlock */
        $navigationBlock = $resultPageFactory->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('giftvoucher');
        }
        return $resultPageFactory;
    }
}
