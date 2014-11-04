<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package XApp\File
 */

/***
 * Copy Collision Modus
 */
define('XAPP_XFILE_OVERWRITE_NONE',1501);
define('XAPP_XFILE_OVERWRITE_ALL',1502);
define('XAPP_XFILE_OVERWRITE_IF_SIZE_DIFFERS',1503);
define('XAPP_XFILE_OVERWRITE_IF_NEWER',1504);

/***
 * Directory listing flags
 *
 * */
define('XAPP_XFILE_SHOW_ISREADONLY',1601);
define('XAPP_XFILE_SHOW_ISDIR',1602);
define('XAPP_XFILE_SHOW_OWNER',1604);
define('XAPP_XFILE_SHOW_MIME',1608);
define('XAPP_XFILE_SHOW_SIZE',1616);
define('XAPP_XFILE_SHOW_PERMISSIONS',1632);
define('XAPP_XFILE_SHOW_TIME',1633);

define('XAPP_NODE_FIELD_NAME','name');
define('XAPP_NODE_FIELD_PATH','path');
define('XAPP_NODE_FIELD_USER','user');
define('XAPP_NODE_FIELD_CHILDREN','children');
define('XAPP_NODE_FIELD_PERMISSIONS','permissions');
define('XAPP_NODE_FIELD_OWNER','owner');
define('XAPP_NODE_FIELD_READ_ONLY','readOnly');
define('XAPP_NODE_FIELD_SIZE','size');
define('XAPP_NODE_FIELD_MIME','mime');
define('XAPP_NODE_FIELD_TIME','modified');
define('XAPP_NODE_FIELD_IS_DIRECTORY','isDir');
define('XAPP_NODE_FIELD_IS_DIRTY','isDirty');
define('XAPP_NODE_FIELD_IS_NEW','isNew');


// may be there are more cases ?

class XApp_File_Utils
{

    const OPTION_RECURSIVE='recursive';
    const OPTION_TIMEOUT='timeout';
    const OPTION_DRYRUN='dryrun';
    const OPTION_CONFLICT_MODUS='overwriteModus';
    const OPTION_LOGGING_STRIP_BASE_PATH='stripBasePath';
    const OPTION_NEW_CHMOD='newFileMask';

    const OPTION_SIZE_LIMIT="sizeLimit";
    const OPTION_CHUNK_SIZE="chunkSize";
    const OPTION_TEMP_PATH="tempPath";
    const OPTION_AS_ATTACHMENT="asAttachment";
    const OPTION_TEST="runTest";
    const OPTION_SEND="send";
    const OPTION_DIR_LIST_FIELDS="fields";
    const OPTION_DIR_LIST="dirOptions";
    const OPTION_EMIT="emit";

    const OPTION_INCLUDE_LIST="includeList";
    const OPTION_EXCLUDE_LIST="excludeList";

	const OPTION_RESIZE_TO="width";
	const OPTION_PREVENT_CACHE="preventCache";


    const GET_FILE_SIZE_LIMIT='6M';   // File size limit for "get" method, in MB
    const GET_FILE_CHUNK_SIZE='1M';     // Chunk size for "get" method, in MB
    /***
     * @return array of scandir queries or reg-expressions
     */
    public static function defaultExclusionPatterns(){
        return array(
        '.svn',
        '.git',
        '.idea'
        );
    }


    /***
     *
     * @return array|null
     */
    public static function defaultInclusionPatterns(){
        return array(
            '*',
            '.*'    // if not, grep skips hidden files
        );
    }

    /***
     * Track the very input directories as copyDirectoryEx is recursive
     */
    protected static $_tmpSrcRootPath;
    protected static $_tmpDstRootPath;

