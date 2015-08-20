/**
 * Created by xpoback on 19.08.15.
 */
define(
    [
        'jquery',
        'Magento_Search/form-mini'
    ],
    function($, form) {
        "use strict";
        return $.widget('mage.quickSearch', form, {
            options: {
                template:
                '<li class="<%- data.row_class %>" id="qs-option-<%- data.index %>" data-url="<%- data.link %>"  role="option">' +
                '<span class="image"><img src="<%- data.image %>" /></span>' +
                '<span class="qs-option-name">' +
                ' <%- data.title %>' +
                '</span>' +
                '<span aria-hidden="true" class="amount">' +
                '<%- data.num_results %>' +
                '</span>' +
                '</li>'
            }
        });
    }

);
