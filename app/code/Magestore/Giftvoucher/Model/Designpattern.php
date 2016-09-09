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
 * Giftvoucher Designpattern Model
 *
 * @category    Magestore
 * @package     Magestore_Giftvoucher
 * @author      Magestore Developer
 */
class Designpattern extends \Magento\Framework\DataObject
{
    const PATTERN_LEFT = 1;
    const PATTERN_TOP = 2;
    const PATTERN_CENTER = 3;
    
    /**
     * Get model option as array
     *
     * @return array
     */
    public static function getOptionArray()
    {
        return array(
            self::PATTERN_LEFT => __('Left'),
            self::PATTERN_TOP => __('Top'),
            self::PATTERN_CENTER => __('Center'),
        );
    }

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
