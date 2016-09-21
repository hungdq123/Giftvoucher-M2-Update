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

namespace Magestore\Giftvoucher\Block\Adminhtml\Gifttemplate;

/**
 * Adminhtml GiftCard Template Edit Block
 *
 * @category Magestore
 * @package  Magestore_Gifttemplate
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;


    /**
     * Edit constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {

        $this->_objectId = 'id';
        $this->_blockGroup = 'Magestore_Giftvoucher';
        $this->_controller = 'adminhtml_gifttemplate';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save'));
        $this->buttonList->update('delete', 'label', __('Delete'));
        $this->buttonList->add(
            'preview',
            array(
                    'label' => __('Preview'),
                    'class' => 'save',
                    'onclick' => "previewImage()",
            ),
            -90
        );
        $this->buttonList->add(
            'saveandcontinue',
            array(
                'label' => __('Save And Continue Edit'),
                'class' => 'save',
                'data_attribute' => array(
                    'mage-init' => array('button' => array('event' => 'saveAndContinueEdit', 'target' => '#edit_form'))
                )
            ),
            -100
        );

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('block_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'hello_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'hello_content');
                }
            }
            function previewImage(element){
                edit_form=$('edit_form').serialize(true);
                form_data=Object.toJSON(edit_form);
                new Ajax.Request('"
                . $this->getUrl('*/*/previewimage', array('_current' => true))
                . "', {
                            method:'post',
                            parameters: {

                                         form_key: FORM_KEY,
                                         value: element,
                                         form_data:form_data  
                                         },
                            evalScripts: true,
                            onSuccess: function(transport) {
                               TINY.box.show('');
                                $('tinycontent').update(transport.responseText);
                            }
                        });
            }
            function removeImage(element){
                
                new Ajax.Request('"
                . $this->getUrl('*/*/removeimage', array('_current' => true))
                . "', {
                            parameters: {
                                         form_key: FORM_KEY,
                                         value: element,
                                         
                                         },
                            evalScripts: true,
                            onSuccess: function(transport) {
                                if(transport.responseText=='success'){
                                 $(element).remove();
                                 if(!$('fileuploaded').down('img')) $('fileuploaded').up('label').hide();
                                }
                            }
                        });
            }
            //window.onload = function(){};
            function changePattern(){
                $('giftcard-notes-center').hide();
                $('giftcard-notes-top').hide();
                $('giftcard-notes-left').hide();
                template_id=$('design_pattern').value;
                $('demo_pattern').down('img').src='" . $this->_storeManager->getStore()
                    ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . '/giftvoucher/template/pattern/GC_'
                    . "'+template_id+'.jpg';
                if(template_id == " . \Magestore\Giftvoucher\Model\Gifttemplate\Type::TYPE_CENTER . ")
                {
//                    $('background_img').up('div').up('div').hide();
                    $('giftcard-notes-center').show();
                    $('style_color-note').innerHTML = '" . __('Choose color of texts in Gift Cart title, value and gift code fields. (Recommended color: #FFFFFF)') . "';
                    $('text_color-note').innerHTML = '" . __('Choose color of other texts (fields title, notes, etc.). (Recommended color: #A9A7A7)') . "';
                }
                else {
//                    $('background_img').up('div').up('div').show();
                    if (template_id == ".\Magestore\Giftvoucher\Model\Gifttemplate\Type::TYPE_TOP."){
                            //('background_img-note').innerHTML = '600x175. Support jpg, jpeg, gif, png files';
                            $('giftcard-notes-top').show();
                            $('style_color-note').innerHTML = '".__('Choose color of texts in Gift Cart title, value and gift code fields. (Recommended color: #FFFFFF)')."';
                            $('text_color-note').innerHTML = '".__('Choose color of other texts (fields title, notes, etc.). (Recommended color: #636363)')."';
                    }	
                    if (template_id == ".\Magestore\Giftvoucher\Model\Gifttemplate\Type::TYPE_LEFT."){
                            //$('background_img-note').innerHTML = '350x365. Support jpg, jpeg, gif, png files';
                            $('giftcard-notes-left').show();
                            $('style_color-note').innerHTML = '".__('Choose color of texts in Gift Cart title, value and gift code fields. (Recommended color: #DC8C71)')."';
                            $('text_color-note').innerHTML = '".__('Choose color of other texts (fields title, notes, etc.). (Recommended color: #949392)')."';	
                    }	
                }	
            }
            require(['jquery', 'Magestore_Giftvoucher/js/jscolor/jscolor'], function($) {
            });
            require(['jquery', 'Magestore_Giftvoucher/js/uploadimage'], function($) {
            });
            require(['jquery', 'Magestore_Giftvoucher/js/tinybox/tinybox'], function($) {
                jQuery('.preview-img').click(function(){
                    alert('3');
                });
            });
        ";
    }

    /**
     * Get edit form container header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('gifttemplate_data')
            && $this->_coreRegistry->registry('gifttemplate_data')->getId()) {
            return __(
                "Edit Gift Card Template '%s'",
                $this->escapeHtml($this->_coreRegistry->registry('gifttemplate_data')->getCaption())
            );
        } else {
            return __('New Gift Card Template');
        }
    }
}
