Lingua.window.ManualSync = function (config) {
    config = config || {};
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
        newData.push([values.resource_id, values.pagetitle]);
        this.grid.getStore().loadData(newData);
        this.grid.getView().refresh();
    }
});
Ext.reg('lingua-window-manualsync', Lingua.window.ManualSync);