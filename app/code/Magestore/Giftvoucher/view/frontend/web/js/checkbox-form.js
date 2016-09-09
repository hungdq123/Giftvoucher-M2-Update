/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true expr:true*/
define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";
    
    $.widget('mage.checkboxForm', {
        options: {
        },

        _create: function() {
            this.element.on('change', $.proxy(function(event) {
                $(event.target).is(':checked') ? this._showGiftData() : this._hideGiftData();
            }, this));
            this.options.showOnDefault && this._showGiftData();
        },

        /**
         * Hide password input fields
         * @private
         */
        _hideGiftData: function() {
            $(this.options.formInfor).hide();
        },

        /**
         * Show password input fields
         * @private
         */
        _showGiftData: function() {
            $(this.options.formInfor).show();
        }
    });
    
    return $.mage.checkboxForm;
});