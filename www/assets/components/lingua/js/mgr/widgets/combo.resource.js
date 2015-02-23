Lingua.combo.Resource = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        fieldLabel: _('resource'),
        id: '',
        width: 370
    });
    Lingua.combo.Resource.superclass.constructor.call(this, config);
};
Ext.extend(Lingua.combo.Resource, MODx.ChangeParentField, {
    end: function (p) {
        var t = Ext.getCmp('modx-resource-tree');
        if (!t)
            return;
        p.d = p.d || p.v;
        t.removeListener('click', this.handleChangeParent, this);
        t.on('click', t._handleClick, t);
        t.disableHref = false;
        Ext.getCmp(this.config.formpanel).fp.getForm().findField('resource_id').setValue(p.v);
        Ext.getCmp(this.config.formpanel).fp.getForm().findField('page_id').setValue(null);
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
Ext.reg('lingua-combo-resource', Lingua.combo.Resource);