    /**
     *
     *  Copies $srcDir into $dstDirectory
     *
     * It must be possible to use this function recursive!
     * Must work with php5.2
     *
     * @param $srcDir : expects sanitized absolute directory
     * @param $dstDirectory : expects sanitized absolute directory, if it doesn't exists, create it!
     * @param array $options : [recursive (true/false) default true, timeout (seconds) default 60, overwriteModus : XAPP_XFILE_OVERWRITE_NONE | XAPP_XFILE_OVERWRITE_ALL | XAPP_XFILE_OVERWRITE_IF_SIZE_DIFFERS
     * @param array|string $inclusionMask : null means all, if its a string : it must compatible to a scandir query, if its a string its a regular expression
     * @param array|string $exclusionMask : null means all, otherwise it must compatible to a scandir query,if its a string its a regular expression
     * @param $error : a pointer to an array reference, please track all errors and don't abort! Check __copyOrMoveFile below how to write the error messages right!
     * @param $success : track all copied items here
     */
    public static function copyDirectoryEx($srcDir,$dstDirectory,$options=Array(),$inclusionMask = Array(),$exclusionMask = Array(),&$error,&$success){
        if(!self::$_tmpSrcRootPath){
            self::$_tmpSrcRootPath=realpath($srcDir . DIRECTORY_SEPARATOR . '..');
        }
        if(!self::$_tmpDstRootPath){
            self::$_tmpDstRootPath=realpath($dstDirectory . DIRECTORY_SEPARATOR . '..');
        }

        if (isset($options[self::OPTION_TIMEOUT])) ini_set('max_execution_time', intval($options[self::OPTION_TIMEOUT]));
        if (!isset($options[self::OPTION_CONFLICT_MODUS])) $options[self::OPTION_CONFLICT_MODUS]=XAPP_XFILE_OVERWRITE_NONE;
        if (!isset($options[self::OPTION_NEW_CHMOD])) $options[self::OPTION_NEW_CHMOD]='0777';
        if (!isset($options[self::OPTION_LOGGING_STRIP_BASE_PATH])) $options[self::OPTION_LOGGING_STRIP_BASE_PATH]=true;


        $scanlist=XApp_Directory_Utils::getFilteredDirList($srcDir,$inclusionMask,$exclusionMask);

        if ($scanlist===FALSE)
            $error[]=XAPP_TEXT_FORMATTED('CAN_NOT_READ_DIR',array($srcDir));
        else {
            // Create dest dir if not exists
            if (!is_dir($dstDirectory)) {
                $mkres=@mkdir($dstDirectory);
                if (!$mkres) {
                    $error[]=XAPP_TEXT_FORMATTED('COULD_NOT_CREATE_DIRECTORY',array($dstDirectory));
                    return;
                } else {
                    if (!self::changeModeDirectory($dstDirectory,$options[self::OPTION_NEW_CHMOD]))
                        $error[]=XAPP_TEXT_FORMATTED('COULD_NOT_CHANGE_DIRECTORY_MODE',array($options[self::OPTION_NEW_CHMOD],$dstDirectory));
                }
            }

            foreach($scanlist as $direntry) {
                $destentry=$dstDirectory.substr($direntry,strlen($srcDir));

                // Dir found
                if (is_dir($direntry)) {
                    if ($options[self::OPTION_RECURSIVE]) {
                        self::copyDirectoryEx($direntry,$destentry,$options,$inclusionMask,$exclusionMask,$error,$success);
                    }
                } else {
                   // File found
                    if (self::singleFileCopy($direntry,$destentry,$options[self::OPTION_CONFLICT_MODUS],$success,$error)) {
                        $success[]=XAPP_TEXT_FORMATTED('THE_FILE').XAPP_TEXT_FORMATTED('COPIED_OK',Array($srcDir,$dstDirectory));
                        if (!self::changeModeFile($destentry,$options[self::OPTION_NEW_CHMOD]))
                            $error[]=XAPP_TEXT_FORMATTED('COULD_NOT_CHANGE_FILE_MODE',array($options[self::OPTION_NEW_CHMOD],$dstDirectory));

                    }
                }
            }
            $success[]=XAPP_TEXT_FORMATTED('DIRECTORY').XAPP_TEXT_FORMATTED('COPIED_OK',Array($srcDir,$dstDirectory));
        }

        if($options[self::OPTION_LOGGING_STRIP_BASE_PATH])
            self::StripBasePaths($error,$success);
    }
    /**
     *
     *  Copies $srcDir into $dstDirectory
     *
     * It must be possible to use this function recursive!
     * Must work with php5.2
     *
     * @param $srcDir : expects sanitized absolute directory
     * @param $dstDirectory : expects sanitized absolute directory, if it doesn't exists, create it!
     * @param array $options : [recursive (true/false) default true, timeout (seconds) default 60, overwriteModus : XAPP_XFILE_OVERWRITE_NONE | XAPP_XFILE_OVERWRITE_ALL | XAPP_XFILE_OVERWRITE_IF_SIZE_DIFFERS
     * @param array|string $inclusionMask : null means all, if its a string : it must compatible to a scandir query, if its a string its a regular expression
     * @param array|string $exclusionMask : null means all, otherwise it must compatible to a scandir query,if its a string its a regular expression
     * @param $error : a pointer to an array reference, please track all errors and don't abort! Check __copyOrMoveFile below how to write the error messages right!
     * @param $success : track all copied items here
     */

