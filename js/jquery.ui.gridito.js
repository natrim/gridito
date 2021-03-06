(function ($, undefined) {

    $.widget("ui.gridito", {

        options: {},

        _create: function () {
            var _this = this;

            this.table = this.element.find("table.gridito-table");
            this.table.addClass("ui-widget ui-widget-content");
            this.table.find("th").addClass("ui-widget-header");
            this.table.find("tbody tr").hover(function () {
                $(this).addClass("ui-state-hover");
            }, function () {
                $(this).removeClass("ui-state-hover");
            });

            // sorting icons
            function initSortingIcons(normalClass, hoverClass) {
                _this.table.find("thead th ." + normalClass).hover(function () {
                    $(this).removeClass(normalClass).addClass(hoverClass);
                }, function () {
                    $(this).removeClass(hoverClass).addClass(normalClass);
                });
            }

            this.table.find("span.sorting-no").addClass("ui-icon ui-icon-carat-2-n-s");
            this.table.find("span.sorting-asc").addClass("ui-icon ui-icon-triangle-1-n");
            this.table.find("span.sorting-desc").addClass("ui-icon ui-icon-triangle-1-s");

            initSortingIcons("ui-icon-carat-2-n-s", "ui-icon-triangle-1-n");
            initSortingIcons("ui-icon-triangle-1-n", "ui-icon-triangle-1-s");
            initSortingIcons("ui-icon-triangle-1-s", "ui-icon-carat-2-n-s");

            //persist check state on check buttons
            this.table.on("mouseout mouseup", "a.gridito-button.checked", function () {
                if (!$(this).hasClass("ui-state-active")) {
                    $(this).addClass("ui-state-active");
                }
            });

            //checkbox column checboxes styling
            this.table.find('.gridito-checkboxcell input').each(function () {
                var el = $(this);

                el.after($("<label />").attr({ for: el.attr("id") }));

                el.button({ text: false, disabled: el[0].disabled });

                el.click(function (e) {
                    $(this).button("option", {
                        icons: {
                            primary: $(this)[0].checked ? "ui-icon-check" : ""
                        }
                    });
                });
            });

            // buttons
            this.element.find("a.gridito-button").each(function () {
                var el = $(this);

                if (el.hasClass("checked")) {
                    el.addClass("ui-state-active");
                }

                el.button({
                    icons: {
                        primary: el.attr("data-gridito-icon")
                    },
                    text: !el.hasClass("gridito-hide-text"),
                    disabled: el.hasClass("disabled")
                });

                // window button
                if (el.hasClass("gridito-window-button")) {
                    el.click(function (e) {
                        e.stopImmediatePropagation();
                        e.preventDefault();

                        var win = $("<div></div>").appendTo("body");
                        var button = $(this);
                        win.attr("id", button.attr("id") + "-window").attr("title", button.attr("data-gridito-window-title"));
                        win.load(this.href, function () {
                            win.dialog({
                                modal: !!(parseInt(button.attr("data-gridito-window-modal")) === 1),
                                width: isNaN(parseInt(button.attr("data-gridito-window-width"))) ? "auto" : button.attr("data-gridito-window-width"),
                                height: isNaN(parseInt(button.attr("data-gridito-window-height"))) ? "auto" : button.attr("data-gridito-window-height"),
                                draggable: !!(parseInt(button.attr("data-gridito-window-draggable")) === 1),
                                resizable: !!(parseInt(button.attr("data-gridito-window-resizable")) === 1),
                                close: function () {
                                    win.remove();
                                }
                            });
                            win.find("input:first").focus();
                        });
                    });
                }

                if (el.attr("data-gridito-question")) {
                    el.click(function (e) {
                        if (!confirm($(this).attr("data-gridito-question"))) {
                            e.stopImmediatePropagation();
                            e.preventDefault();
                        }
                    });
                }
            });

            this.table.on('click', 'span.editable', function (event) {
                $.startedit($(this));
            });
        }

    });

})(jQuery);


jQuery.extend({
    stopedit: function (who) {
        var span = who.data('span');
        if (who.val() != span.attr('data-value')) {
            span.text(who.val());
            span.attr('data-value', who.val());
            var data = {};
            data[span.attr('data-name')] = who.val();
            data['id'] = span.attr('data-id');
            jQuery.post(span.attr('data-url'), data);
        }
        who.replaceWith(span);
    },
    startedit: function (who) {
        if (who.attr('data-type') === 'bool') {
            var data = {};
            data[who.attr('data-name')] = who.attr('data-value') === '1' ? '0' : '1';
            data['id'] = who.attr('data-id');
            jQuery.post(who.attr('data-url'), data);
            return;
        }
        var input = jQuery('<textarea />');
        input.data('span', who);
        input.val(who.attr('data-value'));
        input.addClass('editable');
        input.on('blur', function () {
            jQuery.stopedit(jQuery(this));
        });
        input.on('keyup', function (event) {
                if (event.keyCode == 27) {
                    input.blur();
                }
            }
        );
        who.replaceWith(input);
        input.focus();
    }
});
