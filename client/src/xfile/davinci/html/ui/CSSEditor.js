//>>built
define("davinci/html/ui/CSSEditor",["dojo/_base/declare","davinci/ui/ModelEditor","davinci/html/CSSEditorContext","davinci/html/ui/CSSOutline","davinci/html/CSSFile"],function(a,b,c,d,e){return a(b,{constructor:function(a){this.model=this.cssFile=new e},destroy:function(){this.cssFile.close();this.inherited(arguments)},getOutline:function(){this.outline||(this.outline=new d(this.model));return this.outline},getDefaultContent:function(){return""},getContext:function(){this.context||(this.context=new c(this));
return this.context}})});
//# sourceMappingURL=CSSEditor.js.map