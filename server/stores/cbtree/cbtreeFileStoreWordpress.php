<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

    error_reporting(E_ERROR);
    ini_set('display_errors', 0);


	// Define the possible HTTP result codes returned by this application.
	define( "HTTP_V_OK",                 200);
	define( "HTTP_V_NO_CONTENT",         204);
	define( "HTTP_V_BAD_REQUEST",        400);
	define( "HTTP_V_UNAUTHORIZED",       401);
	define( "HTTP_V_FORBIDDEN",	         403);
	define( "HTTP_V_NOT_FOUND",          404);
	define( "HTTP_V_METHOD_NOT_ALLOWED", 405);
	define( "HTTP_V_CONFLICT",           409);
	define( "HTTP_V_GONE",               410);
	define( "HTTP_V_SERVER_ERROR",       500);

	$docRoot = '.';

    $docRoot = realpath(dirname(__FILE__).DIRECTORY_SEPARATOR. '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR . '..');
	$relPath = "";
	$method  = null;
	$files   = null;
	$total   = 0;
	$status  = 0;

    $XAPP_COM_PATH=realpath(dirname(__FILE__).DIRECTORY_SEPARATOR. '..' .DIRECTORY_SEPARATOR. '..' .DIRECTORY_SEPARATOR. '..' ) . DIRECTORY_SEPARATOR;
    $XAPP_WP_PATH=realpath(dirname(__FILE__).DIRECTORY_SEPARATOR. '..' .DIRECTORY_SEPARATOR. '..' .DIRECTORY_SEPARATOR. '..' .DIRECTORY_SEPARATOR. '..' .DIRECTORY_SEPARATOR. '..' .DIRECTORY_SEPARATOR. '..'  ) . DIRECTORY_SEPARATOR;



    /***
     *  WP Auth & Option Stack
     */
    require_once( $XAPP_WP_PATH . 'wp-config.php' );
    require_once ($XAPP_WP_PATH . 'wp-load.php');
    include_once ($XAPP_COM_PATH . 'class-recursive-arrayaccess.php');
    include_once ($XAPP_COM_PATH . 'class-wp-session.php');

    $XAPP_WP_SESSION = XApp_WP_Session::get_instance();
    $XAPP_PARAMETERS = null;
    if($XAPP_WP_SESSION==null){
        die('');
    }
    $XAPP_PARAMETERS = $XAPP_WP_SESSION['XAPP_PARAMETERS'];

    if($XAPP_PARAMETERS==null){
        die('');
    }

    $XAPP_FILE_ROOT=$XAPP_PARAMETERS['XAPP_FILE_ROOT'];
    $XAPP_FILE_START_PATH=$XAPP_PARAMETERS['XAPP_FILE_START_PATH'];

    if($XAPP_FILE_ROOT!==null && strlen($XAPP_FILE_ROOT)>2  && $XAPP_FILE_START_PATH!==null && strlen($XAPP_FILE_START_PATH)>2){
        $docRoot = '' . $XAPP_FILE_ROOT;
        $docRoot.=DS.$XAPP_FILE_START_PATH;
    }


    $method = $_SERVER["REQUEST_METHOD"];

	// Check the HTTP method first.
	if (!cgiMethodAllowed($method)) {
		cgiResponse( HTTP_V_METHOD_NOT_ALLOWED, "Method Not Allowed", NULL);
		header("Allow: " . getenv("CBTREE_METHODS"));
		return;
	}

	// Validate the HTTP QUERY-STRING parameters
	$args = getArguments($method, $status);
	if ($args == null ) {
		cgiResponse( HTTP_V_BAD_REQUEST, "Bad Request", "Malformed query arguments." );
		return;
	}

	if ($args->authToken) {
		// Your authentication may go here....
	}

	$rootDir  = str_replace( "\\","/", realPath( $docRoot . "/" . $args->basePath ));
    $fullPath = str_replace( "\\","/", realPath( $rootDir . "/" . $args->path ));

	if ($rootDir && $fullPath) {
		// Make sure the caller isn't backtracking by specifying paths like '../../../'
		if ( strncmp($rootDir, $docRoot, strlen($docRoot)) || strncmp($fullPath, $rootDir, strlen($rootDir)) ) {
            cgiResponse( HTTP_V_FORBIDDEN, "Forbidden", "We're not going there..." );
			return;
		}

		switch($method) {
			case "DELETE":
				$files = deleteFile( $fullPath, $rootDir, $args, $status );
				if ($files) {
					// Compile the final result
					$result         = new stdClass();
					$result->total  = count($files);
					$result->status = $status;
					$result->items  = $files;

					header("Content-Type: text/json");
					print( json_encode($result) );
				} else {
					cgiResponse( $status, "Not Found", null );
				}
				break;

			case "GET":
				$files = getFile( $fullPath, $rootDir, $args, $status );
				if ($files) {
					$total = count($files);
					// Compile the final result
					$result         = new stdClass();
					$result->total  = $total;
					$result->status = $total ? HTTP_V_OK : HTTP_V_NO_CONTENT;
					$result->items  = $files;

					header("Content-Type: text/json");
					print( json_encode($result) );
				} else {
					cgiResponse( $status, "Not Found", null );
				}
				break;

			case "POST":
				$files = renameFile( $fullPath, $rootDir, $args, $status );
				// Compile the final result
				if ($status == HTTP_V_OK) {
					$result         = new stdClass();
					$result->total  = count($files);
					$result->status = $status;
					$result->items  = $files;

					header("Content-Type: text/json");
					print( json_encode($result) );
				} else {
					cgiResponse( $status, "system error.", "Failed to rename file." );
				}
				break;
		}
	}	else {

		cgiResponse( HTTP_V_NOT_FOUND, "Not Found", "Invalid path and/or basePath." );
	}

	/**
	*	cgiMethodAllowed
	*
	*		Returns true if the HTTP method is allowed, that is, supported by this
	*		application. (See the description 'ENVIRONMENT VARIABLE' above).
	*
	*	@param	method				Method name string.
	*
	*	@return		true or false
	**/
	function cgiMethodAllowed( /*string*/ $method ) {
		$allowed = "GET,DELETE,POST,PUT" . getenv("CBTREE_METHODS");
		$methods = explode(",", $allowed);
		$count   = count($methods);

		for ($i = 0;$i<$count; $i++) {
			if ($method == trim($methods[$i])) {
				return true;
			}
		}
		return false;
	}

	/**
	*	cgiResponse
	*
	*		Sends a CGI response back to the caller.
	*
	*	@param	status					HTTP result code
	*	@param	statText				HTTP reason phrase.
	*	@param	infoText				Optional text returned to the caller.
	**/
	function cgiResponse( $status, $statText, $infoText = null) {
		header("Content-Type: text/html");
		header("Status: " . $status . $statText );
		if( $infoText ) {
			print( $infoText );
		}
	}

	/**
	*	fileFilter
	*
	*		Returns true if a file is to be exlcuded (filtered) based on the HTTP query
	*		string parameters such as 'showHiddenFiles', otherwise false.
	*		The current and parent directory entries are excluded by default.
	*
	*	@param	fileInfo
	*	@param	args
	*
	*	@return	true or false
	**/
	function fileFilter( /*object*/$fileInfo, /*object*/$args ) {
		if ( (!$args->showHiddenFiles && $fileInfo->name[0] == ".") ||
				 ($fileInfo->name == ".." || $fileInfo->name == ".") ) {
					return true;
		}
		return false;
	}

    function formatSizeUnits($bytes)
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



    function foo_get_file_ownership($file){
        $stat = stat($file);
        if($stat){
            $group = posix_getgrgid($stat[5]);
            $user = posix_getpwuid($stat[4]);
            return compact('user', 'group');
        }
        else
            return false;
    }

    function _dimensions($path, $mime) {
        clearstatcache();
        return strpos($mime, 'image') === 0 && ($s = @getimagesize($path)) !== false ? $s[0].'x'.$s[1] : false;

    }
    function owner($path){
        //$metaData["file_group"] = @filegroup($ajxpNode->getUrl()) || "unknown";
        return @fileowner($path) || "unknown";
    }
    /**
     * Get the permissions of the file/folder at a give path
     *
     * @param	string	$path	The path of a file/folder
     * @return	string	Filesystem permissions
     * @since	1.5
     */
    function getPermissions($path)
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
    function permissions($path,$isLeaf){
        $fPerms = @fileperms($path);
        if($fPerms !== false){
            $fPerms = substr(decoct( $fPerms ), ($isLeaf?2:1));
        }else{
            $fPerms = '0000';
        }

        return $fPerms;
    }
    function countItems($path){
        return 0;
        $i = 0;
        $dir = $path;
        if ($handle = opendir($dir)) {
            while (($file = readdir($handle)) !== false){
                if (!in_array($file, array('.', '..')))
                    $i++;
            }
        }

        return $i . ' Items';
    }
    /**
	*	fileToStruct
	*
	*		Create a FILE_INFO object
	*
	*	@param	dirPath			Directory path string
	*	@param	rootDir			Root directory
	*	@param	filename		Filename
	*
	*	@return		FILE_INFO object.
	**/
	function fileToStruct( /*string*/$dirPath, /*string*/$rootDir, /*string*/$filename, /*object*/$args ) {
		$fullPath = $dirPath . "/" . $filename;
        $fullPath = str_replace('./','',$fullPath);
        $fullPath = str_replace('..','',$fullPath);
        $atts     = stat( realpath($fullPath) );

		$relPath  = "./" . substr( $fullPath, (strlen($rootDir)+1) );
		$relPath  = trim( str_replace( "\\", "/", $relPath ), "/");

		$fileInfo           = new stdClass();
		$fileInfo->name     = $filename;
		$fileInfo->path     = $relPath;
		$fileInfo->modified = $atts[9];
        $fileInfo->owner = foo_get_file_ownership($fullPath);
        $fileInfo->group = @filegroup($fullPath) || "unknown";
        $fileInfo->read  = is_readable($fullPath);
        $fileInfo->write = is_writable($fullPath);

        if($fileInfo->owner===false){
            $fileInfo->access=false;
        }

        $fileInfo->permissions= permissions($fullPath,is_dir($fullPath)) . ' (' .getPermissions($fullPath) . ')';

		if (is_dir($fullPath)) {
			$fileInfo->directory = true;
			$fileInfo->children	= array();
			$fileInfo->_EX  = false;
			$fileInfo->size = 0;
		} else {
			$fileInfo->size = formatSizeUnits(filesize($fullPath));
            $fileInfo->mime = mime_content_type($fullPath);
            $fileInfo->dimension = _dimensions($fullPath,$fileInfo->mime);

		}
		return $fileInfo;
	}

	/**
	*	getArguments
	*
	*		Returns an ARGS object with all HTTP QUERY-STRING parameters extracted and
	*		decoded. See the description on top for the ABNF notation of the parameter.
	*
	*	@note	All QUERY-STRING parameters are optional, if however a parameter is
	*			specified it MUST comply with the formentioned ABNF format.
	*			For security, invalid formatted parameters are not skipped or ignored,
	*			instead they will result in a HTTP Bad Request status (400).
	*
	*	@param	status			Receives the final result code. (200 or 400)
	*
	*	@return		On success an 'args' object otherwise NULL
	**/
	function getArguments( $method, /*integer*/&$status ) {

		$status	= HTTP_V_BAD_REQUEST;		// Lets assume its a malformed query string
		$_ARGS  = null;

		$args                  = new stdClass();
		$args->authToken       = null;
		$args->basePath        = "";
		$args->deep            = false;
		$args->path            = null;
		$args->showHiddenFiles = false;

		switch ($method) {
			case "DELETE":
				$_ARGS = $_GET;
				if (!array_key_exists("path", $_ARGS)) {
					return null;
				}
				break;

			case "GET":
				$_ARGS = $_GET;

				$args->ignoreCase = false;
				$args->rootDir    = "";

				// Get the 'options' and 'queryOptions' first before processing any other parameters.
				if (array_key_exists("options", $_ARGS)) {
					$options = str_replace("\\\"", "\"", $_ARGS['options']);
					$options = json_decode($options);
					if (is_array($options)) {
						if (array_search("showHiddenFiles", $options) > -1) {
							$args->showHiddenFiles = true;
						}
					}
					else	// options is not an array.
					{

					}
				}
				if (array_key_exists("queryOptions", $_ARGS)) {
					$queryOptions = str_replace("\"\"", "\"", $_ARGS['queryOptions']);
					$queryOptions = json_decode($queryOptions);
					if (is_object($queryOptions)) {
						if (property_exists($queryOptions, "deep")) {
							$args->deep = $queryOptions->deep;
						}
						if (property_exists($queryOptions, "ignoreCase")) {
							$args->ignoreCase = $queryOptions->ignoreCase;
						}
					}
					else	// queryOptions is not an object.
					{
					}
				}
                break;

			case "POST":
				$_ARGS = $_POST;

				$args->newValue = null;

				if( !array_key_exists("newValue", $_ARGS) ||
					!array_key_exists("path", $_ARGS)) {
					return null;
				}
				if (is_string($_ARGS['newValue'])) {
					$args->newValue = trim($_ARGS["newValue"],"\"");
				} else {
					return null;
				}
				break;
		} /* end switch($method) */

		// Get authentication token. There are no restrictions with regards to the content
		// of this object.
		if (array_key_exists("authToken", $_ARGS)) {
			$authToken = str_replace("\"\"", "\"", $_ARGS['authToken']);
			$authToken = json_decode($authToken);
			if ($authToken) {
				$args->authToken = $authToken;
			}
		}
		// Check for a basePath
		$args->basePath = getenv("CBTREE_BASEPATH");
		if (!$args->basePath) {
			if (array_key_exists("basePath", $_ARGS)) {
				$args->basePath = trim($_ARGS['basePath'],"\"");
			}
		}
		if ($args->basePath && !is_string($args->basePath)) {
			return null;
		}

		//	Check if a specific path is specified.
		if (array_key_exists("path", $_ARGS)) {
			if (is_string($_ARGS['path'])) {
				$args->path = realURL(trim($_ARGS['path'],"\""));
			} else {
				return null;
			}
		}
		$args->path = trim( ("./" . $args->path), "/" );

        $status = HTTP_V_OK;		// Return success
		return $args;
	}

	/**
	*	getDirectory
	*
	*		Returns the content of a directory as an array of FILE_INFO objects.
	*
	*	@param	dirPath			Directory path string
	*	@param	rootDir			Root directory
	*	@param	args			HTTP QUERY-STRING arguments decoded.
	*	@param	status			Receives the final result (200, 204 or 404).
	*
	*	@return		An array of FILE_INFO objects or NULL in case no match was found.
	**/
	function getDirectory( /*string*/$dirPath, /*string*/$rootDir, /*object*/$args, /*number*/&$status ) {
		if( ($dirHandle = opendir($dirPath)) ) {
			$files = array();
			$stat	 = 0;
			while($file = readdir($dirHandle)) {
				$fileInfo = fileToStruct( $dirPath, $rootDir, $file, $args );
				if (!fileFilter( $fileInfo, $args )) {
					if (property_exists($fileInfo, "directory") && $args->deep) {
						$subDirPath = $dirPath . "/" . $fileInfo->name;

                        $fileInfo->children = getDirectory( $subDirPath, $rootDir, $args, $stat );
                        $fileInfo->_EX			= true;
					}
					$files[] = $fileInfo;
				}else{

                }
			}
			$status = $files ? HTTP_V_OK : HTTP_V_NO_CONTENT;
			closedir($dirHandle);
			return $files;
		}
		$status = HTTP_V_NOT_FOUND;
		return null;
	}

	/**
	*	getFile
	*
	*		Returns the information for the file specified by parameter fullPath.
	*		If the designated file is a directory the directory content is returned
	*		as the children of the file.
	*
	*	@param	filePath		File path string
	*	@param	rootDir			Root directory
	*	@param	args			HTTP QUERY-STRING arguments decoded.
	*	@param	status			Receives the final result (200, 204 or 404).
	*
	*	@return		An array of 1 FILE_INFO object or NULL in case no match was found.
	**/
	function getFile( /*string*/$filePath, /*string*/$rootDir, /*object*/$args, /*number*/&$status ) {
		if( file_exists( $filePath ) ) {
			$files    = array();
			$uri      = parsePath( $filePath, $rootDir );
			$fileInfo = fileToStruct( $uri->dirPath, $rootDir, $uri->filename, $args );

			if (!fileFilter( $fileInfo, $args )) {
				if (property_exists($fileInfo, "directory")) {

                    $children = getDirectory( $filePath, $rootDir, $args, $status );
                    $fileInfo->children=$children;

                    //$d = print_r($fileInfo->children,true);
                    //error_log($d);


                    /*
                    $backPath = realpath($filePath. DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
                    $uriBack = parsePath( $backPath, $rootDir );
                    $fileInfoBack = fileToStruct( $uriBack->dirPath, $rootDir, $uriBack->filename, $args );
                    if($fileInfoBack){
                        $fileInfoBack->name='..back';
                        $fileInfoBack->path.='/'.uniqid();
                        //$fileInfoBack->_EX=false;


//                        $fileInfoBack->path=$filePath . DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;

                        $newArray = array_merge(array(0=>$fileInfoBack) , $fileInfo->children);
                        $fileInfo->children = $newArray;
                        // $fileInfo=array_unshift($fileInfo->children,$fileInfoBack);
                        error_log('back path for' . $filePath . ' is ' .  $backPath . ' : json : ' . json_encode($newArray));

                    }*/





					//$fileInfo->children = getDirectory( $filePath, $rootDir, $args, $status );
					$fileInfo->_EX			= true;
                    //error_log('file dor' . $filePath);








				}
				// Don't give out details about the root directory.
				if ($filePath === $rootDir) {
					$fileInfo->name = ".";
					$fileInfo->size = 0;
				}
				$files[] = $fileInfo;
                //error_log(json_encode($fileInfo));

			}
			$status = $files ? HTTP_V_OK : HTTP_V_NO_CONTENT;
			return $files;
		}
		$status = HTTP_V_NOT_FOUND;
		return null;
	}

	/**
	*	parsePath
	*
	*		Helper function to normalize and seperate a URI into its components. This
	*		is a simplified implementation as we only extract what may be needed.
	*
	*	@param	fullPath		Full path string
	*	@param	rootDir			Root directory
	*
	*	@return
	**/
	function parsePath ($fullPath, $rootDir) {
		$fullPath = str_replace( "\\", "/", $fullPath );
		$fullPath = realURL( $fullPath );

		$lsegm    = strrpos($fullPath,"/");
		$filename = substr( $fullPath, ($lsegm ? $lsegm + 1 : 0));
		$dirPath  = substr( $fullPath, 0, $lsegm);

		$relPath  = substr( $fullPath, (strlen($rootDir)+1));
		$relPath  = trim( ("./" . $relPath), "/" );

		$uri           = new stdClass();
		$uri->relPath  = $relPath;
		$uri->dirPath  = $dirPath;
		$uri->filename = $filename;

		return $uri;
	}

	/**
	*	realURL
	*
	*		Remove all dot (.) segment according to RFC-3986 $5.2.4
	*
	*	@param	path				Path string
	**/
	function realURL( $path ) {
		$url = "";
		do {
			$p = $path;
			if (!strncmp( $path, "../", 3) || !strncmp($path,"./", 2)) {
				$path = substr($path, strpos($path,"/")+1 );
				continue;
			}
			if (!strncmp( $path, "/./", 3)) {
				$path = "/". substr($path, 3);
				continue;
			}
			if (!strcmp( $path, "/.")) {
				$path = "/";
				continue;
			}
			if (!strncmp( $path, "/../", 4)) {
				$path = "/". substr($path, 4);
				$pos = strrpos($url,"/");
				$url = substr($url, 0, $pos);
				continue;
			}
			if (!strcmp( $path, "/..")) {
				$path = "/";
				$pos = strrpos($url,"/");
				$url = substr($url, 0, $pos);
				continue;
			}
			if (!strcmp( $path, "..") || !strcmp($path,".")) {
				break;
			}
			if($path[0] == '/' ) {
				if ($path[1] != '/') {
					$pos	= strcspn( $path, "/", 1 );
				} else {
					$pos = 1;
				}
			} else {
				$pos	= strcspn( $path, "/" );
			}
			$segm = substr( $path, 0, $pos );
			$path = substr( $path, $pos );
			$url  = $url . $segm;

		} while( $path != $p );
		return str_replace( "//", "/", $url );
	}

	/**
	*	renameFile
	*
	*		Rename a file
	*
	*	@param	fullPath		Full path string (file path)
	*	@param	rootDir			Root directory
	*	@param	args			HTTP QUERY-STRING arguments decoded.
	*	@param	status			Receives the final result (200, 204 or 404).
	*
	*	@return		An array of 1 FILE_INFO object or NULL in case no match was found.
	**/
	function renameFile( $fullPath, $rootDir, $args, &$status ) {
		$status = HTTP_V_OK;

		if( file_exists( $fullPath ) ) {
			$fileList = array();
			$newPath	= realURL($rootDir."/".realURL($args->newValue));
			if (!strncmp($newPath, $rootDir, strlen($rootDir))) {
				if (!file_exists( $newPath )) {
					if (rename( $fullPath, $newPath )) {
						$uri		 = parsePath( $newPath, $rootDir );
						$newFile = fileToStruct( $uri->dirPath, $rootDir, $uri->filename, $args );

						$newFile->oldPath = "./" . substr( $fullPath, (strlen($rootDir)+1));
						$fileList[] = $newFile;
					} else {
						$status = HTTP_V_NOT_FOUND;
					}
				} else {
					$status = HTTP_V_CONFLICT;
				}
			} else {
				$status = HTTP_V_FORBIDDEN;
			}
			return $fileList;
		}
		return null;
	}

?>


