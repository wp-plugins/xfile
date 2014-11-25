//>>built
define("davinci/ve/actions/_LayerAction",["dojo/_base/declare","davinci/actions/Action","davinci/ve/metadata"],function(b,c,d){return b("davinci.ve.actions._LayerAction",[c],{isEnabled:function(a){if(!a)return!1;a=a.getSelection();return 1!=a.length?!1:!!d.queryDescriptor(a[0].type,"isLayered")}})});
//# sourceMappingURL=_LayerAction.js.map