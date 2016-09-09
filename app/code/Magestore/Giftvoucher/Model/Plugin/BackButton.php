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
namespace Magestore\Giftvoucher\Model\Plugin;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Context;
/**
 * Giftvoucher Plugin BackButton
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class BackButton
{
    /**
     * BackButton constructor
     *
     * @param Context $context
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        Registry $registry
    ) {
        $this->context = $context;
        $this->registry = $registry;
    }

    /**
     * @return array
     */
    public function afterGetButtonData(\Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Back $button, $result)
    {
        $type = $this->context->getRequestParam('type');
        if($type != 'giftvoucher'){
            return $result;
        }
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $button->getUrl('giftvoucheradmin/giftproduct/')),
            'class' => 'back',
            'sort_order' => 10
        ];
    }
}
