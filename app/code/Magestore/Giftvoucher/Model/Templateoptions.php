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
namespace Magestore\Giftvoucher\Model;

/**
 * Giftvoucher View Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Templateoptions extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @var \Magestore\Giftvoucher\Model\Gifttemplate
     */
    protected $_giftcardTemplate;
    
    /**
     *
     * @param \Magestore\Giftvoucher\Model\Gifttemplate $giftcardTemplate
     */
    public function __construct(
        \Magestore\Giftvoucher\Model\Gifttemplate $giftcardTemplate
    ) {
        $this->_giftcardTemplate = $giftcardTemplate;
    }
    
    
    /**
     * Get Gift Card available templates
     *
     * @return array
     */
    public function getAvailableTemplate()
    {
        $templates = $this->_giftcardTemplate->getCollection()
                ->addFieldToFilter('status', '1');
        $listTemplate = array();
        foreach ($templates as $template) {
            $listTemplate[] = array('label' => $template->getTemplateName(),
                'value' => $template->getId());
        }
        return $listTemplate;
    }

    /**
     * Get model option as array
     *
     * @return array
     */
    public function getAllOptions($withEmpty = true)
    {
        if (is_null($this->_options)) {
            $this->_options = $this->getAvailableTemplate();
        }
        $options = $this->_options;
        if ($withEmpty) {
            array_unshift($options, array(
                'value' => '',
                'label' => __('-- Please Select --'),
            ));
        }
        return $options;
    }

    public function getDefaultData() {
        $templates = $this->getAvailableTemplate();
        return $templates[0]['value'];

    }
}
