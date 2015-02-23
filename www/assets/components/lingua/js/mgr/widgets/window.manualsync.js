Lingua.window.ManualSync = function (config) {
    config = config || {};
    var win = this;
    Ext.applyIf(config, {
        closeAction: 'close',
        fields: [
            {
                xtype: 'hidden',
                name: 'resource_id'
            }, {
                xtype: 'lingua-combo-resource',
                name: 'pagetitle',
                formpanel: this.getId()
            }, {
                xtype: 'modx-combo',
                fieldLabel: _('lingua....or') + ' ' + _('lingua.pagetitle_of_target'),
                name: 'page_id',
                anchor: '100%',
                url: Lingua.config.connectorUrl,
                baseParams: {
                    action: 'mgr/resource/getlist',
                    combo: true
                },
                displayField: 'pagetitle',
                valueField: 'id',
                fields: ['id','pagetitle'],
                editable: true,
                typeAhead: true,
                forceSelection: false,
                listeners: {
                    select: {
                        fn: function(combo, record, index) {
                            if (combo.getValue() === "" || combo.getValue() === 0 || combo.getValue() === "&nbsp;") {
                                combo.setValue(null);
                            } else {
                                win.fp.getForm().findField('resource_id').setValue(record.id);
                            }
                            win.fp.getForm().findField('pagetitle').reset();
                        },
                        scope: this
                    },
                    blur: {
                        fn: function(combo) {
                            if (combo.getValue() === "" || combo.getValue() === 0 || combo.getValue() === "&nbsp;") {
                                combo.setValue(null);
                            }
                        },
                        scope: this
                    }
                }
            }
        ],
        buttons: [{
                text: _('cancel'),
                scope: this,
                handler: this.close
            }, {
                text: _('add'),
                cls: 'primary-button',
                scope: this,
                handler: function () {
                    this.addResourceToGrid();
                    this.close();
                }
            }]
    });
    Lingua.window.ManualSync.superclass.constructor.call(this, config);
};
Ext.extend(Lingua.window.ManualSync, MODx.Window, {
    addResourceToGrid:function() {
        var values = this.fp.getForm().getValues();
        var records = this.grid.getStore().getRange();
        var newData = [];
        Ext.each(records, function(record){
            newData.push([
                record.data.id,
                record.data.pagetitle
            ]);
        });
        newData.push([values.resource_id, values.pagetitle || values.page_id]);
        this.grid.getStore().loadData(newData);
        this.grid.getView().refresh();
    }
});
Ext.reg('lingua-window-manualsync', Lingua.window.ManualSync);