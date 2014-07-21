Lingua.window.Lang = function(config) {
    config = config || {};
    Ext.applyIf(config, {
        url: Lingua.config.connectorUrl,
        fields: [{
                html: _('lingua.ref') + ' <a href="http://www.science.co.il/language/locale-codes.asp" target="_blank">http://www.science.co.il/language/locale-codes.asp</a>',
                border: false,
                bodyCssClass: 'panel-desc'
            }, {
                layout: 'column',
                border: false,
                defaults: {
                    border: false,
                    autoHeight: true,
                    layout: 'form',
                    columnWidth: .5,
                    anchor: '95%'
                },
                items: [{
                        items: [{
                                xtype: 'textfield',
                                fieldLabel: _('lingua.local_name'),
                                name: 'local_name',
                                anchor: '100%'
                            }, {
                                xtype: 'textfield',
                                fieldLabel: _('lingua.lang_code'),
                                name: 'lang_code',
                                anchor: '100%'
                            }, {
                                xtype: 'textfield',
                                fieldLabel: _('lingua.lcid_string'),
                                name: 'lcid_string',
                                anchor: '100%'
                            }, {
                                xtype: 'textfield',
                                fieldLabel: _('lingua.lcid_dec'),
                                name: 'lcid_dec',
                                anchor: '100%'
                            }]
                    }, {
                        items: [{
                                xtype: 'textfield', fieldLabel: _('lingua.date_format_lite'),
                                name: 'date_format_lite',
                                anchor: '100%'
                            }, {
                                xtype: 'textfield',
                                fieldLabel: _('lingua.date_format_full'),
                                name: 'date_format_full',
                                anchor: '100%'
                            }, {
                                xtype: 'modx-combo-browser',
                                fieldLabel: _('lingua.flag'),
                                name: 'flag',
                                anchor: '100%',
                                openTo: 'assets/components/lingua/icons/flags/gif/',
                                hideMode: 'offsets',
                                value: config.record && config.record.flag ? config.record.flag : '',
                                renderer: function(value, metaData, record, rowIndex, colIndex, store) {
                                    if (value) {
                                        return '<div style="text-align:center;">' +
                                                '<img src="../' + value + '" style="max-width:40px; max-height:40px">' +
                                                '</div>';
                                    }
                                }
                            }, {
                                layout: 'column',
                                border: false,
                                defaults: {
                                    border: false,
                                    autoHeight: true,
                                    layout: 'form',
                                    columnWidth: .5,
                                    anchor: '95%'
                                },
                                items: [{
                                        items: [{
                                                xtype: 'xcheckbox',
                                                fieldLabel: _('lingua.right_to_left'),
                                                name: 'is_rtl',
                                                anchor: '50%'
                                            }]
                                    }, {
                                        items: [{
                                                xtype: 'xcheckbox',
                                                fieldLabel: _('lingua.active'),
                                                name: 'active',
                                                anchor: '50%'
                                            }]
                                    }]
                            }]
                    }]
            }]
    });
    Lingua.window.Lang.superclass.constructor.call(this, config);
};
Ext.extend(Lingua.window.Lang, MODx.Window);
Ext.reg('lingua-window-lang', Lingua.window.Lang);
