function Lingua(config) {
    this.config = config;
    this.config['fcRules'] = {};
    this.element = [];
}

Lingua.prototype.initFCRule = function(id, name) {
    var cmp = Ext.getCmp(id);
    if (typeof (cmp) !== "undefined") {
        this.config.fcRules[name] = {visible: cmp.isVisible()};
    }
};

Lingua.prototype.initFCRules = function() {
    this.initFCRule('modx-resource-pagetitle', 'pagetitle');
    this.initFCRule('modx-resource-longtitle', 'longtitle');
    this.initFCRule('modx-resource-description', 'description');
    this.initFCRule('modx-resource-introtext', 'introtext');
    this.initFCRule('modx-resource-content', 'content');
    this.initFCRule('modx-resource-content-static', 'staticContent');
    this.initFCRule('modx-symlink-content', 'symLinkContent');
    this.initFCRule('modx-weblink-content', 'webLinkContent');
    this.initFCRule('modx-resource-alias', 'alias');
    this.initFCRule('modx-resource-menutitle', 'menutitle');
    this.initFCRule('modx-resource-link-attributes', 'linkAttributes');
    this.initFCRule('modx-resource-uri-override', 'uriOverride');
    this.initFCRule('modx-resource-uri', 'uri');
};

Lingua.prototype.getMenu = function(params) {
    var actionButtons = Ext.getCmp("modx-action-buttons");
    if (actionButtons) {
        var languageBtn = new Ext.form.ComboBox({
            id: "lingua-languageBtn",
            tpl: '<tpl for="."><div class="x-combo-list-item"><img src="../{flag}" class="icon"/> {local_name}</div></tpl>',
            store: new Ext.data.ArrayStore({
                id: 0,
                fields: [
                    "lang_code",
                    "local_name",
                    "flag"
                ],
                data: params['storeData']
            }),
            valueField: "lang_code",
            displayField: "local_name",
            typeAhead: false,
            forceSelection: true,
            editable: false,
            mode: "local",
            triggerAction: "all",
            selectOnFocus: true,
            width: 150,
            listeners: {
                select: {
                    fn: function(combo, record, index) {
                        this.switchLanguage(record.get("lang_code"));
                    },
                    scope: this
                },
                render: {
                    fn: function(comboBox) {
                        var store = comboBox.store;
                        var valueField = comboBox.valueField;
                        var displayField = comboBox.displayField;
                        var recordNumber = store.findExact(valueField, this.config.defaultLang, 0);
                        if (recordNumber !== -1) {
                            var displayValue = store.getAt(recordNumber).data[displayField];
                            comboBox.setValue(this.config.defaultLang);
                            comboBox.setRawValue(displayValue);
                            comboBox.selectedIndex = recordNumber;
                        }
                    },
                    scope: this
                }
            }
        });
        actionButtons.insertButton(0, [languageBtn, "-"]);
        actionButtons.doLayout();
    }
};

Lingua.prototype.flagField = function (id) {
    var cmp = Ext.getCmp(id);
    if (typeof (cmp) !== "undefined" && typeof (cmp.label) !== "undefined") {
        Ext.DomHelper.insertAfter(cmp.label, {
            tag: 'img',
            src: '../' + this.config.langs[this.config.defaultLang]['flag'],
            class: 'icon-lingua-insert-flag'
        });
    }
};

