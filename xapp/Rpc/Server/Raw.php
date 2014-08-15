<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('XAPP') || require_once(dirname(__FILE__) . '/../../Core/core.php');

xapp_import('xapp.Rpc.Server.Exception');
xapp_import('xapp.Rpc.Fault');
xapp_import('xapp.Rpc.Server.Json');
xapp_import('xapp.Rpc.Smd.Jsonp');
xapp_import('xapp.Rpc.Server.Jsonp');
xapp_import('xapp.Rpc.Response.Json');
xapp_import('xapp.Rpc.Request.Json');


/**
 * Rpc server jsonp class
 *
 * @package Rpc
 * @subpackage Rpc_Server
 * @class Xapp_Rpc_Server_Raw
 * @error 147
 * @author Guenter Baumgart <support@xapp-studio.com>
 */

class Xapp_Rpc_Server_Raw extends Xapp_Rpc_Server_Jsonp
{
    /**
     * executing requested service if found passing result from service invoking to response
     * or pass compile smd map to response if no service was called. if a callback was supplied
     * will wrap result into callback function
     *
     * @error 14706
     * @return void
     */
    protected function execute()
    {
        $get = $this->request()->getGet();

        if($this->service() !== null)
        {
            $result = $this->invoke($this->getFunction(), $this->getClass(), $this->_params);

            $this->response()->skipHeader();

            if($this->callback() !== null && array_key_exists($this->callback(), $get))
            {

            }else{
                $result = $this->response()->encode($result);
            }
            $this->response()->body($result);
        }else{
            $this->response()->body($this->smd()->compile());
        }
    }
}