<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author Luis Ramos
 * @author Guenter Baumgart
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package XApp\xide\Controller
 */

xapp_import("xapp.Commons.Entity");
xapp_import('xapp.Utils.Strings');
xapp_import('xapp.xide.Base.Scoped');
/***
 * Class XIDE_Manager
 */
class XIDE_Manager extends XIDE_Scoped
{

    /**
     * class constructor
     * call parent constructor for class initialization
     *
     * @error 14601
     * @param null|array|object $options expects optional options
     */
    /*
    public function __construct($options = null)
    {
        //standard constructor
        parent::__construct($options);
        xapp_set_options($options, $this);
        xapp_dumpObject($this,'$options');
    }
    */

    public $logger=null;


    /**
     * @param $message
     * @param string $prefix
     * @param bool $stdError
     * @return null
     */
    public function log($message,$prefix='',$stdError=true){

        if(function_exists('xp_log')){
            xp_log(__CLASS__ . ' : ' . $message);
        }

        if($stdError){
            error_log(__CLASS__ . ' : ' .$message);
        }
        return null;
    }

}


?>