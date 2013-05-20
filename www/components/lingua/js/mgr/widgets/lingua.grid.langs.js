Lingua.grid.Langs = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        id: 'lingua-grid-langs',
        url: Lingua.config.connectorUrl,
        baseParams: {action: 'mgr/langs/getList'},
        fields: ['id', 'active', 'local_name', 'lang_code', 'lcid_string', 'lcid_dec',
            'date_format_lite', 'date_format_full', 'is_rtl', 'flag'],
        paging: true,
        remoteSort: true,
        anchor: '97%',
        save_action: 'mgr/langs/updateFromGrid',
        autosave: true,
        autoExpandColumn: 'local_name',
        columns: [{
                header: _('id'),
                dataIndex: 'id',
                sortable: true,
                hidden: true,
                width: 40
            }, {
                xtype: 'checkcolumn',
                header: _('lingua.active'),
                tooltip: _('lingua.active'),
                dataIndex: 'active',
                sortable: false,
                width: 30,
                processEvent : function(name, e, grid, rowIndex, colIndex){
                    if (name === 'mousedown') {
                        var record = grid.store.getAt(rowIndex);
                        record.set(this.dataIndex, !record.data[this.dataIndex]);
                        MODx.Ajax.request({
                            url: Lingua.config.connectorUrl,
                            params: {
                                action: 'mgr/langs/updateFromGrid',
                                data: JSON.stringify(record.data)
                            },
                            listeners: {
                                'success': {
                                    fn: function(){
                                        Ext.getCmp('lingua-grid-langs').refresh();
                                    }
                                }
                            }
                        });
                        return false;
                    } else {
                        return Ext.grid.ActionColumn.superclass.processEvent.apply(this, arguments);
                    }
                }
            }, {
                header: _('lingua.flag'),
                dataIndex: 'flag',
                sortable: false,
                width: 40,
                renderer: function(value, metaData, record, rowIndex, colIndex, store) {
                    if (value) {
                        return '<div style="text-align:center;">' +
                                '<img src="../' + value + '" style="max-width:40px; max-height:40px">' +
                                '</div>';
                    }
                },
                editor: {
                    xtype: 'modx-combo-browser',
                    browserEl: 'modx-browser',
                    id: 'flag-browser',
                    anchor: '100%',
                    openTo: 'assets/components/lingua/icons/flags/gif/',
                    hideMode: 'offsets',
                    listeners: {
                        'select': {
                            fn: function(value) {
                                var selectedRow = this.getSelectionModel().getSelected();
                                selectedRow.data.flag = value.url;
                                MODx.Ajax.request({
                                    url: Lingua.config.connectorUrl,
                                    params: {
                                        action: 'mgr/langs/updateFromGrid',
                                        data: JSON.stringify(selectedRow.data)
                                    }
                                });
                                this.refresh();
                            },
                            scope: this
                        }
                    }
                }
            }, {
                header: _('lingua.local_name'),
                dataIndex: 'local_name',
                sortable: true,
                editor: {xtype: 'textfield'}
            }, {
                header: _('lingua.lang_code'),
                dataIndex: 'lang_code',
                sortable: true,
                width: 60,
                editor: {xtype: 'textfield'}
            }, {
                header: _('lingua.lcid_string'),
                dataIndex: 'lcid_string',
                sortable: true,
                editor: {xtype: 'textfield'}
            }, {
                header: _('lingua.lcid_dec'),
                dataIndex: 'lcid_dec',
                sortable: true,
                width: 70,
                editor: {xtype: 'textfield'}
            }, {
                header: _('lingua.date_format_lite'),
                dataIndex: 'date_format_lite',
                sortable: false,
                width: 60,
                editor: {xtype: 'textfield'}
            }, {
                header: _('lingua.date_format_full'),
                dataIndex: 'date_format_full',
                sortable: false,
                editor: {xtype: 'textfield'}
            }, {
                xtype: 'checkcolumn',
                header: _('lingua.rtl'),
                dataIndex: 'is_rtl',
                width: 30,
                processEvent : function(name, e, grid, rowIndex, colIndex){
                    if (name === 'mousedown') {
                        var record = grid.store.getAt(rowIndex);
                        record.set(this.dataIndex, !record.data[this.dataIndex]);
                        MODx.Ajax.request({
                            url: Lingua.config.connectorUrl,
                            params: {
                                action: 'mgr/langs/updateFromGrid',
                                data: JSON.stringify(record.data)
                            },
                            listeners: {
                                'success': {
                                    fn: function(){
                                        Ext.getCmp('lingua-grid-langs').refresh();
                                    }
                                }
                            }
                        });
                        return false;
                    } else {
                        return Ext.grid.ActionColumn.superclass.processEvent.apply(this, arguments);
                    }
                }
            }],
        tbar: [{
                text: _('lingua.lang_create'),
                handler: {
                    xtype: 'lingua-window-lang-create',
                    blankValues: true
                }
            }, '->', {
                xtype: 'textfield',
                id: 'langs-search-filter',
                emptyText: _('lingua.search...'),
                listeners: {
                    'change': {
                        fn: this.search,
                        scope: this
                    },
                    'render': {
                        fn: function(cmp) {
                            new Ext.KeyMap(cmp.getEl(), {
                                key: Ext.EventObject.ENTER,
                                fn: function() {
                                    this.fireEvent('change', this);
                                    this.blur();
                                    return true;
                                },
                                scope: cmp
                            });
                        },
                        scope: this}
                }
            }]
    });

    Lingua.grid.Langs.superclass.constructor.call(this, config);
};

