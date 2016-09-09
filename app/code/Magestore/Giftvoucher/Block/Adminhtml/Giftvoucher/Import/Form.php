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
namespace Magestore\Giftvoucher\Block\Adminhtml\Giftvoucher\Import;

/**
 * Adminhtml Giftvoucher Import Form Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Form extends \Magestore\Giftvoucher\Block\Adminhtml\Giftvoucher\Edit\Tab\Abstractgiftvoucher
{

    protected function _prepareForm()
    {
        
        $form = $this->_formFactory->create(['data' => array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/processImport'),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        )]);

        $fieldset = $form->addFieldset('profile_fieldset', array('legend' => __('Import Form')));

        $fieldset->addField('filecsv', 'file', array(
            'label' => __('Import File'),
            'title' => __('Import File'),
            'name' => 'filecsv',
            'required' => true,
        ));

        $fieldset->addField('sample', 'note', array(
            'label' => __('Download Sample CSV File'),
            'text' => '<a href="' .
            $this->getUrl('*/*/downloadSample') .
            '" title="' .
            __('Download Sample CSV File') .
            '">import_giftcode_sample.csv</a>'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
