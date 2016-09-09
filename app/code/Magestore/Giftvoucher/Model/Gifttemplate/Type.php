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

namespace Magestore\Giftvoucher\Model\Gifttemplate;

/**
 * Giftvoucher GiftCard Template Type Model
 *
 * @category    Magestore
 * @package     Magestore_Giftvoucher
 * @author      Magestore Developer
 */
class Type extends \Magento\Framework\DataObject
{

    const TYPE_LEFT = 1;
    const TYPE_TOP = 2;
    const TYPE_CENTER = 3;

    /**
     * Get the gift cart template type options as array
     *
     * @return array
     */
    public static function getOptionArray()
    {
        return array(
            self::TYPE_LEFT => __('Left'),
            self::TYPE_TOP => __('Top'),
            self::TYPE_CENTER => __('Center')
        );
    }

    /**
     * Get the gift cart template type options
     *
     * @return array
     */
    public static function getOptions()
    {
        $options = array();
        foreach (self::getOptionArray() as $value => $label) {
            $options[] = array(
                'value' => $value,
                'label' => $label
            );
        }
        return $options;
    }

    public function toOptionArray()
    {
        return self::getOptions();
    }
}
