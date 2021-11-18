$(function () {
    $('.search-field').instantSearch({
        delay: 100,
    });
});

/**
 * jQuery plugin for an instant searching.
 *
 * @author Oleg Voronkovich <oleg-voronkovich@yandex.ru>
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 */
(function ($) {
    'use strict';

    String.prototype.render = function (parameters) {
        return this.replace(/({{ (\w+) }})/g, function (match, pattern, name) {
            return parameters[name];
        })
    };

    // INSTANTS SEARCH PUBLIC CLASS DEFINITION
    // =======================================

    var InstantSearch = function (element, options) {
        this.$input = $(element);
        this.$form = this.$input.closest('form');
        this.$preview = $('<ul class="search-preview row" style="padding-left: 0px;">'). appendTo(this.$form);
        this.options = $.extend({}, InstantSearch.DEFAULTS, this.$input.data(), options);
        this.$input.keyup(this.debounce());
    };

    InstantSearch.DEFAULTS = {
        minQueryLength: 3,
        limit: 10,
        delay: 500,
        noResultsMessage: 'No results found',
        itemTemplate: '<div class="col-6 col-md-3 col-lg-3 col-xl-2">\
                             <div class="card">\
                                 <div class="card-header">\
                                 <a href="{{ urldesc }}">\
                                     <h6 class="single-line">{{ nombre }}</h6>\
                                     </a>\
                                 </div>\
                                 <div class="card-body">\
                                    <div class="div-img-prod">\
                                        <a href="{{ urldesc }}">\
                                            <img src="{{ imagen }}" \
                                                class="img-fluid mx-auto img-prod d-block" alt="">\
                                        </a>\
                                    </div>\
                                    <hr>\
                                    <h5 class="text-center">${{ precio }}\
                                    <h6 class="text-center single-line">( {{ cambiocup }} cup)\
                                    </h6>\
                                    </h5>\
                                 </div>\
                             </div>\
                         </div>'
    };

    InstantSearch.prototype.debounce = function () {
        var delay = this.options.delay;
        var search = this.search;
        var timer = null;
        var self = this;

        return function () {
            clearTimeout(timer);
            timer = setTimeout(function () {
                search.apply(self);
            }, delay);
        };
    };

    InstantSearch.prototype.search = function () {
        var query = $.trim(this.$input.val()).replace(/\s{2,}/g, ' ');
        if (query.length < this.options.minQueryLength) {
            this.$preview.empty();
            return;
        }

        var self = this;
        var data = this.$form.serializeArray();
        data['l'] = this.limit;

        $.getJSON(this.$form.attr('action'), data, function (items) {
            self.show(items);
        });
    };

    InstantSearch.prototype.show = function (items) {
        var $preview = this.$preview;
        var itemTemplate = this.options.itemTemplate;

        if (0 === items.length) {
            $preview.html(this.options.noResultsMessage);
        } else {
            $preview.empty();
            $.each(items, function (index, item) {
                $preview.append(itemTemplate.render(item));
            });
        }
    };

    // INSTANTS SEARCH PLUGIN DEFINITION
    // =================================

    function Plugin(option) {
        return this.each(function () {
            var $this = $(this);
            var instance = $this.data('instantSearch');
            var options = typeof option === 'object' && option;

            if (!instance) $this.data('instantSearch', (instance = new InstantSearch(this, options)));

            if (option === 'search') instance.search();
        })
    }

    $.fn.instantSearch = Plugin;
    $.fn.instantSearch.Constructor = InstantSearch;

})(window.jQuery);
