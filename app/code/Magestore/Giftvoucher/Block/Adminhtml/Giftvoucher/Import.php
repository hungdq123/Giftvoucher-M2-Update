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
namespace Magestore\Giftvoucher\Block\Adminhtml\Giftvoucher;

/**
 * Adminhtml Giftvoucher Import Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Import extends \Magento\Backend\Block\Widget\Form\Container
{

    public function _construct()
    {
        parent::_construct();
        $this->_blockGroup = 'Magestore_Giftvoucher';
        $this->_controller = 'adminhtml_giftvoucher';
        $this->_mode = 'import';
        $this->buttonList->update('save', 'label', __('Import'));
        $this->buttonList->remove('delete');
        $this->buttonList->remove('reset');
        
        $this->buttonList->add(
            'print',
            array(
                'label' => __('Import and Print'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'save',
                            'target' => '#edit_form',
                            'eventData' => ['action' => ['args' => ['print' => 'print']]],
                        ],
                    ],
                ]
            ),
            100
        );
    }
}