    public static function copyDirectory($srcDir,$dstDirectory,$options=Array(),$inclusionMask = Array(),$exclusionMask = Array(),&$error,&$success){

        // defaults
        if (!isset($options[self::OPTION_RECURSIVE])) $options[self::OPTION_RECURSIVE]=true;
        if (!isset($options[self::OPTION_TIMEOUT])) $options[self::OPTION_TIMEOUT]=60;
        if (!isset($options[self::OPTION_CONFLICT_MODUS])) $options[self::OPTION_CONFLICT_MODUS]=XAPP_XFILE_OVERWRITE_NONE;
        if (!isset($options[self::OPTION_LOGGING_STRIP_BASE_PATH])) $options[self::OPTION_LOGGING_STRIP_BASE_PATH]=true;
        if (!isset($options[self::OPTION_NEW_CHMOD])) $options[self::OPTION_NEW_CHMOD]='0777';


        self::copyDirectoryEx($srcDir,$dstDirectory,$options,$inclusionMask,$exclusionMask,$error,$success);

    }

    /**
     * Moves $srcDir into $dstDirectory
     *
     * @param $srcDir : expects sanitized absolute directory
     * @param $dstDirectory : expects sanitized absolute directory, if it doesn't exists, create it!
     * @param array $options        : [recursive (true/false) default true, timeout (seconds) default 60, overwriteModus : XAPP_XFILE_OVERWRITE_NONE | XAPP_XFILE_OVERWRITE_ALL | XAPP_XFILE_OVERWRITE_IF_SIZE_DIFFERS
     * @param array|string $inclusionMask : null means all, if its a string : it must compatible to a scandir query, if its a string its a regular expression
     * @param array|string $exclusionMask : null means all, otherwise it must compatible to a scandir query,if its a string its a regular expression
     * @param $error : a pointer to an array reference, please track all errors and don't abort! Check __copyOrMoveFile below how to write the error messages right!
     * @param $success : track all moved items here
     */
    public static function moveDirectoryEx($srcDir,$dstDirectory,$options=Array(),$inclusionMask = Array(),$exclusionMask = Array(),&$error,&$success){
        if(!self::$_tmpSrcRootPath){
            self::$_tmpSrcRootPath=realpath($srcDir . DIRECTORY_SEPARATOR . '..');
        }
        if(!self::$_tmpDstRootPath){
            self::$_tmpDstRootPath=realpath($dstDirectory . DIRECTORY_SEPARATOR . '..');
        }
        // defaults
        if (!isset($options[self::OPTION_RECURSIVE])) $options[self::OPTION_RECURSIVE]=true;
        if (!isset($options[self::OPTION_TIMEOUT])) $options[self::OPTION_TIMEOUT]=60;
        if (!isset($options[self::OPTION_CONFLICT_MODUS])) $options[self::OPTION_CONFLICT_MODUS]=XAPP_XFILE_OVERWRITE_NONE;
        if (!isset($options[self::OPTION_LOGGING_STRIP_BASE_PATH])) $options[self::OPTION_LOGGING_STRIP_BASE_PATH]=true;
        if (!isset($options[self::OPTION_NEW_CHMOD])) $options[self::OPTION_NEW_CHMOD]='0777';

        // First copy
        self::copyDirectoryEx($srcDir,$dstDirectory,$options,$inclusionMask,$exclusionMask,$error,$success);

        // If everything went ok, delete source
        if (Count($error)==0) {
            self::deleteDirectoryEx($srcDir,$options,$inclusionMask,$exclusionMask,$error,$success);
        } else {    // If not, notify and delete destination folder (undo copy)
            $error[]=XAPP_TEXT_FORMATTED('CAN_NOT_MOVE',array($srcDir));
            self::deleteDirectoryEx($dstDirectory,$options,$inclusionMask,$exclusionMask,$error,$success);
        }

        if($options[self::OPTION_LOGGING_STRIP_BASE_PATH])
            self::StripBasePaths($error,$success);

    }
    /**
     *  Removes directory and all its contents
     *
     * @param $path                 : expects sanitized absolute directory
     * @param array $options        : [recursive (true/false) default true, timeout (seconds) default 60, dryrun (true/false) - don't delete for real, default false]
     * @param array $inclusionMask  : null means all, if its a string : it must compatible to a scandir query, if its a string its a regular expression
     * @param array $exclusionMask  : null means all, otherwise it must compatible to a scandir query,if its a string its a regular expression
     * @param $error                : a pointer to an array reference
     * @param $success              : track all copied items here
     */
    public static function deleteDirectoryEx($path,$options=Array(),$inclusionMask = Array(),$exclusionMask = Array(),&$error,&$success){
        if(!self::$_tmpSrcRootPath){
            self::$_tmpSrcRootPath=realpath($path . DIRECTORY_SEPARATOR . '..');
        }
        if(!self::$_tmpDstRootPath){
            self::$_tmpDstRootPath=realpath($path. DIRECTORY_SEPARATOR . '..');
        }

        // defaults
        if (!isset($options[self::OPTION_RECURSIVE])) $options[self::OPTION_RECURSIVE]=true;
        if (!isset($options[self::OPTION_TIMEOUT])) $options[self::OPTION_TIMEOUT]=60;
        if (!isset($options[self::OPTION_DRYRUN])) $options[self::OPTION_DRYRUN]=false;
        if (!isset($options[self::OPTION_LOGGING_STRIP_BASE_PATH])) $options[self::OPTION_LOGGING_STRIP_BASE_PATH]=true;
        ini_set('max_execution_time', intval($options[self::OPTION_TIMEOUT]));


        $scanlist=XApp_Directory_Utils::getFilteredDirList($path,$inclusionMask,$exclusionMask);
        if ($scanlist===FALSE)
            $error[]=XAPP_TEXT_FORMATTED('CAN_NOT_READ_DIR',array($path));
        else {
            foreach($scanlist as $direntry) {
                // Dir found
                if (is_dir($direntry)) {
                    if ($options[self::OPTION_RECURSIVE]) {
                        self::deleteDirectoryEx($direntry,$options,$inclusionMask,$exclusionMask,$error,$success);
                    }
                } else {
                    // File found
                    if (!$options[self::OPTION_DRYRUN]) {
                        if (!@unlink($direntry)) {
                            $error[]=XAPP_TEXT_FORMATTED('THE_FILE')." ".$direntry." ".XAPP_TEXT_FORMATTED('HAS_NOT_BEEN_DELETE');
                        } else {
                            $success[]=XAPP_TEXT_FORMATTED('THE_FILE')." ".$direntry." ".XAPP_TEXT_FORMATTED('HAS_BEEN_DELETED');
                        }
                    } else
                        $success[]=XAPP_TEXT_FORMATTED('SIMULATED')." ".XAPP_TEXT_FORMATTED('THE_FILE')." ".$direntry." ".XAPP_TEXT_FORMATTED('HAS_BEEN_DELETED');
                }
            }

            // Remove dir
            if (!$options[self::OPTION_DRYRUN]) {
                if (!@rmdir($path)) {
                    $error[]=XAPP_TEXT_FORMATTED('THE_FOLDER')." ".$path." ".XAPP_TEXT_FORMATTED('HAS_NOT_BEEN_DELETE');
                } else {
                    $success[]=XAPP_TEXT_FORMATTED('THE_FOLDER')." ".$path." ".XAPP_TEXT_FORMATTED('HAS_BEEN_DELETED');
                }
            } else
                $success[]=XAPP_TEXT_FORMATTED('SIMULATED')." ".XAPP_TEXT_FORMATTED('THE_FOLDER')." ".$path." ".XAPP_TEXT_FORMATTED('HAS_BEEN_DELETED');

        }
        if($options[self::OPTION_LOGGING_STRIP_BASE_PATH])
            self::StripBasePaths($error,$success);
    }


