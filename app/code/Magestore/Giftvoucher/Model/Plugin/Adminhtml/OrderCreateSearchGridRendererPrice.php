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
namespace Magestore\Giftvoucher\Model\Plugin\Adminhtml;

/**
 * Giftvoucher Plugin BackButton
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class OrderCreateSearchGridRendererPrice
{
    /**
     * @return array
     */
    public function beforeRender(\Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Price $price, $row)
    {
        if ($row->getTypeId() == 'giftvoucher') {
            $row->setPrice($row->getGiftValue());
        }
    }
}