Lingua.prototype.flagDefaultFields = function () {
    this.flagField('modx-resource-pagetitle');
    this.flagField('modx-resource-longtitle');
    this.flagField('modx-resource-description');
    this.flagField('modx-resource-introtext');

    var content = Ext.getCmp('modx-resource-content');
    if (typeof (content) !== "undefined") {
        if (typeof (content.label) !== "undefined") {
            Ext.DomHelper.insertAfter(content.label, {
                tag: 'img',
                src: '../' + this.config.langs[this.config.defaultLang]['flag'],
                class: 'icon-lingua-insert-flag'
            });
        } else {
            this.contentTitle = content.title;
            content.setTitle(this.contentTitle + '&nbsp;<img src="../' + this.config.langs[this.config.defaultLang]['flag'] + '">');
        }
    }

    this.flagField('modx-resource-content-static');
    this.flagField('modx-symlink-content');
    this.flagField('modx-weblink-content');
    this.flagField('modx-resource-alias');
    this.flagField('modx-resource-menutitle');
    this.flagField('modx-resource-link-attributes');

    var uriOverride = Ext.getCmp('modx-resource-uri-override');
    if (typeof (uriOverride) !== "undefined" && typeof (uriOverride.label) !== "undefined") {
        uriOverride.wrap.child('.x-form-cb-label').update(_('resource_uri_override') + '&nbsp;<img src="../' + this.config.langs[this.config.defaultLang]['flag'] + '">');
    }

    this.flagField('modx-resource-uri');
};

Lingua.prototype.flagDefaultTVFields = function () {
    var _this = this;
    Ext.each(this.config.tmplvars, function (tv) {
        var captionEl = Ext.get('tv' + tv['id'] + '-caption');
        if (typeof (captionEl) !== 'undefined' && captionEl !== null) {
            captionEl.dom.innerHTML += ' <img src="../' + _this.config.langs[_this.config.defaultLang]['flag'] + '">';
        }
    });
};

Lingua.prototype.switchLanguage = function (selectedLang) {
    var els = Ext.query(".lingua-hidden-fields");
    Ext.each(els, function (item, idx) {
        var cmp, el;
        if (cmp = Ext.getCmp(item.id)) {
            cmp.getEl().setVisibilityMode(Ext.Element.DISPLAY);
            cmp.hide();
        } else if (el = Ext.get(item.id)) {
            el.setVisibilityMode(Ext.Element.DISPLAY);
            el.hide();
        }
    });

    this.switchMainFields(selectedLang);
    this.switchTVFields(selectedLang);
};

Lingua.prototype.createHiddenFields = function (langs) {
    var _this = this;
    Ext.each(langs, function (item) {
        _this.createHiddenField(item);
    });
};

Lingua.prototype.rteToggle = function (lang) {
    // textarea content
    var ta = Ext.getCmp('ta');
    if (ta) {
        var rteToggle = Ext.get('tiny-toggle-rte');
        var id = 'ta-' + lang['lang_code'];
        if (rteToggle) {
            rteToggle.on('click', function (a, b) {
                var cb = Ext.get(b);
                if (cb.dom.checked) {
                    if (typeof(tinyMCE) !== 'undefined') {
                        tinyMCE.execCommand('mceAddControl', false, id);
                    }
                } else {
                    if (typeof(tinyMCE) !== 'undefined') {
                        tinyMCE.execCommand('mceRemoveControl', false, id);
                    }
                }
            }, Ext.getCmp('modx-panel-resource'));
        }
    }
};

