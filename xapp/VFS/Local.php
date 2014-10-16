<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

xapp_import('xapp.VFS.Base');
xapp_import('xapp.VFS.Interface.Access');
xapp_import('xapp.Utils.Strings');
xapp_import('xapp.Utils.SystemTextEncoding');
xapp_import('xapp.File.FileException');

/*
spl_autoload_register(function($class) {

    if (!substr($class, 0, 17) === 'League\\Flysystem') {
        return;
    }

    $location = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
    if (is_file($location)) {
        require_once($location);
    }else{
        error_log('not an location : ' . $location);
    }
});

use League\Flysystem\Filesystem as Filesystem;
use League\Flysystem\Adapter\Ftp as Adapter;
*/
/***
 * Class XApp_VFS_Local implements the access interface for a local file system
 */
class XApp_VFS_Local extends XApp_VFS_Base implements Xapp_VFS_Interface_Access
{

    ////////////////////////////////////////////////////////////////////////////
    //
    //  Xapp_VFS_Interface_Access implementation
    //
    ////////////////////////////////////////////////////////////////////////////

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
	/**
	 * @param $fullPath
	 * @return stdClass
	 */
	protected  function fileToStruct($mount,$root,$fullPath) {
		$fullPath = str_replace('./','',$fullPath);
		$fullPath = str_replace('..','',$fullPath);
		$fileInfo           = new stdClass();
		$options= Array(
			XApp_File_Utils::OPTION_DIR_LIST_FIELDS=>
				XAPP_XFILE_SHOW_SIZE|
				XAPP_XFILE_SHOW_PERMISSIONS|
				XAPP_XFILE_SHOW_ISREADONLY|
				XAPP_XFILE_SHOW_ISDIR|
				XAPP_XFILE_SHOW_OWNER|
				XAPP_XFILE_SHOW_TIME|
				XAPP_XFILE_SHOW_MIME
		);

		self::add_ls_file_information($fullPath,$fileInfo,$options[XApp_File_Utils::OPTION_DIR_LIST_FIELDS]);

		$fileInfo->name     = basename($fullPath);

		$fileInfo->mount       = $mount;
		$fileInfo->path       = str_replace($root, "",$fullPath);
		$fileInfo->pathInStore= str_replace($root, "",dirname($fullPath));

		return $fileInfo;
	}

	/***
	 * Simple search
	 * @param $searchConf
	 * @return array|null
	 */
	public function find($mount,$searchConf){

		if(!class_exists('Xapp_File_Find')){
			xapp_import('xapp.VFS.Find');
		}
		$this->safeIniSet('memory_limit', '128M');
		@set_time_limit( 10 );

		$conf = json_decode($searchConf,true);
		if(empty ($conf) || !is_array($conf)){
			return null;
		}
		$root=$this->toRealPath($mount);
		$searchIn=XApp_Path_Utils::normalizePath($conf['searchIn'],false,false);
		$searchIn=$root.DIRECTORY_SEPARATOR .$searchIn . DIRECTORY_SEPARATOR;
		$conf['searchIn']=$searchIn;
		$finder = new Xapp_File_Find();
		$res = $finder->find($conf);
		$results = array();
		if($res && count($res)>0){
			foreach($res as $item){
				$fileInfo = $this->fileToStruct($mount,$root,$item);
				$fileInfo->showPath=true;
				$results[]=$fileInfo;
			}
		}
		return $results;
	}

	/***
	 * @param $filePath
	 * @param $filename_new
	 * @param null $dest
	 * @param $errors
	 */

	public function compress($mount,$selection, $type='zip',&$errors)
	{
		$to = '';
		$firstItem = $selection[0];
		$firstItem = XApp_Path_Utils::normalizePath($firstItem,false,false);
		require_once(realpath(dirname(__FILE__))."/Archive/archive.php");
		$this->safeIniSet('memory_limit','128M');
		@set_time_limit( 0 );
		$archive = new xFileArchive();
		$zipSelection = array();

		$root=$this->toRealPath($mount);

		foreach ($selection as $selectedFile)
		{
			$selectedFile = XApp_Path_Utils::normalizePath($selectedFile,false,false);
			$selectedFileFull  = $root . DIRECTORY_SEPARATOR .$selectedFile;
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
			$to =$root.DIRECTORY_SEPARATOR.$firstItem.'.zip';
		}
		$archive->create( $to, $zipSelection,'zip','',$root,true);
		return $to;
	}
    /***
     * @param $filePath
     * @param $filename_new
     * @param null $dest
     * @param $errors
     */

