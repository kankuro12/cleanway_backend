/* Compact mobile filter bar + bottom sheet (mobile_filters_compact_lists_plan) */
(function ($) {
    'use strict';

    var mq = window.matchMedia('(max-width: 767.98px)');
    var form = $('#filter-form');
    if (!form.length) return;

    var toggleBtn = $('#filter-toggle');
    var pillsWrap = $('#filter-pills');

    function fieldLabel(el) {
        if (el.id) {
            var label = $('label[for="' + el.id + '"]').first().text().trim();
            if (label) return label;
        }
        return el.placeholder || el.name || '';
    }

    function pillText(el) {
        var base = fieldLabel(el);
        if (!base) return '';
        if (el.tagName === 'SELECT' && el.selectedOptions && el.selectedOptions[0]) {
            return base + ': ' + el.selectedOptions[0].textContent.trim();
        }
        return base + ': ' + $(el).val();
    }

    function activeCount() {
        return form.find('select, input').filter(function () {
            var name = this.name;
            var val = $(this).val();
            return name && !/^(search|page)$/i.test(name) && val !== '' && val !== null;
        }).length;
    }

    function syncToggle() {
        var n = activeCount();
        toggleBtn.attr('aria-expanded', form.hasClass('open') ? 'true' : 'false');
        toggleBtn.find('.filter-count').remove();
        if (n > 0) {
            toggleBtn.append('<span class="badge text-bg-primary ms-1 filter-count">' + n + '</span>');
        }
    }

    function buildPills() {
        if (!mq.matches) return;
        pillsWrap.empty();
        form.find('select, input[type!="hidden"]').each(function () {
            var el = this;
            var name = el.name;
            var val = $(el).val();
            if (!name || /^(search|page)$/i.test(name) || val === '' || val === null) return;
            var text = pillText(el);
            if (!text) return;
            var pill = $('<a href="#" class="pill"></a>');
            pill.append($('<span></span>').text(text));
            pill.append(' <i class="bi bi-x-lg" aria-hidden="true"></i>');
            pill.on('click', function (e) {
                e.preventDefault();
                $(el).val('');
                form.trigger('submit');
            });
            pillsWrap.append(pill);
        });
        pillsWrap.toggleClass('d-none', !pillsWrap.children().length);
        syncToggle();
    }

    toggleBtn.on('click', function () {
        form.toggleClass('open');
        syncToggle();
        if (form.hasClass('open')) {
            var barSearch = $('.compact-filter-bar .cf-search input').val();
            if (barSearch !== undefined && barSearch !== '') {
                form.find('input[type="search"]').val(barSearch);
            }
        }
    });

    $('[data-filter-close]').on('click', function () {
        form.removeClass('open');
        syncToggle();
    });

    $(document).on('click', function (e) {
        if (form.hasClass('open') && !$(e.target).closest('.filter-form, #filter-toggle').length) {
            form.removeClass('open');
            syncToggle();
        }
    });

    buildPills();
})(jQuery);
