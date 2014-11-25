//>>built
define("davinci/ve/utils/ImageUtils",[],function(){return{ImageUpdateFocus:function(a,c){if(c&&a&&a.domNode&&"IMG"===a.domNode.tagName)var e=dojo.connect(a.domNode,"onload",function(){for(var d=c.getSelection(),b=0;b<d.length;b++)if(d[b]==a){c.updateFocus(a,b);break}dojo.disconnect(e)})}}});
//# sourceMappingURL=ImageUtils.js.map