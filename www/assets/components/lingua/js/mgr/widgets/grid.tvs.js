Lingua.grid.TVs = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        id: 'lingua-grid-tvs',
        url: Lingua.config.connectorUrl,
        baseParams: {action: 'mgr/tv/getList'},
        fields: ['id', 'name', 'type'],
        paging: true,
        remoteSort: true,
        anchor: '97%',
        autoExpandColumn: 'name',
        columns: [{
                header: _('id'),
                dataIndex: 'id',
                sortable: true,
                hidden: true,
                width: 40
            }, {
                header: _('name'),
                dataIndex: 'name',
                sortable: true
            }, {
                header: _('type'),
                dataIndex: 'type',
                sortable: true
            }],
        tbar: [{
                text: _('lingua.add_tv'),
                handler: {
                    xtype: 'lingua-window-tv',
                    title: _('lingua.add_tv'),
                    baseParams: {
                        action: 'mgr/tv/create'
                    },
                    blankValues: true
                }
            }, '->', {
                xtype: 'textfield',
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
                        scope: this
                    }
                }
            }]
    });

    Lingua.grid.TVs.superclass.constructor.call(this, config);
};

Ext.extend(Lingua.grid.TVs, MODx.grid.Grid, {
    search: function(tf, nv, ov) {
        var s = this.getStore();
        s.baseParams.query = tf.getValue();
        this.getBottomToolbar().changePage(1);
        this.refresh();
    },
    getMenu: function() {
        return [{
                text: _('lingua.delete'),
                handler: this.removeTV
            }];
    },
    removeTV: function() {
        MODx.msg.confirm({
            title: _('lingua.delete'),
            text: _('lingua.delete_tv_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/tv/remove',
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
Ext.reg('lingua-grid-tvs', Lingua.grid.TVs);