Lingua.panel.Home = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        border: false,
        baseCls: 'modx-formpanel',
        cls: 'container',
        items: [{
                html: '<b>' + _('lingua') + '</b> ' + Lingua.config.version,
                border: false,
                cls: 'modx-page-header'
            }, {
                xtype: 'modx-tabs',
                defaults: {
                    border: false,
                    autoHeight: true
                },
                border: true,
                items: [{
                        title: _('lingua.settings'),
                        defaults: {
                            autoHeight: true
                        },
                        items: [{
                                html: '<p>' + _('lingua.settings_desc') + '</p>',
                                border: false,
                                bodyCssClass: 'panel-desc'
                            }, {
                                xtype: 'lingua-grid-langs',
                                cls: 'main-wrapper',
                                preventRender: true
                            }]
                    }, {
                        title: _('tmplvars'),
                        defaults: {
                            autoHeight: true
                        },
                        items: [{
                                html: '<p>' + _('lingua.tmplvars_desc') + '</p>',
                                border: false,
                                bodyCssClass: 'panel-desc'
                            }, {
                                xtype: 'modx-tabs',
                                defaults: {
                                    border: false,
                                    autoHeight: true
                                },
                                border: true,
                                items: [
                                    {
                                        title: _('lingua.list'),
                                        layout: 'fit',
                                        defaults: {
                                            autoHeight: true
                                        },
                                        items: [
                                            {
                                                xtype: 'lingua-grid-tvs',
                                                cls: 'main-wrapper',
                                                preventRender: true
                                            }
                                        ]
                                    }, {
                                        title: _('lingua.cloningpatterns'),
                                        layout: 'fit',
                                        defaults: {
                                            autoHeight: true
                                        },
                                        items: [
                                            {
                                                xtype: 'panel',
                                                html: _('lingua.cloningpatterns_desc'),
                                                bodyCssClass: 'panel-desc',
                                                anchor: '100%',
                                                border: false
                                            }, {
                                                xtype: 'lingua-grid-tvspatterns',
                                                cls: 'main-wrapper',
                                                preventRender: true
                                            }
                                        ]
                                    }
                                ]
                            }]
                    }],
                listeners: {
                    'afterrender': function (tabPanel) {
                        tabPanel.doLayout();
                    }
                }
            }, {
                html: '<a href="javascript:void(0);" style="color: #bbbbbb;" id="lingua_about">' + _('lingua.about') + '</a>',
                border: false,
                bodyStyle: 'font-size: 10px; text-align: right; margin: 5px; background-color: transparent;',
                listeners: {
                    afterrender: function () {
                        Ext.get('lingua_about').on('click', function () {
                            var msg = '&copy; 2013-2014, ';
                            msg += '<a href="http://www.virtudraft.com" target="_blank">';
                            msg += 'www.virtudraft.com';
                            msg += '</a><br/>';
                            msg += 'License GPL v3';
                            Ext.MessageBox.alert('Lingua', msg);
                        });
                    }
                }
            }]
    });

    Lingua.panel.Home.superclass.constructor.call(this, config);
};
Ext.extend(Lingua.panel.Home, MODx.Panel);
Ext.reg('lingua-panel-home', Lingua.panel.Home);