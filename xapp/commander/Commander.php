<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

/***
 * Class XApp_App_Renderer
 */
class XApp_App_Commander extends XApp_App_Renderer{


    public static function render(){


    }
    public static function loadDependencies(){

        include_once(XAPP_BASEDIR . '/commander/defines.php');
        /*include_once(XAPP_BASEDIR . '/commander/XApp_Session.php');*/
        include_once(XAPP_BASEDIR . '/commander/Bootstrap.php');
        /*require_once(XAPP_BASEDIR . '/connect/utils/Debugging.php');*/

        require_once(XAPP_BASEDIR. '/Log/Exception/Exception.php');
        require_once(XAPP_BASEDIR. '/Log/Interface/Interface.php');
        require_once(XAPP_BASEDIR. '/Log/Log.php');
        require_once(XAPP_BASEDIR. '/Log/Writer.php');
        require_once(XAPP_BASEDIR. '/Log/Writer/File.php');
        require_once(XAPP_BASEDIR. '/Store/StoreBase.php');
        require_once(XAPP_BASEDIR. '/Store/Store.php');
        require_once(XAPP_BASEDIR. '/Store/Interface/Interface.php');

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
        xapp_set_options($options, $this);
    }

}
