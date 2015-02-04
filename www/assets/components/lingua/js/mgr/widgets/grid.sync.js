Lingua.grid.Sync = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        id: 'lingua-grid-sync',
        fields: ['id', 'pagetitle'],
        autoExpandColumn: 'pagetitle',
        autoHeight: true,
        columns: [{
                header: _('id'),
                dataIndex: 'id',
                sortable: true,
                width: 40,
                hidden: false,
                fixed: true
            }, {
                header: _('pagetitle'),
                dataIndex: 'pagetitle',
                sortable: true
            }],
        tbar: [{
                text: _('lingua.auto_sync'),
                scope: this,
                handler: this.autoSync
            }],
        bbar: ['->', {
                text: _('add'),
                scope: this,
                handler: this.addManualResource
            }, {
                text: _('lingua.manual_sync'),
                scope: this,
                handler: this.manualSync
            }]
    });

    Lingua.grid.Sync.superclass.constructor.call(this, config);
};

Ext.extend(Lingua.grid.Sync, MODx.grid.LocalGrid, {
    getMenu: function() {
        var menu = [
            {
                text: _('delete'),
                handler: this.removeResource
            }
        ];
        return menu;
    },
    removeResource: function() {
        this.getStore().remove(this.getSelectionModel().getSelections()[0]);
        this.getView().refresh();
    },
    autoSync: function() {
        var _this = this;
        _this._loadMask();
        MODx.Ajax.request({
            url: Lingua.config.connectorUrl,
            params: {
                action: 'mgr/contexts/getlist',
            },
            listeners: {
                'success': {
                    fn: function(response) {
                        if (response.success) {
                            _this.autoSyncWin(response.results);
                        }
                    }
                },
                'failure': {
                    fn: function(response) {
                        if (typeof(response.message) !== 'undefined') {
                            MODx.msg.alert(_('error'), response.message, Ext.emptyFn);
                        }
                        _this._hideMask();
                    }
                }
            }
        });
    },
    autoSyncWin: function(contexts) {
        var _this = this;
        var win = new Lingua.window.AutoSync({
            title: _('lingua.auto_sync'),
            contexts: contexts,
            baseParams: {
                action: 'mgr/tools/autosync',
                limit: 20,
                start: 0
            },
            listeners: {
                'success': {
                    fn: function(r) {
                        var response = r.a.result;
                        if (response.success) {
                            var total = response.total - 0; // typecasting
                            if (total > response.object.nextStart) {
                                // recursive loop
                                _this.autoSyncLoop(20, response.object.nextStart);
                            } else {
                                _this._hideMask();
                                MODx.msg.alert(_('success'), response.message || '', Ext.emptyFn);
                            }
                        }
                    }
                },
                'failure': {
                    fn: function(response) {
                        if (typeof(response.message) !== 'undefined') {
                            MODx.msg.alert(_('error'), response.message, Ext.emptyFn);
                        }
                        _this._hideMask();
                    }
                }
            }
        });
        win.on('close', function(){
            _this._hideMask();
        });
        win.show();
    },
    autoSyncLoop: function(limit, start) {
        var _this = this;
        MODx.Ajax.request({
            url: Lingua.config.connectorUrl,
            params: {
                action: 'mgr/tools/autosync',
                limit: (limit ? limit : 20),
                start: (start ? start : 0)
            },
            listeners: {
                'success': {
                    fn: function(response) {
                        if (response.success) {
                            var total = response.total - 0; // typecasting
                            if (total > response.nextStart) {
                                // recursive loop
                                _this.autoSyncLoop(limit, response.nextStart);
                            } else {
                                _this._hideMask();
                            }
                        }
                    }
                },
                'failure': {
                    fn: function(response) {
                        if (typeof(response.message) !== 'undefined') {
                            MODx.msg.alert(_('error'), response.message, Ext.emptyFn);
                        }
                        _this._hideMask();
                    }
                }
            }
        });
    },
    _loadMask: function() {
        if (!this.loadToolMask) {
            this.loadToolMask = new Ext.LoadMask(Ext.getBody().dom, {
                msg: _('lingua.please_wait')
            });
        }
        this.loadToolMask.show();
    },
    _hideMask: function() {
        if (this.loadToolMask) {
            this.loadToolMask.hide();
        }
    },
    addManualResource: function() {
        var win = MODx.load({
            title: _('add'),
            xtype: 'lingua-window-manualsync',
            grid: this
        });
        win.reset();
        win.show();
    },
    manualSync: function() {
        var records = this.getStore().getRange();
        var res = [];
        Ext.each(records, function(record){
            res.push(record.data.id);
        });
        if (res.length === 0) {
            MODx.msg.alert(_('error'), _('lingua.manual_sync_err_ns'), Ext.emptyFn);
            return false;
        }
        this._loadMask();
        this.manualSyncLoop(20, 0, res);
    },
    manualSyncLoop: function(limit, start, ids) {
        var _this = this;
        MODx.Ajax.request({
            url: Lingua.config.connectorUrl,
            params: {
                action: 'mgr/tools/manualsync',
                limit: (limit ? limit : 20),
                start: (start ? start : 0),
                ids: JSON.stringify(ids)
            },
            listeners: {
                'success': {
                    fn: function(response) {
                        if (response.success) {
                            var total = response.total - 0; // typecasting
                            if (total > response.nextStart) {
                                // recursive loop
                                _this.manualSyncLoop(limit, response.nextStart, ids);
                            } else {
                                _this._hideMask();
                                _this.getStore().loadData([]);
                                _this.getView().refresh();
                                MODx.msg.alert(_('success'), response.message || '', Ext.emptyFn);
                            }
                        }
                    }
                },
                'failure': {
                    fn: function(response) {
                        if (typeof(response.message) !== 'undefined') {
                            MODx.msg.alert(_('error'), response.message, Ext.emptyFn);
                        }
                        _this._hideMask();
                    }
                }
            }
        });
    }
});
Ext.reg('lingua-grid-sync', Lingua.grid.Sync);