Lingua.prototype.createHiddenField = function (lang) {
    var record = this.config.siteContent[lang['lang_code']];
    var flag = lang['flag'];
    var pagetitle = Ext.getCmp('modx-resource-pagetitle');
    if (pagetitle) {
        var hiddenCmp = new Ext.form.TextField({
            fieldLabel: _('resource_pagetitle') + '<span class="required">*</span>' + '&nbsp;<img src="../' + flag + '">'
            , description: '<b>[[*pagetitle]]</b><br />' + _('resource_pagetitle_help')
            , name: 'pagetitle_lingua[' + lang['lang_code'] + ']'
            , id: 'modx-resource-pagetitle-' + lang['lang_code']
            , cls: 'lingua-hidden-fields'
            , maxLength: 255
            , anchor: '100%'
            , allowBlank: true
            , enableKeyEvents: true
            , hidden: true
            , value: (record && record.pagetitle ? record.pagetitle : '')
            , listeners: {
                'keyup': {scope: this, fn: function (f, e) {
                        var titlePrefix = MODx.request.a == MODx.action['resource/create'] ? _('new_document') : _('document');
                        var title = Ext.util.Format.stripTags(f.getValue());
                        Ext.getCmp('modx-resource-header').getEl().update('<h2>' + title + '</h2>');
                    }}
            }

        });
        Ext.getCmp(pagetitle.ownerCt.id).insert(pagetitle.ownerCt.items.indexOf(pagetitle) + 1, hiddenCmp);
    }

    var longtitle = Ext.getCmp('modx-resource-longtitle');
    if (longtitle) {
        var hiddenCmp = new Ext.form.TextField({
            fieldLabel: _('resource_longtitle') + '&nbsp;<img src="../' + flag + '">'
            , description: '<b>[[*longtitle]]</b><br />' + _('resource_longtitle_help')
            , name: 'longtitle_lingua[' + lang['lang_code'] + ']'
            , id: 'modx-resource-longtitle-' + lang['lang_code']
            , cls: 'lingua-hidden-fields'
            , maxLength: 255
            , anchor: '100%'
            , hidden: true
            , value: (record && record.longtitle ? record.longtitle : '')
        });
        Ext.getCmp(longtitle.ownerCt.id).insert(longtitle.ownerCt.items.indexOf(longtitle) + 1, hiddenCmp);
    }

    var description = Ext.getCmp('modx-resource-description');
    if (description) {
        var hiddenCmp = new Ext.form.TextArea({
            fieldLabel: _('resource_description') + '&nbsp;<img src="../' + flag + '">'
            , description: '<b>[[*description]]</b><br />' + _('resource_description_help')
            , name: 'description_lingua[' + lang['lang_code'] + ']'
            , id: 'modx-resource-description-' + lang['lang_code']
            , cls: 'lingua-hidden-fields'
            , maxLength: 255
            , anchor: '100%'
            , hidden: true
            , value: (record && record.description ? record.description : '')
        });
        Ext.getCmp(description.ownerCt.id).insert(description.ownerCt.items.indexOf(description) + 1, hiddenCmp);
    }

    var introtext = Ext.getCmp('modx-resource-introtext');
    if (introtext) {
        var hiddenCmp = new Ext.form.TextArea({
            fieldLabel: _('resource_summary') + '&nbsp;<img src="../' + flag + '">'
            , description: '<b>[[*introtext]]</b><br />' + _('resource_summary_help')
            , name: 'introtext_lingua[' + lang['lang_code'] + ']'
            , id: 'modx-resource-introtext-' + lang['lang_code']
            , cls: 'lingua-hidden-fields'
            , grow: true
            , anchor: '100%'
            , hidden: true
            , value: (record && record.introtext ? record.introtext : '')
        });
        Ext.getCmp(introtext.ownerCt.id).insert(introtext.ownerCt.items.indexOf(introtext) + 1, hiddenCmp);
    }

    var modxPanelResource = Ext.getCmp('modx-panel-resource');

    // textarea content
    var ta = Ext.getCmp('ta');
    if (ta) {
        var hiddenCmp = new Ext.form.TextArea({
            name: 'ta_lingua[' + lang['lang_code'] + ']'
            , id: 'ta-' + lang['lang_code']
            , cls: 'modx-richtext lingua-hidden-fields'
            , hideLabel: true
            , anchor: '100%'
            , height: 400
            , grow: false
            , hidden: true
            , value: (record && record.content ? record.content : (record && record.ta ? record.ta : ''))
        });
        Ext.getCmp(ta.ownerCt.id).insert(ta.ownerCt.items.indexOf(ta) + 1, hiddenCmp);

        MODx.triggerRTEOnChange = function () {
            triggerDirtyField(Ext.getCmp('ta-' + lang['lang_code']));
        };

        var _this = this;
        _this.rteToggle(lang);
        modxPanelResource.on('load', function () {
            _this.rteToggle(lang);
        });

        var usingRTE = Ext.getCmp('modx-resource-richtext');
        if (MODx.config.use_editor && typeof (usingRTE) !== "undefined" && usingRTE.checked) {
            hiddenCmp.on('afterrender', function () {
                var f = modxPanelResource.getForm().findField('richtext');
                modxPanelResource.rteLoaded = false;
                if (f && !!f.getValue() && !modxPanelResource.rteLoaded) {
                    if (MODx.loadRTE) {
                        MODx.loadRTE(this.getId());
                    }
                    modxPanelResource.rteLoaded = true;
                } else if (f && !f.getValue() && modxPanelResource.rteLoaded) {
                    if (MODx.unloadRTE) {
                        MODx.unloadRTE(this.getId());
                    }
                    modxPanelResource.rteLoaded = false;
                }
            });
        }
    }

    // hidden  content
    var content = Ext.getCmp('content');
    if (content) {
        var hiddenCmp = new Ext.form.Hidden({
            name: 'content_lingua[' + lang['lang_code'] + ']'
            , id: 'hiddenContent-' + lang['lang_code']
            , value: (record && record.content ? record.content : (record && record.ta ? record.ta : ''))
        });
        Ext.getCmp(content.ownerCt.id).insert(content.ownerCt.items.indexOf(content) + 1, hiddenCmp);
    }

    // duplicate textarea content to hidden content before form submission
    modxPanelResource.on('beforeSubmit', function (o) {
        var ta = Ext.get('ta-' + lang['lang_code']);
        if (ta) {
            var v = ta.dom.value;
            var hc = Ext.getCmp('hiddenContent-' + lang['lang_code']);
            if (hc) {
                hc.setValue(v);
            }
        }
    });

    // static resource
    var staticContent = Ext.getCmp('modx-resource-content-static');
    if (typeof (staticContent) !== "undefined") {
        var hiddenCmp = new MODx.combo.Browser({
            browserEl: 'modx-browser'
            , prependPath: false
            , prependUrl: false
            , hideFiles: true
            , fieldLabel: _('static_resource') + '&nbsp;<img src="../' + flag + '">'
            , description: '<b>[[*content]]</b>'
            , name: 'content_lingua[' + lang['lang_code'] + ']'
            , id: 'modx-resource-content-static-' + lang['lang_code']
            , cls: 'lingua-hidden-fields'
//            , hidden: true
            , maxLength: 255
            , anchor: '100%'
            , value: (record.content || record.ta) || ''
            , openTo: record.openTo
            , listeners: {
                'select': {
                    fn: function (data) {
                        var str = data.fullRelativeUrl;
                        if (MODx.config.base_url != '/') {
                            str = str.replace(MODx.config.base_url, '');
                        }
                        if (str.substring(0, 1) == '/') {
                            str = str.substring(1);
                        }
                        Ext.getCmp('modx-resource-content-static-' + lang['lang_code']).setValue(str);
                        modxPanelResource.markDirty();
                    }
                    , scope: this
                }
            }
        });
        Ext.getCmp(staticContent.ownerCt.id).insert(staticContent.ownerCt.items.indexOf(staticContent) + 1, hiddenCmp);
        // lazy hiding
        hiddenCmp.on('afterrender', function(cmp){
            setTimeout(function () {
                cmp.hide();
            }, 0);
        });
    }

    var symLinkContent = Ext.getCmp('modx-symlink-content');
    if (symLinkContent) {
        var hiddenCmp = new Ext.form.TextField({
            fieldLabel: _('symlink') + '&nbsp;<img src="../' + flag + '">'
            , description: '<b>[[*content]]</b><br />'+_('symlink_help')
            , name: 'content_lingua[' + lang['lang_code'] + ']'
            , id: 'modx-symlink-content-' + lang['lang_code']
            , cls: 'lingua-hidden-fields'
            , hidden: true
            , maxLength: 255
            , anchor: '100%'
            , value: (record.content || record.ta) || ''
        });
        Ext.getCmp(symLinkContent.ownerCt.id).insert(symLinkContent.ownerCt.items.indexOf(symLinkContent) + 1, hiddenCmp);
    }

    var webLinkContent = Ext.getCmp('modx-weblink-content');
    if (webLinkContent) {
        var hiddenCmp = new Ext.form.TextField({
            fieldLabel: _('weblink') + '&nbsp;<img src="../' + flag + '">'
            , description: '<b>[[*content]]</b><br />'+_('weblink_help')
            , name: 'content_lingua[' + lang['lang_code'] + ']'
            , id: 'modx-weblink-content-' + lang['lang_code']
            , cls: 'lingua-hidden-fields'
            , hidden: true
            , maxLength: 255
            , anchor: '100%'
            , value: (record.content || record.ta) || 'http://'
        });
        Ext.getCmp(webLinkContent.ownerCt.id).insert(webLinkContent.ownerCt.items.indexOf(webLinkContent) + 1, hiddenCmp);
    }

    var alias = Ext.getCmp('modx-resource-alias');
    if (alias) {
        var hiddenCmp = new Ext.form.TextField({
            fieldLabel: _('resource_alias') + '&nbsp;<img src="../' + flag + '">'
            , description: '<b>[[*alias]]</b><br />' + _('resource_alias_help')
            , name: 'alias_lingua[' + lang['lang_code'] + ']'
            , id: 'modx-resource-alias-' + lang['lang_code']
            , cls: 'lingua-hidden-fields'
            , hidden: true
            , maxLength: 100
            , anchor: '100%'
            , value: (record && record.alias ? record.alias : '')
        });
        Ext.getCmp(alias.ownerCt.id).insert(alias.ownerCt.items.indexOf(alias) + 1, hiddenCmp);
    }

    var menutitle = Ext.getCmp('modx-resource-menutitle');
    if (menutitle) {
        var hiddenCmp = new Ext.form.TextField({
            fieldLabel: _('resource_menutitle') + '&nbsp;<img src="../' + flag + '">'
            , description: '<b>[[*menutitle]]</b><br />' + _('resource_menutitle_help')
            , name: 'menutitle_lingua[' + lang['lang_code'] + ']'
            , id: 'modx-resource-menutitle-' + lang['lang_code']
            , cls: 'lingua-hidden-fields'
            , hidden: true
            , maxLength: 255
            , anchor: '100%'
            , value: (record && record.menutitle ? record.menutitle : '')
        });
        Ext.getCmp(menutitle.ownerCt.id).insert(menutitle.ownerCt.items.indexOf(menutitle) + 1, hiddenCmp);
    }

    var linkAttributes = Ext.getCmp('modx-resource-link-attributes');
    if (linkAttributes) {
        var hiddenCmp = new Ext.form.TextField({
            fieldLabel: _('resource_link_attributes') + '&nbsp;<img src="../' + flag + '">'
            , description: '<b>[[*link_attributes]]</b><br />' + _('resource_link_attributes_help')
            , name: 'link_attributes_lingua[' + lang['lang_code'] + ']'
            , id: 'modx-resource-link-attributes-' + lang['lang_code']
            , cls: 'lingua-hidden-fields'
            , hidden: true
            , maxLength: 255
            , anchor: '100%'
            , value: (record && record.link_attributes ? record.link_attributes : '')
        });
        Ext.getCmp(linkAttributes.ownerCt.id).insert(linkAttributes.ownerCt.items.indexOf(linkAttributes) + 1, hiddenCmp);
    }

    var uriOverride = Ext.getCmp('modx-resource-uri-override');
    if (uriOverride) {
        var hiddenCmp = new Ext.form.XCheckbox({
            boxLabel: _('resource_uri_override') + '&nbsp;<img src="../' + flag + '">'
            , description: _('resource_uri_override_help')
            , hideLabel: true
            , name: 'uri_override_lingua[' + lang['lang_code'] + ']'
            , value: 1
            , checked: (record && record.uri_override ? (parseInt(record.uri_override) ? true : false) : false)
            , id: 'modx-resource-uri-override-' + lang['lang_code']
            , cls: 'lingua-hidden-fields'
            , hidden: true
        });
        if (hiddenCmp) {
            hiddenCmp.on('check', function (cb) {
                var uri = Ext.getCmp('modx-resource-uri-' + lang['lang_code']);
                if (cb.checked) {
                    uri.show();
                } else {
                    uri.hide();
                }
            });
        }
        Ext.getCmp(uriOverride.ownerCt.id).insert(uriOverride.ownerCt.items.indexOf(uriOverride) + 1, hiddenCmp);
    }

    var uri = Ext.getCmp('modx-resource-uri');
    if (uri) {
        var hiddenCmp = new Ext.form.TextField({
            fieldLabel: _('resource_uri') + '&nbsp;<img src="../' + flag + '">'
            , description: '<b>[[*uri]]</b><br />' + _('resource_uri_help')
            , name: 'uri_lingua[' + lang['lang_code'] + ']'
            , id: 'modx-resource-uri-' + lang['lang_code']
            , cls: 'lingua-hidden-fields'
            , hidden: true
            , maxLength: 255
            , anchor: '70%'
            , value: (record && record.uri ? record.uri : '')
        });
        Ext.getCmp(uri.ownerCt.id).insert(uri.ownerCt.items.indexOf(uri) + 1, hiddenCmp);
    }

};

