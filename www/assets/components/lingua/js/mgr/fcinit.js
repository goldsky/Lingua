Ext.onReady(function() {
    if (typeof(window.lingua) === 'undefined') {
        return false;
    }
    lingua.initFCRules();
});