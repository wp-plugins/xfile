//>>built
define("davinci/ve/actions/CopyAction",["dojo/_base/declare","./_CutCopyAction","../../Runtime"],function(a,b,d){return a("davinci.ve.actions.CopyAction",[b],{_invokeSourceEditorAction:function(c){c.htmlEditor.copyAction.run()},_executeAction:function(c,a,b,e){e=d.clipboard;d.clipboard=b;if(!e)c.onSelectionChange(a)}})});
//# sourceMappingURL=CopyAction.js.map