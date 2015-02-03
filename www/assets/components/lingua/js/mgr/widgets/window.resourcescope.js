Lingua.window.ResourceScope = function (config) {
    config = config || {};
    var fields = [];
    if (typeof (config.baseParams.id) !== 'undefined' && config.baseParams.id > 0) {
    } else {
        fields.push({
            xtype: 'hidden',
            name: 'id'
        }, {
            xtype: 'hidden',
            name: 'resource_id'
        }, {
            xtype: 'lingua-combo-resource',
            name: 'resource-combo',
            formpanel: this.getId()
        });
    }
    fields.push({
        xtype: 'textarea',
        fieldLabel: _('properties'),
        name: 'properties',
        anchor: '100%',
        hidden: true
    });
    fields.push({
        xtype: 'textfield',
        fieldLabel: _('lingua.languages'),
        description: _('lingua.languages_field_desc'),
        name: 'property_langs',
        anchor: '100%'
    });
    fields.push({
        xtype: 'xcheckbox',
        boxLabel: _('lingua.as_parent'),
        name: 'as_parent'
    });
    fields.push({
        xtype: 'xcheckbox',
        boxLabel: _('lingua.as_ancestor'),
        name: 'as_ancestor'
    });
    fields.push({
        xtype: 'xcheckbox',
        boxLabel: _('lingua.exclude_self'),
        name: 'exclude_self'
    });
    Ext.applyIf(config, {
        url: Lingua.config.connectorUrl,
        fields: fields
    });
    Lingua.window.ResourceScope.superclass.constructor.call(this, config);
};
Ext.extend(Lingua.window.ResourceScope, MODx.Window);
Ext.reg('lingua-window-resourcescope', Lingua.window.ResourceScope);