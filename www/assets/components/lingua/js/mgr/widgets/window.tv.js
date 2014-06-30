Lingua.window.TV = function(config) {
    config = config || {};
    Ext.applyIf(config, {
        url: Lingua.config.connectorUrl,
        fields: [
            {
                xtype: 'lingua-combo-tv'
            }
        ],
        width: 330
    });
    Lingua.window.TV.superclass.constructor.call(this, config);
};
Ext.extend(Lingua.window.TV, MODx.Window);
Ext.reg('lingua-window-tv', Lingua.window.TV);
