function Lingua(config) {
    this.config = config;
}

Lingua.prototype.createHiddenFields = function(lang) {
    var modxPanelResource = Ext.getCmp('modx-panel-resource');
    var record = this.config.siteContent[lang];
    var flag = this.config.langs[lang]['flag'];
    var pagetitle = Ext.getCmp('modx-resource-pagetitle');
    if (pagetitle) {
        var hiddenCmp = new Ext.form.TextField({
            fieldLabel: _('resource_pagetitle') + '<span class="required">*</span>' + '&nbsp;<img src="../' + flag + '">'
            , description: '<b>[[*pagetitle]]</b><br />' + _('resource_pagetitle_help')
            , name: 'pagetitle_lingua[' + lang + ']'
            , id: 'modx-resource-pagetitle-' + lang
            , cls: 'lingua-hidden-fields'
            , maxLength: 255
            , anchor: '100%'
            , allowBlank: true
            , enableKeyEvents: true
            , hidden: true
            , value: (record && record.pagetitle ? record.pagetitle : '')
            , listeners: {
                'keyup': {scope: this, fn: function(f, e) {
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
            , name: 'longtitle_lingua[' + lang + ']'
            , id: 'modx-resource-longtitle-' + lang
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
            , name: 'description_lingua[' + lang + ']'
            , id: 'modx-resource-description-' + lang
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
            , name: 'introtext_lingua[' + lang + ']'
            , id: 'modx-resource-introtext-' + lang
            , cls: 'lingua-hidden-fields'
            , grow: true
            , anchor: '100%'
            , hidden: true
            , value: (record && record.introtext ? record.introtext : '')
        });
        Ext.getCmp(introtext.ownerCt.id).insert(introtext.ownerCt.items.indexOf(introtext) + 1, hiddenCmp);
    }

    // textarea content
    var ta = Ext.getCmp('ta');
    if (ta) {
        var hiddenCmp = new Ext.form.TextArea({
            name: 'ta_lingua[' + lang + ']'
            , id: 'ta-' + lang
            , cls: 'modx-richtext lingua-hidden-fields'
            , hideLabel: true
            , anchor: '100%'
            , height: 400
            , grow: false
            , hidden: true
            , value: (record && record.content ? record.content : (record && record.ta ? record.ta : ''))
        });
        Ext.getCmp(ta.ownerCt.id).insert(ta.ownerCt.items.indexOf(ta) + 1, hiddenCmp);
        
        MODx.triggerRTEOnChange = function() {
            triggerDirtyField(Ext.getCmp('ta-' + lang));
        };

        if (MODx.config.use_editor && MODx.loadRTE) {
            hiddenCmp.on('afterrender', function(){
                var f = modxPanelResource.getForm().findField('richtext');
                modxPanelResource.rteLoaded = false;
                if (f && f.getValue() == 1 && !modxPanelResource.rteLoaded) {
                    MODx.loadRTE(this.getId());
                    modxPanelResource.rteLoaded = true;
                } else if (f && f.getValue() == 0 && modxPanelResource.rteLoaded) {
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
            name: 'content_lingua[' + lang + ']'
            , id: 'hiddenContent-' + lang
            , value: (record && record.content ? record.content : (record && record.ta ? record.ta : ''))
        });
        Ext.getCmp(content.ownerCt.id).insert(content.ownerCt.items.indexOf(content) + 1, hiddenCmp);
    }

    // duplicate textarea content to hidden content before form submission
    modxPanelResource.on('beforeSubmit', function(o){
        var ta = Ext.get('ta-' + lang);
        if (ta) {
            var v = ta.dom.value;
            var hc = Ext.getCmp('hiddenContent-' + lang);
            if (hc) {
                hc.setValue(v);
            }
        }
    });

    var alias = Ext.getCmp('modx-resource-alias');
    if (alias) {
        var hiddenCmp = new Ext.form.TextField({
            fieldLabel: _('resource_alias') + '&nbsp;<img src="../' + flag + '">'
            , description: '<b>[[*alias]]</b><br />' + _('resource_alias_help')
            , name: 'alias_lingua[' + lang + ']'
            , id: 'modx-resource-alias-' + lang
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
            , name: 'menutitle_lingua[' + lang + ']'
            , id: 'modx-resource-menutitle-' + lang
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
            , name: 'link_attributes_lingua[' + lang + ']'
            , id: 'modx-resource-link-attributes-' + lang
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
            , name: 'uri_override_lingua[' + lang + ']'
            , value: 1
            , checked: (record && record.uri_override ? (parseInt(record.uri_override) ? true : false) : false)
            , id: 'modx-resource-uri-override-' + lang
            , cls: 'lingua-hidden-fields'
            , hidden: true
        });
        if (hiddenCmp) {
            hiddenCmp.on('check', function(cb) {
                var uri = Ext.getCmp('modx-resource-uri-' + lang);
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
            , name: 'uri_lingua[' + lang + ']'
            , id: 'modx-resource-uri-' + lang
            , cls: 'lingua-hidden-fields'
            , hidden: true
            , maxLength: 255
            , anchor: '70%'
            , value: (record && record.uri ? record.uri : '')
        });
        Ext.getCmp(uri.ownerCt.id).insert(uri.ownerCt.items.indexOf(uri) + 1, hiddenCmp);
    }

};

Lingua.prototype.switchLanguage = function(selectedLang) {
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

    var els = Ext.query(".lingua-hidden-fields");
    Ext.each(els, function(item, idx) {
        Ext.getCmp(item.id).hide();
    });
    
    var pagetitle = Ext.getCmp('modx-resource-pagetitle');
    if (pagetitle) {
        if (selectedLang !== this.config.defaultLang) {
            pagetitle.hide();
            Ext.getCmp('modx-resource-pagetitle-' + selectedLang).show();
        } else {
            pagetitle.show();
        }
    }

    var longtitle = Ext.getCmp('modx-resource-longtitle');
    if (longtitle) {
        if (selectedLang !== this.config.defaultLang) {
            longtitle.hide();
            Ext.getCmp('modx-resource-longtitle-' + selectedLang).show();
        } else {
            longtitle.show();
        }
    }

    var description = Ext.getCmp('modx-resource-description');
    if (description) {
        if (selectedLang !== this.config.defaultLang) {
            description.hide();
            Ext.getCmp('modx-resource-description-' + selectedLang).show();
        } else {
            description.show();
        }
    }

    var introtext = Ext.getCmp('modx-resource-introtext');
    if (introtext) {
        if (selectedLang !== this.config.defaultLang) {
            introtext.hide();
            Ext.getCmp('modx-resource-introtext-' + selectedLang).show();
        } else {
            introtext.show();
        }
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

    var alias = Ext.getCmp('modx-resource-alias');
    if (alias) {
        if (selectedLang !== this.config.defaultLang) {
            alias.hide();
            Ext.getCmp('modx-resource-alias-' + selectedLang).show();
        } else {
            alias.show();
        }
    }

    var menutitle = Ext.getCmp('modx-resource-menutitle');
    if (menutitle) {
        if (selectedLang !== this.config.defaultLang) {
            menutitle.hide();
            Ext.getCmp('modx-resource-menutitle-' + selectedLang).show();
        } else {
            menutitle.show();
        }
    }

    var linkAttributes = Ext.getCmp('modx-resource-link-attributes');
    if (linkAttributes) {
        if (selectedLang !== this.config.defaultLang) {
            linkAttributes.hide();
            Ext.getCmp('modx-resource-link-attributes-' + selectedLang).show();
        } else {
            linkAttributes.show();
        }
    }

    var uriOverride = Ext.getCmp('modx-resource-uri-override');
    if (uriOverride) {
        if (selectedLang !== this.config.defaultLang) {
            uriOverride.hide();
            Ext.getCmp('modx-resource-uri-override-' + selectedLang).show();
        } else {
            uriOverride.show();
        }
    }

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