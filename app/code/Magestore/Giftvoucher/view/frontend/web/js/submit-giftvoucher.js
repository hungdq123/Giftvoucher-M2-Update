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
    
    $.widget('mage.submitGiftvoucher', {
        options: {
            checkboxGiftcard: '#giftvoucher',
            checkboxGiftcredit: '#giftvoucher_credit',
            formGiftcard: 'dd.giftvoucher',
            formGiftcredit: 'dd.giftvoucher_credit',
            formButton: '#giftcard_shoppingcart_apply',
            buttonSubmit: '#apply_giftvoucher',
            giftvoucherInput: "#giftvoucher_code",
            existedCode: '#giftvoucher_existed_code',
            giftcardNotice: '#giftcard_notice',
            giftcreditNotice: '#giftcredit_notice',
            discountGiftcardForm: '#discount-giftcard-form',
            giftcardShoppingcartReloading: '#giftcard_shoppingcart_reloading',
            inputCreditAmount: '#credit_amount',
            giftcardChange: '.giftcard_change',
            discountCode: '.giftvoucher-discount-code'
        },

        _create: function() {
            this.element.on('click', this.options.checkboxGiftcard , $.proxy(function(event) {
                $(event.target).is(':checked') ? this._showFormGiftcard() : this._hideFormGiftcard();
            }, this));
            this.element.on('click', this.options.checkboxGiftcredit , $.proxy(function(event) {
                $(event.target).is(':checked') ? this._showFormGiftcredit() : this._hideFormGiftcredit();
            }, this));
            if($(this.options.checkboxGiftcard).is(':checked')){
                this._showFormGiftcard();
            }
            if($(this.options.checkboxGiftcredit).is(':checked')){
                this._showFormGiftcredit();
            }
            this.element.on('click', this.options.buttonSubmit , $.proxy(this._submitForm, this));
            this.element.on('click', this.options.giftcardChange , $.proxy(function(event) {
                $(event.target).parents('label').find('input')[0].style.display='';
                $(event.target).parents('label').find('input')[0].removeAttribute('disabled');
                $(event.target).parents('label').find('span')[0].style.display='none';
            }, this));
            
        },

        /**
         * Hide form gift card
         * @private
         */
        _hideFormGiftcard: function() {
            $(this.options.formGiftcard).hide();
            if(!$(this.options.checkboxGiftcard).is(':checked')){
                $(this.options.formButton).hide();
                $(this.options.giftcardShoppingcartReloading).show();
                $(this.options.discountGiftcardForm).submit();
            }
        },

        /**
         * Show form gift card
         * @private
         */
        _showFormGiftcard: function() {
            $(this.options.formGiftcard).show();
            $(this.options.formButton).show();
        },
        
        /**
         * Hide form gift card
         * @private
         */
        _hideFormGiftcredit: function() {
            $(this.options.formGiftcredit).hide();
            if(!$(this.options.checkboxGiftcredit).is(':checked')){
                $(this.options.formButton).hide();
                $(this.options.giftcardShoppingcartReloading).show();
                $(this.options.discountGiftcardForm).submit();
            }
        },

        /**
         * Show form gift card
         * @private
         */
        _showFormGiftcredit: function() {
            $(this.options.formGiftcredit).show();
            $(this.options.formButton).show();
        },
        
        /**
         * Submit Gift Card form
         * @private
         */
        _submitForm: function(){
            var giftcredit_checked = false;
            if($(this.options.checkboxGiftcredit)) giftcredit_checked = $(this.options.checkboxGiftcredit).is(':checked');
            if(giftcredit_checked)
            {
                if(jQuery('dd.giftvoucher_credit .input-text')[0].value==0)
                {
                      if(jQuery('#giftcredit_notice')[0]!=null) jQuery('#giftcredit_notice').show();
                      if(jQuery('dd.giftvoucher_credit')[0]!=null) jQuery('dd.giftvoucher_credit .input-text').addClass('mage-error');
                } 
                else
                {
                    if(jQuery('#giftcredit_notice')[0]!=null) jQuery('#giftcredit_notice').hide();
                    if(jQuery('dd.giftvoucher_credit')[0]!=null) jQuery('dd.giftvoucher_credit .input-text').removeClass('mage-error');
                    if(jQuery('#giftcard_notice')[0]!=null) jQuery('#giftcard_notice').text('');
                    $(this.element).submit();     
                    return;
                }
            }
            if($(this.options.checkboxGiftcard).is(':checked')){
                if($(this.options.discountCode)[0]!=null || (giftcredit_checked && $(this.options.formGiftcredit)[0]!=null && $(this.options.inputCreditAmount).val()!=0))
                {
                    if(jQuery('#giftcredit_notice')[0]!=null) jQuery('#giftcredit_notice').show();
                    $(this.element).submit();   
                }
                else
                {
                    if($(this.options.giftvoucherInput).attr('value')!=''){
                        if(jQuery('#giftcredit_notice').length>0) jQuery('#giftcredit_notice').hide();
                        if($(this.options.giftcardNotice).length>0) $(this.options.giftcardNotice).text('');
                        $(this.element).submit();
                    }else{
                        if($(this.options.existedCode).length==0){
                            if($(this.options.giftvoucherInput).length>0) $(this.options.giftvoucherInput).addClass('mage-error');
                            if($(this.options.giftcardNotice).length>0) $(this.options.giftcardNotice).text(this.options.enterYourCode);
                        }
                        if($(this.options.existedCode).length!=0 && $(this.options.existedCode).attr('value')=='')
                        {   
                            if($(this.options.giftvoucherInput).length>0) $(this.options.giftvoucherInput).addClass('mage-error');
                            $(this.options.existedCode).addClass('mage-error');
                            if($(this.options.giftcardNotice).length>0) $(this.options.giftcardNotice).text(this.options.enterOrChooseCode);
                        }
                        if($(this.options.existedCode).length>0 && $(this.options.existedCode).attr('value')!='')
                        {
                            if($(this.options.giftcreditNotice).length>0) $(this.options.giftcardNotice).hide();
                            if($(this.options.giftcardNotice).length>0) $(this.options.giftcardNotice).text('');
                            $(this.element).submit();
                        }
                    }
                }
            }
        }
        
    });
    
    return $.mage.checkboxForm;
});