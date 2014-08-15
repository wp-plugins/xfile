/***
 *
 *  Plugin main entry file.
 *  Also used at compile time to include all the plugin's depenedencies.
 *
 *  Remarks : please add all your includes here instead of defining them in submodules.
 *
 */
define([ 'dojo/has', 'require' ], function (has, require) {

    if (has('host-browser'))
    {
      require([
            'dojo/_base/lang',
            'Shell/xfile/ShellManager',
            'xide/factory',
            'xide/types'
      ], function (lang,ShellManager,factory,types)
        {
            var _init = function(eventData){

                try{
                    var ctrArgs = {};
                    if(eventData.name!=='Shell'){//not for us
                        return;
                    }
                    lang.mixin(ctrArgs,eventData);
                    if(!ctrArgs.fileManager){
                        ctrArgs.fileManager=xfile.getContext().getFileManager();
                    }
                    new Shell.xfile.ShellManager(ctrArgs);
                }catch(e){
                    debugger;
                }
            };

            factory.subscribe(types.EVENTS.ON_PLUGIN_READY,_init,this);
            factory.publish(types.EVENTS.ON_PLUGIN_LOADED,{
                name:'Shell'
            },this);

        });
    }
    else {
        console.log('Hello from the server!');
    }
});