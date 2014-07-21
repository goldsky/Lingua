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
                width: 40,
                processEvent: function(name, e, grid, rowIndex, colIndex) {
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
                                    fn: function() {
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
                tooltip: _('lingua.right_to_left'),
                dataIndex: 'is_rtl',
                width: 40,
                sortable: false,
                processEvent: function(name, e, grid, rowIndex, colIndex) {
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
                                    fn: function() {
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
                    xtype: 'lingua-window-lang',
                    title: _('lingua.lang_create'),
                    baseParams: {
                        action: 'mgr/langs/create'
                    },
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
                xtype: 'lingua-window-lang',
                title: _('lingua.update'),
                baseParams: {
                    action: 'mgr/langs/update',
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