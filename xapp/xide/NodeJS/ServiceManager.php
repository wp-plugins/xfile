<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author Luis Ramos
 * @author Guenter Baumgart
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package XApp\xide\NodeJS
 */
xapp_import("xapp.Commons.Entity");
xapp_import('xapp.Utils.Strings');
xapp_import('xapp.Utils.Shell');
xapp_import('xapp.xide.Base.Manager');

/***
 * Class XIDE_NodeJS_Service_Manager provides a useful set of NodeJS related functions like :
 * -start, stop, kill and also debug (uses and starts a 'node-inspector', needs Chrome on the clients side)!
 * -enumerate running services, as well its child processes ('spawned')
 */
class XIDE_NodeJS_Service_Manager extends XIDE_Manager{

    /////////////////////////////////////////////////////////////////////////////////////////
    //
    //  Hook/Event Keys
    //
    ////////////////////////////////////////////////////////////////////////////////////////

    const EVENT_ON_NODE_META_CREATED        = "NODEJS_EVENT_ON_NODE_META_CREATED";  //hook when service meta data has been created : best place to add/change
    const EVENT_ON_NODE_ADD                 = "NODEJS_EVENT_ON_NODE_ADD";           //hook to remove a certain node in the service list
    const EVENT_ON_NODE_ADDED               = "NODEJS_EVENT_ON_NODE_ADDED";         //event when a node has beed added to final service list

    /////////////////////////////////////////////////////////////////////////////////////////
    //
    //  Constants
    //
    ////////////////////////////////////////////////////////////////////////////////////////

    //a set of keys to describe a NodeJS service status
    const SERVICE_STATUS_OFFLINE            = "offline";
    const SERVICE_STATUS_ONLINE             = "online";
    const SERVICE_STATUS_TIMEOUT            = "timeout";
    const SERVICE_STATUS_UNKNOWN            = "unknown";
    //@TODO : determine other conditions like memory warnings,...

    //a set of keys to be used when gathering the NodeJS service's
    const FIELD_STATUS                      = 'status';
    const FIELD_INFO                        = 'info';
    const FIELD_CLIENTS                     = 'clients';
    const FIELD_CHILD_PROCESSES             = 'childs'; //list child processes
    const FIELD_OPTIONS                     = 'options'; //get command line options of a service


    /////////////////////////////////////////////////////////////////////////////////////////
    //
    //  Options
    //
    ////////////////////////////////////////////////////////////////////////////////////////

    //Debug options
    const DEBUG_TCP_PORT                    = "XAPP_NODEJS_DEBUG_TCP_PORT";
    const DEBUG_TCP_HOST                    = "XAPP_NODEJS_DEBUG_TCP_HOST";
    const DEBUG_PORT                        = "XAPP_NODEJS_DEBUG_PORT";
    const DEBUGGER_PATH                     = "XAPP_NODEJS_DEBUGGER_PATH";

