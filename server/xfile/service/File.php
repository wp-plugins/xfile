<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

include_once(dirname(__FILE__) . '/FileException.php');
include_once(dirname(__FILE__) . '/Find.php');

xapp_import('xapp.File.Utils');
xapp_import('xapp.Directory.Utils');
xapp_import('xapp.Path.Utils');
/***
 * Class Xapp_FileService
 */
class Xapp_FileService implements Xapp_Rpc_Interface_Callable, Xapp_Singleton_Interface{

    const SANITIZE_HTML =  1;
    const SANITIZE_HTML_STRICT = 2;
    const SANITIZE_ALPHANUM = 3;
    const SANITIZE_EMAILCHARS=4;

    /***************************************************************************************/
    /*          Options                                                                    */

    /***
     * Local path on disc
     */
    const REPOSITORY_ROOT           = "XAPP_FILE_REPOSITORY_ROOT";

    /***
     * Maximum node length
     */
    const NODENAME_MAX_LENGTH           = "XAPP_FILE_NODENAME_MAX_LENGTH";

    /***
     * Allowed upload extensions
     */
    const UPLOAD_EXTENSIONS         = "XAPP_FILE_UPLOAD_EXTENSIONS";


    /***
     * A delegate to perform permission checks
     */
    const AUTH_DELEGATE             = "XAPP_FILE_AUTH_DELEGATE";


    /***
     * A standard temp directory for zipping
     */
    const FILE_TEMP_DIRECTORY       = "XAPP_FILE_TEMP_DIRECTORY";

    /***
     * Do auto rename for upload & uncompress
     */
    const AUTO_RENAME               = "XAPP_FILE_AUTO_RENAME";

    /***
     * Do use HTTPS
     */
    const USE_HTTPS                 = "XAPP_FILE_USE_HTTPS";

    /***
     * Do use posix subsystem
     */
    const USE_POSIX                 = "XAPP_FILE_USE_POSIX";

    /***
     * Node creation mask
     */
    const CREATION_MASK             = "XAPP_FILE_CREATION_MASK";

    /***
     * Memory allocation medium
     */
    const MEMORY_ALLOCATION_MEDIUM  = "XAPP_FILE_MEMORY_ALLOCATION_MEDIUM";


