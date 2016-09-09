/**
 * Copyright © 2015 Magestore. All rights reserved.
 */
define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'mage/storage',
        'Magestore_Giftvoucher/js/model/giftvoucher',
        'Magento_Checkout/js/model/quote',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/action/get-totals',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/payment/method-list',
        'Magento_Checkout/js/action/get-payment-information',
        'prototype',
        'Magento_Checkout/js/model/totals'
    ],
    function (
        $,
        ko,
        Component,
        storage,
        giftmodel,
        quote,
        messageList,
        getTotalsAction,
        paymentService,
        paymentMethodList,
        getPaymentInformationAction,
        totals
    ) {
        'use strict';
        var tempAllGiftvoucherData = window.giftvoucherInfo;
        var allGiftvoucherData = ko.observable(tempAllGiftvoucherData);
        var useGiftCredit = ko.observable(tempAllGiftvoucherData.useGiftcredit);
        var useGiftVoucher = ko.observable(tempAllGiftvoucherData.useGiftVoucher);
        var countGiftcard = ko.observable(tempAllGiftvoucherData.giftVoucherDiscount.length);
        var giftcards = ko.observableArray(tempAllGiftvoucherData.giftVoucherDiscount);
        var countExistedGiftcards = ko.observable(tempAllGiftvoucherData.existedGiftcards.length);
        var existedGiftcards = ko.observableArray(tempAllGiftvoucherData.existedGiftcards);
        var showCreditInput = ko.observable(false);
        var giftcardCode = ko.observable('');
        var selectedGiftcardCode = ko.observable('');
        
        return Component.extend({
            defaults: {
                template: 'Magestore_Giftvoucher/payment/giftvoucher'
            },
            
            initialize: function () {
                this._super();
                var main = this;
                ko.computed(function() {
                    return quote.totals()['base_shipping_amount'];
                }).subscribe(function() {
                    var url = 'giftvoucher/checkout/reloadData';
                    main.sentRequest(url, main);
                });
                ko.computed(function() {
                    return quote.totals()['coupon_code'];
                }).subscribe(function() {
                    var url = 'giftvoucher/checkout/reloadData';
                    main.sentRequest(url, main);
                });
            },
            
            allGiftvoucherData: allGiftvoucherData,
            useGiftCredit: useGiftCredit,
            useGiftVoucher: useGiftVoucher,
            countGiftcard: countGiftcard,
            giftcards: giftcards,
            countExistedGiftcards: countExistedGiftcards,
            existedGiftcards: existedGiftcards,
            showCreditInput: showCreditInput,
            giftcardCode: giftcardCode,
            selectedGiftcardCode: selectedGiftcardCode,
            
            getAllGiftvoucherData: function(){
                return allGiftvoucherData;
            },
            
            changeUseGiftCredit: function() {
                var url = '';
                if(useGiftCredit.call())
                    url = 'giftvoucher/checkout/giftcardcredit/giftcredit/1';
                else
                    url = 'giftvoucher/checkout/giftcardcredit';
                this.sentRequest(url, this);
            },
            
            enterUpdateCreditInput: function (data, e) {
                if (e.keyCode == 13) {
                    return false;
                }
                return true;
            },

            updateCreditInput: function(){
                var url = 'giftvoucher/checkout/creditamount/amount/';
                if(allGiftvoucherData()['useGiftcreditAmount'] == null || allGiftvoucherData()['useGiftcreditAmount'] == 0){
                    url = 'giftvoucher/checkout/giftcardcredit';
                }else{
                    url+=allGiftvoucherData()['useGiftcreditAmount'];
                }
                this.sentRequest(url, this)
            },
            
            changeUseGiftvoucher: function(){
                var url = '';
                if(useGiftVoucher.call())
                    url = 'giftvoucher/checkout/giftcard/giftvoucher/1';
                else
                    url = 'giftvoucher/checkout/giftcard';
                this.sentRequest(url, this);
            },
            
            addGiftVoucher: function(){
                var giftvoucher_code = giftcardCode.call();
                var add_code = selectedGiftcardCode.call();
                if (giftvoucher_code != '' || add_code != '') {
                    if ($$('#giftcard_notice_1')[0])
                        $$('#giftcard_notice_1')[0].style.display = "none";
                    if ($$('#giftcard_notice_2')[0])
                        $$('#giftcard_notice_2')[0].style.display = "none";
                    if ($$('#giftvoucher_code')[0])
                        $$('#giftvoucher_code')[0].setAttribute('class','form-control input-text');
                    if ($$('#giftvoucher_existed_code')[0])
                        $$('#giftvoucher_existed_code')[0].setAttribute('class','form-control input-text');
                    $$('#giftvoucher_add')[0].style.display = 'none';
                    if($$('#giftvoucher_wait')[0])
                        $$('#giftvoucher_wait')[0].style.display = 'block';

                    var url = 'giftvoucher/checkout/addgift';
	
                    if (giftvoucher_code != '')
                        url += '/code/' + giftvoucher_code;
                    if (add_code != '')
                        url += '/addcode/' + add_code;
                    
                    var main = this;
                    
                    return storage.get(
                        url,
                        true
                    ).done(
                        function (response) {
                            if(response.isJSON()){
                                var res = JSON.parse(response);
                                var needUpdate = true;
                                if (res.ajaxExpired && res.ajaxRedirect) {
                                    setLocation(res.ajaxRedirect);
                                    needUpdate = false;
                                }
                                if (needUpdate) {
                                    if (!res.html) {
                                        if($$('#giftvoucher_wait')[0])
                                            $$('#giftvoucher_wait')[0].style.display = 'none';
                                        if($$('#giftvoucher_add')[0])
                                        $$('#giftvoucher_add')[0].style.display = 'block';
                                    } else {
                                        main.resetData(res.html);
                                    }
                                    if($$('#giftvoucher_wait')[0])
                                        $$('#giftvoucher_wait')[0].hide();
                                    if($$('#giftvoucher_add')[0])
                                        $$('#giftvoucher_add')[0].show();
                                    if (res.notice) 
                                        messageList.addErrorMessage({'message': res.notice});
                                    if (res.success) 
                                        messageList.addSuccessMessage({'message': res.success});
                                    if (res.error)
                                        messageList.addErrorMessage({'message': res.error});
                                }
                            }else{
                                if($$('#giftvoucher_wait')[0])
                                    $$('#giftvoucher_wait')[0].hide();
                                if($$('#giftvoucher_add')[0])
                                    $$('#giftvoucher_add')[0].show();
                                alert(response);
                            }
                        }
                    ).fail(
                        function (response){
                            $$('#giftvoucher_add')[0].style.display = 'block';
                            $$('#giftvoucher_wait')[0].style.display = 'none';
                        }
                    )
                }else {
                    if (giftvoucher_code == '' && add_code == '')
                    {
                        if ($$('#giftcard_notice_1')[0] && ($$('#giftcard_notice_1')[0] == null))
                            $$('#giftcard_notice_1')[0].style.display = 'block';
                        else
                            $$('#giftcard_notice_2')[0].style.display = "block";
                        if ($$('#giftvoucher_code')[0])
                            $$('#giftvoucher_code')[0].setAttribute('class','form-control input-text mage-error');
                        if ($$('#giftvoucher_existed_code')[0])
                            $$('#giftvoucher_existed_code')[0].setAttribute('class','form-control input-text mage-error');
                    }
//                    else
//                    {
//                        payment.save();
//                    }
                }
            },
            
            removeGiftVoucher: function(code, main){
                var url = 'giftvoucher/checkout/remove/code/'+code;
                
                return storage.get(
                    url,
                    true
                ).done(
                    function (response) {
                        if(response.isJSON()){
                            var res = JSON.parse(response);
                            var needUpdate = true;
                            if (res.ajaxExpired && res.ajaxRedirect) {
                                setLocation(res.ajaxRedirect);
                                needUpdate = false;
                            }
                            if (needUpdate) {
                                if (res.html) {
                                    main.resetData(res.html);
                                }
                                if (res.notice) 
                                    messageList.addErrorMessage({'message': res.notice});
                                if (res.success) 
                                    messageList.addSuccessMessage({'message': res.success});
                                if (res.error)
                                    messageList.addErrorMessage({'message': res.error});
                            }
                        }else{
                            alert(response);
                        }
                    }
                )
        
            },
            
            showGiftCardInput: function (index){
                var id = 'giftcard_change_'+index.call();
                var input = 'giftcard_input_'+index.call();
                $$('#'+id)[0].style.display='none';
                $$('#'+input)[0].style.display='';
            },
            
            updateGiftCardInput: function(index, main){
                var apply = 'apply_code_'+index.call();
                var load = 'ajax_loader_'+index.call();
                $$('#'+apply)[0].style.display='none';
                $$('#'+load)[0].style.display='';
                var url = 'giftvoucher/checkout/updateAmount/code/'+this.code+'/amount/'+this.value;
                
                return storage.get(
                    url,
                    true
                ).done(
                    function (response) {
                        if(response.isJSON()){
                            var res = JSON.parse(response);
                            var needUpdate = true;
                            if (res.ajaxExpired && res.ajaxRedirect) {
                                setLocation(res.ajaxRedirect);
                                needUpdate = false;
                            }
                            if (needUpdate) {
                                if (res.html) {
                                    main.resetData(res.html);
                                }
                                if (res.notice) 
                                    messageList.addErrorMessage({'message': res.notice});
                                if (res.success) 
                                    messageList.addSuccessMessage({'message': res.success});
                                if (res.error)
                                    messageList.addErrorMessage({'message': res.error});
                            }
                        }else{
                            alert(response);
                        }
                    }
                )
            },
            
            sentRequest: function(url, main){
                return storage.get(
                    url,
                    true
                ).done(
                    function (response) {
                        main.resetData(JSON.parse(response));
                    }
                )
            },
            
            resetData: function(response){
                if(response){
                    if(!response.notice){
                        giftmodel.setData(response);
                        this.allGiftvoucherData(response);
                        this.useGiftCredit(response.useGiftcredit);
                        this.useGiftVoucher(response.useGiftVoucher);
                        this.countGiftcard(response.giftVoucherDiscount.length);
                        this.giftcards(response.giftVoucherDiscount);
                        this.countExistedGiftcards(response.existedGiftcards.length);
                        this.existedGiftcards(response.existedGiftcards);
                        this.giftcardCode('');
                        this.selectedGiftcardCode('');
                        var deferred = $.Deferred();                        
                        var result = paymentMethodList();                        
                        getPaymentInformationAction(deferred);
                        if (result.length == 1 && result[0].method == 'free') {
                            
							$.when(deferred).done(function () {
								totals.isLoading(false);
							});
                        } else {
                            getTotalsAction([], deferred);
                            $.when(deferred).done(function() {
                                paymentService.setPaymentMethods(
                                    paymentMethodList()
                                );
                            });
                        }
                        
                    }else{
                        messageList.addErrorMessage({'message': response.notice});
                    }
                }
                this.showCreditInput(false);
            },
            
        });
    }
);
