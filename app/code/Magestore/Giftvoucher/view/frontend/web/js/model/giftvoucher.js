/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


define(
    [
        'ko'
    ],
    function (ko) {
        'use strict';
        var tempAllGiftvoucherData = window.giftvoucherInfo;
        var allData = ko.observable(tempAllGiftvoucherData);
        
        return {
            allData: allData,
            getData: function(){
                return allData;
            },
            
            setData: function(data){
                allData(data);
            }
        }
    }
);
