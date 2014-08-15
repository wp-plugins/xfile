<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('XAPP') || require_once(dirname(__FILE__) . '/../../Core/core.php');

/**
 * Rpc callable interface
 *
 * @package Rpc
 * @author Frank Mueller <support@xapp-studio.com>
 */
interface Xapp_Rpc_Interface_Callable
{
    /**
     * method that will be called before the actual requested method is called
     *
     * @return boolean
     */
    public function onBeforeCall($function=null, $class=null, $params=null);


    /**
     * method that will be called after the requested method has been invoked
     *
     * @return boolean
     */
    public function onAfterCall($function=null, $class=null, $params=null);
}