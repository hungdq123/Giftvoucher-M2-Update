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
 * @package     Magestore_Storelocator
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
define([
    'jquery',
    'mage/template',
    'jquery/ui',
    'jquery/file-uploader',
    'mage/translate'
], function($, mageTemplate) {
    'use strict';

    $.widget('magestore.baseImage', {
        options: {
            maximumImageCount: 15,
            galleryContainer: ''
        },
        /**
         * Button creation
         * @protected
         */
        _create: function() {
            var $container = this.element,
                imageTmpl = mageTemplate(this.element.find('.image-template').html()),
                $dropPlaceholder = this.element.find('.image-placeholder'),
                $galleryContainer = $(this.options.galleryContainer),
                loaderImage = $('.image-loader-wrapper'),
                mainClass = 'base-image',
                maximumImageCount = this.options.maximumImageCount,
                $fieldCheckBox = $container.closest('[data-attribute-code=image]').find(':checkbox'),
                isDefaultChecked = $fieldCheckBox.is(':checked');
            if (isDefaultChecked) {
                $fieldCheckBox.trigger('click');
            }

            var findElement = function(data) {
                return $container.find('.image:not(.image-placeholder)').filter(function() {
                    return $(this).data('image').file === data.file;
                }).first();
            };

            var updateVisibility = function() {
                var elementsList = $container.find('.image-item:not(.removed-item)');
                elementsList.each(function(index) {
                    $(this)[index < maximumImageCount ? 'show' : 'remove']();
                });
                $dropPlaceholder[elementsList.length >= maximumImageCount ? 'hide' : 'show']();
            };

            $galleryContainer.on('setImageType', function(event, data) {
                if (data.type === 'image') {
                    $container.find('.' + mainClass).removeClass(mainClass);

                    if (data.imageData) {
                        findElement(data.imageData).addClass(mainClass);
                    }
                }
            });

            $galleryContainer.on('addItem', function(event, data) {
                data.imageJSON = JSON.stringify(data);

                var tmpl = imageTmpl({data: data}).trim(),
                    $image = $(tmpl);

                if(!data.image_id) {
                    $image.removeAttr('id');
                }

                $image.data('image', data).insertBefore($dropPlaceholder);

                updateVisibility();
            });

            $galleryContainer.on('removeItem', function(event, image) {
                findElement(image).addClass('removed-item').hide();

                setJsonData(findElement(image).find('.arrayImages'), 'remove', 1);
                removeJsonData(findElement(image).find('.arrayImages'), 'base');

                updateVisibility();
            });

            var imagesJson = this.options.imagesJson;
            for (var i = 0; i < imagesJson.length; i++) {

                imagesJson[i].imageJSON = JSON.stringify(imagesJson[i]);
                var tmpl = imageTmpl({data: imagesJson[i]}).trim();

                $(tmpl).data('image', imagesJson[i]).insertBefore($dropPlaceholder);

                if (imagesJson[i].base) {
                    $('.element-image' + imagesJson[i].image_id).addClass('base-image');
                }

                updateVisibility();
            };

            var setJsonData = function(element, property, value) {
                var json = jQuery.parseJSON(element.val());
                json[property] = value;
                element.val(JSON.stringify(json));
            }

            var removeJsonData = function(element, property) {
                if(element.length) {
                    var json = _.omit(jQuery.parseJSON(element.val()), property)
                    element.val(JSON.stringify(json));
                }
            }

            window.removeImage = function(btn) {
                $(btn).parents('.image').addClass('removed-item').hide();
                setJsonData($(btn).parents('.image').find('.arrayImages'), 'remove', 1);
                removeJsonData($(btn).parents('.image').find('.arrayImages'), 'base');
                updateVisibility();
            }

            $container.on('click', '[data-role=make-base-button]', function(event) {
                event.preventDefault();
                var data = $(event.target).closest('.image').data('image');

                var currentImage = $container.find('.image.base-image').removeClass('base-image');
                removeJsonData(currentImage.find('.arrayImages'), 'base');

                currentImage = $(event.target).parents('.image').addClass('base-image');
                setJsonData(currentImage.find('.arrayImages'), 'base', 1);
            });

            $container.on('click', '.image-label', function(event) {
                event.preventDefault();
                var data = $(event.target).closest('.image').data('image');
                var currentImage = $(event.target).parents('.image').removeClass('base-image');
                removeJsonData(currentImage.find('.arrayImages'), 'base');
            });

            $container.on('click', '[data-role=delete-button]', function(event) {
                event.preventDefault();
                $galleryContainer.trigger('removeItem', $(event.target).closest('.image').data('image'));
            });

            this.element.find('input[type="file"]').fileupload({
                dataType: 'json',
                dropZone: $dropPlaceholder.closest('[data-attribute-code]'),
                acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
                maxFileSize: this.element.data('maxFileSize'),
                done: function(event, data) {
                    $dropPlaceholder.find('.progress-bar').text('').removeClass('in-progress');

                    if (!data.result) {
                        return;
                    }
                    try {
                        if (!data.result.error) {
                            $galleryContainer.trigger('addItem', data.result);
                        } else {
                            alert($.mage.__('File extension not known or unsupported type.'));
                        }
                    } catch (e) {
                        console.log(e);
                        console.trace();
                    }
                },
                add: function(event, data) {
                    $(this).fileupload('process', data).done(function() {
                        data.submit();
                    });
                },
                progress: function(e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    $dropPlaceholder.find('.progress-bar').addClass('in-progress').text(progress + '%');
                },
                start: function(event) {
                    var uploaderContainer = $(event.target).closest('.image-placeholder');

                    uploaderContainer.addClass('loading');
                    loaderImage.show();
                },
                stop: function(event) {
                    var uploaderContainer = $(event.target).closest('.image-placeholder');

                    uploaderContainer.removeClass('loading');
                    loaderImage.hide();
                }
            });
        }
    });

    return $.magestore.baseImage;
});
