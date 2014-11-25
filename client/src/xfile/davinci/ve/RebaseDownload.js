//>>built
define("davinci/ve/RebaseDownload",["dojo/_base/declare","./RebuildPage","../library"],function(e,f,g){return e(f,{constructor:function(a){this.libs=a},getLibraryBase:function(a,d){for(var b in this.libs){var c=this.libs[b];if(c.id==a&&c.version==d)return b=new Deferred,b.resolve(c.root),b}return g.getLibRoot(a,d)||""}})});
//# sourceMappingURL=RebaseDownload.js.map