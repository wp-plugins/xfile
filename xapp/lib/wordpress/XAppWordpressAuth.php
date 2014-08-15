<?php
/**
 * Created by JetBrains PhpStorm.
 * @package Wordpress
 * Date: 5/27/13
 * Time: 3:33 PM
 * To change this template use File | Settings | File Templates.
 */

class XAppWordpressAuth {

    var $logger=null;

    public function  isLoggedIn(){
        return true;
    }

    /***
     * @param $action
     * @param string $component
     * @return bool
     */
    public static function getToken(){
        return wp_create_nonce( 'nounce' );
    }

    /***
     * @param $action
     * @param string $component
     * @return bool
     */
    public static function getUserName(){
        global $current_user;
        get_currentuserinfo();
        return $current_user->user_login;
    }

    public function log($message,$stdError=true){
        if($this->logger){
            $this->logger->log($message);
        }
        if($stdError){
            error_log('Error : '.$message);
        }
    }


    function loginUserEx($username, $password)
    {
        $userId = -1;

        $user = wp_authenticate($username, $password);
        if (is_wp_error($user)) {
            return -1;
        } else {
            return 1;
        }
    }

    function loginUser($username, $password)
    {
        //do the auth
        $userId = -1;
        try{
            $userId= $this->loginUserEx($username,$password);
        }catch (Exception $e){
            $this->log('XAppWordpressAuth::loginUserfailed ' . $e->getMessage());
            return -1;
        }
        return $userId;

    }

}