    public function rename($mount,$filePath, $filename_new, $dest = null,&$errors)
    {
        $filename_new=XApp_Path_Utils::sanitizeEx(XApp_SystemTextEncoding::magicDequote($filename_new), XApp_Path_Utils::SANITIZE_HTML_STRICT);
        $filename_new= substr($filename_new, 0, xapp_get_option(self::NODENAME_MAX_LENGTH,$this));
        $old=$this->toRealPath($mount . DIRECTORY_SEPARATOR . $filePath);

        if(!is_writable($old))
        {
            $errors[]=XAPP_TEXT_FORMATTED('FILE_NOT_WRITEABLE',array($old),55100);
            return;
        }
        if($dest == null)
            $new=dirname($old)."/".$filename_new;
        else{
            $new = $this->toRealPath($mount . DIRECTORY_SEPARATOR . $dest);
        }



        if($filename_new=="" && $dest == null)
        {
            $errors[]=XAPP_TEXT_FORMATTED('DIRECTORY_NOT_WRITEABLE',array($old),55100);
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
     * method set will write content into a file
     * @param $mount
     * @param $relativePath
     * @param $content
     * @return mixed|void
     */
    public function set($mount,$relativePath,$content){

        return XApp_File_Utils::set(XApp_Path_Utils::securePath(self::toAbsolutePath($mount) . DIRECTORY_SEPARATOR . $relativePath),$content);
    }

    /***
     * @param $mount
     * @param $relativePath
     * @return null|string
     */
    function mkFile($mount, $relativePath){

        return XApp_File_Utils::createEmptyFile(XApp_Path_Utils::securePath(self::toAbsolutePath($mount) . DIRECTORY_SEPARATOR . $relativePath));
    }

    /***
     * @param $mount
     * @param $relativePath
     * @return null|string
     */
    function mkDir($mount, $relativePath){

        return XApp_File_Utils::mkDir(XApp_Path_Utils::securePath(self::toAbsolutePath($mount) . DIRECTORY_SEPARATOR . $relativePath));
    }

    /***
     * Method get returns the file's content, or sends it via echo
     * @param $mount
     * @param $relativePath
     * @param bool $attachment
     * @param array $options
     * @return mixed|string
     */
    public function get($mount,$relativePath,$attachment=false,$options=Array()){
        $basePath=self::toAbsolutePath($mount);
        $options=array_merge($options,Array(
            XApp_File_Utils::OPTION_AS_ATTACHMENT=>$attachment
        ));
        return XApp_File_Utils::get($basePath,$relativePath,$options);
    }
    /***
     * ls, directory listing, '/' means all mounted directories are visible
     *
     * output format for an item :
     *
     * item['isReadOnly]=
     * item['isDirectory]=
     * item['owner']=
     * item['mime']=
     * item['size']=
     * item['permissions']=
     *
     * Look in src/server/xfile/File or src/server/stores/cbtree/cbTreeFileStoreStandalone.
     * There are many methods about owner,permissions,.... Copy them into File/Utils
     *
     * options['fields'] = isReadOnly|mime|owner,... specifies the fields we want in the output!
     * Define those fields as string constants in File/Utils.php for now.
     *
     *
     *
     * @param string $path
     * @param $recursive
     * @param $options
     * @return array
     */
    public function ls($path='/',$recursive=false,$options=Array()) {

        xapp_import('xapp.Xapp.Hook');
	    xapp_import('xapp.File.Utils');
	    //xapp_clog('enum files : ' . $path);

        // Default options
        if (!isset($options[XApp_File_Utils::OPTION_DIR_LIST_FIELDS])){
            $options[XApp_File_Utils::OPTION_DIR_LIST_FIELDS]=0;
        }

        // Default option : emit new node
        if (!isset($options[XApp_File_Utils::OPTION_EMIT])){
            $options[XApp_File_Utils::OPTION_EMIT]=true;
        }

        //default scan options
        $get_list_options=Array(
            XApp_Directory_Utils::OPTION_CLEAR_PATH=>true,
            XApp_Directory_Utils::OPTION_RECURSIVE=>$recursive

        );
        //overwrite from options
        if (isset($options[XApp_File_Utils::OPTION_DIR_LIST])){
            $get_list_options = $options[XApp_File_Utils::OPTION_DIR_LIST];
        }

        //default include & exclude list
        $inclusionMask=XApp_File_Utils::defaultInclusionPatterns();
        $exclusionMask=XApp_File_Utils::defaultExclusionPatterns();

        //overwrite include from options
        if (isset($options[XApp_Directory_Utils::OPTION_INCLUDE_LIST])){
            $inclusionMask = $options[XApp_Directory_Utils::OPTION_INCLUDE_LIST];
        }

        //overwrite excludes from options
        if (isset($options[XApp_Directory_Utils::OPTION_EXCLUDE_LIST])){
            $exclusionMask = $options[XApp_Directory_Utils::OPTION_EXCLUDE_LIST];
        }

        $list=$this->getFilteredDirList($path,$inclusionMask,$exclusionMask,$get_list_options);
        $ret_list=Array();
        /***
         * Use 'readOnly' from the paths's resource information
         */
        $isReadOnly = null;
        /*
        if (($options[XApp_File_Utils::OPTION_DIR_LIST_FIELDS] & XAPP_XFILE_SHOW_ISREADONLY)==XAPP_XFILE_SHOW_ISREADONLY){
            error_log('is read only');
            $instance = self::instance();
            $resource = $instance->toResource($path);
            if($resource!==null && xapp_property_exists($resource,'readOnly')){
                $isReadOnly=$resource->{XAPP_NODE_FIELD_READ_ONLY};
            }
        }
        */

        foreach($list as $item_name) {
            $item=new stdClass();
            $item->{XAPP_NODE_FIELD_NAME}=$item_name;
            if ($path!="/"){



                //self::add_ls_file_information($this->toRealPath($path.DIRECTORY_SEPARATOR .$item_name),$item,$options[XApp_File_Utils::OPTION_DIR_LIST_FIELDS]);
                self::add_ls_file_information($this->toRealPath($path.$item_name),$item,$options[XApp_File_Utils::OPTION_DIR_LIST_FIELDS]);


                //tell plugins, if anyone doesnt want the item, skip it
                $addItem=Xapp_Hook::trigger(self::EVENT_ON_NODE_ADD,array('item'=>$item));
                if($addItem===false){
                    continue;
                }

                //tell plugins, if anyone doesnt want the item, skip it
                if($options[XApp_File_Utils::OPTION_EMIT]===true){

                    $item=Xapp_Hook::trigger(self::EVENT_ON_NODE_META_CREATED,array('item'=>$item));
                    if($item===null){
                        continue;
                    }
                }

                //now overwrite readOnly flag
                if ($isReadOnly!=null && ($options[XApp_File_Utils::OPTION_DIR_LIST_FIELDS] & XAPP_XFILE_SHOW_ISREADONLY)==XAPP_XFILE_SHOW_ISREADONLY){
                    $item->{XAPP_NODE_FIELD_READ_ONLY}=$isReadOnly;
                }

                //tell plugins
                Xapp_Hook::trigger(self::EVENT_ON_NODE_ADDED,array('item'=>$item));
            }
            $ret_list[]=$item;
        }


        return $ret_list;
    }

    public function delete($selection,$options=Array(),$inclusionMask = Array(),$exclusionMask = Array(),&$error,&$success){

        $log = array();
        foreach ($selection as $selectedFile)
        {
            $itemPath = $this->toRealPath($selectedFile);

            if($selectedFile == "" || $selectedFile == DIRECTORY_SEPARATOR)
            {
                $log[] = XAPP_TEXT_FORMATTED('FAILED_TO_DELETE',array(XApp_SystemTextEncoding::toUTF8($selectedFile)));
            }

            $fileToDelete=$this->toRealPath($selectedFile);
            if(!file_exists($fileToDelete))
            {
                $log[] = XAPP_TEXT_FORMATTED('FAILED_TO_DELETE',array(XApp_SystemTextEncoding::toUTF8($selectedFile)));
                continue;
            }

            if(is_file($fileToDelete)){
                $this->deleteFile($selectedFile,$options,$error,$success);
            }elseif (is_dir($fileToDelete)){
                $this->deleteDirectory($selectedFile,$options,$inclusionMask,$exclusionMask,$error,$success);
            }
        }
        return $error;
    }

    /**
     *
     * Copies $srcDir into $dstDirectory across multiple mount points
     *
     * @param $srcDir : expects sanitized absolute directory
     * @param $dstDirectory : expects sanitized absolute directory, if it doesn't exists, create it!
     * @param array $options : [recursive (true/false) default true, timeout (seconds) default 60, overwriteModus : XAPP_XFILE_OVERWRITE_NONE | XAPP_XFILE_OVERWRITE_ALL | XAPP_XFILE_OVERWRITE_IF_SIZE_DIFFERS
     * @param array|string $inclusionMask : null means all, if its a string : it must compatible to a scandir query, if its a string its a regular expression
     * @param array|string $exclusionMask : null means all, otherwise it must compatible to a scandir query,if its a string its a regular expression
     * @param $error : a pointer to an array reference, please track all errors and don't abort! Check __copyOrMoveFile below how to write the error messages right!
     * @param $success : track all copied items here
     */
    public function move($selection,$dst,$options=Array(),$inclusionMask = Array(),$exclusionMask = Array(),&$error,&$success,$mode){

        $dstDirectory=$this->toRealPath($dst);
        if(file_exists($dstDirectory) &&   !is_writable($dstDirectory))
        {
            throw new Xapp_XFile_Exception(XAPP_TEXT_FORMATTED('DIRECTORY_NOT_WRITEABLE',array($dstDirectory),55100));
        }

        foreach ($selection as $selectedFile)
        {

            $itemPath = $this->toRealPath($selectedFile);

            if(is_dir($itemPath)){


                $dstFile = $dstDirectory.DIRECTORY_SEPARATOR.basename($itemPath);

                XApp_File_Utils::moveDirectoryEx(
                    XApp_Directory_Utils::normalizePath($itemPath,false),
                    XApp_Directory_Utils::normalizePath($dstFile,false),
                    Array(XApp_File_Utils::OPTION_RECURSIVE=>true,
                        XApp_File_Utils::OPTION_CONFLICT_MODUS=>$mode),
                    $inclusionMask,
                    $exclusionMask,
                    $error,
                    $success
                );
            }else if(is_file($itemPath)){

                $destFile = $dstDirectory.DIRECTORY_SEPARATOR.basename($itemPath);
                if(!is_readable($itemPath)){
                    $error[] = XAPP_TEXT_FORMATTED('CAN_NOT_READ_FILE',array(basename($itemPath)));
                    continue;
                }

                // auto rename file
                if(file_exists($destFile)){
                    $base = basename($destFile);
                    $ext='';
                    $dotPos = strrpos($base, ".");
                    if($dotPos>-1){
                        $radic = substr($base, 0, $dotPos);
                        $ext = substr($base, $dotPos);
                    }

                    $i = 1;
                    $newName = $base;
                    while (file_exists($dstDirectory."/".$newName)) {
                        $suffix = "-$i";
                        if(isSet($radic)) $newName = $radic . $suffix . $ext;
                        else $newName = $base.$suffix;
                        $i++;
                    }
                    $destFile = $dstDirectory."/".$newName;
                }

                if(file_exists($destFile)){
                    unlink($destFile);
                }else{

                }

                $res = rename($itemPath, $destFile);

                $success[] = XAPP_TEXT('THE_FILE')." ".XApp_SystemTextEncoding::toUTF8(basename($itemPath))." ".XAPP_TEXT('HAS_BEEN_MOVED')." ".XApp_SystemTextEncoding::toUTF8($dst);
            }
        }
        return $error;
    }

    /**
     *
     * Copies $srcDir into $dstDirectory across multiple mount points
     *
     * @param $srcDir : expects sanitized absolute directory
     * @param $dstDirectory : expects sanitized absolute directory, if it doesn't exists, create it!
     * @param array $options : [recursive (true/false) default true, timeout (seconds) default 60, overwriteModus : XAPP_XFILE_OVERWRITE_NONE | XAPP_XFILE_OVERWRITE_ALL | XAPP_XFILE_OVERWRITE_IF_SIZE_DIFFERS
     * @param array|string $inclusionMask : null means all, if its a string : it must compatible to a scandir query, if its a string its a regular expression
     * @param array|string $exclusionMask : null means all, otherwise it must compatible to a scandir query,if its a string its a regular expression
     * @param $error : a pointer to an array reference, please track all errors and don't abort! Check __copyOrMoveFile below how to write the error messages right!
     * @param $success : track all copied items here
     */
    public function copy($selection,$dst,$options=Array(),$inclusionMask = Array(),$exclusionMask = Array(),&$error,&$success,$mode){

	    if($this->isRemoteOperation($selection[0],$dst)){}

        $dstDirectory=$this->toRealPath($dst);
        if(file_exists($dstDirectory) &&   !is_writable($dstDirectory))
        {
            throw new Xapp_XFile_Exception(XAPP_TEXT_FORMATTED('DIRECTORY_NOT_WRITEABLE',array($dstDirectory),55100));
        }

        foreach ($selection as $selectedFile)
        {

            $itemPath = $this->toRealPath($selectedFile);

            if(is_dir($itemPath)){

                $dstFile = $dstDirectory.DIRECTORY_SEPARATOR.basename($itemPath);

                XApp_File_Utils::copyDirectory(
                    XApp_Directory_Utils::normalizePath($itemPath,false),
                    XApp_Directory_Utils::normalizePath($dstFile,false),
                    Array(XApp_File_Utils::OPTION_RECURSIVE=>true,
                        XApp_File_Utils::OPTION_CONFLICT_MODUS=>$mode),
                    $inclusionMask,
                    $exclusionMask,
                    $error,
                    $success
                );
            }else if(is_file($itemPath)){

                $destFile = $dstDirectory.DIRECTORY_SEPARATOR.basename($itemPath);
                if(!is_readable($itemPath)){
                    $error[] = XAPP_TEXT_FORMATTED('CAN_NOT_READ_FILE',array(basename($itemPath)));
                    continue;
                }

                // auto rename file
                if(file_exists($destFile)){
                    $base = basename($destFile);
                    $ext='';
                    $dotPos = strrpos($base, ".");
                    if($dotPos>-1){
                        $radic = substr($base, 0, $dotPos);
                        $ext = substr($base, $dotPos);
                    }

                    $i = 1;
                    $newName = $base;
                    while (file_exists($dstDirectory."/".$newName)) {
                        $suffix = "-$i";
                        if(isSet($radic)) $newName = $radic . $suffix . $ext;
                        else $newName = $base.$suffix;
                        $i++;
                    }
                    $destFile = $dstDirectory."/".$newName;
                }

                try{
                    copy($itemPath, $destFile);
                }catch (Exception $e){
                    $error[] = $e->getMessage();
                    return $error;
                }
                $success[] = XAPP_TEXT('THE_FILE')." ".XApp_SystemTextEncoding::toUTF8(basename($itemPath))." ".XAPP_TEXT('HAS_BEEN_COPIED')." ".XApp_SystemTextEncoding::toUTF8($dst);
            }
        }
        //xapp_cdump('success',$success);
        /*XApp_File_Utils::copyDirectoryEx($srcDir,$dstDirectory,$options,$inclusionMask,$exclusionMask,$error,$success);*/
        return $error;
    }

    /**
     *
     * Copies $srcDir into $dstDirectory across multiple mount points
     *
     * @param $srcDir : expects sanitized absolute directory
     * @param $dstDirectory : expects sanitized absolute directory, if it doesn't exists, create it!
     * @param array $options : [recursive (true/false) default true, timeout (seconds) default 60, overwriteModus : XAPP_XFILE_OVERWRITE_NONE | XAPP_XFILE_OVERWRITE_ALL | XAPP_XFILE_OVERWRITE_IF_SIZE_DIFFERS
     * @param array|string $inclusionMask : null means all, if its a string : it must compatible to a scandir query, if its a string its a regular expression
     * @param array|string $exclusionMask : null means all, otherwise it must compatible to a scandir query,if its a string its a regular expression
     * @param $error : a pointer to an array reference, please track all errors and don't abort! Check __copyOrMoveFile below how to write the error messages right!
     * @param $success : track all copied items here
     */
    public function copyDirectory($srcDir,$dstDirectory,$options=Array(),$inclusionMask = Array(),$exclusionMask = Array(),&$error,&$success){
        if (($srcDir=="/") || ($dstDirectory=="/")) {
            $error[]=XAPP_TEXT_FORMATTED("CAN_NOT_COPY_MOUNT_POINTS");
        } else {
            $srcDir=$this->toRealPath($srcDir);
            $dstDirectory=$this->toRealPath($dstDirectory);
            XApp_File_Utils::copyDirectoryEx($srcDir,$dstDirectory,$options,$inclusionMask,$exclusionMask,$error,$success);
        }
    }

    /**
     * @param $path
     * @param array $options
     * @param array $inclusionMask
     * @param array $exclusionMask
     * @param $error
     * @param $success
     * @return mixed|void
     */
    public function deleteDirectory($path,$options=Array(),$inclusionMask = Array(),$exclusionMask = Array(),&$error,&$success) {
        if ($path=="/") {
            $error[]=XAPP_TEXT_FORMATTED("DIRECTORY_NOT_WRITEABLE",Array($path));
        } else {
            $path=$this->toRealPath($path);
            XApp_File_Utils::deleteDirectoryEx($path,$options,$inclusionMask,$exclusionMask,$error,$success);
        }
    }

    /**
     * @param $path
     * @param array $options
     * @param $error
     * @param $success
     */
    public function deleteFile($path,$options=Array(),&$error,&$success){
        if ($path=="/") {
            $error[]=XAPP_TEXT_FORMATTED("DIRECTORY_NOT_WRITEABLE",Array($path));
        } else {
            XApp_File_Utils::deleteFile($this->toRealPath($path),$options,$error,$success);
        }
    }
    ////////////////////////////////////////////////////////////////////////////
    //
    //  Utils
    //
    ////////////////////////////////////////////////////////////////////////////

    /***
     * Node meta completion, fields are evaluated on demand
     * @param $filepath
     * @param $item
     * @param $field_options
     */
    public static function add_ls_file_information($filepath,&$item,$field_options) {


        //sanity check
        if(empty($filepath)||!file_exists($filepath)){
            /*throw new Exception('add_ls_file_information : failed because \'filepath=\'' . $filepath . ' doesnt exists or is not a path ');*/
	        $item->{'read'}=false;
	        $item->{'write'}=false;
	        $item->{XAPP_NODE_FIELD_IS_DIRECTORY}=false;
	        return;
        }
        // show permissions
        if (($field_options&XAPP_XFILE_SHOW_PERMISSIONS)==XAPP_XFILE_SHOW_PERMISSIONS){
            $item->{XAPP_NODE_FIELD_PERMISSIONS}=XApp_File_Utils::get_file_permissions($filepath);
	        $item->{'read'}=is_readable($filepath);
	        $item->{'write'}  = is_writeable($filepath);
        }

        // show owner
        if (($field_options&XAPP_XFILE_SHOW_OWNER)==XAPP_XFILE_SHOW_OWNER){
            $item->{XAPP_NODE_FIELD_OWNER}=XApp_File_Utils::get_file_ownership($filepath);

        }

        // force read only
        if (($field_options&XAPP_XFILE_SHOW_ISREADONLY)==XAPP_XFILE_SHOW_ISREADONLY){
            $item->{XAPP_NODE_FIELD_READ_ONLY}=!is_writable($filepath);
        }

        // show is directory
        if (($field_options&XAPP_XFILE_SHOW_ISDIR)==XAPP_XFILE_SHOW_ISDIR){

            $item->{XAPP_NODE_FIELD_IS_DIRECTORY}=is_dir($filepath);
        }

        // show size
        if (($field_options&XAPP_XFILE_SHOW_SIZE)==XAPP_XFILE_SHOW_SIZE) {
            $file_size=filesize($filepath);
            $item->{XAPP_NODE_FIELD_SIZE}=($file_size?number_format($file_size/1024,2)."Kb":"");
        }

        // show mime
        if (($field_options&XAPP_XFILE_SHOW_MIME)==XAPP_XFILE_SHOW_MIME){
            $item->{XAPP_NODE_FIELD_MIME}=XApp_File_Utils::getMime($filepath);
        }

        // show time
        if (($field_options & XAPP_XFILE_SHOW_TIME)==XAPP_XFILE_SHOW_TIME){
            $item->{XAPP_NODE_FIELD_TIME}=XApp_File_Utils::getFileTime($filepath);
        }
    }

    /**
     * method ls returns the directory listing, '/' means it will include the directory names
     * of all enabled and valid mounted directories.
     *
     * This is however a wrapper for Directory::Utils::getFilteredDirList.
     * You will implement this in a way that copyDirectory,moveDirectory can work with mounted
     * resources.
     */
    public function getFilteredDirList($path,$inclusionMask = Array(),$exclusionMask = Array(),$options=Array()){

        if ($path=="/") {   // Return all mounted directories
            $all_file_proxys=$this->getResourcesByType(XAPP_RESOURCE_TYPE_FILE_PROXY);
            $retlist=Array();
            $clear_path=((isset($options[XApp_Directory_Utils::OPTION_CLEAR_PATH])) && ($options[XApp_Directory_Utils::OPTION_CLEAR_PATH]));
            foreach($all_file_proxys as $f_proxy){
                $retlist[]=($clear_path?"":"/").$f_proxy->name;
            }
            return $retlist;
        } else {            // Call getFilteredDirList from XApp_Directory_Utils
            $path=$this->toRealPath($path);
            if(!file_exists($path)){
                throw new Exception('path doesn exists');
            }
            $retlist = XApp_Directory_Utils::getFilteredDirList($path,$inclusionMask,$exclusionMask,$options);
            return $retlist;
        }
    }

    /**
     *  Convert VFS path into real local filesystem path
     *
     * @param $path     :   expected path /mount_point/relative_path/...
     * @return string   :   real local filesystem path
     */
    public function toRealPath($path) {
        if (substr($path,0,1)=="/") $path=substr($path,1);
        $split_path=explode("/",$path);
        $mount=reset($split_path);
        $relative=implode(DIRECTORY_SEPARATOR,array_slice($split_path,1));
        return $this->toAbsolutePath($mount).$relative;
    }

    /**
     *  Convert VFS path into xapp resources item
     *
     * @param $path         :   expected path /mount_point/relative_path/...
     * @param $enabledOnly  :   return only a resources which is enabled
     * @return string       :   xapp-resource
     */
    public function toResource($path,$enabledOnly=true) {
        if (substr($path,0,1)=="/") $path=substr($path,1);
        $split_path=explode("/",$path);
        $mount=reset($split_path);
        return $this->hasMount($mount,$enabledOnly);
    }

    /**
     * method that will return the absolute path of a mounted resource
     * @param $name :   mount point name
     * @return mixed|null
     */
    public function toAbsolutePath($name){

        $name=str_replace('%','',$name);
        $resource = $this->getResource($name,true);
        if($resource){
            $resource = $this->resolveResource($resource);
        }else{
            /*xapp_dump($this);*/
            error_log('couldnt resolve resource : ' .$name);
            return null;
        }

        if(xapp_property_exists($resource,XAPP_RESOURCE_PATH_ABSOLUTE)){
            return xapp_property_get($resource,XAPP_RESOURCE_PATH_ABSOLUTE);
        }

        return null;
    }

    /**
     * creates singleton instance for this class passing options to constructor
     *
     * @error 11102
     * @param null|mixed $options expects valid option object
     * @return null|XApp_VFS_Local
     */
    public static function instance($options = null)
    {
        if(self::$_instance === null)
        {
            self::$_instance = new self($options);
        }
        return self::$_instance;
    }

    /***
     * Factory method, expects an array of items :
     *
        $vfsItems[]=array(
            'name'=>'root',     //name of the VFS
            'path'=>$root       //resolved absolute path
        );

     * @param $items
     * @return null|XApp_VFS_Local
     */
    public static function factory($items){

        $head = array(
            "items"=>array(),
            "class"=>'cmx.types.Resources'
        );

        $absoluteVars = array();

        foreach($items as $item){

            $head['items'][]=array(

                "class"=>'cmx.types.Resource',
                'path'=>'%'.$item['name'].'%',
                'enabled'=>true,
                'type'=>'FILE_PROXY',
                'name'=>$item['name']
            );
            $absoluteVars[$item['name']]=$item['path'];
        }
        $vfsOptions = array(
            XApp_VFS_Base::ABSOLUTE_VARIABLES=>$absoluteVars,
            XApp_VFS_Base::RELATIVE_VARIABLES=>array(),
            XApp_VFS_Base::RESOURCES_DATA => (object)$head
        );

        $vfs = self::instance($vfsOptions);
        return $vfs;
    }
	public function isRemoteFS(){
		return false;
	}
}
