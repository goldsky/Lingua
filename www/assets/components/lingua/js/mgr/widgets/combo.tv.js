Lingua.combo.TV = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        url: Lingua.config.connectorUrl,
        baseParams: {
            action: 'mgr/tv/combogetlist',
            excludeExisting: 1
        },
        width: 300,
        pageSize: 10,
        typeAhead: true,
        editable: true,
        forceSelection: true,
        minChars: 1,
        triggerAction: 'all',
        lazyRender: true,
        fields: ['id', 'name'],
        name: 'tmplvarid',
        hiddenName: 'tmplvarid',
        displayField: 'name',
        valueField: 'id'
    });
    Lingua.combo.TV.superclass.constructor.call(this, config);
};
Ext.extend(Lingua.combo.TV, MODx.combo.ComboBox);
Ext.reg('lingua-combo-tv', Lingua.combo.TV);