    /**
     *  Removes a file
     * @param $path                 : expects sanitized absolute directory
     * @param array $options        : [recursive (true/false) default true, timeout (seconds) default 60, dryrun (true/false) - don't delete for real, default false]
     * @param $error                : a pointer to an array reference
     * @param $success              : track all copied items here
     */
    public static function deleteFile($path,$options=Array(),&$error,&$success){
        error_log('del file ' .$path);
	    if(!self::$_tmpSrcRootPath){
            self::$_tmpSrcRootPath=realpath($path . DIRECTORY_SEPARATOR . '..');
        }

        //defaults
        if (!isset($options[self::OPTION_LOGGING_STRIP_BASE_PATH])) $options[self::OPTION_LOGGING_STRIP_BASE_PATH]=true;

        if(file_exists($path)){
            if (!@unlink($path)) {
                $error[]=XAPP_TEXT_FORMATTED('THE_FILE')." ".$path." ".XAPP_TEXT_FORMATTED('HAS_NOT_BEEN_DELETE');
            } else {
                $success[]=XAPP_TEXT_FORMATTED('THE_FILE')." ".$path." ".XAPP_TEXT_FORMATTED('HAS_BEEN_DELETED');
            }
        }else{

        }
        if($options[self::OPTION_LOGGING_STRIP_BASE_PATH])
            self::StripBasePaths($error,$success);
    }

