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

namespace Magestore\Giftvoucher\Block\Adminhtml;

/**
 * Adminhtml GiftCard Template Block
 *
 * @category Magestore
 * @package  Magestore_Gifttemplate
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Gifttemplate extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_gifttemplate';
        $this->_blockGroup = 'Magestore_Giftvoucher';
        $this->_headerText = __('Gift Card Template Manager');
        $this->_addButtonLabel = __('Add Gift Card Template');
        parent::_construct();
    }
}
