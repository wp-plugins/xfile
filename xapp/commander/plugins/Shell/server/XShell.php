<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
xapp_import('xapp.Path.Utils');

/***
 * Example server plugin.
 * @remarks
    - This class is running in the CMS context already!
    - A function's result will be wrapped automatically into the specified transport envelope, eg: JSON-RPC-2.0 or JSONP
    - Implementing Xapp_Rpc_Interface_Callable is just for demonstration
    -
 */

class XShell extends Xapp_Commander_Plugin implements Xapp_Rpc_Interface_Callable
{
    /***
     * @param $command
     * @param null $cwd current working directory in this format root://myfolder.
     * @return string
     */
    protected function runBash($command,$cwd=null){

	    $shell_result_final = "";
        $command = $command." 2>&1";

        if($cwd && file_exists($cwd)){
            chdir($cwd);
        }

	    /**
	     * try to get a system shell
	     */

        if(is_callable('system')) {

            //
            ob_start();

            @system($command);

            $shell_result_final = ob_get_contents();
            ob_end_clean();

            if(!empty($shell_result_final)) {
                return $shell_result_final;
            }
        }
        if(is_callable('shell_exec')){
            $shell_result_final = @shell_exec($command);
            if(!empty($shell_result_final)) {
                return $shell_result_final;
            }
        }
        if(is_callable('exec')) {

            @exec($command,$shell_result);


            if(!empty($shell_result)){
                foreach($shell_result as $shell_res_item) {
                    $shell_result_final .= $shell_res_item;
                }
            }
            if(!empty($shell_result_final)) {
                return $shell_result_final;
            }
        }
        if(is_callable('passthru')) {

            ob_start();

            @passthru($command);

            $shell_result_final = ob_get_contents();

            ob_end_clean();

            if(!empty($shell_result_final)){
                return $shell_result_final;
            }
        }

        if(is_callable('proc_open')) {

            $shell_descriptor_spec = array(

                0 => array("pipe", "r"),

                1 => array("pipe", "w"),

                2 => array("pipe", "w")
            );

            $shell_proc = @proc_open($command, $shell_descriptor_spec, $shell_pipes, getcwd(), array());

            if (is_resource($shell_proc)) {

                while($shell_in_proc_open = fgets($shell_pipes[1])) {

                    if(!empty($shell_in_proc_open)){
                        $shell_result_final .= $shell_in_proc_open;
                    }
                }
                while($s_se = fgets($shell_pipes[2])) {
                    if(!empty($s_se)) $shell_result_final .= $s_se;
                }
            }
            @proc_close($shell_proc);
            if(!empty($shell_result_final)){
                return $shell_result_final;
            }
        }
        //test popen
        if(is_callable('popen')){

            $shell_open = @popen($command, 'r');

            if($shell_open){
                while(!feof($shell_open)){
                    $shell_result_final .= fread($shell_open, 2096);
                }
                pclose($shell_open);
            }
            if(!empty($shell_result_final)) {
                return $shell_result_final;
            }
        }
        return "";
    }

    /***
     * @param $shellType
     * @param $cmd
     * @param null $cwd
     * @return string
     */
    public function run($shellType,$cmd,$cwd=null){


	    //determine real fs path
	    if($this->directoryService){

		    $mount = XApp_Path_Utils::getMount($cwd);
		    $rel = XApp_Path_Utils::getRelativePart($cwd);
		    $vfs = null;
		    if($mount && $rel){
			    $vfs = $this->directoryService->getFileSystem($mount);
			    if($vfs){
			        $fullPath = $vfs->toRealPath($mount . DIRECTORY_SEPARATOR . $rel);
				    if(file_exists($fullPath)){
					    $cwd = $fullPath;
				    }
			    }
		    }

	    }

	    $code = base64_decode($cmd);
        if($shellType==='sh'){
            $code = escapeshellcmd($code);
            $res = $this->runBash($code,$cwd);
            return $res;
        }
        return 'not implemented';
    }

    /***
     * Invoked by the plugin manager, before 'load'!. time to register our subscriptions
     * @return int|void
     */
    public function setup(){
        /***
         * Listen to file changes
         */
        /*
        xcom_subscribe(XC_OPERATION_WRITE_STR,function($mixed)
        {
            if (preg_match(XSVN::MATCH_PATTERN, $mixed[XAPP_EVENT_KEY_PATH])) {
                XSVN::instance()->onSavingSVNFile($mixed);
            }
        });
        */
    }

    /***
     * Xapp_Rpc_Interface_Callable Impl. Before the actual call is being invoked
     */
    public function onBeforeCall($function=null, $class=null, $params=null){}
    /***
     *Xapp_Rpc_Interface_Callable Impl. After the actual call
     */
    public function onAfterCall($function=null, $class=null, $params=null){}

    /***
     * Invoked by the plugin manager, time to pull dependencies but we don't !
     * @return int|void
     */
    public function load(){}

    /**
     * Xapp_Singleton interface impl. Its actually done in the base class,...
     *
     * static singleton method to create static instance of driver with optional third parameter
     * xapp options array or object
     *
     * @error 15501
     * @param null|mixed $options expects optional xapp option array or object
     * @return XSVN
     */
    public static function instance($options = null)
    {
        if(self::$_instance === null)
        {
            self::$_instance = new self($options);
        }
        return self::$_instance;
    }

}