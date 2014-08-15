<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package XApp\Service
 */

xapp_import('xapp.Commons.Mixins');


/***
 * Class XApp_Service provides a minimal of functions and exposes a managed class to a public
 * RPC interface
 */
class XApp_Service implements Xapp_Rpc_Interface_Callable
{


    /***
     * The name of the class as string or an instance. An instance does not need options of course.
     */
    const MANAGED_CLASS          = 'XAPP_SERVICE_MANAGED_CLASS';

    /***
     * The name of the class as string or an instance. An instance does not need options of course.
     */
    const MANAGED_CLASS_BASE_CLASSES          = 'XAPP_SERVICE_MANAGED_CLASS_BASE_CLASSES';

    /***
     * The options of the wrapped managed class
     */
    const MANAGED_CLASS_OPTIONS  = 'XAPP_SERVICE_MANAGED_CLASS_OPTIONS';

    /***
     * In case we have managed class, publish this options to
     */
    const PUBLISH_METHODS        = 'XAPP_SERVICE_PUBLISH_METHODS';




    /***
     * A instance to a logger
     */
    const LOGGER                 = 'XAPP_SERVICE_LOGGER';

    /*******************************************************************************/
    /*  XApp option satisfaction
    /*******************************************************************************/

    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::MANAGED_CLASS                 => array(XAPP_TYPE_OBJECT,XAPP_TYPE_STRING),
        self::MANAGED_CLASS_OPTIONS         => XAPP_TYPE_ARRAY,
        self::MANAGED_CLASS_BASE_CLASSES    => XAPP_TYPE_ARRAY,
        self::PUBLISH_METHODS               => XAPP_TYPE_ARRAY,
        self::LOGGER                        => XAPP_TYPE_OBJECT
    );
    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::MANAGED_CLASS                 => 0,
        self::MANAGED_CLASS_OPTIONS         => 0,
        self::MANAGED_CLASS_BASE_CLASSES    => 0,
        self::PUBLISH_METHODS               => 0,
        self::LOGGER                        => 0
    );
    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::MANAGED_CLASS                 => null,
        self::MANAGED_CLASS_OPTIONS         => null,
        self::MANAGED_CLASS_BASE_CLASSES    => null,
        self::PUBLISH_METHODS               => null,
        self::LOGGER                        => null
    );


    /**
     * class constructor
     * call parent constructor for class initialization
     *
     * @error 14601
     * @param null|array|object $options expects optional options
     */
    public function __construct($options = null)
    {
        //standard constructor
        xapp_set_options($options, $this);

        //now parse options
        $this->parseOptions(xapp_get_options($this));

        //now publish methods
        $this->publishMethods(xapp_get_options($this));

    }

    /***
     * The managed class instance
     */
    protected $_object;

    /***
     * Getter for _object
     * @return mixed
     */
    public function getObject(){
        return $this->_object;
    }

    /***
     * Create and wire the managed class instance
     * @param $options
     */
    private function parseOptions($options){

        if($this->_object == null && $options && xo_has(self::MANAGED_CLASS,$options)){

            //the class  
            $_managedClass = xo_get(self::MANAGED_CLASS,$options);

            //check its an instance already :
            if(is_object($_managedClass)){
                $this->_object = $_managedClass;
            }//its a string
            elseif(is_string($_managedClass) && class_exists($_managedClass)){

                $baseClasses = xo_has(self::MANAGED_CLASS_BASE_CLASSES,$options) ? xo_get(self::MANAGED_CLASS_BASE_CLASSES,$options) : null;

                $_ctrArgs = xo_has(self::MANAGED_CLASS_OPTIONS,$options) ? xo_get(self::MANAGED_CLASS_OPTIONS,$options) : array();
                /*
                if($_managedClass==='XIDE_NodeJS_Service_Manager'){
                    xapp_dump($baseClasses);
                    exit;
                }
                */
                //no additional base classes :
                if($baseClasses==null || !count($baseClasses)){
                    $this->_object = new $_managedClass($_ctrArgs);
                }else{
                    //mixin new base classes
                    xapp_import('xapp.Commons.ClassMixer');
                    $newClassName = "NEW_" .$_managedClass;
                    //error_log('create mixed class with : ' . json_encode($_ctrArgs));

                    //xapp_dump($_ctrArgs);

                    //exit;
                    XApp_ClassMixer::create_mixed_class($newClassName , $_managedClass, $baseClasses);


                    //$rclass = new ReflectionClass($newClassName);
                    //$methods = $rclass->getMethods();
                    //xapp_dump($methods);
                    //$t = new $newClassName($_ctrArgs);
                    //xapp_dump($t);
                   // exit;


                    //xapp_dump($_ctrArgs);
                    $this->_object = new $newClassName($_ctrArgs);
                    //xapp_dump($this->_object);
                    //error_log('did create new mixin class ' . $newClassName);
                    //exit;

                }

            }else{
                error_log('$_managedClass : '  . $_managedClass . ' doesnt exists');
            }

            //xapp_dump($this);
            if($this->_object){
                if(method_exists($this->_object,'init')){
                    $this->_object->init();
                }
            }
        }

    }

    /***
     * Create or wire the managed class instance
     * @param $options
     */
    private function publishMethods($options){


        if($this->_object != null && $options && xo_has(self::PUBLISH_METHODS,$options)){
            $methods = xo_get(self::PUBLISH_METHODS,$this);
            if(is_array($methods) && count($methods)){
                foreach($methods as $method){
                    //error_log('parsing publish methods in ' . __CLASS__ . ' method : ' . $method);
                }
            }

        }
    }

    /*******************************************************************************/
    /*  Constants
    /*******************************************************************************/

    /***
     * Fields of a service structure.
     */
    const XAPP_SERVICE_CLASS    = 'XAPP_SERVICE_CLASS';
    const XAPP_SERVICE_CONF     = 'XAPP_SERVICE_CONF';
    const XAPP_SERVICE_INSTANCE = 'XAPP_SERVICE_INSTANCE';

    /***
     * Xapp_Rpc_Interface_Callable Impl. Before the actual call is being invoked
     */
    public function onBeforeCall($function=null, $class=null, $params=null){

    }

    /***
     *Xapp_Rpc_Interface_Callable Impl. After the actual call
     */
    public function onAfterCall($function=null, $class=null, $params=null){

    }

    /***
     * @param int $code
     * @param $messages
     * @return array
     */
    static function toRPCError($code=1,$messages){
        $result = array();
        $result['error']['code']=$code;
        $result['error']['message']=$messages;
        return $result;
    }
    /***
     * @param array $imports
     * @param $className is in Java Import style : xapp.Directory.Service => XApp_Directory_Service
     * @param $configuration
     */
    public static function factory(
        $importName,
        $configuration=Array(),
        $baseClasses=null)
    {

        $className ='' .$importName;
        if(!class_exists($className)){
            $className = str_replace('xapp','XApp',$importName);
            $className = str_replace('.','_',$className);
            if(!class_exists($className)){
                xapp_import($importName);
            }
        }

        return array(
            self::XAPP_SERVICE_CLASS             =>$className,
            self::XAPP_SERVICE_CONF              =>$configuration,
            self::XAPP_SERVICE_INSTANCE          =>null
        );
    }
    /***
     * Another factory method to bypass xapp_import problems,
     * @param $className
     * @param null $configuration
     * @return mixed
     */
    public static function factoryEx(
        $className,
        $configuration=null)
    {
        if(is_string($className)){
            $reflection = new ReflectionClass($className);
            if($reflection->implementsInterface('Xapp_Singleton_Interface')){
                $instance = $className::instance($configuration);
            }else{
                $instance = new $className($configuration);
            }
        }elseif (is_object($className)){
            $instance = $className;
        }


        return array(
            self::XAPP_SERVICE_CLASS             =>$className,
            self::XAPP_SERVICE_CONF              =>xapp_get_options($instance),
            self::XAPP_SERVICE_INSTANCE          =>$instance
        );
    }

}