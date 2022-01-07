/*
 * jQuery MultiSelect UI Widget 1.14pre
 * Copyright (c) 2012 Eric Hynds
 *
 * http://www.erichynds.com/jquery/jquery-ui-multiselect-widget/
 *
 * Depends:
 *   - jQuery 1.4.2+
 *   - jQuery UI 1.8 widget factory
 *
 * Optional:
 *   - jQuery UI position utility
 *
 */
(function($, undefined) {
    var multiselectID = 0;
    var rEscape = /[\-\[\]{}()*+?.,\\\^$|#\s]/g;

    $.widget('multiselect.multiselect', {

        options: {
            header: true,
            footer: true,
            maxHeight: 200,
            width: '100%',
            classes: '',
            checkAllText: unikatSettings.multiselect.checkAll,
            uncheckAllText: unikatSettings.multiselect.uncheckAll,
            noneSelectedText: unikatSettings.multiselect.noneSelected,
            noneSelectedSingleText: unikatSettings.multiselect.noneSelectedSingle,
            selectedText: unikatSettings.multiselect.selected,
            totalText: unikatSettings.multiselect.total,
            filtered: unikatSettings.multiselect.filtered,
            selectedList: 3,
            speed: null,
            autoOpen: false,
            multiple: true,
            showSubText: false,
            searchDelay: 100,
            filter: true,
            filterMinOptions: 10,
            filterLabel: unikatSettings.multiselect.filterLabel,
            filterPlaceholder: unikatSettings.multiselect.filterPlaceholder,
            autoReset: true,
            position: {},
        },

        _create: function() {
            var el = this.element.hide();
            var o = this.options;

            this.speed = o.speed || $.fx.speeds._default;
            this._isOpen = false;

            // create a unique namespace for events that the widget
            // factory cannot unbind automatically. Use eventNamespace if on
            // jQuery UI 1.9+, and otherwise fallback to a custom string.
            this._namespaceID = this.eventNamespace || ('multiselect' + multiselectID);

            this.wrapper = $('<div />').addClass('multiselect-wrapper');
            el.wrap(this.wrapper);

            this.button = $('<button type="button" class="dropdown-toggle form-control">' + (o.multiple ? o.noneSelectedText : o.noneSelectedSingleText) + '</button>').addClass(o.classes).attr({ 'title': el.attr('title'), 'aria-haspopup': true, 'tabIndex': el.attr('tabIndex') }).insertAfter(el);

            this.menu = $('<section />').addClass('dropdown-menu').addClass(o.classes).insertAfter(this.button);
            if (!o.multiple) { // some addl. logic for single selects
                this.menu.addClass('multiselect-single');
            }

            this.header = $('<header />').appendTo(this.menu);

            this.filterWrapper = $('<div class="multiselect-filter">' + o.filterLabel + '</div>').appendTo(this.header);

            this.filter = $('<input class="form-control" placeholder="' + o.filterPlaceholder + '" type="search" />').appendTo(this.filterWrapper);

            this.headerLinkContainer = $('<ul />').html(function() {
                if (o.header === true) {
                    return '<li><a class="multiselect-all fa-left" href="#"><i class="fas fa-check"></i>' + o.checkAllText + '</a></li><li><a class="multiselect-none fa-left" href="#"><i class="fas fa-times"></i>' + o.uncheckAllText + '</a></li>';
                } else if (typeof o.header === "string") {
                    return '<li>' + o.header + '</li>';
                } else {
                    return '';
                }
            }).appendTo(this.header);

            this.checkboxContainer = $('<ul tabindex="-1" />').appendTo(this.menu);

            this.footer = $('<footer />').appendTo(this.menu);
            this.footerText = $('<p />').appendTo(this.footer);

            this._bindEvents(); // perform event bindings

            this.refresh(true); // build menu

            this.updateCache(); // cache input values for searching

            multiselectID++; // bump unique ID
        },

        _init: function() {
            if (this.options.header === false) {
                this.header.hide();
            }

            if (this.options.footer === false) {
                this.footer.hide();
            }

            if (this.options.filter === false) {
                this.filterWrapper.hide();
            }

            if (!this.options.multiple) {
                this.headerLinkContainer.find('.multiselect-all, .multiselect-none').hide();
            }

            if (this.options.autoOpen) {
                this.open();
            }

            if (this.element.is(':disabled')) {
                this.disable();
            }
        },

        refresh: function(init) {
            var o = this.options;
            var optgroups = [];
            var html = '';
            var id = this.element.attr('id') || multiselectID++; // unique ID for the label & option tags
            var self = this;
            var counter = 0;

            // build items
            this.element.find('option').each(function(i) {
                counter++;
                var inputID = 'multiselect-' + (this.id || id + '-option-' + i);
                var labelClasses = [];
                var liClasses = this.className;
                var optLabel;
                var subText = $(this).data('subText');

                // is this an optgroup?
                if (this.parentNode.tagName === 'OPTGROUP') {
                    optLabel = this.parentNode.getAttribute('label');

                    // has this optgroup been added already?
                    if ($.inArray(optLabel, optgroups) === -1) {
                        html += '<li class="multiselect-optgroup ' + this.parentNode.className + (this.parentNode.disabled ? ' disabled multiselect-state-disabled' : '') + '"><a href="#">' + optLabel + '</a></li>';
                        optgroups.push(optLabel);

                        // disable all children options
                        if (this.parentNode.disabled) {
                            $(this.parentNode).children().each(self._toggleState('disabled', true));
                        }
                    }
                }

                if (this.disabled) {
                    labelClasses.push('multiselect-state-disabled');
                    liClasses += ' disabled';
                }

                if (this.selected) {
                  labelClasses.push('multiselect-state-selected');
                }

                html += '<li class="' + liClasses + '">';

                // create the label
                html += '<label for="' + inputID + '" title="' + this.title + '" class="' + labelClasses.join(' ') + '">';
                html += '<input id="' + inputID + '" name="multiselect_' + id + '" type="' + (o.multiple ? "checkbox" : "radio") + '" value="' + this.value + '" title="' + this.title + '"';

                // pre-selected?
                if (this.selected) {
                    html += ' checked';
                    html += ' aria-selected="true"';
                }

                // disabled?
                if (this.disabled) {
                    html += ' disabled';
                    html += ' aria-disabled="true"';
                }

                // add the title and close everything off
                html += ' />' + this.innerHTML + (subText ? '<span class="sub-text">/ ' + subText + '</span>' : '') + '</label></li>';
            });

            this.footerText.html(o.totalText.replace('#', counter));

            // insert into the DOM
            this.checkboxContainer.html(html);

            // cache some more useful elements
            this.optgroups = this.checkboxContainer.find('li.multiselect-optgroup');
            this.labels = this.checkboxContainer.find('label');
            this.inputs = this.labels.children('input');

            // enable/disable filter depending on max options
            if (o.filterMinOptions && this.inputs.length >= o.filterMinOptions) {
                this._setOption('filter', true);
            } else {
                this._setOption('filter', false);
            }

            // set widths
            this._setButtonWidth();

            // remember default value
            this.button.defaultValue = this.update();

            // broadcast refresh event; useful for widgets
            if (!init) {
                this._trigger('refresh');
            }
        },

        // updates the button text. call refresh() to rebuild
        update: function() {
            var self = this;
            var $checked = self.inputs.filter(':checked');
            var numChecked = $checked.length;
            var value;

            if (numChecked === 0) {
                value = self.options.multiple ? self.options.noneSelectedText : self.options.noneSelectedSingleText;
            } else {
                if ($.isFunction(self.options.selectedText)) {
                    value = self.options.selectedText.call(self, numChecked, self.inputs.length, $checked.get());
                } else if (/\d/.test(self.options.selectedList) && self.options.selectedList > 0 && numChecked <= self.options.selectedList) {
                    value = $checked.map(function() {
                        var next = $(this).next();
                        var subText = next.length ? next[0].outerHTML : null;
                        return $(this)[0].nextSibling.nodeValue + ((self.options.showSubText && subText) ? subText : '');
                    }).get().join(', ');
                } else {
                    value = self.options.selectedText.replace('#', numChecked).replace('#', self.inputs.length);
                }
            }

            self._setButtonValue(value);

            return value;
        },

        _setButtonValue: function(value) {
            this.button.html(value);
        },

        _bindEvents: function() {
            var self = this;

            // button events
            self.button.on('click', function() {
                self[self._isOpen ? 'close' : 'open']();
                return false;
            }).on('keydown', function(e) {
                switch (e.which) {
                    case 38: // up
                    case 37: // left
                        self.close();
                    break;
                    case 39: // right
                    case 40: // down
                        self.open();
                    break;
                }
            });

            // dropdown menu
            self.menu.on('keydown', function(e) {
                switch (e.which) {
                    case 27: // esc
                        e.stopPropagation();
                        self.close();
                    break;
                }
            });

            // filter
            self.filter.on('keydown', function(e) {
                switch (e.which) {
                    case 13: // prevent the enter key from submitting the form / closing the widget
                        e.preventDefault();
                    break;
                }
            }).on('focus', function() {
                self.labels.removeClass('selected');
                self.optgroups.removeClass('selected');
            }).on('keyup search input paste cut', debounce(function() {
                self._handler();
            }, self.options.searchDelay));

            $(document).on('multiselectrefresh', function() { // rebuild cache when multiselect is updated
                self.updateCache();
                self._handler();
            }).on('multiselectclose', function() {
                self._reset();
            }).on('mousedown.' + self._namespaceID, function(event) { // close each widget when clicking on any other element/anywhere else on the page
                var target = event.target;

                if (self._isOpen && target !== self.button[0] && target !== self.menu[0] && !$.contains(self.menu[0], target) && !$.contains(self.button[0], target)) {
                    self.close();
                }
            });

            // header links
            self.header.on('click', 'a', function(e) {
                self[$(this).hasClass('multiselect-all') ? 'checkAll' : 'uncheckAll']();
                e.preventDefault();
            }).on('focus', 'a', function() {
                self.labels.removeClass('selected');
                self.optgroups.removeClass('selected');
            });

            self.checkboxContainer.on('click', '.multiselect-optgroup a', function(e) { // optgroup label toggle support
                e.preventDefault();

                var $inputs = $(this).parent().nextUntil('li.multiselect-optgroup').find('input:visible:not(:disabled)');
                if ($inputs.length) {
                    var nodes = $inputs.get();
                    var label = $(this).parent().text();

                    // trigger event and bail if the return is false
                    if (self._trigger('beforeoptgrouptoggle', e, { inputs: nodes, label: label }) === false) {
                        return;
                    }

                    // toggle inputs
                    self._toggleChecked($inputs.filter(':checked').length !== $inputs.length, $inputs);

                    self._trigger('optgrouptoggle', e, { inputs: nodes, label: label, checked: nodes[0].checked });
                }
            }).on('mouseenter', 'label', function() { // option
                if (!$(this).hasClass('multiselect-state-disabled')) {
                    $(this).addClass('selected').find('input').focus();
                }
            }).on('mouseleave', 'label', function() { // option
                if (!$(this).hasClass('multiselect-state-disabled')) {
                    self.labels.removeClass('selected');
                }
            }).on('mouseenter', '.multiselect-optgroup', function() { // optgroup
                if (!$(this).hasClass('multiselect-state-disabled')) {
                    $(this).addClass('selected').find('a').focus();
                }
            }).on('mouseleave', '.multiselect-optgroup', function() { // optgroup
                if (!$(this).hasClass('multiselect-state-disabled')) {
                    self.optgroups.removeClass('selected');
                }
            }).on('keydown', 'label, .multiselect-optgroup a', function(e) { // option
                if (e.which != 9) { // tab
                    e.preventDefault();
                }

                switch(e.which) {
                    case 9: // tab
                        if ($(this).parent()[e.shiftKey ? 'prevAll' : 'nextAll']('li:not(:hidden, .disabled)').first().length) {
                            e.preventDefault();

                            if (e.shiftKey) { // up
                                self._traverse(38, this);
                            } else { // down
                                self._traverse(40, this);
                            }
                        }
                    break;
                    case 38: // up
                    case 40: // down
                    case 37: // left
                    case 39: // right
                        self._traverse(e.which, this);
                    break;
                    case 13: // enter
                        var input = $(this).find('input');
                        if (input.length) {
                            input[0].click();
                        } else {
                            this.click();
                        }
                    break;
                }
            }).on('click', 'input[type="checkbox"], input[type="radio"]', function(e) {
                var val = this.value;
                var checked = this.checked;

                // bail if this input is disabled or the event is cancelled
                if (this.disabled || self._trigger('click', e, { value: val, text: this.title, checked: checked }) === false) {
                    e.preventDefault();
                    return;
                }

                // toggle input
                self._toggleChecked(checked, $(this));

                // some additional single select-specific logic
                if (!self.options.multiple) {
                    self.close();
                }
            }).on('focus', 'input, .multiselect-optgroup a', function() {
                var $this = $(this).parent();
                if (!$this.hasClass('selected')) { // tab from header = select first option
                    self.checkboxContainer.scrollTop(0);
                    $this.trigger('mouseenter');
                }
            });
        },

        _handler: function(e) {
            var term = $.trim(this.filter[0].value.toLowerCase());
            var rows = this.rows;
            var inputs = this.inputs;
            var results = [];

            if (term.indexOf(',') !== -1) {
                var terms = term.split(/\s*,\s*/g);
            }

            if (!term) {
                rows.show();
                this.footerText.html(this.options.totalText.replace('#', rows.length));
            } else {
                rows.hide();

                var regex;
                if (terms) {
                    var that = this;
                    var length = 0;
                    $.each(terms, function(index, term) {
                        regex = new RegExp('^' + term.replace(rEscape, "\\$&") + '$', 'gi');
                        results = that.search(regex, rows, inputs);
                        that._trigger('filter', e, results);
                        length += results.length;
                    });
                    this.footerText.html(this.options.totalText.replace('#', length) + this.options.filtered.replace('#', rows.length));
                } else {
                    regex = new RegExp(term.replace(rEscape, "\\$&"), 'gi');
                    results = this.search(regex, rows, inputs);
                    this._trigger('filter', e, results);
                    this.footerText.html(this.options.totalText.replace('#', results.length) + this.options.filtered.replace('#', rows.length));
                }
            }

            // show/hide optgroups
            this.checkboxContainer.find('.multiselect-optgroup').each(function() {
                var isVisible = $(this).nextUntil('.multiselect-optgroup').filter(function() {
                    return $.css(this, 'display') !== 'none';
                }).length;

                $(this)[isVisible ? 'show' : 'hide']();
            });
        },

        search: function(regex, rows, inputs) {
          return $.map(this.cache, function(v, i) {
              if (v.search(regex) !== -1) {
                  rows.eq(i).show();
                  return inputs.get(i);
              }

              return null;
          });
        },

        _reset: function() {
            this.filter.val('').trigger('keyup');
        },

        updateCache: function() {
            this.rows = this.checkboxContainer.find('li:not(.multiselect-optgroup)');

            this.cache = this.element.children().map(function() {
                var elem = $(this);

                // account for optgroups
                if (this.tagName.toLowerCase() === 'optgroup') {
                    elem = elem.children();
                }

                return elem.map(function() {
                    var subText = elem.data('subText');
                    return this.innerHTML.toLowerCase() + (subText ? ' ' + String(subText).toLowerCase() : '');
                }).get();
            }).get();
        },

        _setButtonWidth: function() {
            this.button.outerWidth(this.options.width);
        },

        // move up or down within the menu
        _traverse: function(which, start) {
            $(start).trigger('mouseleave');

            var moveToLast = which === 38 || which === 37; // up or left

            // select the first li that isn't disabled
            var $next = $(start).parent()[moveToLast ? 'prevAll' : 'nextAll']('li:not(:hidden, .disabled)').first();

            if ($next.hasClass('multiselect-optgroup')) {
                $next.trigger('mouseenter');
            } else {
                if (!$next.length) { // if at the first/last element then move to the first/last
                    this.checkboxContainer.find('label')[moveToLast ? 'last' : 'first']().trigger('mouseenter');
                    moveToLast ? this.checkboxContainer.scrollTop(this.checkboxContainer[0].scrollHeight) : this.checkboxContainer.scrollTop(0);
                } else {
                    $next.find('label').trigger('mouseenter');
                }
            }
        },

        // This is an internal function to toggle the checked property and
        // other related attributes of a checkbox.
        //
        // The context of this function should be a checkbox; do not proxy it.
        _toggleState: function(prop, flag) {
            return function() {
                if (!this.disabled) {
                    this[prop] = flag;
                }

                if (flag) {
                    this.setAttribute('aria-selected', true);
                    $(this).closest('label').toggleClass('multiselect-state-selected', true);
                } else {
                    this.removeAttribute('aria-selected');
                    $(this).closest('label').toggleClass('multiselect-state-selected', false);
                }
            };
        },

        _toggleChecked: function(flag, group) {
            var self = this;
            var $inputs = (group && group.length) ? group : this.inputs;

            if (this.options.filter) {
                $inputs = $inputs.not(':hidden');
            }

            if (!this.options.multiple) {
                this.inputs.each(this._toggleState('checked', false));
            }

            $inputs.each(this._toggleState('checked', flag));

            // update button text
            this.update();

            // gather an array of the values that actually changed
            var values = $inputs.map(function() {
                return this.value;
            }).get();

            // toggle state on original option tags
            this.element.find('option').each(function() {
                if (!self.options.multiple) {
                    self._toggleState('selected', false).call(this);
                }

                if (!this.disabled && $.inArray(this.value, values) > -1) {
                    self._toggleState('selected', flag).call(this);
                }
            });

            // trigger the change event on the select
            if ($inputs.length) {
                this.element.trigger("change");
            }
        },

        _toggleDisabled: function(flag) {
            this.button.attr({ 'disabled': flag, 'aria-disabled': flag })[flag ? 'addClass' : 'removeClass']('multiselect-state-disabled');

            var inputs = this.checkboxContainer.find('input');
            var key = "multiselect-disabled";

            if (flag) {
                // remember which elements this widget disabled (not pre-disabled)
                // elements, so that they can be restored if the widget is re-enabled.
                inputs = inputs.filter(':enabled').data(key, true);
            } else {
                inputs = inputs.filter(function() {
                    return $.data(this, key) === true;
                }).removeData(key);
            }

            inputs.attr({ 'disabled': flag, 'arial-disabled': flag }).parent()[flag ? 'addClass' : 'removeClass']('multiselect-state-disabled');

            this.element.attr({ 'disabled': flag, 'aria-disabled': flag });
        },

        open: function(e) {
            // bail if the multiselect open event returns false, this widget is disabled, or is already open
            if (this._trigger('beforeopen') === false || this.button.hasClass('multiselect-state-disabled') || this._isOpen) {
                return;
            }

            // set the scroll & maxHeight of the checkbox container
            this.checkboxContainer.css('max-height', this.options.maxHeight);

            this.position();

            this.menu.fadeIn(this.speed);

            // select the first option
            // this.checkboxContainer.children().eq(0).trigger('mouseenter').find('input').trigger('focus');

            if (this.options.filter) {
                this.filter.focus();
            }

            this.checkboxContainer.scrollTop(0);

            this.button.addClass('multiselect-state-active');
            this._isOpen = true;
            this._trigger('open');
        },

        close: function() {
            if (this._trigger('beforeclose') === false) {
                return;
            }

            // this.menu.fadeOut(this.speed);
            this.menu.hide();
            this.button.removeClass('multiselect-state-active').trigger('blur').trigger('mouseleave');
            this._isOpen = false;
            this._trigger('close');
        },

        enable: function() {
            this._toggleDisabled(false);
        },

        disable: function() {
            this._toggleDisabled(true);
        },

        checkAll: function(e) {
            this._toggleChecked(true);
            this._trigger('checkAll');
        },

        uncheckAll: function() {
            this._toggleChecked(false);
            this._trigger('uncheckAll');
        },

        getChecked: function() {
            return this.checkboxContainer.find('input').filter(':checked');
        },

        destroy: function() {
            // remove classes + data
            $.Widget.prototype.destroy.call(this);

            // unbind events
            $(document).off(this._namespaceID);

            this._reset();

            this.button.remove();
            this.menu.remove();
            this.element.show();

            return this;
        },

        isOpen: function() {
            return this._isOpen;
        },

        widget: function() {
            return this.menu;
        },

        getButton: function() {
            return this.button;
        },

        position: function() {
            // use the position utility if it exists and options are specifified
            if ($.ui.position && !$.isEmptyObject(this.options.position)) {
                this.options.position.of = this.options.position.of || this.button;

                this.menu.show().position(this.options.position).hide();
            }
        },

        // react to option changes after initialization
        _setOption: function(key, value) {
            switch(key) {
                case 'header':
                    this.header[value ? 'show' : 'hide']();
                break;
                case 'footer':
                    this.footer[value ? 'show' : 'hide']();
                break;
                case 'filter':
                    this.filterWrapper[value ? 'show' : 'hide']();
                break;
                case 'checkAllText':
                    this.header.find('a.multiselect-all').contents().last().replaceWith(value);
                break;
                case 'uncheckAllText':
                    this.header.find('a.multiselect-none').contents().last().replaceWith(value);
                break;
                case 'maxHeight':
                    this.checkboxContainer.css('max-height', parseInt(value, 10));
                break;
                case 'width':
                    this.options[key] = value;
                    this._setButtonWidth();
                break;
                case 'selectedText':
                case 'selectedList':
                case 'noneSelectedText':
                case 'noneSelectedSingleText':
                    this.options[key] = value; // these all needs to update immediately for the update() call
                    this.update();
                break;
                case 'classes':
                    this.menu.add(this.button).removeClass(this.options.classes).addClass(value);
                break;
                case 'multiple':
                    this.menu.toggleClass('multiselect-single', !value);
                    this.options.multiple = value;
                    this.element[0].multiple = value;
                    this.refresh();
                break;
                case 'position':
                    this.position();
                break;
            }

            $.Widget.prototype._setOption.apply(this, arguments);
        }
    });
})(jQuery);
