//>>built
define("davinci/ve/utils/StyleArray",[],function(){return{mergeStyleArrays:function(c,a){c||(c=[]);a||(a=[]);for(var b=dojo.clone(c),d=0;d<a.length;d++)for(var f in a[d])for(j=b.length-1;0<=j;j--){var g=b[j],e;for(e in g)if(f==e){b.splice(j,1);break}}return b.concat(a)}}});
//# sourceMappingURL=StyleArray.js.map