/**
 * Created by xpoback on 21.08.15.
 */
define([
    'jquery'
], function ($) {
    "use strict";
    $.widget('mage.exportLink', {
        _create: function () {
            this.element.on('click', function (e) {
                e.preventDefault();
                window.location = this.element.data('action');
            }.bind(this));
       }
    });

    return $.mage.exportLink;
});