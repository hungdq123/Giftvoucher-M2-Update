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

namespace Magestore\Giftvoucher\Controller\Checkout;

use Magento\Customer\Model\Session;

/**
 * Giftvoucher Checkout ReloadData Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class ReloadData extends \Magestore\Giftvoucher\Controller\Action
{
    public function execute()
    {
        $result = $this->getModel('Magestore\Giftvoucher\Block\Payment\Form')->getAllGiftvoucherData();
        return $this->getResponse()->setBody(\Zend_Json::encode($result));
    }
}