    /***
     * @param $dir
     */
    public function _setRootDirectory($dir){
        self::$rootDirectory=$dir;
    }

    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::REPOSITORY_ROOT           => XAPP_TYPE_STRING,
        self::AUTH_DELEGATE             => XAPP_TYPE_OBJECT,
        self::FILE_TEMP_DIRECTORY       => XAPP_TYPE_STRING,
        self::UPLOAD_EXTENSIONS         => XAPP_TYPE_STRING,
        self::AUTO_RENAME               => XAPP_TYPE_BOOL,
        self::USE_HTTPS                 => XAPP_TYPE_BOOL,
        self::NODENAME_MAX_LENGTH       => XAPP_TYPE_INT,
        self::USE_POSIX                 => XAPP_TYPE_BOOL,
        self::CREATION_MASK             => XAPP_TYPE_STRING,
        self::MEMORY_ALLOCATION_MEDIUM  => XAPP_TYPE_STRING
    );


    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::REPOSITORY_ROOT         => 1,
        self::AUTH_DELEGATE           => 1,
        self::FILE_TEMP_DIRECTORY     => 1,
        self::UPLOAD_EXTENSIONS       => 1,
        self::AUTO_RENAME             => 0,
        self::USE_HTTPS               => 0,
        self::NODENAME_MAX_LENGTH     => 0,
        self::USE_POSIX               => 0,
        self::CREATION_MASK           => 0,
        self::MEMORY_ALLOCATION_MEDIUM=> 1
    );

    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::REPOSITORY_ROOT            => null,
        self::AUTH_DELEGATE              => null,
        self::FILE_TEMP_DIRECTORY        => null,
        self::AUTO_RENAME                => TRUE,
        self::UPLOAD_EXTENSIONS          => 'js,css,less,bmp,csv,doc,gif,ico,jpg,jpeg,odg,odp,ods,odt,pdf,png,ppt,swf,txt,xcf,xls,BMP,CSV,DOC,GIF,ICO,JPG,JPEG,ODG,ODP,ODS,ODT,PDF,PNG,PPT,SWF,TXT,XCF,XLS',
        self::USE_HTTPS                  => true,
        self::NODENAME_MAX_LENGTH        => 256,
        self::USE_POSIX                  => false,
        self::CREATION_MASK              => '0666',
        self::MEMORY_ALLOCATION_MEDIUM   => '128M'
    );

    public $wrapperClassName='Xapp_FileService';
    protected $urlBase;
    protected static $crtZip;
    public $rpcServer;
    public $gateway;
    public static $rootDirectory;
    public static $filteringDriverInstance;



    /**
     * contains the singleton instance for this class
     *
     * @var null|XFile
     */
    protected static $_instance = null;

    /**
     * Bloody message queue
     *
     * @var array
     */
    private $clientEvents = array();

    /***
     * Push client event to client message queue
     * @param $eventData
     */
    public function addClientEvent($eventData){
        $this->clientEvents[]=$eventData;
    }

    /***
     * @return int
     */
    protected function hasClientMessages(){
        return count($this->clientEvents);
    }


    /***
     * Simple search
     * @param $searchConf
     * @return array|null
     */
    public function find($searchConf){

        $this->init();
        ini_set('memory_limit', xapp_get_option(self::MEMORY_ALLOCATION_MEDIUM),$this);
        @set_time_limit( 10 );

        $conf = json_decode($searchConf,true);
        if(empty ($conf) || !is_array($conf)){
            return null;
        }
        $searchIn=self::sanitize($conf['searchIn']);
        $searchIn=$this->urlBase.DIRECTORY_SEPARATOR .$searchIn;
        $conf['searchIn']=$searchIn;
        $finder = new Xapp_File_Find();
        $res = $finder->find($conf);
        $results = array();
        if($res && count($res)>0){
            foreach($res as $item){
                $fileInfo = $this->fileToStruct($item);
                $fileInfo->showPath=true;
                $results[]=$fileInfo;
            }
        }
        return $results;

    }


    /**
     * @param $path
     * @return string
     */
    public function resolve($path){
        $this->init();
        return $this->urlBase. DIRECTORY_SEPARATOR . self::decodeSecureMagic($this->sanitizeUrl($path));
    }

    /***
     * @param int $code
     * @param $messages
     * @return array
     */
    protected function getClientMessages ($code=3){
        $result = array();
        $result['error']['code']=$code;
        $result['error']['events']=json_encode($this->clientEvents);
        return $result;
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


    /***
     * Event factory
     * @param $operation
     * @param $args
     */
    protected function event($operation,$args){
        xcom_event($operation,null,$args,$this);
    }



    /***
     * Xapp_Rpc_Interface_Callable Impl. Before the actual call is being invoked
     */
    public function onBeforeCall($function=null, $class=null, $params=null){

        if(self::$_instance === null){
            self::$_instance=$this;
        }
        /*
         * Listen for client messages to send
         */
        xcom_subscribe(XAPP_CLIENT_EVENT,function($mixed)
        {
            Xapp_FileService::instance()->addClientEvent($mixed);
        });
    }

    /***
     *Xapp_Rpc_Interface_Callable Impl. After the actual call, you may turn all the lights off here!
     */
    public function onAfterCall($function=null, $class=null, $params=null){}


    /**
     * Xapp_Singleton interface impl. Its actually done in the base class,...
     *
     * static singleton method to create static instance of driver with optional third parameter
     * xapp options array or object
     *
     * @error 15501
     * @param null|mixed $options expects optional xapp option array or object
     * @return XFile
     */
    public static function instance($options = null)
    {
        if(self::$_instance === null)
        {
            self::$_instance = new self($options);
        }
        return self::$_instance;
    }

    /**
     * Parse the $fileVars[] PHP errors
     * @static
     * @param $boxData
     * @return array|null
     */
    static function parseFileDataErrors($boxData)
    {
        $userfile_error = $boxData["error"];
        $userfile_tmp_name = $boxData["tmp_name"];
        $userfile_size = $boxData["size"];
        if ($userfile_error != UPLOAD_ERR_OK) {
            $errorsArray = array();
            $errorsArray[UPLOAD_ERR_FORM_SIZE] = $errorsArray[UPLOAD_ERR_INI_SIZE] = array(409, "File is too big! Max is" . ini_get("upload_max_filesize"));
            $errorsArray[UPLOAD_ERR_NO_FILE] = array(410, "No file found on server!");
            $errorsArray[UPLOAD_ERR_PARTIAL] = array(410, "File is partial");
            $errorsArray[UPLOAD_ERR_INI_SIZE] = array(410, "No file found on server!");
            if ($userfile_error == UPLOAD_ERR_NO_FILE) {
                // OPERA HACK, do not display "no file found error"
                if (!ereg('Opera', $_SERVER['HTTP_USER_AGENT'])) {
                    return $errorsArray[$userfile_error];
                }
            }
            else
            {
                return $errorsArray[$userfile_error];
            }
        }
        if ($userfile_tmp_name == "none" || $userfile_size == 0) {
            return array(410, 'no file name');
        }
        return null;
    }
    public static function autoRenameForDest($destination, $fileName){
        if(!is_file($destination."/".$fileName)) return $fileName;
        $i = 1;
        $ext = "";
        $name = "";
        $split = explode(".", $fileName);
        if(count($split) > 1){
            $ext = ".".$split[count($split)-1];
            array_pop($split);
            $name = join("\.", $split);
        }else{
            $name = $fileName;
        }
        while (is_file($destination."/".$name."-$i".$ext)) {
            $i++; // increment i until finding a non existing file.
        }
        return $name."-$i".$ext;
    }

    /***
     * @param $filePath
     * @param $filename_new
     * @param null $dest
     * @param $errors
     */

    private function renameEx($filePath, $filename_new, $dest = null,&$errors)
    {
        $filename_new=self::sanitize(SystemTextEncoding::magicDequote($filename_new), self::SANITIZE_HTML_STRICT);
        $filename_new = substr($filename_new, 0, xapp_get_option(self::NODENAME_MAX_LENGTH,$this));
        $old=$this->urlBase."/$filePath";

        if(!$this->isWriteable($old))
        {
            $errors[]=XAPP_TEXT_FORMATTED('FILE_NOT_WRITEABLE',array(self::$rootDirectory.$old),55100);
            return;
        }
        if($dest == null)
            $new=dirname($old)."/".$filename_new;
        else{
            $new = $this->urlBase.$dest;
        }

        /***
         * Prevent scriptables
         * @TODO, use backend managed filters
         */
        if(!@is_dir($old)){
            $ext = pathinfo( strtolower($filename_new), PATHINFO_EXTENSION);
            $allowable = explode(',', xapp_get_option(self::UPLOAD_EXTENSIONS,$this));
            if ($ext == '' || $ext == false || (!in_array($ext, $allowable)))
            {
                $errors[]=XAPP_TEXT_FORMATTED('UPLOAD_EXTENSIONS_NOT_ALLOWED',array($filename_new,$ext));
                return;
            }
        }

        if($filename_new=="" && $dest == null)
        {
            $errors[]=XAPP_TEXT_FORMATTED('DIRECTORY_NOT_WRITEABLE',array(self::$rootDirectory.$old),55100);
            return;
        }
        if(file_exists($new))
        {
            $errors[]=XAPP_TEXT_FORMATTED('FILE_EXISTS',array($filename_new),55100);
        }
        if(!file_exists($old))
        {
            $errors[]=XAPP_TEXT_FORMATTED('CAN_NOT_FIND_FILE',array(basename($filePath)),55100);
            return;
        }
        rename($old,$new);
    }

    /**
     * @param $path
     * @param $newFileName
     * @return array
     */
    public function rename($path,$newFileName){

        $this->init();
        $error=array();
        $file = self::decodeSecureMagic($path);
        $filename_new = self::decodeSecureMagic($newFileName);
        $this->renameEx($file,$filename_new,null,$error);
        return self::toRPCError( count($error) > 0 ? 1 : 0, $error);
    }

    public function fileUpdate(){

        $this->init();

        $srcPath=null;
        if(array_key_exists('srcPath', $_GET)){
            $srcPath =$_GET['srcPath'];
        }
        $remoteUrl=null;
        if(array_key_exists('image', $_GET)){
            $remoteUrl ='' . $_GET['image'];
        }

        $title=null;
        if(array_key_exists('title', $_GET)){
            $title ='' . $_GET['title'];
        }
        $type=null;
        if(array_key_exists('type', $_GET)){
            $type ='' . $_GET['type'];
        }

        if($remoteUrl && $srcPath){
            if($type && $title){
                $srcPath = dirname($srcPath) . DIRECTORY_SEPARATOR . $title . '.' . $type;
            }
            $this->downloadTo($remoteUrl,$srcPath);
            return;
        }
    }

    /***
     * @param $selection
     * @param $to
     * @param $type
     * @param $remove
     * @return string
     */
    public function zohoUpload()
    {

        $this->init();

        $vars = array_merge($_GET, $_POST);
        $fileName = self::decodeSecureMagic(rawurldecode($vars['fileName']));
        $this->init();
        $dstIn ='/';
        if(array_key_exists('dstDir', $_GET)){
            $dstIn =self::decodeSecureMagic($_GET['dstDir']);
        }
        if($dstIn==='.'){
            $dstIn='/';
        }
        $destination =$this->urlBase. $dstIn . DIRECTORY_SEPARATOR;

        //writable check
        if(!$this->isWriteable($destination))
        {
            throw new Xapp_XFile_Exception(XAPP_TEXT_FORMATTED('DIRECTORY_NOT_WRITEABLE',array($destination),55100));
        }

        $filezoho = $_FILES['content']["tmp_name"];
        $path_parts = pathinfo($fileName);
        $dstFilePath = $destination. $path_parts['dirname'] . DIRECTORY_SEPARATOR . $path_parts['filename'].'.'.$vars["format"];
        $dstFilePath = str_replace('./','/',$dstFilePath);

        //writable check
        if(!$this->isWriteable($dstFilePath))
        {
            throw new Xapp_XFile_Exception(XAPP_TEXT_FORMATTED('DIRECTORY_NOT_WRITEABLE',array($destination),55100));
        }

        move_uploaded_file($filezoho, $dstFilePath);


    }

    /***
     * @param $selection
     * @param $to
     * @param $type
     * @param $remove
     * @return string
     */
    public function upload(){


        $vars = array_merge($_GET, $_POST);

        $errors=array();
        $this->init();
        $dstIn ='/';
        if(array_key_exists('dstDir', $_GET)){
            $dstIn =self::decodeSecureMagic($_GET['dstDir']);
        }
        if($dstIn==='.'){
            $dstIn='/';
        }
        $destination =$this->urlBase. $dstIn . DIRECTORY_SEPARATOR;


        //writable check
        if(!$this->isWriteable($destination))
        {
            throw new Xapp_XFile_Exception(XAPP_TEXT_FORMATTED('DIRECTORY_NOT_WRITEABLE',array($destination),55100));
        }







        //parse files
        $fileVars = $_FILES;
        foreach ($fileVars as $boxName => $boxData)
        {
            if(substr($boxName, 0, 9) != "userfile_"){
                continue;
            }
            $err = self::parseFileDataErrors($boxData);
            if($err != null)
            {
                $errorCode = $err[0];
                $errorMessage = $err[1];
                break;
            }

            //basic sanitize
            $userfile_name = $boxData["name"];
            $userfile_name=self::sanitize(SystemTextEncoding::fromPostedFileName($userfile_name), self::SANITIZE_HTML_STRICT);
            $userfile_name = substr($userfile_name, 0, 128);


            //rename if needed!
            $autorename = xapp_get_option(self::AUTO_RENAME);
            if($autorename){
                $userfile_name = self::autoRenameForDest($destination, $userfile_name);
            }

            /***
             * file extension check
             */
            $ext = pathinfo( strtolower($userfile_name), PATHINFO_EXTENSION);
            $allowable = explode(',', xapp_get_option(self::UPLOAD_EXTENSIONS,$this));
            if ($ext == '' || $ext == false || (!in_array($ext, $allowable)))
            {
                $errors[]=XAPP_TEXT_FORMATTED('UPLOAD_EXTENSIONS_NOT_ALLOWED',array($userfile_name,$ext));
                continue;
            }

            try {
                if(file_exists($destination."/".$userfile_name)){

                }else{

                }

            }catch (Exception $e){
                $errorMessage = $e->getMessage();
                $errors[]=XAPP_TEXT_FORMATTED('UPLOAD_UNKOWN_ERROR',array($userfile_name,$errorMessage));
                break;
            }

            if(isSet($boxData["input_upload"])){
                try{
                    $input = fopen("php://input", "r");
                    $output = fopen("$destination/".$userfile_name, "w");
                    $sizeRead = 0;
                    while($sizeRead < intval($boxData["size"])){
                        $chunk = fread($input, 4096);
                        $sizeRead += strlen($chunk);
                        fwrite($output, $chunk, strlen($chunk));
                    }
                    fclose($input);
                    fclose($output);
                }catch (Exception $e){
                    $errorMessage = $e->getMessage();
                    $errors[]=XAPP_TEXT_FORMATTED('UPLOAD_UNKOWN_ERROR',array($userfile_name,$errorMessage));
                    break;
                }
            }else{
                $result = @move_uploaded_file($boxData["tmp_name"], "$destination/".$userfile_name);
                if(!$result){
                    $realPath = $destination.DS. $userfile_name;
                    $result = move_uploaded_file($boxData["tmp_name"], $realPath);
                }
                if (!$result)
                {
                    $errors[]=XAPP_TEXT_FORMATTED('UPLOAD_UNKOWN_ERROR',array($userfile_name));
                    break;
                }
            }
        }
        return $errors;
    }

    /***
     * @param $selection
     * @param $to
     * @param $type
     * @param $remove
     * @return string
     */
    public function compressEx($selection,$to,$type,$remove){
        $zip = false;

        $dir='';
        $dir .= "/".dirname($selection[0]);

        $firstItem = $selection[0];
        $firstItem = self::sanitizeUrl($firstItem);

        require_once(realpath(dirname(__FILE__))."/Archive/archive.php");

        $this->safeIniSet('memory_limit',xapp_get_option(self::MEMORY_ALLOCATION_MEDIUM,$this));

        @set_time_limit( 0 );
        $archive = new xFileArchive();
        $zipSelection = array();

        foreach ($selection as $selectedFile)
        {
            $selectedFile = XApp_Path_Utils::normalizePath(self::sanitizeUrl($selectedFile));
            $selectedFileFull  = $this->urlBase.$selectedFile;

            if(is_dir($selectedFileFull)){
                $includeFileMask = XApp_File_Utils::defaultInclusionPatterns();
                $excludeFileMask = XApp_File_Utils::defaultExclusionPatterns();

                $options = array(XApp_File_Utils::OPTION_RECURSIVE=>true);

                $scanlist=XApp_Directory_Utils::getFilteredDirList($selectedFileFull,$includeFileMask,$excludeFileMask,$options);
                if($scanlist!=null && count($scanlist)){
                    $zipSelection = array_merge($zipSelection,$scanlist);
                }

            }elseif(is_file($selectedFileFull)){
                $zipSelection[]=$selectedFileFull;
            }
        }
        if($to==null){
            $to =$this->urlBase.$firstItem.'.zip';
        }
        $archive->create( $to, $zipSelection,'zip','',$this->urlBase,true);
        return $to;

    }

    /***
     * @param $selection
     * @param null $to
     * @param string $type
     * @param bool $remove
     */
    public function compress($selection,$to=null,$type='zip',$remove=false){

        $this->init();
        $this->compressEx($selection,$to,$type,$remove);
    }
    /***
     * @param $filePath
     * @return bool
     */
    public function isAuthentificated($filePath){
        $authDelegate = xapp_get_option(self::AUTH_DELEGATE);
        if($authDelegate!==null && $authDelegate->isLoggedIn()){
            return true;
        }
        return false;
    }
    /***
     *
     */
    public function init(){
        require_once(realpath(dirname(__FILE__))."/SystemTextEncoding.php");
        $this->urlBase=self::$rootDirectory;
        error_reporting(E_ERROR);
        ini_set('display_errors', 0);
    }

    /***
     * @param $url
     * @return mixed
     */
    static function sanitizeUrl($url){
        return self::decodeSecureMagic($url);
    }
    /***
     *
     */
    protected static function closeWrapper(){
        if(self::$crtZip != null) {
            self::$crtZip = null;
        }
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
     * @param $dir
     * @param string $type
     * @return bool
     */
    public function isWriteable($dir, $type="dir")
    {
        if(xapp_get_option(self::USE_POSIX,$this) == true && extension_loaded('posix')){
            $real = call_user_func(array( $this->wrapperClassName, "getRealFSReference"), $dir);
            return posix_access($real, POSIX_W_OK);
        }
        return is_writable($dir);
    }

    // A function to copy files from one directory to another one, including subdirectories and
    // nonexisting or newer files. Function returns number of files copied.
    // This function is PHP implementation of Windows xcopy  A:\dir1\* B:\dir2 /D /E /F /H /R /Y
    // Syntaxis: [$number =] dircopy($sourcedirectory, $destinationdirectory [, $verbose]);
    // Example: $num = dircopy('A:\dir1', 'B:\dir2', 1);
    function dircopy($srcdir, $dstdir, &$errors, &$success, $verbose = false, $convertSrcFile = true)
    {
        $num = 0;
        $verbose = true;
        $recurse = array();
        if(!is_dir($dstdir)) mkdir($dstdir);
        if($curdir = opendir($srcdir))
        {
            while($file = readdir($curdir))
            {
                if($file != '.' && $file != '..')
                {
                    $srcfile = $srcdir . "/" . $file;
                    $dstfile = $dstdir . "/" . $file;
                    if(is_file($srcfile))
                    {
                        if(is_file($dstfile)) $ow = filemtime($srcfile) - filemtime($dstfile); else $ow = 1;
                        if($ow > 0)
                        {
                            try {
                                if($convertSrcFile)
                                    $tmpPath = call_user_func(array($this->wrapperClassName, "getRealFSReference"), $srcfile);
                                else
                                    $tmpPath = $srcfile;
                                if($verbose){
                                    /*error_log("Copying '$tmpPath' to '$dstfile'...");*/
                                }

                                copy($tmpPath, $dstfile);
                                $success[] = $srcfile;
                                $num ++;
                                $this->changeMode($dstfile);
                            }catch (Exception $e){
                                $errors[] = $srcfile;
                            }
                        }
                    }
                    else{
                        $recurse[] = array("src" => $srcfile, "dest"=> $dstfile);
                    }
                }
            }
            closedir($curdir);
            foreach($recurse as $rec){
                $num += $this->dircopy($rec["src"], $rec["dest"], $errors, $success, $verbose, $convertSrcFile);
            }
        }
        return $num;
    }



    /***
     * @param $path
     * @param bool $persistent
     * @return string
     */
    public static function getRealFSReference($path, $persistent = false){
        $contextOpened =false;
        if(self::$crtZip != null){
            $contextOpened = true;
            $crtZip = self::$crtZip;
            self::$crtZip = null;
        }
        $realPath = ''.$path;
        if(!$contextOpened) {
            self::closeWrapper();
        }else{
            self::$crtZip = $crtZip;
        }
        return $realPath;
    }
    /***
     * @param $destDir
     * @param $srcFile
     * @param $error
     * @param $success
     * @param bool $move
     */
    function downloadToEx($url, $srcFile, &$error, &$success, $move = true)
    {
        $srcFile = '' . self::sanitizeUrl($srcFile);
        $destFile = ''.  $this->urlBase.dirname($srcFile)."/".basename($srcFile);
        $realSrcFile = '' . $url;
        try{
            $src = fopen($realSrcFile, "r");
            $dest = fopen($destFile, "w");
            if($dest !== false){
                while (!feof($src)) {
                    stream_copy_to_stream($src, $dest, 4096);
                }
                fclose($dest);
            }
            fclose($src);
            $this->changeMode($destFile);
        }catch (Exception $e){

            $error[] = $e->getMessage();
            return ;
        }
        return;
    }
    public function downloadTo($url,$srcFile){

        $this->init();
        $success=array();
        $error=array();
        $this->downloadToEx($url,$srcFile,$error,$success);
        return self::toRPCError( count($error) > 0 ? 1 : 0, $error);
    }
    /***
     * @param $destDir
     * @param $srcFile
     * @param $error
     * @param $success
     * @param bool $move
     */
    private function copyOrMoveFile($destDir, $srcFile,$include,$exclude,$mode,&$error, &$success, $move = false)
    {
        $srcFile = self::sanitizeUrl($srcFile);
        $destFile = $this->urlBase.$destDir."/".basename($srcFile);
        $realSrcFile = $this->urlBase.$srcFile;
        if(!file_exists($realSrcFile))
        {
            $error[] = XAPP_TEXT('FILE_DOESNT_EXISTS').$srcFile;
            return ;
        }

        if(dirname($realSrcFile)==dirname($destFile))
        {
            if($move){
                $error[] = $error[]=XAPP_TEXT('SAME_SOURCE_AND_DESTINATION');
                return ;
            }else{
                $base = basename($srcFile);
                $i = 1;
                if(is_file($realSrcFile)){
                    $dotPos = strrpos($base, ".");
                    if($dotPos>-1){
                        $radic = substr($base, 0, $dotPos);
                        $ext = substr($base, $dotPos);
                    }
                }
                // auto rename file
                $i = 1;
                $newName = $base;
                while (file_exists($this->urlBase.$destDir."/".$newName)) {
                    $suffix = "-$i";
                    if(isSet($radic)) $newName = $radic . $suffix . $ext;
                    else $newName = $base.$suffix;
                    $i++;
                }
                $destFile = $this->urlBase.$destDir."/".$newName;
            }
        }
        if(!is_file($realSrcFile))
        {
            $errors = array();
            $succFiles = array();
            if($move){

                $dErrors = array();
                $dSuccess = array();
                XApp_File_Utils::moveDirectoryEx(
                    XApp_Directory_Utils::normalizePath($realSrcFile,false),
                    XApp_Directory_Utils::normalizePath($destFile,false),
                    Array(XApp_File_Utils::OPTION_RECURSIVE=>true,
                        XApp_File_Utils::OPTION_CONFLICT_MODUS=>$mode),
                    $include,
                    $exclude,
                    $dErrors,
                    $dSuccess
                );
                $dirRes = true;
            }else{
                $dErrors = array();
                $dSuccess = array();

                XApp_File_Utils::copyDirectory(
                    XApp_Directory_Utils::normalizePath($realSrcFile,false),
                    XApp_Directory_Utils::normalizePath($destFile,false),
                    Array(XApp_File_Utils::OPTION_RECURSIVE=>true,
                        XApp_File_Utils::OPTION_CONFLICT_MODUS=>$mode),
                    $include,
                    $exclude,
                    $dErrors,
                    $dSuccess
                );
                $dirRes = true;


            }
            if(count($errors) || (isSet($res) && $res!==true))
            {
                $error[] = $error[]=XAPP_TEXT('UNKNOW_ERROR_WHILST_COPY');
                return ;
            }else{

            }
        }else
        {
            if($move){
                if(file_exists($destFile)){
                    unlink($destFile);
                }else{
                }
                $res = rename($realSrcFile, $destFile);
            }else{
                try{
                    if(call_user_func(array($this->wrapperClassName, "isRemote"))){
                        $src = fopen($realSrcFile, "r");
                        $dest = fopen($destFile, "w");
                        if($dest !== false){
                            while (!feof($src)) {
                                stream_copy_to_stream($src, $dest, 4096);
                            }
                            fclose($dest);
                        }
                        fclose($src);
                    }else{
                        copy($realSrcFile, $destFile);
                    }
                }catch (Exception $e){
                    $error[] = $e->getMessage();
                    return ;
                }
            }
        }

        if($move)
        {
            // Now delete original
            $messagePart = XAPP_TEXT('HAS_BEEN_MOVED')." ".SystemTextEncoding::toUTF8($destDir);
            if(isset($dirRes))
            {
                $success[] = XAPP_TEXT('THE_FOLDER')." ".SystemTextEncoding::toUTF8(basename($srcFile))." ".$messagePart." (".SystemTextEncoding::toUTF8($dirRes)." ".XAPP_TEXT('FILES').") ";
            }
            else
            {
                $success[] = XAPP_TEXT('THE_FILE')." ".SystemTextEncoding::toUTF8(basename($srcFile))." ".$messagePart;
            }
        }
        else
        {
            if(isSet($dirRes))
            {
                $success[] = XAPP_TEXT('THE_FOLDER')." ".SystemTextEncoding::toUTF8(basename($srcFile))." ".XAPP_TEXT('HAS_BEEN_COPIED')." ".SystemTextEncoding::toUTF8($destDir)." (".SystemTextEncoding::toUTF8($dirRes)." ".XAPP_TEXT('FILES').")";
            }
            else
            {
                $success[] = XAPP_TEXT('THE_FILE')." ".SystemTextEncoding::toUTF8(basename($srcFile))." ".XAPP_TEXT('HAS_BEEN_COPIED')." ".SystemTextEncoding::toUTF8($destDir);
            }
        }

    }

    protected function _convert($path){
        $s_system = php_uname();
        // check os
        $s_win = (strtolower(substr($s_system,0,3)) == "win")? true : false;
        return ($s_win)? preg_replace("/\\\\+/is", "\\", $path):$path;
    }

    /**
     * @param $destDir
     * @param $selectedFiles
     * @param $error
     * @param $success
     * @param bool $move
     * @throws Xapp_XFile_Exception
     */
    private function copyOrMove($destDir, $selectedFiles,$include,$exclude,$mode,&$error, &$success, $move = false)
    {



        if(file_exists(self::$rootDirectory.$destDir) &&   !$this->isWriteable(self::$rootDirectory.$destDir))
        {
            throw new Xapp_XFile_Exception(XAPP_TEXT_FORMATTED('DIRECTORY_NOT_WRITEABLE',array(self::$rootDirectory.$destDir),55100));
        }

        foreach ($selectedFiles as $selectedFile)
        {
            if($move && !$this->isWriteable(dirname(self::$rootDirectory. self::sanitizeUrl($selectedFile))))
            {
                $error[]=XAPP_TEXT_FORMATTED('DIRECTORY_NOT_WRITEABLE',array(self::$rootDirectory.$destDir),55100);
                continue;
            }

            if(is_file(self::$rootDirectory. self::sanitizeUrl($selectedFile)) && !is_readable(self::$rootDirectory. self::sanitizeUrl($selectedFile))){

                $error[] = XAPP_TEXT_FORMATTED('CAN_NOT_READ_FILE',array(basename($selectedFile)));
                continue;
            }
            $this->copyOrMoveFile($destDir, $selectedFile,$include,$exclude,$mode,$error, $success, $move);
        }
    }

    /**
     * @param $selection
     * @param $dst
     * @param bool $move
     * @return array
     */
    public function copy($selection,$dst,$inclusion=Array(),$exclusion=Array(),$mode=1504,$move=false){

        $this->init();
        $success=array();
        $error=array();
        if($dst==='.'){
            $dst='/';
        }
        xapp_import('xapp.File.Utils');
        xapp_import('xapp.Directory.Utils');

        if(!count($inclusion)){
            $inclusion=array('*','.*');
        }
        $this->copyOrMove(self::sanitizeUrl($dst),$selection,$inclusion,$exclusion,$mode,$error,$success,$move);
        return self::toRPCError( count($error) > 0 ? 1 : 0, $error);
    }

    /**
     * @param $selection
     * @param $dst
     * @param bool $move
     * @return array
     */
    public function move($selection,$dst,$inclusion=Array(),$exclusion=Array(),$mode=1504,$move=true){

        $this->init();
        $success=array();
        $error=array();
        if($dst==='.'){
            $dst='/';
        }
        xapp_import('xapp.File.Utils');
        xapp_import('xapp.Directory.Utils');

        if(!count($inclusion)){
            $inclusion=array('*','.*');
        }
        $this->copyOrMove(self::sanitizeUrl($dst),$selection,$inclusion,$exclusion,$mode,$error,$success,$move);
        return self::toRPCError( count($error) > 0 ? 1 : 0, $error);
    }

    /***
     * @param $newDir
     * @return null|string
     */
    protected  function mkDirEx($cwd,$newDir)
    {

        if($newDir=="")
        {
            return XAPP_TEXT('INVALID_FILE_NAME');
        }
        if(file_exists($this->urlBase."/$cwd/$newDir"))
        {
            return XAPP_TEXT('DIRECTORY_EXISTS');
        }
        if(!$this->isWriteable($this->urlBase."$cwd"))
        {
            return XAPP_TEXT_FORMATTED('DIRECTORY_NOT_WRITEABLE',array($cwd),55100);
        }

        $dirMode = 0775;
        $chmodValue = xapp_get_option(self::CREATION_MASK,$this);
        if(isSet($chmodValue) && $chmodValue != "")
        {
            $dirMode = octdec(ltrim($chmodValue, "0"));
            if ($dirMode & 0400) $dirMode |= 0100; // User is allowed to read, allow to list the directory
            if ($dirMode & 0040) $dirMode |= 0010; // Group is allowed to read, allow to list the directory
            if ($dirMode & 0004) $dirMode |= 0001; // Other are allowed to read, allow to list the directory
        }
        $old = umask(0);
        mkdir($this->urlBase."/$cwd/$newDir", $dirMode);
        umask($old);
        return null;
    }
    public function mkdir($cwd,$newDirectoryName){

        $this->init();
        $error = $this->mkDirEx($this->sanitizeUrl($cwd),$newDirectoryName);
        return self::toRPCError( $error!==null ? 1 : 0, $error);
    }

    protected function createEmptyFile($crtDir, $newFileName, $content = "")
    {
        if(($content == "") && preg_match("/\.html$/",$newFileName)||preg_match("/\.htm$/",$newFileName)){
            $content = "<html>\n<head>\n<title>New Document</title>\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">\n</head>\n<body bgcolor=\"#FFFFFF\" text=\"#000000\">\n\n</body>\n</html>\n";
        }

        if($newFileName=="")
        {
            return XAPP_TEXT('INVALID_FILE_NAME');
        }
        if(file_exists($this->urlBase."$crtDir/$newFileName"))
        {
            return XAPP_TEXT('FILE_EXISTS');
        }
        if(!$this->isWriteable($this->urlBase."$crtDir"))
        {

            return XAPP_TEXT_FORMATTED('DIRECTORY_NOT_WRITEABLE',array(self::$rootDirectory.$crtDir),55100);
        }
        $fp=fopen($this->urlBase."$crtDir/$newFileName","w");
        if($fp)
        {
            if($content != ""){
                fputs($fp, $content);
            }
            $this->changeMode($this->urlBase."$crtDir/$newFileName");
            fclose($fp);
            return null;
        }
        else
        {
            return XAPP_TEXT_FORMATTED('COULD_NOT_CREATE_FILE',array($crtDir.'/'.$newFileName),55100);
        }
    }

    /***
     * @param $crtDir
     * @param $newFileName
     * @param string $content
     * @return array
     */
    function mkfile($crtDir, $newFileName, $content = ""){
        $this->init();

        $ext = pathinfo( strtolower($newFileName), PATHINFO_EXTENSION);
        $allowable = explode(',', xapp_get_option(self::UPLOAD_EXTENSIONS,$this));
        if ($ext == '' || $ext == false || (!in_array($ext, $allowable)))
        {
            $error[]=XAPP_TEXT_FORMATTED('UPLOAD_EXTENSIONS_NOT_ALLOWED',array($newFileName,$ext));
            return self::toRPCError( 1, $error);
        }


        $error = $this->createEmptyFile($this->sanitizeUrl($crtDir),$newFileName,$content);
        return self::toRPCError( $error!==null ? 1 : 0, $error);
    }

    protected  function deldir($location)
    {
        if(is_dir($location))
        {
            $all=opendir($location);
            while ($file=readdir($all))
            {
                if (is_dir("$location/$file") && $file !=".." && $file!=".")
                {
                    $this->deldir("$location/$file");
                    if(file_exists("$location/$file")){
                        rmdir("$location/$file");
                    }
                    unset($file);
                }
                elseif (!is_dir("$location/$file"))
                {
                    if(file_exists("$location/$file")){
                        unlink("$location/$file");
                    }
                    unset($file);
                }
            }
            closedir($all);
            rmdir($location);
        }
        else
        {
            if(file_exists("$location")) {
                $test = @unlink("$location");
                if(!$test) throw new Exception("Cannot delete file ".$location);
            }
        }
    }
    protected function deleteEx($selectedFiles)
    {

        $log = array();
        foreach ($selectedFiles as $selectedFile)
        {
            if($selectedFile == "" || $selectedFile == DIRECTORY_SEPARATOR)
            {
                $log[] = XAPP_TEXT_FORMATTED('FAILED_TO_DELETE',array(SystemTextEncoding::toUTF8($selectedFile)));
            }
            $fileToDelete=$this->urlBase.$selectedFile;
            if(!file_exists($fileToDelete))
            {
                $log[] = XAPP_TEXT_FORMATTED('FAILED_TO_DELETE',array(SystemTextEncoding::toUTF8($selectedFile)));
                continue;
            }
            $this->deldir($fileToDelete);
            if(is_dir($fileToDelete))
            {
                $logMessages[]=XAPP_TEXT('THE_FOLDER'). ".SystemTextEncoding::toUTF8($selectedFile)." .XAPP_TEXT('HAS_BEEN_DELETED');
            }
            else
            {
                $logMessages[]=XAPP_TEXT('THE_FILE'). ".SystemTextEncoding::toUTF8($selectedFile)." .XAPP_TEXT('HAS_BEEN_DELETED');
            }
        }
        return $log;
    }

    /***
     * @param $selection
     * @param bool $secure
     * @return array
     */
    public function delete($selection,$secure=true){

        $this->init();
        $finalSelection = array();
        if($selection)
        {
            if(is_string($selection)){
                array_push($finalSelection,self::sanitizeUrl($selection));
            }elseif(is_array($selection)){
                foreach ($selection as $selectedFile)
                {
                    $finalSelection[]=self::sanitizeUrl($selectedFile);
                }
            }
        }
        $error = $this->deleteEx($finalSelection);
        return self::toRPCError( count($error) > 0 ? 1 : 0, $error);
    }

    /***
     * @param $selection
     * @throws Exception
     */
    protected  function downloadEx($selection){
        $zip = false;

        $dir='';
        $dir .= "/".dirname($selection[0]);

        $firstItem = $selection[0];

        $firstItem = self::sanitizeUrl($firstItem);

        $isUnique = false;
        if(count($selection) == 1)
        {
            $isUnique = true;
        }
        if($isUnique){
            if(is_dir($this->urlBase.$firstItem)) {
                $zip = true;
                $base = basename($firstItem);
                $dir .= "/".dirname($firstItem);
            }else{
                if(!file_exists($this->urlBase.$firstItem)){
                    throw new Exception("Cannot find file! : " . $this->urlBase.$firstItem . ' for url base : ' . $this->urlBase);
                }
            }
        }else{
            $zip = true;
        }
        if($zip){
            $localName = ($base==""?"Files":$base).".zip";
            $file = sys_get_temp_dir() . DIRECTORY_SEPARATOR.$localName;
            if(file_exists($file)){
                unlink($file);
            }
            $zipFile = $this->compressEx($selection,$file,'zip',false); //$this->makeZip($selection, $file, $dir);
            if(!$zipFile) {
                throw new Exception("Error while compressing");
            }
            register_shutdown_function("unlink", $file);
            $this->readFile($file, "force-download", $localName, false, false, true);
        }else{
            $localName = "";
            $this->readFile($this->urlBase.$firstItem, "force-download", $localName);
        }
        if(isSet($node)){
        }

    }


    public function download($selection){

        $this->init();
        $selection = array(0=> DIRECTORY_SEPARATOR .$selection);
        $this->downloadEx($selection);
    }


    public function get($mount,$path){

        $this->init();
        $path = self::sanitizeUrl($path);
        $realPath = self::$rootDirectory . DIRECTORY_SEPARATOR . $path;

        if(!is_readable($realPath)){
            throw new Xapp_XFile_Exception(XAPP_TEXT_FORMATTED('CAN_NOT_READ_FILE',array(basename($realPath)),55100));
        }
        if(file_exists($realPath)){

            if(($content = file_get_contents($realPath)) !== false){
                return $content;
            }
        }else{
            throw new Xapp_XFile_Exception(XAPP_TEXT_FORMATTED('CAN_NOT_FIND_FILE',array(basename($realPath)),55100));
        }
        throw new Xapp_XFile_Exception(XAPP_TEXT_FORMATTED('CAN_NOT_FIND_FILE',array(basename($realPath)),55100));
    }
    /***
     * @param $path
     * @param $content
     * @throws Xapp_Util_Exception_Storage
     */
    public function set($mount,$path,$content){

        $this->init();
        $path = self::decodeSecureMagic($path);
        $realPath = self::$rootDirectory . DS . $path;
        $return =null;
        $error=array();

        if($content){
            if(!file_exists($realPath)){
                $this->mkfile(dirname($path),basename($realPath),'');
            }
            if(file_exists($realPath)){

                if(!is_writeable($realPath))
                {
                    throw new Xapp_Util_Exception_Storage(vsprintf('File: %s is not writable', array(basename($realPath))), 1640102);
                }else{

                    //write out
                    $fp=fopen($realPath,"w");
                    fputs ($fp,$content);
                    fclose($fp);
                    clearstatcache(true, $realPath);
                    $return=true;

                    //tell plugins
                    $this->event(XC_OPERATION_WRITE_STR,array(
                        XAPP_EVENT_KEY_PATH=>$realPath,
                        XAPP_EVENT_KEY_REL_PATH=>$path,
                        XAPP_EVENT_KEY_CONTENT=>&$content
                    ));

                }

            }else{
                throw new Xapp_Util_Exception_Storage('unable to write storage to file  :  ' . $path . ' at : ' . $realPath, 1640104);
            }
        }else{
            throw new Xapp_Util_Exception_Storage('unable to save to storage, empty content', 1640401);
        }

        if($return === false)
        {
            throw new Xapp_Util_Exception_Storage('unable to save to storage', 1640401);
        }

        if($this->hasClientMessages()){
            return $this->getClientMessages();
        }
        return self::toRPCError( count($error) > 0 ? 1 : 0, $error);
    }

    public function isRemote(){
        return false;
    }
    /**
     * @param $filePath
     */
    function changeMode($filePath)
    {
        $chmodValue = xapp_get_option(self::CREATION_MASK,$this);
        if(isSet($chmodValue) && $chmodValue != "")
        {
            $chmodValue = octdec(ltrim($chmodValue, "0"));
            @chmod($filePath, $chmodValue);
        }
    }

    /***
     * @param $src
     * @param $dest
     * @param $basedir
     * @return array|int
     * @throws Exception
     */
    protected function makeZip ($src, $dest, $basedir)
    {
        @set_time_limit(0);
        require_once(realpath(dirname(__FILE__))."/pclzip.lib.php");
        $filePaths = array();
        foreach ($src as $item){
            $realFile = call_user_func(array($this->wrapperClassName, "getRealFSReference"), $this->urlBase."/".$item);
            $realFile = self::securePath($realFile);

            $basedir = trim(dirname($realFile));
            if(basename($item) == ""){
                $filePaths[] = array(PCLZIP_ATT_FILE_NAME => $realFile);
            }else{
                $filePaths[] = array(PCLZIP_ATT_FILE_NAME => $realFile,
                    PCLZIP_ATT_FILE_NEW_SHORT_NAME => basename($item));
            }
        }
        self::$filteringDriverInstance = $this;
        $archive = new PclZip($dest);
        $vList = $archive->create($filePaths, PCLZIP_OPT_REMOVE_PATH, $basedir, PCLZIP_OPT_NO_COMPRESSION, PCLZIP_OPT_ADD_TEMP_FILE_ON, PCLZIP_CB_PRE_ADD, 'zipPreAddCallback');
        if(!$vList){
            throw new Exception("Zip creation error : ($dest) ".$archive->errorInfo(true));
        }
        self::$filteringDriverInstance = null;
        return $vList;
    }


    /***
     * @param $path
     * @return mixed|string
     */
    static function securePath($path)
    {
        if ($path == null) $path = "";
        //
        // REMOVE ALL "../" TENTATIVES
        //
        $path = str_replace(chr(0), "", $path);
        $dirs = explode('/', $path);
        for ($i = 0; $i < count($dirs); $i++)
        {
            if ($dirs[$i] == '.' or $dirs[$i] == '..') {
                $dirs[$i] = '';
            }
        }
        // rebuild safe directory string
        $path = implode('/', $dirs);

        //
        // REPLACE DOUBLE SLASHES
        //
        while (preg_match('/\/\//', $path))
        {
            $path = str_replace('//', '/', $path);
        }
        return $path;
    }

    /***
     * @param $fileName
     * @return bool
     */
    public  function filterFile($fileName){
        return false;

        $pathParts = pathinfo($fileName);
        if(array_key_exists("HIDE_FILENAMES", $this->driverConf) && !empty($this->driverConf["HIDE_FILENAMES"])){
            if(!is_array($this->driverConf["HIDE_FILENAMES"])) {
                $this->driverConf["HIDE_FILENAMES"] = explode(",",$this->driverConf["HIDE_FILENAMES"]);
            }
            foreach ($this->driverConf["HIDE_FILENAMES"] as $search){
                if(strcasecmp($search, $pathParts["basename"]) == 0) return true;
            }
        }
        if(array_key_exists("HIDE_EXTENSIONS", $this->driverConf) && !empty($this->driverConf["HIDE_EXTENSIONS"])){
            if(!is_array($this->driverConf["HIDE_EXTENSIONS"])) {
                $this->driverConf["HIDE_EXTENSIONS"] = explode(",",$this->driverConf["HIDE_EXTENSIONS"]);
            }
            foreach ($this->driverConf["HIDE_EXTENSIONS"] as $search){
                if(strcasecmp($search, $pathParts["extension"]) == 0) return true;
            }
        }
        return false;
    }

    /***
     * @param $folderName
     * @param string $compare
     * @return bool
     */
    public  function filterFolder($folderName, $compare = "equals"){
        return false;
        if(array_key_exists("HIDE_FOLDERS", $this->driverConf) && !empty($this->driverConf["HIDE_FOLDERS"])){
            if(!is_array($this->driverConf["HIDE_FOLDERS"])) {
                $this->driverConf["HIDE_FOLDERS"] = explode(",",$this->driverConf["HIDE_FOLDERS"]);
            }
            foreach ($this->driverConf["HIDE_FOLDERS"] as $search){
                if($compare == "equals" && strcasecmp($search, $folderName) == 0) return true;
                if($compare == "contains" && strpos($folderName, "/".$search) !== false) return true;
            }
        }
        return false;
    }

    /**
     * Try to set an ini config, without errors
     * @static
     * @param string $paramName
     * @param string $paramValue
     * @return void
     */
    protected function safeIniSet($paramName, $paramValue)
    {
        $current = ini_get($paramName);
        if ($current == $paramValue) return;
        @ini_set($paramName, $paramValue);
    }
    /***
     * @param $filePathOrData
     * @param string $headerType
     * @param string $localName
     * @param bool $data
     * @param null $gzip
     * @param bool $realfileSystem
     * @param $byteOffset
     * @param $byteLength
     */
    protected function readFile($filePathOrData,
                                $headerType="plain",
                                $localName="",
                                $data=false,
                                $gzip=null,
                                $realfileSystem=false,
                                $byteOffset=-1,
                                $byteLength=-1)
    {
        require_once(realpath(dirname(__FILE__)). DIRECTORY_SEPARATOR. "SystemTextEncoding.php");

        if($gzip === null){
            $gzip = false;
        }
        if(!$realfileSystem && $this->wrapperClassName == "fsAccessWrapper"){
            $originalFilePath = $filePathOrData;
            $filePathOrData = fsAccessWrapper::patchPathForBaseDir($filePathOrData);
        }
        session_write_close();
        restore_error_handler();
        restore_exception_handler();

        // required for IE, otherwise Content-disposition is ignored
        if(ini_get('zlib.output_compression')) {
            $this->safeIniSet('zlib.output_compression', 'Off');
        }


        $isFile = !$data && !$gzip;
        if($byteLength == -1){
            if($data){
                $size = strlen($filePathOrData);
            }else if ($realfileSystem){
                $size = sprintf("%u", filesize($filePathOrData));
            }else{
                $size = $this->filesystemFileSize($filePathOrData);
            }
        }else{
            $size = $byteLength;
        }
        if($gzip && ($size > ConfService::getCoreConf("GZIP_LIMIT") || !function_exists("gzencode") || @strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') === FALSE)){
            $gzip = false; // disable gzip
        }

        $localName = ($localName=="" ? basename((isSet($originalFilePath)?$originalFilePath:$filePathOrData)) : $localName);
        if($headerType == "plain")
        {
            header("Content-type:text/plain");
        }
        else if($headerType == "image")
        {
            header("Content-Type: ".self::getImageMimeType(basename($filePathOrData))."; name=\"".$localName."\"");
            header("Content-Length: ".$size);
            header('Cache-Control: public');
        }
        else
        {

            if(preg_match('/ MSIE /',$_SERVER['HTTP_USER_AGENT']) || preg_match('/ WebKit /',$_SERVER['HTTP_USER_AGENT'])){
                $localName = str_replace("+", " ", urlencode(SystemTextEncoding::toUTF8($localName)));
            }

            if ($isFile) {
                header("Accept-Ranges: 0-$size");
            }

            // Check if we have a range header (we are resuming a transfer)
            if ( isset($_SERVER['HTTP_RANGE']) && $isFile && $size != 0 )
            {
                if($headerType == "stream_content"){
                    if(extension_loaded('fileinfo')  && $this->wrapperClassName == "fsAccessWrapper"){
                        $fInfo = new fInfo( FILEINFO_MIME );
                        $realfile = call_user_func(array($this->wrapperClassName, "getRealFSReference"), $filePathOrData);
                        $mimeType = $fInfo->file( $realfile);
                        $splitChar = explode(";", $mimeType);
                        $mimeType = trim($splitChar[0]);
                    }else{
                        $mimeType = self::getStreamingMimeType(basename($filePathOrData));
                    }
                    header('Content-type: '.$mimeType);
                }
                // multiple ranges, which can become pretty complex, so ignore it for now
                $ranges = explode('=', $_SERVER['HTTP_RANGE']);
                $offsets = explode('-', $ranges[1]);
                $offset = floatval($offsets[0]);

                $length = floatval($offsets[1]) - $offset;
                if (!$length) $length = $size - $offset;
                if ($length + $offset > $size || $length < 0) $length = $size - $offset;
                header('HTTP/1.1 206 Partial Content');
                header('Content-Range: bytes ' . $offset . '-' . ($offset + $length) . '/' . $size);

                header("Content-Length: ". $length);
                header("bla: ". $length);

                $file = fopen($filePathOrData, 'rb');
                fseek($file, 0);
                $relOffset = $offset;
                while ($relOffset > 2.0E9)
                {
                    // seek to the requested offset, this is 0 if it's not a partial content request
                    fseek($file, 2000000000, SEEK_CUR);
                    $relOffset -= 2000000000;
                    // This works because we never overcome the PHP 32 bit limit
                }
                fseek($file, $relOffset, SEEK_CUR);

                while(ob_get_level()) ob_end_flush();
                $readSize = 0.0;
                $bufferSize = 1024 * 8;
                while (!feof($file) && $readSize < $length && connection_status() == 0)
                {
                    echo fread($file, $bufferSize);
                    $readSize += $bufferSize;
                    flush();
                }

                fclose($file);
                return;
            } else
            {
                if($gzip){
                    $gzippedData = ($data?gzencode($filePathOrData,9):gzencode(file_get_contents($filePathOrData), 9));
                    $size = strlen($gzippedData);
                }
                $this->generateAttachmentsHeader($localName, $size, $isFile, $gzip);
                if($gzip){
                    print $gzippedData;
                    return;
                }
            }
        }

        if($data){
            print($filePathOrData);
        }else{
            $xsendFile = false;
            if($xsendFile){
                if(!$realfileSystem) $filePathOrData = fsAccessWrapper::getRealFSReference($filePathOrData);
                $filePathOrData = str_replace("\\", "/", $filePathOrData);
                header("X-Sendfile: ".SystemTextEncoding::toUTF8($filePathOrData));
                header("Content-type: application/octet-stream");
                header('Content-Disposition: attachment; filename="' . basename($filePathOrData) . '"');
                return;
            }
            $stream = fopen("php://output", "a");

            if($realfileSystem){

                $fp = fopen($filePathOrData, "rb");
                if($byteOffset != -1){
                    fseek($fp, $byteOffset);
                }
                $sentSize = 0;
                $readChunk = 4096;
                while (!feof($fp)) {
                    if( $byteLength != -1 &&  ($sentSize + $readChunk) >= $byteLength){
                        // compute last chunk and break after
                        $readChunk = $byteLength - $sentSize;
                        $break = true;
                    }
                    $data = fread($fp, $readChunk);
                    $dataSize = strlen($data);
                    fwrite($stream, $data, $dataSize);
                    $sentSize += $dataSize;
                    if(isSet($break)){
                        break;
                    }
                }
                fclose($fp);
            }else{
                call_user_func(array($this->wrapperClassName, "copyFileInStream"), $filePathOrData, $stream);
            }
            fflush($stream);
            fclose($stream);
        }
    }


    /***
     * @param $path
     * @param $stream
     */
    public static function copyFileInStream($path, $stream){
        $fp = fopen(self::getRealFSReference($path), "rb");
        while (!feof($fp)) {
            if(!ini_get("safe_mode")) @set_time_limit(60);
            $data = fread($fp, 4096);
            fwrite($stream, $data, strlen($data));
        }
        fclose($fp);
    }

    /**
     * @param $filePath
     * @return int|mixed|string
     */
    protected function filesystemFileSize($filePath){
        $bytesize = "-";
        $bytesize = @filesize($filePath);
        if(method_exists($this->wrapperClassName, "getLastRealSize")){
            $last = call_user_func(array($this->wrapperClassName, "getLastRealSize"));
            if($last !== false){
                $bytesize = $last;
            }
        }
        if($bytesize < 0){
            $bytesize = sprintf("%u", $bytesize);
        }

        return $bytesize;
    }

    protected function generateAttachmentsHeader(&$attachmentName, $dataSize, $isFile=true, $gzip=false){

        if(preg_match('/ MSIE /',$_SERVER['HTTP_USER_AGENT']) || preg_match('/ WebKit /',$_SERVER['HTTP_USER_AGENT'])){
            $attachmentName = str_replace("+", " ", urlencode(SystemTextEncoding::toUTF8($attachmentName)));
        }

        header("Content-Type: application/force-download; name=\"".$attachmentName."\"");
        header("Content-Transfer-Encoding: binary");
        if($gzip){
            header("Content-Encoding: gzip");
        }
        header("Content-Length: ".$dataSize);
        if ($isFile && ($dataSize != 0))
        {
            header("Content-Range: bytes 0-" . ($dataSize- 1) . "/" . $dataSize . ";");
        }
        header("Content-Disposition: attachment; filename=\"".$attachmentName."\"");
        header("Expires: 0");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        if (preg_match('/ MSIE /',$_SERVER['HTTP_USER_AGENT']))
        {
            header("Cache-Control: max_age=0");
            header("Pragma: public");
        }

        // IE8 is dumb
        if (preg_match('/ MSIE /',$_SERVER['HTTP_USER_AGENT']))
        {
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private",false);
        }

        // For SSL websites there is a bug with IE see article KB 323308
        // therefore we must reset the Cache-Control and Pragma Header
        if ( xapp_get_option(self::USE_HTTPS,$this) ==1 && preg_match('/ MSIE /',$_SERVER['HTTP_USER_AGENT']))
        {
            header("Cache-Control:");
            header("Pragma:");
        }
    }
    /***************************************************************************************/
    /*          Static tools                                                               */

    /***
     * Do standard checks : urldecode, sanitization, secure path and magicDequote
     * @param $data
     * @param int $sanitizeLevel
     * @return string
     */
    public static function decodeSecureMagic($data, $sanitizeLevel = self::SANITIZE_HTML)
    {
        return SystemTextEncoding::fromUTF8(self::sanitize(self::securePath(SystemTextEncoding::magicDequote($data)), $sanitizeLevel));
    }

    /**
     * Static image mime type headers
     * @static
     * @param $fileName
     * @return string
     */
    static function getImageMimeType($fileName)
    {
        if (preg_match("/\.jpg$|\.jpeg$/i", $fileName)) {
            return "image/jpeg";
        }
        else if (preg_match("/\.png$/i", $fileName)) {
            return "image/png";
        }
        else if (preg_match("/\.bmp$/i", $fileName)) {
            return "image/bmp";
        }
        else if (preg_match("/\.gif$/i", $fileName)) {
            return "image/gif";
        }
        return null;
    }

    /**
     * Headers to send when streaming
     * @static
     * @param $fileName
     * @return bool|string
     */
    static function getStreamingMimeType($fileName)
    {
        if (preg_match("/\.mp3$/i", $fileName)) {
            return "audio/mp3";
        }
        else if (preg_match("/\.wav$/i", $fileName)) {
            return "audio/wav";
        }
        else if (preg_match("/\.aac$/i", $fileName)) {
            return "audio/aac";
        }
        else if (preg_match("/\.m4a$/i", $fileName)) {
            return "audio/m4a";
        }
        else if (preg_match("/\.aiff$/i", $fileName)) {
            return "audio/aiff";
        }
        else if (preg_match("/\.mp4$/i", $fileName)) {
            return "video/mp4";
        }
        else if (preg_match("/\.mov$/i", $fileName)) {
            return "video/quicktime";
        }
        else if (preg_match("/\.m4v$/i", $fileName)) {
            return "video/x-m4v";
        }
        else if (preg_match("/\.3gp$/i", $fileName)) {
            return "video/3gpp";
        }
        else if (preg_match("/\.3g2$/i", $fileName)) {
            return "video/3gpp2";
        }
        else return false;
    }

    /**
     * Function to clean a string from specific characters
     *
     * @static
     * @param string $s
     * @param int $level Can be SANITIZE_ALPHANUM, SANITIZE_EMAILCHARS, SANITIZE_HTML, SANITIZE_HTML_STRICT
     * @param string $expand
     * @return mixed|string
     */
    public static function sanitize($s, $level = self::SANITIZE_HTML, $expand = 'script|style|noframes|select|option')
    {
        $s = str_replace('./','',$s);
        /**/ //prep the string
        $s = ' ' . $s;
        if ($level == self::SANITIZE_ALPHANUM) {
            return preg_replace("/[^a-zA-Z0-9_\-\.]/", "", $s);
        } else if ($level == self::SANITIZE_EMAILCHARS) {
            return preg_replace("/[^a-zA-Z0-9_\-\.@!%\+=|~\?]/", "", $s);
        }

        //begin removal
        //remove comment blocks
        while (stripos($s, '<!--') > 0) {
            $pos[1] = stripos($s, '<!--');
            $pos[2] = stripos($s, '-->', $pos[1]);
            $len[1] = $pos[2] - $pos[1] + 3;
            $x = substr($s, $pos[1], $len[1]);
            $s = str_replace($x, '', $s);
        }

        //remove tags with content between them
        if (strlen($expand) > 0) {
            $e = explode('|', $expand);
            for ($i = 0; $i < count($e); $i++) {
                while (stripos($s, '<' . $e[$i]) > 0) {
                    $len[1] = strlen('<' . $e[$i]);
                    $pos[1] = stripos($s, '<' . $e[$i]);
                    $pos[2] = stripos($s, $e[$i] . '>', $pos[1] + $len[1]);
                    $len[2] = $pos[2] - $pos[1] + $len[1];
                    $x = substr($s, $pos[1], $len[2]);
                    $s = str_replace($x, '', $s);
                }
            }
        }

        $s = strip_tags($s);
        if ($level == self::SANITIZE_HTML_STRICT) {
            $s = preg_replace("/[\",;\/`<>:\*\|\?!\^\\\]/", "", $s);
        } else {
            $s = str_replace(array("<", ">"), array("&lt;", "&gt;"), $s);
        }
        return trim($s);
    }

    protected  function foo_get_file_ownership($file){
        $stat = stat($file);
        if($stat){
            if(function_exists('posix_getgrgid') && function_exists('posix_getpwuid')){
                $group = posix_getgrgid($stat[5]);
                $user = posix_getpwuid($stat[4]);
                return compact('user', 'group');
            }
        }

        return false;
    }


    protected  function  permissions($path,$isLeaf){
        $fPerms = @fileperms($path);
        if($fPerms !== false){
            $fPerms = substr(decoct( $fPerms ), ($isLeaf?2:1));
        }else{
            $fPerms = '0000';
        }

        return $fPerms;
    }
    protected  function  formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

    protected  function _dimensions($path, $mime) {
        clearstatcache();
        return strpos($mime, 'image') === 0 && ($s = @getimagesize($path)) !== false ? $s[0].'x'.$s[1] : false;

    }

    /**
     * Get the permissions of the file/folder at a give path
     *
     * @param	string	$path	The path of a file/folder
     * @return	string	Filesystem permissions
     * @since	1.5
     */
    protected  function getPermissions($path)
    {
        $mode = @ decoct(@ fileperms($path) & 0777);

        if (strlen($mode) < 3) {
            return '---------';
        }
        $parsed_mode = '';
        for ($i = 0; $i < 3; $i ++)
        {
            // read
            $parsed_mode .= ($mode { $i } & 04) ? "r" : "-";
            // write
            $parsed_mode .= ($mode { $i } & 02) ? "w" : "-";
            // execute
            $parsed_mode .= ($mode { $i } & 01) ? "x" : "-";
        }
        return $parsed_mode;
    }

    /**
     * @param $fullPath
     * @return stdClass
     */
    protected  function fileToStruct($fullPath) {
        $fullPath = str_replace('./','',$fullPath);
        $fullPath = str_replace('..','',$fullPath);
        $atts     = stat( realpath($fullPath) );

        $fileInfo           = new stdClass();
        $fileInfo->name     = basename($fullPath);





        $fileInfo->path       = str_replace($this->sanitizeUrl($this->urlBase), "",$fullPath);
        $fileInfo->pathInStore= str_replace($this->sanitizeUrl($this->urlBase), "",dirname($fullPath));
        $fileInfo->modified = $atts[9];
        $fileInfo->owner = $this->foo_get_file_ownership($fullPath);
        $fileInfo->group = @filegroup($fullPath) || "unknown";

        $fileInfo->read  = is_readable($fullPath);
        $fileInfo->write = is_writable($fullPath);

        if($fileInfo->owner===false){
            $fileInfo->access=false;
        }

        $fileInfo->permissions= $this->permissions($fullPath,is_dir($fullPath)) . ' (' . $this->getPermissions($fullPath) . ')';

        if (is_dir($fullPath)) {
            $fileInfo->directory = true;
            $fileInfo->children	= array();
            $fileInfo->_EX  = false;
            $fileInfo->size = 0;
        } else {
            $fileInfo->size = $this->formatSizeUnits(filesize($fullPath));
            $fileInfo->dimension = $this->_dimensions($fullPath,$fileInfo->mime);

            if(function_exists('mime_content_type')){
                $fileInfo->mime = mime_content_type($fullPath);
            }else{
                /*$fi = new finfo(FILEINFO_MIME,$fullPath);
                $fileInfo->mime = $fi->buffer(file_get_contents($fullPath));
                error_log($fileInfo->mime);*/
            }
        }
        return $fileInfo;
    }
}

/***
 *
 * @param $value
 * @param $header
 * @return bool
 */
function zipPreAddCallback($value, $header){
    if(Xapp_FileService::$filteringDriverInstance == null) return true;

    $search = $header["filename"];
    return !(Xapp_FileService::$filteringDriverInstance->filterFile($search)
        || Xapp_FileService::$filteringDriverInstance->filterFolder($search, "contains"));
}
