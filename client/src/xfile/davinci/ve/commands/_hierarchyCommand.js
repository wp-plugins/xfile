//>>built
define("davinci/ve/commands/_hierarchyCommand",["dojo/_base/declare"],function(c){return c("davinci.ve.commands._hierarchyCommand",null,{_isRefreshOnDescendantChange:function(a){for(var b;a&&a.domNode&&"BODY"!=a.domNode.tagName;)(a=a.getParent())&&davinci.ve.metadata.queryDescriptor(a.type,"refreshOnDescendantChange")&&(b=a);return b}})});
//# sourceMappingURL=_hierarchyCommand.js.map