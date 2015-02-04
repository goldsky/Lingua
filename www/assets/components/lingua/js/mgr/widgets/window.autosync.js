Lingua.window.AutoSync = function (config) {
    config = config || {};
    var items = [];
    if (typeof(config.contexts) !== 'undefined' && config.contexts.length > 0) {
        Ext.each(config.contexts, function(item) {
            items.push({
                boxLabel: item.name || item.key,
                name: 'contexts[]',
                inputValue: item.key
            });
        });
    }

    Ext.applyIf(config, {
        url: Lingua.config.connectorUrl,
        closeAction: 'close',
        fields: [
            {
                xtype: 'checkboxgroup',
                fieldLabel: _('contexts'),
                items: items
            }
        ]
    });
    Lingua.window.AutoSync.superclass.constructor.call(this, config);
};
Ext.extend(Lingua.window.AutoSync, MODx.Window);
Ext.reg('lingua-window-autosync', Lingua.window.AutoSync);