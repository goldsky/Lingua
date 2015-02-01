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
            xtype: 'lingua-window-resourcecombo',
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

Lingua.window.ResourceCombo = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        fieldLabel: _('resource'),
        id: '',
        width: 370
    });
    Lingua.window.ResourceCombo.superclass.constructor.call(this, config);
};
Ext.extend(Lingua.window.ResourceCombo, MODx.ChangeParentField, {
    end: function (p) {
        var t = Ext.getCmp('modx-resource-tree');
        if (!t)
            return;
        p.d = p.d || p.v;
        t.removeListener('click', this.handleChangeParent, this);
        t.on('click', t._handleClick, t);
        t.disableHref = false;
        Ext.getCmp(this.config.formpanel).fp.getForm().findField('resource_id').setValue(p.v);
        this.setValue(p.d);
        this.oldValue = false;
    },
    disableTreeClick: function () {
        MODx.debug('Disabling tree click');
        var t = Ext.getCmp('modx-resource-tree');
        if (!t) {
            MODx.debug('No tree found in disableTreeClick!');
            return false;
        }
        this.oldDisplayValue = this.getValue();
        this.oldValue = this.config.oldValue || '';

        this.setValue(_('resource_parent_select_node'));

        t.expand();
        t.removeListener('click', t._handleClick);
        t.on('click', this.handleChangeParent, this);
        t.disableHref = true;

        return true;
    }
});
Ext.reg('lingua-window-resourcecombo', Lingua.window.ResourceCombo);