Ext.extend(Lingua.grid.Langs, MODx.grid.Grid, {
    search: function(tf, nv, ov) {
        var s = this.getStore();
        s.baseParams.query = tf.getValue();
        this.getBottomToolbar().changePage(1);
        this.refresh();
    },
    getMenu: function() {
        return [{
                text: _('lingua.update'),
                handler: this.updateLang
            }, '-', {
                text: _('lingua.delete'),
                handler: this.removeLang
            }];
    },
    updateLang: function(btn, e) {
        if (!this.updateLangWindow) {
            this.updateLangWindow = MODx.load({
                xtype: 'lingua-window-lang-update',
                record: this.menu.record,
                listeners: {
                    'success': {
                        fn: this.refresh,
                        scope: this
                    }
                }
            });
        }
        this.updateLangWindow.setValues(this.menu.record);
        this.updateLangWindow.show(e.target);
    },
    removeLang: function() {
        MODx.msg.confirm({
            title: _('lingua.delete'),
            text: _('lingua.delete_lang_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/langs/remove',
                id: this.menu.record.id
            },
            listeners: {
                'success': {
                    fn: this.refresh,
                    scope: this
                }
            }
        });
    }
});
Ext.reg('lingua-grid-langs', Lingua.grid.Langs);

Lingua.window.CreateLang = function(config) {
    config = config || {};
    Ext.applyIf(config, {
        title: _('lingua.lang_create'),
        url: Lingua.config.connectorUrl,
        baseParams: {
            action: 'mgr/langs/create'
        },
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
                                                xtype: 'checkbox',
                                                fieldLabel: _('lingua.right_to_left'),
                                                name: 'is_rtl',
                                                anchor: '50%'
                                            }]
                                    }, {
                                        items: [{
                                                xtype: 'checkbox',
                                                fieldLabel: _('lingua.active'),
                                                name: 'active',
                                                anchor: '50%'
                                            }]
                                    }]
                            }]
                    }]
            }]
    });
    Lingua.window.CreateLang.superclass.constructor.call(this, config);
};
Ext.extend(Lingua.window.CreateLang, MODx.Window);
Ext.reg('lingua-window-lang-create', Lingua.window.CreateLang);

Lingua.window.UpdateLang = function(config) {
    config = config || {};
    Ext.applyIf(config, {
        title: _('lingua.update'), url: Lingua.config.connectorUrl,
        baseParams: {
            action: 'mgr/lang/update'
        },
        defaults: {
            xtype: 'textfield',
            anchor: '100%'
        },
        fields: [{
                html: _('lingua.ref') + ' <a href="http://www.science.co.il/language/locale-codes.asp" target="_blank">http://www.science.co.il/language/locale-codes.asp</a>',
                border: false,
                bodyCssClass: 'panel-desc'
            }, {
                xtype: 'hidden',
                name: 'id'
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
                                value: config.record && config.record.local_name ? config.record.local_name : '',
                                anchor: '100%'
                            }, {
                                xtype: 'textfield',
                                fieldLabel: _('lingua.lang_code'),
                                name: 'lang_code',
                                value: config.record && config.record.lang_code ? config.record.lang_code : '',
                                anchor: '100%'
                            }, {
                                xtype: 'textfield',
                                fieldLabel: _('lingua.lcid_string'),
                                name: 'lcid_string',
                                value: config.record && config.record.lcid_string ? config.record.lcid_string : '',
                                anchor: '100%'
                            }, {
                                xtype: 'textfield',
                                fieldLabel: _('lingua.lcid_dec'),
                                name: 'lcid_dec',
                                value: config.record && config.record.lcid_dec ? config.record.lcid_dec : '',
                                anchor: '100%'
                            }]
                    }, {
                        items: [{
                                xtype: 'textfield',
                                fieldLabel: _('lingua.date_format_lite'),
                                name: 'date_format_lite',
                                value: config.record && config.record.date_format_lite ? config.record.date_format_lite : '',
                                anchor: '100%'
                            }, {
                                xtype: 'textfield',
                                fieldLabel: _('lingua.date_format_full'),
                                name: 'date_format_full',
                                value: config.record && config.record.date_format_full ? config.record.date_format_full : '',
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
                                                xtype: 'checkbox',
                                                fieldLabel: _('lingua.right_to_left'),
                                                name: 'is_rtl',
                                                anchor: '50%',
                                                checked: config.record && config.record.is_rtl ? config.record.is_rtl : false
                                            }]
                                    }, {
                                        items: [{
                                                xtype: 'checkbox',
                                                fieldLabel: _('lingua.active'),
                                                name: 'active',
                                                anchor: '50%',
                                                checked: config.record && config.record.active ? config.record.active : false
                                            }]
                                    }]
                            }]
                    }]
            }]
    });
    Lingua.window.UpdateLang.superclass.constructor.call(this, config);
};
Ext.extend(Lingua.window.UpdateLang, MODx.Window);
Ext.reg('lingua-window-lang-update', Lingua.window.UpdateLang);