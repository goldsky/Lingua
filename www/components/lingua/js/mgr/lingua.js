var Lingua = function(config) {
	config = config || {};
	Lingua.superclass.constructor.call(this, config);
};
Ext.extend(Lingua, Ext.Component, {
	page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}
});
Ext.reg('lingua', Lingua);
Lingua = new Lingua();