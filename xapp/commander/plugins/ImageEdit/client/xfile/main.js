/***
 *
 *  Plugin main entry file.
 *  Also used at compile time to include all the plugin's dependencies.
 *
 *  Remarks : please add all your includes here instead of defining them in submodules.
 *
 */
define([ 'dojo/has', 'require' ], function (has, require) {

    if (has('host-browser'))
    {
      require([
            'dojo/_base/lang',
            './ImageEditManager',
            'xide/factory',
            'xide/types'
      ], function (lang,ImageEditManager,factory,types)
        {
            var _init = function(eventData){
                try{
                    console.log('plugin ready : ' +eventData.name);
                if(eventData.name!=='ImageEdit'){//not for us
                    return;
                }
                var ctrArgs = {};

                lang.mixin(ctrArgs,eventData);
                if(!ctrArgs.fileManager){
                    ctrArgs.fileManager=xfile.getContext().getFileManager();
                }

                var imgManager =new ImageEditManager(ctrArgs);
                }catch(e){
                    debugger;
                }


            };
            factory.subscribe(types.EVENTS.ON_PLUGIN_READY,_init,this);
            factory.publish(types.EVENTS.ON_PLUGIN_LOADED,{
                name:'ImageEdit'
            },this);
        });
    }
    else {
        console.log('Hello from the server!');
    }
});