Lingua.prototype.switchMainField = function (id, name, selectedLang) {
    var cmp = Ext.getCmp(id);
    if (cmp) {
        if (selectedLang !== this.config.defaultLang) {
            cmp.hide();
            if (this.config.formCustomized) {
                if (this.config.fcRules[name].visible) {
                    Ext.getCmp(id + '-' + selectedLang).show();
                }
            } else {
                Ext.getCmp(id + '-' + selectedLang).show();
            }
        } else {
            if (this.config.formCustomized) {
                if (this.config.fcRules[name].visible) {
                    cmp.show();
                }
            } else {
                cmp.show();
            }
        }
    }
};

Lingua.prototype.switchMainFields = function (selectedLang) {
    var title;
    if (selectedLang !== this.config.defaultLang) {
        title = Ext.util.Format.stripTags(Ext.getCmp('modx-resource-pagetitle-' + selectedLang).getValue());
    } else {
        title = Ext.util.Format.stripTags(Ext.getCmp('modx-resource-pagetitle').getValue());
    }
    if (!title) {
        title = '&nbsp;';
    }
    Ext.getCmp('modx-resource-header').getEl().update('<h2>' + title + '</h2>');

    this.switchMainField('modx-resource-pagetitle', 'pagetitle', selectedLang);
    this.switchMainField('modx-resource-longtitle', 'longtitle', selectedLang);
    this.switchMainField('modx-resource-description', 'description', selectedLang);
    this.switchMainField('modx-resource-introtext', 'introtext', selectedLang);

    var content = Ext.getCmp('modx-resource-content');
    if (typeof (content) !== "undefined") {
        content.setTitle(this.contentTitle + '&nbsp;<img src="../' + this.config.langs[selectedLang]['flag'] + '">');
    }

    // textarea content
    var ta = Ext.getCmp('ta');
    if (ta) {
        if (selectedLang !== this.config.defaultLang) {
            ta.hide();
            Ext.getCmp('ta-' + selectedLang).show();
        } else {
            ta.show();
        }
    }

    this.switchMainField('modx-resource-content-static', 'staticContent', selectedLang);
    this.switchMainField('modx-symlink-content', 'symLinkContent', selectedLang);
    this.switchMainField('modx-weblink-content', 'webLinkContent', selectedLang);
    this.switchMainField('modx-resource-alias', 'alias', selectedLang);
    this.switchMainField('modx-resource-menutitle', 'menutitle', selectedLang);
    this.switchMainField('modx-resource-link-attributes', 'linkAttributes', selectedLang);
    this.switchMainField('modx-resource-uri-override', 'uriOverride', selectedLang);

    var uri = Ext.getCmp('modx-resource-uri');
    if (uri) {
        if (selectedLang !== this.config.defaultLang) {
            uri.hide();
            if (Ext.getCmp('modx-resource-uri-override-' + selectedLang).checked) {
                Ext.getCmp('modx-resource-uri-' + selectedLang).show();
            }
        } else {
            if (typeof (uriOverride) !== 'undefined' && uriOverride.checked) {
                uri.show();
            }
        }
    }
};

