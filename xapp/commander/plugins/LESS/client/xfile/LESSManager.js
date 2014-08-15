define([
    'dojo/_base/declare',
    'dojo/_base/connect',
    "dojo/_base/lang",
    'xapp/factory',
    'xide/types',
    'xapp/manager/ManagerBase',
    'dojo/cookie',
    'dojox/encoding/digests/MD5',
    'dojo/json'
], function (declare, connect, lang, eventFactory, types, ManagerBase,cookie, MD5, json,LESS) {

    return declare("LESS.xfile.LESSManager", [ManagerBase],
        {
            mainView: null,
            ctx: null,
            config: null,
            panelManager: null,
            fileManager: null,
            currentItem: null,
            persistent: true,
            cookiePrefix: 'XLESS',
            ctorArgs:null,
            onItemSelected: function (eventData) {
                this.currentItem = eventData.item;
            },
            _registerListeners: function () {
                this.inherited(arguments);
                eventFactory.subscribe(types.EVENTS.ITEM_SELECTED, this.onItemSelected, this);
            },
            constructor: function (ctorArgs) {
                this.ctorArgs=ctorArgs;
            }
        });
});