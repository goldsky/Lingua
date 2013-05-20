Ext.onReady(function() {
	MODx.load({xtype: 'lingua-page-home'});
});

Lingua.page.Home = function(config) {
	config = config || {};
	Ext.applyIf(config, {
		components: [{
				xtype: 'lingua-panel-home',
				renderTo: 'lingua-panel-home-div',
				id: 'lingua-panel-home'
			}]
	});
	Lingua.page.Home.superclass.constructor.call(this, config);
};
Ext.extend(Lingua.page.Home, MODx.Component);
Ext.reg('lingua-page-home', Lingua.page.Home);