    /***
     * @param $path
     * @param $content
     * @throws Xapp_Util_Exception_Storage
     */
    public static function set($path,$content){

        $realPath = '' . $path;
        $return =null;
        $error=array();
        $autoCreate=false;

        if($content){
            if(!file_exists($realPath)){

                //$this->mkfile(dirname($path),basename($realPath),'');
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

                    /*
                    //tell plugins
                    $this->event(XC_OPERATION_WRITE_STR,array(
                        XAPP_EVENT_KEY_PATH=>$realPath,
                        XAPP_EVENT_KEY_REL_PATH=>$path,
                        XAPP_EVENT_KEY_CONTENT=>&$content
                    ));*/

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
        return true;
    }


    /**
     * @param $basePath : absolute path on disc
     * @param $relativePath : can be file path within the baseDirectory or a directory. In case its a directory, it must zip it and send it back as attachment
     * @param $options      : options array
     * @param array|string $exclusionMask : null means all, otherwise it must compatible to a scandir query,if its a string its a regular expression. This is a blacklist.
     */
    public static function get($basePath,$relativePath,$options=Array(),$exclusionMask=Array()) {

	    ini_set('memory_limit', '128M');

	    if (!isset($options[self::OPTION_SIZE_LIMIT])) $options[self::OPTION_SIZE_LIMIT]=self::GET_FILE_SIZE_LIMIT;
        if (!isset($options[self::OPTION_CHUNK_SIZE])) $options[self::OPTION_CHUNK_SIZE]=self::GET_FILE_CHUNK_SIZE;
        if (!isset($options[self::OPTION_TEMP_PATH])) $options[self::OPTION_TEMP_PATH]=sys_get_temp_dir();
        if (!isset($options[self::OPTION_AS_ATTACHMENT])) $options[self::OPTION_AS_ATTACHMENT]=false;
        if (!isset($options[self::OPTION_TEST])) $options[self::OPTION_TEST]=false;
        if (!isset($options[self::OPTION_SEND])) $options[self::OPTION_SEND]=true;
	    if (!isset($options[self::OPTION_PREVENT_CACHE])) $options[self::OPTION_PREVENT_CACHE]=false;


        $target_file=XApp_Directory_Utils::normalizePath($basePath.$relativePath);
        if (is_dir($target_file)) {  // ZIP dir contents
            $error=Array();
            $success=Array();

            $tempFile=tempnam($options[self::OPTION_TEMP_PATH], 'getZIP');
            XApp_Directory_Utils::zipDir($basePath.$relativePath,$tempFile,self::defaultInclusionPatterns(),$exclusionMask,$error,$success);
            $target_file=$tempFile;
            $attach_name=basename($relativePath).".zip";
            $mime="application/zip";
        } else{
            $mime=self::getMime($target_file);

        }

	    if ($options[self::OPTION_SEND]===true) {
            if (!$options[self::OPTION_TEST]) {

	            self::sendHeader($mime,($options[self::OPTION_AS_ATTACHMENT] ? $target_file:''), basename($target_file));
               if (strpos($mime,"text")!==FALSE) {
	               if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
		               ob_start("ob_gzhandler");
	               } else {
		               ob_start();
	               }
               }
            }

	        /**
	         * take care about resumed downloads
	         */
	        $file_size  = filesize($target_file);
	        if(isset($_SERVER['HTTP_RANGE'])){

		        $file = @fopen($target_file,"rb");
		        list($size_unit, $range_orig) = explode('=', $_SERVER['HTTP_RANGE'], 2);
		        if ($size_unit == 'bytes')
		        {
			        //multiple ranges could be specified at the same time, but for simplicity only serve the first range
			        //http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt
			        if(strpos($range_orig,',')!==false) {
				        list($range, $extra_ranges) = explode(',', $range_orig, 2);
			        }else{
				        $range = $range_orig;
			        }
		        }
		        else
		        {
			        $range = '';
			        header('HTTP/1.1 416 Requested Range Not Satisfiable');
			        exit;
		        }
		        //figure out download piece from range (if set)
		        list($seek_start, $seek_end) = explode('-', $range, 2);

		        //set start and end based on range (if set), else set defaults
		        //also check for invalid ranges.
		        $seek_end   = (empty($seek_end)) ? ($file_size - 1) : min(abs(intval($seek_end)),($file_size - 1));
		        $seek_start = (empty($seek_start) || $seek_end < abs(intval($seek_start))) ? 0 : max(abs(intval($seek_start)),0);

		        //Only send partial content header if downloading a piece of the file (IE workaround)
		        if ($seek_start > 0 || $seek_end < ($file_size - 1))
		        {
			        header('HTTP/1.1 206 Partial Content');
			        header('Content-Range: bytes '.$seek_start.'-'.$seek_end.'/'.$file_size);
			        header('Content-Length: '.($seek_end - $seek_start + 1));
		        }
		        else

			        header("Content-Length: $file_size");

		        header('Accept-Ranges: bytes');

		        set_time_limit(0);
		        fseek($file, $seek_start);

		        while(!feof($file))
		        {
			        print(@fread($file, 1024*8));
			        ob_flush();
			        flush();
			        if (connection_status()!=0)
			        {
				        @fclose($file);
				        exit;
			        }
		        }

		        // file save was a success
		        @fclose($file);

	        }else {

		        // send streamed or complete
		        $limit = intval($options[self::OPTION_SIZE_LIMIT]) * 1024 * 1024; // Convert limit to bytes
		        if (filesize($target_file) > $limit) {


			        // chunk file
			        $chunk_size = intval(
					        $options[self::OPTION_CHUNK_SIZE]
				        ) * 1024 * 1024; // Convert chunk size to bytes
			        $handle = fopen($target_file, 'rb');
			        while (!feof($handle)) {
				        $buffer = fread($handle, $chunk_size);
				        echo $buffer;
				        set_time_limit(50);
				        ob_flush();
				        flush();
			        }
			        fclose($handle);

		        } else {
			        self::sendHeader(
				        $mime,
				        ($options[self::OPTION_AS_ATTACHMENT] ? $target_file : ''),
				        basename($target_file)
			        );
			        $content = file_get_contents($target_file);
			        echo $content;
		        }

	        }


        }else{


		    /**
		     * deal with resize!
		     */
		    if (isset($options[self::OPTION_RESIZE_TO]) &&

			    //check we have some image tools
			    (
				    (extension_loaded('gd') && function_exists('gd_info')) || //GD is fine , otherwise try imagick
			        extension_loaded('imagick')
		        )
		    )
		    {
			    xapp_import('xapp.Image.Utils');

			    $cacheImage = false;

			    $options = array(
				    XApp_Image_Utils::OPTION_WIDTH => $options[self::OPTION_RESIZE_TO],
				    XApp_Image_Utils::OPTION_PREVENT_CACHE => self::OPTION_PREVENT_CACHE
			    );

			    //enable caching if possible
			    $cacheDir = XApp_Directory_Utils::getCacheDirectory(true,'xcom');
			    if($cacheDir!=null && is_writable($cacheDir)){
				    $cacheImage=true;
				    $options[XApp_Image_Utils::OPTION_CACHE_DIR] = $cacheDir;
				    XApp_Image_Utils::$cacheDir =$cacheDir;
			    }

			    $job = array(
				    XApp_Image_Utils::IMAGE_OPERATION   => XApp_Image_Utils::OPERATION_RESIZE,
				    XApp_Image_Utils::OPERATION_OPTIONS => $options
			    );

			    $jobs = array();
			    $jobs[]=$job;


			    $errors = array();
			    XApp_Image_Utils::execute($target_file,null,json_encode($jobs),$errors,false,$cacheImage,true);
			    exit;
		    }
	        // send streamed or complete
	        $limit=intval($options[self::OPTION_SIZE_LIMIT])*1024*1024; // Convert limit to bytes
	        if (filesize($target_file)>$limit) {

		        self::sendHeader($mime,false, basename($target_file));
		        // chunk file
		        $chunk_size=intval($options[self::OPTION_CHUNK_SIZE])*1024*1024; // Convert chunk size to bytes
		        $handle = fopen($target_file, 'rb');
		        while (!feof($handle)) {
			        $buffer = fread($handle, $chunk_size);
			        echo $buffer;
			        set_time_limit(50);
			        ob_flush();
			        flush();
		        }
		        fclose($handle);
	        }else{
		        self::sendHeader($mime,false, basename($target_file));
	            return file_get_contents($target_file);
	        }
        }
    }



    /**
     * Returns the MIME content type of file.
     * @param  string
     * @return string
     */
    public static function mimeFromString($data)
    {
        if (extension_loaded('fileinfo') && preg_match('#^(\S+/[^\s;]+)#', finfo_buffer(finfo_open(FILEINFO_MIME), $data), $m)) {
            return $m[1];

        } elseif (strncmp($data, "\xff\xd8", 2) === 0) {
            return 'image/jpeg';

        } elseif (strncmp($data, "\x89PNG", 4) === 0) {
            return 'image/png';

        } elseif (strncmp($data, "GIF", 3) === 0) {
            return 'image/gif';

        } else {
            return 'application/octet-stream';
        }
    }
    /**
     * Returns the mimetype for a file
     *
     * @param $filepath             :   full path for the file to check
     * @return bool|mixed|string    :   mime string if success, false if not
     */
    public static function getMime($filepath) {
        if(empty($filepath)||!file_exists($filepath)){
            return false;
        }
        $mime="";
        if (function_exists("finfo_file")) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $filepath);
            finfo_close($finfo);

	        //php bug
	        if(strpos($filepath,'.css')!==false){
		        $mime = 'text/css';
	        }

        } else if (function_exists("mime_content_type")) {
            $mime=mime_content_type($filepath);
        } else if (!stristr(ini_get("disable_functions"), "shell_exec")) {  // Unix systems
            $file = escapeshellarg($filepath);
            $mime = shell_exec("file -bi " . $file);
            $mime_arr = explode(";",$mime);
            $mime = $mime_arr[0];
        }

