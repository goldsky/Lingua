Lingua.grid.ResourceScopes = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        id: 'lingua-grid-resourcescopes',
        url: Lingua.config.connectorUrl,
        baseParams: {action: 'mgr/resourcescopes/getList'},
        fields: ['id', 'resource_id', 'pagetitle', 'properties', 'property_langs',
            'as_parent', 'as_ancestor', 'exclude_self'],
        paging: true,
        remoteSort: true,
        anchor: '97%',
        autoExpandColumn: 'pagetitle',
        columns: [{
                header: _('id'),
                dataIndex: 'id',
                sortable: true,
                hidden: true,
                width: 40
            }, {
                header: _('id'),
                dataIndex: 'resource_id',
                width: 40,
                sortable: true
            }, {
                header: _('pagetitle'),
                dataIndex: 'pagetitle',
                sortable: true
            }, {
                header: _('properties'),
                dataIndex: 'properties',
                sortable: false,
                editor: {xtype: 'textarea'}
            }, {
                xtype: 'checkcolumn',
                header: _('parent'),
                tooltip: _('lingua.as_parent'),
                dataIndex: 'as_parent',
                width: 80,
                fixed: true,
                sortable: false,
                processEvent: this.processCheckColumn
            }, {
                xtype: 'checkcolumn',
                header: _('lingua.as_ancestor'),
                tooltip: _('lingua.as_ancestor_desc'),
                dataIndex: 'as_ancestor',
                width: 100,
                fixed: true,
                sortable: false,
                processEvent: this.processCheckColumn
            }, {
                xtype: 'checkcolumn',
                header: _('lingua.exclude_self'),
                tooltip: _('lingua.exclude_self_desc'),
                dataIndex: 'exclude_self',
                width: 100,
                fixed: true,
                sortable: false,
                processEvent: this.processCheckColumn
            }],
        tbar: [{
                text: _('add'),
                handler: {
                    xtype: 'lingua-window-resourcescope',
                    title: _('lingua.resourcescope_create'),
                    baseParams: {
                        action: 'mgr/resourcescopes/create'
                    },
                    blankValues: true
                }
            }, '->', {
                xtype: 'textfield',
                id: 'scopes-search-filter',
                emptyText: _('lingua.search...'),
                listeners: {
                    'change': {
                        fn: this.search,
                        scope: this
                    },
                    'render': {
                        fn: function (cmp) {
                            new Ext.KeyMap(cmp.getEl(), {
                                key: Ext.EventObject.ENTER,
                                fn: function () {
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
    Lingua.grid.ResourceScopes.superclass.constructor.call(this, config);
};

Ext.extend(Lingua.grid.ResourceScopes, MODx.grid.Grid, {
    search: function (tf, nv, ov) {
        var s = this.getStore();
        s.baseParams.query = tf.getValue();
        this.getBottomToolbar().changePage(1);
        this.refresh();
    },
    getMenu: function () {
        return [{
                text: _('lingua.update'),
                handler: this.updateResourceScope
            }, '-', {
                text: _('lingua.delete'),
                handler: this.removeResourceScope
            }];
    },
    processCheckColumn: function (name, e, grid, rowIndex, colIndex) {
        if (name === 'mousedown') {
            var record = grid.store.getAt(rowIndex);
            record.set(this.dataIndex, !record.data[this.dataIndex]);
            MODx.Ajax.request({
                url: Lingua.config.connectorUrl,
                params: {
                    action: 'mgr/resourcescopes/updateFromGrid',
                    data: JSON.stringify(record.data)
                },
                listeners: {
                    'success': {
                        fn: function () {
                            Ext.getCmp('lingua-grid-resourcescopes').refresh();
                        }
                    }
                }
            });
            return false;
        } else {
            return Ext.grid.ActionColumn.superclass.processEvent.apply(this, arguments);
        }
    },
    updateResourceScope: function (e) {
        var updateResourceScopeWindow = MODx.load({
            xtype: 'lingua-window-resourcescope',
            title: _('lingua.update'),
            baseParams: {
                action: 'mgr/resourcescopes/update',
                id: this.menu.record.id
            },
            closeAction: 'close',
            listeners: {
                'success': {
                    fn: this.refresh,
                    scope: this
                }
            }
        });
        updateResourceScopeWindow.setValues(this.menu.record);
        updateResourceScopeWindow.show(e.target);
    },
    removeResourceScope: function () {
        MODx.msg.confirm({
            title: _('lingua.delete'),
            text: _('lingua.delete_resourcescope_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/resourcescopes/remove',
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
Ext.reg('lingua-grid-resourcescopes', Lingua.grid.ResourceScopes);