/*******************************************************************************
 * Initiate All cloned Template Variables' Field
 * @param {object} langs
 * @returns {undefined}
 ******************************************************************************/
Lingua.prototype.initAllClonedTVFields = function (langs) {
    var _this = this;
    if (typeof (this.config.tmplvars) === 'undefined') {
        false;
    }
    Ext.each(this.config.tmplvars, function (tv) {
        Ext.each(langs, function (lang) {
            // field
            _this.initCloneTVField(lang, tv);
        });
    });

    // lazy hiding
    setTimeout(function () {
        var els = Ext.query(".lingua-hidden-fields");
        Ext.each(els, function (item, idx) {
            var cmp, el;
            if (cmp = Ext.getCmp(item.id)) {
                cmp.getEl().setVisibilityMode(Ext.Element.DISPLAY);
                cmp.hide();
            } else if (el = Ext.get(item.id)) {
                el.setVisibilityMode(Ext.Element.DISPLAY);
                el.hide();
            }
        });
    }, 1);
};

Lingua.prototype.initCloneTVField = function (lang, tv) {
    // tv row
    var cloneTVrowId = 'tv' + tv['id'] + '_' + lang['lang_code'] + '_lingua_tv-tr';
    var cloneTVEl = Ext.get(cloneTVrowId);
    var oriTVrowEl = Ext.get('tv' + tv['id'] + '-tr');
    if (typeof (cloneTVEl) !== 'undefined' && cloneTVEl !== null) {
        cloneTVEl.addClass('lingua-hidden-fields');
        cloneTVEl.addClass('lingua-tv-fields-' + lang['lang_code']);
        if (oriTVrowEl.hasClass('tv-first')) {
            cloneTVEl.addClass('tv-first');
        } else if (oriTVrowEl.hasClass('tv-last')) {
            cloneTVEl.addClass('tv-last');
        }
        cloneTVEl.insertAfter(oriTVrowEl);

        /**
         * DO NOT HIDE IT IN HERE, use "lazy hiding" above!
         * Otherwise the component under this element won't be rendered
         * correctly inside the container.
         *
         * cloneTVEl.setVisibilityMode(Ext.Element.DISPLAY);
         * cloneTVEl.hide();
         */
    }
    // caption
    var captionEl = Ext.get('tv' + tv['id'] + '_' + lang['lang_code'] + '_lingua_tv-caption');
    if (typeof (captionEl) !== 'undefined' && captionEl !== null) {
        captionEl.dom.innerHTML += ' <img src="../' + lang['flag'] + '">';
    }
};

Lingua.prototype.switchTVFields = function (selectedLang) {
    var _this = this;
    if (typeof (this.config.tmplvars) === 'undefined') {
        false;
    }
    Ext.each(this.config.tmplvars, function (tv) {
        _this.toggleFieldByLanguage('tv' + tv['id'] + '-tr', 'tv' + tv['id'] + '_' + selectedLang + '_lingua_tv-tr', selectedLang);
    });
};

Lingua.prototype.toggleFieldByLanguage = function (sourceId, cloneId, selectedLang) {
    var TVEl = Ext.get(sourceId);
    if (typeof (TVEl) !== 'undefined' && TVEl !== null) {
        if (selectedLang !== this.config.defaultLang) {
            TVEl.setVisibilityMode(Ext.Element.DISPLAY);
            TVEl.hide();
        } else {
            TVEl.show();
        }
    }
    var cloneTVEl = Ext.get(cloneId);
    if (typeof (cloneTVEl) !== 'undefined' && cloneTVEl !== null) {
        cloneTVEl.addClass('lingua-hidden-fields');
        if (selectedLang !== this.config.defaultLang) {
            cloneTVEl.show();
        }
    }
};
