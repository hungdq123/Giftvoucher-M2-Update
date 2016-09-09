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
 * Adminhtml Giftvoucher Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Giftvoucher extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_giftvoucher';
        $this->_blockGroup = 'Magestore_Giftvoucher';
        $this->_headerText = __('Gift Code Manager');
        $this->buttonList->add(
            'importgiftcode',
            array(
                'label' => __('Import Gift Codes'),
                'class' => 'delete primary',
                'on_click' =>  'setLocation("'.$this->getUrl('*/*/import', array()).'")'
            ),
            -100
        );
        $this->_addButtonLabel = __('Add Gift Code');
        parent::_construct();
        
    }
}
