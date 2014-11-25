//>>built
define("davinci/commands/OrderedCompoundCommand",["dojo/_base/declare","davinci/commands/CompoundCommand"],function(b,c){return b("davinci.commands.OrderedCompoundCommand",c,{undo:function(){if(this._commands)for(var a=0;a<this._commands.length;a++)this._commands[a].undo()}})});
//# sourceMappingURL=OrderedCompoundCommand.js.map