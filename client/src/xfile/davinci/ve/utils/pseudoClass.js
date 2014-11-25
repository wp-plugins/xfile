//>>built
define("davinci/ve/utils/pseudoClass",[],function(){return{MAQETTA_PSEUDO_CLASS:"maqettaPseudoClass",replace:function(a){return"hover link visited active focus first-letter first-line first-child before after".split(" ").reduce(function(a,b){return a.replace(RegExp(":"+b,"g"),".maqettaPseudoClass"+b[0].toUpperCase()+b.slice(1))},a)}}});
//# sourceMappingURL=pseudoClass.js.map