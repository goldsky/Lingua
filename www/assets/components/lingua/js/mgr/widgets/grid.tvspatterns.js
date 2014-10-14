Lingua.grid.TVsPatterns = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        id: 'lingua-grid-tvspatterns',
        url: Lingua.config.connectorUrl,
        baseParams: {action: 'mgr/tvpatterns/getList'},
        fields: ['id', 'type', 'search', 'replacement'],
        paging: true,
        remoteSort: true,
        anchor: '97%',
        save_action: 'mgr/tvpatterns/updateFromGrid',
        autosave: true,
        autoExpandColumn: 'search',
        columns: [{
                header: _('id'),
                dataIndex: 'id',
                sortable: true,
                hidden: true,
                width: 40
            }, {
                header: _('type'),
                dataIndex: 'type',
                sortable: true,
                editor: {xtype: 'textfield'}
            }, {
                header: _('search'),
                dataIndex: 'search',
                sortable: true,
                editor: {xtype: 'textarea'}
            }, {
                header: _('lingua.replacement'),
                dataIndex: 'replacement',
                sortable: true,
                editor: {xtype: 'textarea'}
            }],
        tbar: [{
                text: _('lingua.add_pattern'),
                handler: {
                    xtype: 'lingua-window-tvpattern',
                    title: _('lingua.add_pattern'),
                    baseParams: {
                        action: 'mgr/tvpatterns/create'
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

    Lingua.grid.TVsPatterns.superclass.constructor.call(this, config);
};

Ext.extend(Lingua.grid.TVsPatterns, MODx.grid.Grid, {
    search: function(tf, nv, ov) {
        var s = this.getStore();
        s.baseParams.query = tf.getValue();
        this.getBottomToolbar().changePage(1);
        this.refresh();
    },
    getMenu: function() {
        return [{
                text: _('lingua.update'),
                handler: this.updatePattern
            }, {
                text: _('duplicate'),
                handler: this.duplicatePattern
            }, '-', {
                text: _('lingua.delete'),
                handler: this.removePattern
            }];
    },
    updatePattern: function(btn, e) {
        if (!this.updatePatternWindow) {
            this.updatePatternWindow = MODx.load({
                xtype: 'lingua-window-tvpattern',
                title: _('lingua.update'),
                baseParams: {
                    action: 'mgr/tvpatterns/update'
                },
                listeners: {
                    'success': {
                        fn: this.refresh,
                        scope: this
                    }
                }
            });
        }
        this.updatePatternWindow.baseParams['id'] = this.menu.record.id;
        this.updatePatternWindow.setValues(this.menu.record);
        this.updatePatternWindow.show(e.target);
    },
    duplicatePattern: function(btn, e) {
        if (!this.duplicatePatternWindow) {
            this.duplicatePatternWindow = MODx.load({
                xtype: 'lingua-window-tvpattern',
                title: _('duplicate'),
                baseParams: {
                    action: 'mgr/tvpatterns/create'
                },
                listeners: {
                    'success': {
                        fn: this.refresh,
                        scope: this
                    }
                }
            });
        }
        var values = this.menu.record;
        delete(values['id']);
        this.duplicatePatternWindow.setValues(values);
        this.duplicatePatternWindow.show(e.target);
    },
    removePattern: function() {
        MODx.msg.confirm({
            title: _('lingua.delete'),
            text: _('lingua.delete_lang_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/tvpatterns/remove',
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
Ext.reg('lingua-grid-tvspatterns', Lingua.grid.TVsPatterns);