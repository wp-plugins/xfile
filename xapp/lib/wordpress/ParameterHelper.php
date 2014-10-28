<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

/***
 * Class XApp_Resource_Renderer
 *
 *
 */
class XApp_Wordpress_Parameter_Helper
{


    protected static $panelOptionKeys = array(
        'ALLOW_NEW_TABS', 'ALLOW_INFO_VIEW', 'ALLOW_BREADCRUMBS',
 	    'ALLOW_LOG_VIEW', 'ALLOW_CONTEXT_MENU', 'ALLOW_SOURCE_SELECTOR',
 	    'ALLOW_LAYOUT_SELECTOR','ALLOW_COLUMN_RESIZE','ALLOW_COLUMN_REORDER','ALLOW_COLUMN_HIDE'
 	);

    public static  function getComponentParameters(){
        global $xcommanderOptions;
        return $xcommanderOptions;
    }
    /**
     * @param $jParams
     * @param $key
     * @param $result
     */
    public static function toGatewayOption($params,$key,&$result){
        if(array_key_exists($key,$params)){
            $list = explode(',',$params[$key]);
            if($list &&  is_array($list) && count($list)){
                $result[$key]=$list;
            }
        }else{
            $result[$key]=null;
        }
    }

    /**
     * @param $jParams
     * @param $key
     * @param $result
     */
    public static function getGatewayOption($params,$key){
        if(is_string($params) && strlen($params)){

            $list = explode(',',$params);
            if($list &&  is_array($list) && count($list)){
                return $list;
            }
        }

        return null;
    }
    public static function isOptionEnabled($searchArray,$option){

        foreach($searchArray as $key =>$value){
            if( intval($value)===intval($option)){
                return true;
            }
        }
        return false;
    }
    public static function parsePanelOptions($prefix='FILE_PANEL_OPTIONS_',$name,$jParams,&$result){

        $result[$prefix.$name]=array();
        $result[$prefix.$name]['LAYOUT']=2;
        $result[$prefix.$name]['AUTO_OPEN']=true;
        if($jParams->get($prefix.'LAYOUT_'.$name)!==null){
            $result[$prefix.$name]['LAYOUT']=$jParams->get($prefix.'LAYOUT_'.$name);
        }

        if($jParams->get($prefix.'AUTO_OPEN_'.$name)!=null){
            $result[$prefix.$name]['AUTO_OPEN']=$jParams->get($prefix.'AUTO_OPEN_'.$name);
        }


    }
    public static  function toXFileConfig($params){

        $defaultPanelOption = true;

        $result['LAYOUT_PRESET']=1;

	    $result['NEEDS_TOKEN']=1;

        $result['PANEL_OPTIONS'] = array_fill_keys(self::$panelOptionKeys, $defaultPanelOption);

        $result['PANEL_OPTIONS']['ALLOW_MULTI_TAB']=false;

	    $XCOM__DEFAULT_ACTIONS = array(
            XC_OPERATION_NONE=>0,
            XC_OPERATION_EDIT=>1,
            XC_OPERATION_COPY=>1,
            XC_OPERATION_MOVE=>1,
            XC_OPERATION_INFO=>1,
            XC_OPERATION_DOWNLOAD=>1,
            XC_OPERATION_COMPRESS=>1,
            XC_OPERATION_DELETE=>1,
            XC_OPERATION_RENAME=>1,
            XC_OPERATION_DND=>1,
            XC_OPERATION_COPY_PASTE=>1,
            XC_OPERATION_OPEN=>1,
            XC_OPERATION_RELOAD=>1,
            XC_OPERATION_NEW_FILE=>1,
            XC_OPERATION_NEW_DIRECTORY=>1,
	        XC_OPERATION_UPLOAD=>1,
	        XC_OPERATION_READ=>1,
	        XC_OPERATION_WRITE=>1,
	        XC_OPERATION_PLUGINS=>1,
	        XC_OPERATION_ADD_MOUNT=>1,
	        XC_OPERATION_REMOVE_MOUNT=>1,
	        XC_OPERATION_EDIT_MOUNT=>1

        );
        $result['ALLOWED_ACTIONS']=$XCOM__DEFAULT_ACTIONS;
        $result['FILE_PANEL_OPTIONS_LEFT']['LAYOUT']=2;
        $result['FILE_PANEL_OPTIONS_LEFT']['AUTO_OPEN']=true;

        $result['FILE_PANEL_OPTIONS_MAIN']['LAYOUT']=2;
        $result['FILE_PANEL_OPTIONS_MAIN']['AUTO_OPEN']=true;

        $result['FILE_PANEL_OPTIONS_RIGHT']['LAYOUT']=2;
        $result['FILE_PANEL_OPTIONS_RIGHT']['AUTO_OPEN']=true;

	    if(xapp_array_get($params,'LAYOUTPRESET')){
            $result['LAYOUT_PRESET']=xapp_array_get($params,'LAYOUTPRESET');
        }

	    if(xapp_array_get($params,'XAPP-DEFAULT-ACTIONS')){
            $actions = xapp_array_get($params,'XAPP-DEFAULT-ACTIONS');
            foreach($XCOM__DEFAULT_ACTIONS as $key =>$value){
                $result['ALLOWED_ACTIONS'][$key] = self::isOptionEnabled($actions,$key);
            }
        }
	    $result['START_PATH'] = xapp_array_get($params,'PATH');
        if(xapp_array_get($params,'JQTHEME')){
            $result['JQTHEME'] = xapp_array_get($params,'JQTHEME');
        }
        if(xapp_array_get($params,'PANELOPTIONS')){
            $pOptions = xapp_array_get($params,'PANELOPTIONS');
            foreach (self::$panelOptionKeys as $i => $key) {
                $result['PANEL_OPTIONS'][$key] = !empty($pOptions[$i]);
            }
        }
        $result['UPLOADEXTENSIONS']='js,css,less,bmp,csv,doc,gif,ico,jpg,jpeg,odg,odp,ods,odt,pdf,png,ppt,swf,txt,xcf,xls,BMP,CSV,DOC,GIF,ICO,JPG,JPEG,ODG,ODP,ODS,ODT,PDF,PNG,PPT,SWF,TXT,XCF,XLS';
        if(xapp_array_get($params,'UPLOADEXTENSIONS')){
            $result['XAPP_UPLOAD_EXTENSIONS']=xapp_array_get($params,'UPLOADEXTENSIONS');
        }
        self::toGatewayOption($params,Xapp_Rpc_Gateway::ALLOW_IP,$result);
        return $result;
    }

}
