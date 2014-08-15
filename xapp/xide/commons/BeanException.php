<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * File exception class
 *
 * @package xFile
 * @class XApp_Bean_Exception
 * @author mc007 <support@xapp-studio.com>
 */
if(!class_exists('XApp_Bean_Exception')){

	class XApp_Bean_Exception extends ErrorException
    {
        /**
         * error exception class constructor directs instance
         * to error xapp error handling
         *
         * @param string $message excepts error message
         * @param int $code expects error code
         * @param int $severity expects severity flag
         */
        public function __construct($message, $code = 0, $severity = XAPP_ERROR_ERROR)
        {
            parent::__construct($message, $code, $severity);
            xapp_error($this);
        }
    }
}