<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author Guenter Baumgart
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package XApp\xide\Logging
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
class XIDE_Log_Manager extends XIDE_Manager{

    /////////////////////////////////////////////////////////////////////////////////////////
    //
    //  Hook/Event Keys
    //
    ////////////////////////////////////////////////////////////////////////////////////////

    /////////////////////////////////////////////////////////////////////////////////////////
    //
    //  Constants
    //
    ////////////////////////////////////////////////////////////////////////////////////////

    /////////////////////////////////////////////////////////////////////////////////////////
    //
    //  Options
    //
    ////////////////////////////////////////////////////////////////////////////////////////

    //Standard options
    const LOG_PATH                          = "XAPP_NODE_JS_LOGGING_PATH";
    const EMITS                             = "XAPP_EMITS"; //disable or enable hooks

    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::LOG_PATH              => XAPP_TYPE_STRING
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::LOG_PATH              => 0
    );

    /**
     * options default value array containing all class option default values
     * @var array
     */
    public $options = array
    (
       self::LOG_PATH              => 'logs/all.log'
    );

    public function ls(){

	    $path       = realpath(xo_get(self::LOG_PATH,$this));
	    if(!file_exists($path)){
		    return '{}';
	    }
	    $result = array();
	    $handle = fopen($path, "r");
	    if ($handle) {
		    while (($line = fgets($handle)) !== false) {
			    $result[]=json_decode($line);
		    }
	    } else {
		    // error opening the file.
	    }
	    fclose($handle);
	    /*
	    $raw = file_get_contents($path);

	    $logList = preg_split('/\r\n|\r|\n/', $raw,-1,PREG_SPLIT_NO_EMPTY);//winston logger doesn't write valid json
	    $result = array();
	    foreach($logList as $item){
		    $result[]=json_decode($item);
	    }
	    */
	    return $result;
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

}


?>