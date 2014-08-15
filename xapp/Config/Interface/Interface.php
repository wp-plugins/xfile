<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('XAPP') || require_once(dirname(__FILE__) . '/../../Core/core.php');

/**
 * Config interface
 *
 * @package Config
 * @author Frank Mueller <set@cooki.me>
 */
interface Xapp_Config_Interface
{
    /**
     * loads config values from string, file pointer or elsewhere and
     * returns an array/object of config values
     *
     * @param mixed $config expects config string or config file pointer
     * @return mixed|array
     */
    static function load($config);
}