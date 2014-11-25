var isMaster = false;
var debug=true;
var device=null;
var sctx=null;
var ctx=null;
var cctx=null;
var mctx=null;
var rtConfig="release";
var returnUrl= "";
var dataHost ="";
function JSCOMPILER_PRESERVE(){}

var xFileConfigMixin =%XFILE_CONFIG_MIXIN%;
var xFileConfig={
    mixins:[
        {
            declaredClass:'xide.manager.ServerActionBase',
            mixin:{
                serviceUrl:'%INDEX%?view=rpc',
                singleton:true
            }
        },
        {
            declaredClass:'xfile.manager.FileManager',
            mixin:{
                serviceUrl:'%INDEX%?view=rpc',
                singleton:true
            }
        },
        {
            declaredClass:'xide.manager.SettingsManager',
            mixin:{
                serviceUrl:'%INDEX%?view=rpc',
                singleton:true
            }
        },
        {
            declaredClass:'xide.manager.ResourceManager',
            mixin: {
                serviceUrl: '%INDEX%?view=rpc',
                singleton: true,
                resourceVariables: %RESOURCE_VARIABLES%
            }
        }
    ],
    FILES_STORE_URL:'%FILES_STORE_URL%',
    CODDE_MIRROR:'%CODDE_MIRROR_URL%',
    THEME_ROOT:'%APP_URL%/themes/',
    WEB_ROOT:'%APP_URL%',
    FILE_SERVICE:'%FILE_SERVICE%',
    FILE_SERVICE_FULL:'%FILE_SERVICE_FULL%',
    REPO_URL:'%REPO_URL%',
    FILES_STORE_SERVICE_CLASS:'XCOM_Directory_Service',
    RPC_PARAMS:{
        rpcUserField:'user',
        rpcUserValue:'%RPC_USER_VALUE%',
        rpcSignatureField:'sig',
        rpcSignatureToken:'%RPC_SIGNATURE_TOKEN%',
        rpcFixedParams:{

        }
    },
    ACTION_TOOLBAR_MODE:'self'
};
var xappPluginResources=%XAPP_PLUGIN_RESOURCES%;
/*
window.$zopim||(function(d,s){var z=$zopim=function(c){z._.push(c)},$=z.s=
d.createElement(s),e=d.getElementsByTagName(s)[0];z.set=function(o){z.set.
    _.push(o)};z._=[];z.set._=[];$.async=!0;$.setAttribute('charset','utf-8');
$.src='//v2.zopim.com/?2NTMTET7LTUMmledW9IT55fQkq6DeptG';z.t=+new Date;$.
type='text/javascript';e.parentNode.insertBefore($,e)})(document,'script');
*/