    //Standard options
    const WORKING_PATH                      = "XAPP_NODEJS_WORKING_PATH";
    const EMITS                             = "XAPP_EMITS"; //disable or enable hooks
    const REWRITE_HOST                      = "XAPP_NODEJS_HOST_REWRITE"; //rewrite server host to this server IP
	const FORCE_HOST                        = "XAPP_NODEJS_FORCE_HOST"; //rewrite server host to this server IP


    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::DEBUG_TCP_PORT            => XAPP_TYPE_INTEGER,
        self::DEBUG_TCP_HOST            => XAPP_TYPE_STRING,
        self::DEBUG_PORT                => XAPP_TYPE_INTEGER,
        self::DEBUGGER_PATH             => XAPP_TYPE_STRING,
        self::WORKING_PATH              => XAPP_TYPE_STRING,
        self::EMITS                     => XAPP_TYPE_BOOL,
        self::REWRITE_HOST              => XAPP_TYPE_BOOL,
	    self::FORCE_HOST                => XAPP_TYPE_STRING
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::EMITS                     => 0,
        self::DEBUG_TCP_PORT            => 0,
        self::DEBUG_TCP_HOST            => 0,
        self::DEBUG_PORT                => 0,
        self::DEBUG_TCP_PORT            => 0,
        self::WORKING_PATH              => 0,
        self::REWRITE_HOST              => 0,
	    self::FORCE_HOST                => 0
    );

    /**
     * options default value array containing all class option default values
     * @var array
     */
    public $options = array
    (
        self::DEBUG_TCP_PORT            => 9090,
        self::DEBUG_TCP_HOST            => '0.0.0.0',
        self::DEBUG_PORT                => 5858,
        self::DEBUGGER_PATH             => 'nxappmain/debugger.js',
        self::WORKING_PATH              => 'Utils/nodejs/',
        self::EMITS                     => true,
        self::REWRITE_HOST              => true,
	    self::FORCE_HOST                => null

    );

    /////////////////////////////////////////////////////////////////////////////////////////
    //
    //  Public API & main entries, expects this class has fully valid options.
    //  This class is being mixed also with the XApp_VariableMixin which comes
    //  with its own set of options, needed to have a full service description map
    //  as well a set of resolvable variables within the service description map.
    //
    ////////////////////////////////////////////////////////////////////////////////////////

    private function _serviceByName($services,$name){
        foreach($services as $service){
            //xapp_cdump('s',$name);
            if($service->name===$name){
                return $service;
            }
        }
        return null;
    }

    protected function stopService($serviceResource){

        if(property_exists($serviceResource,'ps_tree')&&
            count($serviceResource->ps_tree)>0){
            $firstProcess =$serviceResource->ps_tree[0];
            $pid =$firstProcess['pid'];
            $args=array();
            $cmd= "kill ".$pid;
            XApp_Shell_Utils::run($cmd,$args,null,Array(
                XApp_Shell_Utils::OPTION_BACKGROUND => false
            ));
            return true;
        }

        return false;

    }

	protected function fixWindowsPath($path){
		if($this->isWindows()){
			return str_replace('/','\\',$path);
		}

		return $path;
	}

	protected function isWindows(){
		$os = PHP_OS;
		switch($os)
		{
			case "WINNT": {
				return true;
			}
		}

		return false;
	}

    protected function startService($serviceResource){

        $workingPath = $serviceResource->{XAPP_RESOURCE_PATH_ABSOLUTE};
        $nodeapp = $this->fixWindowsPath($serviceResource->main);

	    if ($nodeapp!='') {
            $args=array();
            $cmd= "node ".$nodeapp;
            XApp_Shell_Utils::run($cmd,$args,null,Array(
                XApp_Shell_Utils::OPTION_WORKING_PATH => $workingPath,
                XApp_Shell_Utils::OPTION_BACKGROUND => true
            ));
            return true;
        } else
        {
            return false;
        }
    }

    public function stop($services){

        if(is_string($services)){
            $services=array($services);
        }
        $allServices=$this->ls(false);

        for ($i = 0; $i < count($services); ++$i) {
            $service = $this->_serviceByName($allServices,$services[$i]);
            if($service){
                $this->stopService($service);
            }

        }

    }

    public function start($services){

        if(is_string($services)){
            $services=array($services);
        }
        $allServices=$this->ls(false);
        for ($i = 0; $i < count($services); ++$i) {
            $service = $this->_serviceByName($allServices,$services[$i]);
            if($service){
                /*
                $resolved = $this->getVariableDelegate()->resolveAbsolute(xapp_property_get($service,'host'));
                if($resolved && strlen($resolved)){
                    $service->host = $resolved;
                }
                */
                /*error_log('start at ' . $service->host);*/
                $this->startService($service);
            }
        }
        return true;
    }

    /***
     * Method to enumerate all available NodeJS services. This function also
     * checks the status of the service as well it does gather additional information about
     * the service's child processes
     * @return array|null
     */
    public function ls($removePath=true){

        $this->prepareResources();

        $type       = xo_get(XApp_Variable_Mixin::RESOURCES_TYPE,$this);
        $services   = $this->getVariableDelegate()->getResourcesByType($type);
        $emits      = xo_get(self::EMITS,$this)===true;

        $result     = array();//final list

        if($services!=null && count($services)){
            foreach($services as $service){

                $this->completeServiceResource($service);

                if(xo_get(self::REWRITE_HOST,$this) && property_exists($service,'host')){
                    $host= gethostname();
                    $resolved= gethostbyname($host);
	                if(xo_get(self::FORCE_HOST,$this) && strlen(xo_get(self::FORCE_HOST,$this))>0){
		                $resolved = xo_get(self::FORCE_HOST,$this);
	                }
                    if($resolved && strlen($resolved)){
                        $service->host = $resolved;
                    }
                }

                if($emits && Xapp_Hook::trigger(self::EVENT_ON_NODE_ADD,array('item'=>$service))===false){//skip if wanted
                    continue;
                }else{
                    $result[]=$service;
                }

                if($emits){//tell everyone
                    Xapp_Hook::trigger(self::EVENT_ON_NODE_ADDED,array('item'=>$service));
                }

	            if($removePath && property_exists($service,'pathResolved')){
		           $service->pathResolved='';
	            }
            }
        }
        return $services;
    }

    /**
     * class constructor
     * call parent constructor for class initialization
     *
     * @error 14601
     * @param null|array|object $options expects optional options
     */
    function __construct($options = null)
    {
        parent::__construct($options);
        //standard constructor
        xapp_set_options($options, $this);
    }

    /////////////////////////////////////////////////////////////////////////////////////////
    //
    //  Internals
    //
    ////////////////////////////////////////////////////////////////////////////////////////

    /**
     * PHPStorm warning/error display fix because this class is mixed with the generic class Variable_Mixin
     * @return XApp_Variable_Mixin
     */
    private function getVariableDelegate(){
        return $this;
    }

    /***
     * Prepare all service descriptors.
     */
    private function prepareResources(){
        $this->getVariableDelegate()->initVariables();//done in XApp_Variable_Mixin
        $this->getVariableDelegate()->resolveResources();//done in XApp_Variable_Mixin
    }

    /**
     *
     * Runs a node application with the --jhelp argument. A standard node application
     * should return a number of command line options on --help. This is usually bad for parsing and
     * so the node application should return a JSON string on --jhelp.
     *
     * @param $serviceResource
     * @return bool|string
     *
     * @TODO : what about npm --help ?
     */
    private function getServiceOptions($serviceResource,$helpArg='--jhelp'){

        $workingPath = $serviceResource->{XAPP_RESOURCE_PATH_ABSOLUTE};
        $nodeapp = $serviceResource->main;
        if ($nodeapp!='') {

            $args=array();
            $cmd= "node ".$nodeapp;
            $args[] =  $helpArg;

            $result = XApp_Shell_Utils::run($cmd,$args,null,Array(
                XApp_Shell_Utils::OPTION_WORKING_PATH => $workingPath,
                XApp_Shell_Utils::OPTION_BACKGROUND => false
            ));

            //try to decode
            $dSerialized=json_decode($result,true);
            if($dSerialized!==false && is_array($dSerialized)){
                return $dSerialized;
            }

            return $result;
        } else
        {
            return false;
        }
    }

    /**
     *
     * Runs a node application with the --info argument.
     * @param $serviceResource
     * @return bool|string
     *
     * @TODO : what about npm --help ?
     */
    private function getServiceInfo($serviceResource,$helpArg='--info'){

        $workingPath = $serviceResource->{XAPP_RESOURCE_PATH_ABSOLUTE};
        if(property_exists($serviceResource,'main')){
            $nodeapp = $this->fixWindowsPath($serviceResource->main);
            if ($nodeapp!='') {

                $args=array();
                $cmd= "node ".$nodeapp;
                $args[] =  $helpArg;


                $result = XApp_Shell_Utils::run($cmd,$args,null,Array(
                    XApp_Shell_Utils::OPTION_WORKING_PATH => $workingPath,
                    XApp_Shell_Utils::OPTION_BACKGROUND => false
                ));

	            //error_log('running service info for ' . $cmd . ' returns : ' . json_encode($result));

                //try to decode
                $dSerialized=json_decode($result,true);
                if($dSerialized!==false && is_array($dSerialized)){
                    if(xo_get(self::REWRITE_HOST,$this) && $dSerialized['host'])
                    {
                            $host= gethostname();
                            $resolved= gethostbyname($host);
	                        if(xo_get(self::FORCE_HOST,$this) && strlen(xo_get(self::FORCE_HOST,$this))>0){
			                    $resolved = xo_get(self::FORCE_HOST,$this);
		                    }
                            if($resolved && strlen($resolved)){
                                $dSerialized['host'] = 'http://'.$resolved;
                            }

                    }
                    return $dSerialized;
                }else{
                    xapp_clog('couldn deserialize ' . $result);
                }



                return $result;
            } else
            {
                return false;
            }
        }else{
            //xapp_cdump('service',$serviceResource);
        }
        return false;
    }

    /***
     * CompleteServiceResource evaluates and completes a NodeJS service configuration with
     * additional fields as the status or available command line options.
     * @param $resource
     * @param $fields : evaluate fields of interest
     */
    private function completeServiceResource($resource,$fields=array()){


        $emits              = xo_get(self::EMITS,$this)===true;

        // Defaults
        if (!isset($fields[self::FIELD_STATUS]))$fields[self::FIELD_STATUS]=true;
        if (!isset($fields[self::FIELD_INFO]))$fields[self::FIELD_INFO]=true;
        if (!isset($fields[self::FIELD_CLIENTS])) $fields[self::FIELD_CLIENTS]=true;
        if (!isset($fields[self::FIELD_CHILD_PROCESSES])) $fields[self::FIELD_CHILD_PROCESSES]=false;
        if (!isset($fields[self::FIELD_OPTIONS])) $fields[self::FIELD_OPTIONS]=false;

        $resource->clients  = 0;
        $resource->status   = self::SERVICE_STATUS_UNKNOWN;//default to unknown



        ///$this->resolveAbsolute(xapp_property_get($resource,XAPP_RESOURCE_PATH));
        //check status
        if(
            $fields[self::FIELD_STATUS]===true  &&
            property_exists($resource,'port')   &&
            property_exists($resource,'host')){
            if(xo_get(self::REWRITE_HOST,$this) && property_exists($resource,'host')){
                //$resolved = '192.168.1.37'; //$this->getVariableDelegate()->resolveAbsolute(xapp_property_get($service,'SERVER_IP'));
                $host= gethostname();
                $resolved= gethostbyname($host);

	            if(xo_get(self::FORCE_HOST,$this) && strlen(xo_get(self::FORCE_HOST,$this))>0){
		            //error_log('force host : ' . xo_get(self::FORCE_HOST,$this));
		            $resolved = xo_get(self::FORCE_HOST,$this);
	            }

	            if($resolved && strlen($resolved)){
                    $resource->host = $resolved;
                }
            }

            if(self::_isTCPListening($resource->host,$resource->port)){
                $resource->status = self::SERVICE_STATUS_ONLINE;
            }else{
                $resource->status = self::SERVICE_STATUS_OFFLINE;
            }
        }

        //check info
        if($fields[self::FIELD_INFO]===true){
            $info = $this->getServiceInfo($resource);
            if($info){
                $resource->info = $info;//can be string or object
            }
        }

        //check options
        if(
            $fields[self::FIELD_OPTIONS]===true &&
            property_exists($resource,'main')&&
            property_exists($resource,'has')&&
            property_exists($resource->has,'options'))
        {
            $options = $this->getServiceOptions($resource,$resource->has->options);
            if($options){
                $resource->options = $options;//can be string or object
            }
        }

        //list child processes
        if (
            $fields[self::FIELD_CHILD_PROCESSES]===true &&      //only when of interest
            $resource->status == self::SERVICE_STATUS_ONLINE && //must be online
            isset($resource->main) )                            //must a have valid main path
        {
            //get processes for command "node", filtered by "resource->main"
            $ps_list = XApp_Shell_Utils::getProcesses("node",array(
                XApp_Shell_Utils::OPTION_FILTER_PROCESSES => $resource->main,
                XApp_Shell_Utils::OPTION_RETURN_PROCESSES_TREE => true
            ));
            $resource->clients = count($ps_list);
            $resource->ps_tree = $ps_list;
        }
        //tell everyone
        if($emits)  Xapp_Hook::trigger(self::EVENT_ON_NODE_META_CREATED,array('item'=>$resource));
    }


    /////////////////////////////////////////////////////////////////////////////////////////
    //
    //  Utils
    //  @TODO : find a better place to dance!
    //
    ////////////////////////////////////////////////////////////////////////////////////////

    /***
     * Check if it's a TCP server ready on host:port
     *
     * @param $host
     * @param $port
     * @return bool
     *
     * @TODO : Check avaibility of @fsockopen on platforms and hardened systems
     */
    private static function _isTCPListening($host,$port) {
        //error_log('checking on ' . $host. ' : ' . $port);
	    $fp = @fsockopen($host, $port, $errno, $errstr, 30);
        if (!$fp) {
            return false;
        } else {
            fclose($fp);
            return true;
        }
    }
}


?>