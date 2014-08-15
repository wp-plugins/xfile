<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package XApp\xcf\Driver
 */

xapp_import('xapp.xide.Service.Service');

/***
 * Class XIDE_Log_Service extends the standard service
 * @link : http://192.168.1.37:81/x4mm/Code/trunk/xide-php/xapp/xcf/index.php?debug=true&view=smdCall&service=XIDE_Log_Service.ls&callback=asdf
 */
class XIDE_Log_Service extends XIDE_Service implements Xapp_Singleton_Interface, Xapp_Rpc_Interface_Callable
{
	public static $_instance=null;

    /***
     * Returns all registered services
     * @link http://192.168.1.37:81/x4mm/Code/trunk/xide-php/xapp/xcf/index.php?debug=true&view=smdCall&service=XIDE_Log_Service.ls&callback=asd
     * @return mixed
     */
    public function ls(){
        return $this->getObject()->ls();
    }
    /**
     * class constructor
     * call parent constructor for class initialization
     *
     * @error 14601
     * @param null|array|object $options expects optional options
     */
    public function __construct($options = null)
    {
        parent::__construct($options);
        xapp_set_options($options, $this);
    }


    /**
     * Xapp_Singleton interface impl.
     *
     * static singleton method to create static instance of driver with optional third parameter
     * xapp options array or object
     *
     * @error 15501
     * @param null|mixed $options expects optional xapp option array or object
     * @return XCF_Driver_Service
     */
    public static function instance($options = null)
    {

        if(self::$_instance === null)
        {
            self::$_instance = new self($options);

        }
        return self::$_instance;
    }

    ////////////////////////////////////////////////////////////////////////
    //
    //  Xapp_Rpc_Interface_Callable impl.
    //
    ////////////////////////////////////////////////////////////////////////

    /**
     * Method that will be called before the actual requested method is called.
     * Before any call
     * @return boolean
     */
    public function onBeforeCall($function=null, $class=null, $params=null){}


    /**
     * method that will be called after the requested method has been invoked
     *
     * @return boolean
     */
    public function onAfterCall($function=null, $class=null, $params=null){

    }
}