        if (empty($mime)) {
            xapp_import("/File/mime_types.php");
            $mimes=mime_types();
            $parts= explode(".",$filepath);
            $ext=end($parts);
            if (array_key_exists($ext,$mimes))
                $mime=$mimes[$ext];
            if (isset($mime))
                return $mime;
            else
                return FALSE;
        }
        return $mime;
    }

    /**
     * Guesses the mimetype for a file name
     *
     * @param $filepath             :   file name
     * @return bool|mixed|string    :   mime string if success, false if not
     */
    public static function guessMime($filepath) {
        if(empty($filepath)){
            return false;
        }
        $mime="";
        xapp_import("/File/mime_types.php");
        $mimes=mime_types();
        $parts= explode(".",$filepath);
        $ext=end($parts);
        if (array_key_exists($ext,$mimes))
            $mime=$mimes[$ext];

        if (isset($mime))
            return $mime;
        return $mime;
    }
    /***
     * @param string $mime
     * @param string $attachment
     * @param string $rename_attachment
     */
    public static function sendHeader($mime="",$attachment="",$rename_attachment="") {
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false);
        if ($mime!="") header("Content-Type: ".$mime);
        if ($attachment!="") {
            $attachment=XApp_Directory_Utils::normalizePath($attachment);
            $attachment_name=($rename_attachment!=''?$rename_attachment:basename($attachment));
            header("Content-Disposition: attachment; filename=".$attachment_name.";" );
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: ".filesize($attachment));
        }
    }





    /***
     * @param $error
     * @param $success
     */
    private static function StripBasePaths(&$error,&$success) {
        $error = str_replace(Array(self::$_tmpSrcRootPath,self::$_tmpDstRootPath), '', $error);
        $success = str_replace(Array(self::$_tmpSrcRootPath,self::$_tmpDstRootPath), '', $success);
    }




    /***
     * @param $source
     * @param $destination
     * @param $overwriteModus
     * @param $success
     * @param $error
     * @return bool
     */
    private static function singleFileCopy($source,$destination,$overwriteModus,&$success,&$error) {


        if ((is_file($destination)) && (!($overwriteModus==XAPP_XFILE_OVERWRITE_ALL)))
            if ($overwriteModus==XAPP_XFILE_OVERWRITE_NONE) {
                $error[]= XAPP_TEXT_FORMATTED('FILE_EXISTS').": ".$destination;
                return false;
            } elseif ($overwriteModus==XAPP_XFILE_OVERWRITE_IF_SIZE_DIFFERS) {
                if (@filesize($source)==@filesize($destination)) {
                    $error[] = XAPP_TEXT_FORMATTED('FILES_NOT_COPIED_SAME_SIZE',Array($source,$destination));
                    return false;
                }
            } elseif ($overwriteModus==XAPP_XFILE_OVERWRITE_IF_NEWER) {
                if (@filemtime($source)<=@filemtime($destination)) {
                    $error[] = XAPP_TEXT_FORMATTED('FILES_NOT_COPIED_NOT_NEWER',Array($source,$destination));
                    return false;
                }
            }
        if (@copy($source,$destination)===FALSE) {
            $error[] = XAPP_TEXT_FORMATTED('UNKNOW_ERROR_WHILST_COPY').$destination;
            return false;
        }
        $success[]= XAPP_TEXT_FORMATTED('THE_FILE')." ".$source." ".XAPP_TEXT_FORMATTED('HAS_BEEN_COPIED');
        return true;
    }

    /**
     * @param $file
     * @return array|bool
     */
    public static function get_file_ownership($file){
        if(empty($file) || !file_exists($file)){
            return false;
        }

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


    public static function getFileTime($path){
        if(file_exists($path)){
            $atts     = stat( realpath($path) );
            return $atts[9];
        }
        return 'nodate';
    }

    /***
     * @param $path
     * @return int|string
     */
    public static function get_file_permissions($path){
        $fPerms = @fileperms($path);
        if($fPerms !== false){
            $fPerms = substr(decoct( $fPerms ), 1);
        }else{
            $fPerms = '0000';
        }
        return $fPerms;
    }


    /**
     * @param $path
     * @param $chmodValue
     * @return bool         : change with success
     */
    public static function changeModeFile($path,$chmodValue)
    {
        if(isSet($chmodValue) && $chmodValue != "")
        {
            $chmodValue = octdec(ltrim($chmodValue, "0"));
            return @chmod($path, $chmodValue);
        } else
            return true;
    }
    /**
     * @param $path
     * @param $chmodValue
     * @return bool         : change with success
     */
    protected  function changeModeDirectory($path,$chmodValue)
    {
        if(isSet($chmodValue) && $chmodValue != "")
        {
            $dirMode = octdec(ltrim($chmodValue, "0"));
            if ($dirMode & 0400) $dirMode |= 0100; // User is allowed to read, allow to list the directory
            if ($dirMode & 0040) $dirMode |= 0010; // Group is allowed to read, allow to list the directory
            if ($dirMode & 0004) $dirMode |= 0001; // Other are allowed to read, allow to list the directory
            return @chmod($path,$dirMode);
        } else
            return true;
    }
    /***
     * @param $path
     * @return string
     */
    public static function createEmptyFile($path)
    {
        error_log('create file at : ' . $path);
        if($path=="")
        {
            return XAPP_TEXT('INVALID_FILE_NAME');
        }

        if(file_exists($path))
        {
            return XAPP_TEXT('FILE_EXISTS');
        }
        if(!is_writeable(dirname($path)))
        {

            return XAPP_TEXT_FORMATTED('DIRECTORY_NOT_WRITEABLE',array(dirname($path)),55100);
        }
        $fp=fopen($path,"w");
        if($fp)
        {
            fclose($fp);
            return 'OK';
        }
        else
        {
            return XAPP_TEXT_FORMATTED('COULD_NOT_CREATE_FILE',array(basename($path)),55100);
        }
    }

    /***
     * @param $path
     * @return string
     */
    public static function mkDir($path,$mask=0755,$recursive=true)
    {
        if($path=="")
        {
            return XAPP_TEXT('INVALID_FILE_NAME');
        }

        if(file_exists($path))
        {
            return XAPP_TEXT_FORMATTED('DIRECTORY_EXISTS',array(dirname($path)),55100);
        }

        if(!is_writeable(dirname($path)) && $recursive===false)
        {

            return XAPP_TEXT_FORMATTED('DIRECTORY_NOT_WRITEABLE',array(dirname($path)),55100);
        }

        $res = @mkdir($path,$mask,$recursive);

        if(!is_dir($path) || $res==false)
        {
            return XAPP_TEXT_FORMATTED('COULD_NOT_CREATE_DIRECTORY',array(dirname($path)),55100);
        }

        return true;
    }

    /**
     * Format to human readable size
     * @param $bytes
     * @return string
     */
    public static function formatSizeUnits($bytes)
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

}