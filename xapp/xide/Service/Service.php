<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package XApp\xide\Directory
 */

xapp_import('xapp.Service.Service');
xapp_import('xapp.Directory.Service');

/***
 * Class XIDE_Service extends the standard service
 */
class XIDE_Service extends XApp_Service
{
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
        //standard constructor
        xapp_set_options($options, $this);
    }

    /***
     * Xapp_Rpc_Interface_Callable Impl. Before the actual call is being invoked.
     *
     */
    public function onBeforeCall($function=null, $class=null, $params=null){

    }

}