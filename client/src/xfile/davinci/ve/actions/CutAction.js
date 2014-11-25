//>>built
define("davinci/ve/actions/CutAction",["dojo/_base/declare","./_CutCopyAction","../../Runtime"],function(c,b,d){return c("davinci.ve.actions.CutAction",[b],{_invokeSourceEditorAction:function(a){a.htmlEditor.cutAction.run()},_executeAction:function(a,c,b,e){d.clipboard=b;a.select(null);a.getCommandStack().execute(e)}})});
//# sourceMappingURL=CutAction.js.map