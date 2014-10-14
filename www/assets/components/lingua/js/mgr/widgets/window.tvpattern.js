Lingua.window.TVPattern = function(config) {
    config = config || {};
    Ext.applyIf(config, {
        url: Lingua.config.connectorUrl,
        labelAlign: 'left',
        width: 600,
        fields: [
            {
                fieldLabel: _('type'),
                xtype: 'textfield',
                name: 'type',
                anchor: '100%'
            }, {
                fieldLabel: _('search'),
                xtype: 'textarea',
                name: 'search',
                anchor: '100%'
            }, {
                fieldLabel: _('lingua.replacement'),
                xtype: 'textarea',
                name: 'replacement',
                anchor: '100%'
            }
        ],
        keys: [
            {
                key: [Ext.EventObject.ENTER],
                handler: function(keyCode, event) {
                    var elem = event.getTarget();
                    var component = Ext.getCmp(elem.id);
                    if (component instanceof Ext.form.TextArea) {
                        return component.append("\n");
                    } else if (!this.fp.getForm().isValid()) {
                        return;
                    } else {
                        this.submit();
                    }

                },
                scope: this
            }
        ]
    });
    Lingua.window.TVPattern.superclass.constructor.call(this, config);
};
Ext.extend(Lingua.window.TVPattern, MODx.Window);
Ext.reg('lingua-window-tvpattern', Lingua.window.TVPattern);
