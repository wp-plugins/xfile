<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package XApp\Directory
 */

class XApp_Directory_Utils
{
    const OPTION_RECURSIVE='recursive';
    const OPTION_ONLY_DIRS='only_dirs';
    const OPTION_ONLY_FILES='only_files';
    const OPTION_CLEAR_PATH='clear_path';
    const OPTION_INCLUDE_LIST="includeList";
    const OPTION_EXCLUDE_LIST="excludeList";

    /**
     * @param $path                 : expects sanitized absolute directory
     * @param array $inclusionMask  : null means all, if its a string : it must compatible to a scandir query, if its a string its a regular expression
     * @param array $exclusionMask  : null means all, if its a string : it must compatible to a scandir query, if its a string its a regular expression
     * @param array $options        : options
     * @return array|bool           : filtered list, or false if error
     */
    public static function getFilteredDirList($path,$inclusionMask = Array(),$exclusionMask = Array(),$options=Array()) {
        // Defaults
        if (!isset($options[self::OPTION_RECURSIVE])) $options[self::OPTION_RECURSIVE]=false;
        if (!isset($options[self::OPTION_ONLY_DIRS])) $options[self::OPTION_ONLY_DIRS]=false;
        if (!isset($options[self::OPTION_ONLY_FILES])) $options[self::OPTION_ONLY_FILES]=false;
        if (!isset($options[self::OPTION_CLEAR_PATH])) $options[self::OPTION_CLEAR_PATH]=false;

        $list=self::scanDirForList($path,$inclusionMask);
        if ($list===FALSE)
            return false;
        else {
            // If we have exclusionMask, apply it
            if ((Count($exclusionMask)) || (is_string($exclusionMask))) {
                $exclude_list=self::scanDirForList($path,$exclusionMask);
                if ($exclude_list!==FALSE) $list=array_diff($list,$exclude_list);
            }

            $to_remove=Array();
            if (($options[self::OPTION_RECURSIVE]) || ($options[self::OPTION_ONLY_FILES]) || ($options[self::OPTION_ONLY_DIRS])) {
                foreach($list as $n=>$direntry) {
                    if (is_dir($direntry)) {
                        if ($options[self::OPTION_ONLY_FILES])
                            $to_remove[]=$list[$n];
                         if ($options[self::OPTION_RECURSIVE]) {
                             // Don't clean path for recursive calls
                             $options_to_passed=$options;
                             $options_to_passed[self::OPTION_CLEAR_PATH]=false;

                             $list=array_merge($list,self::getFilteredDirList($direntry,$inclusionMask,$exclusionMask,$options_to_passed));
                         }
                    } else {
                        if ($options[self::OPTION_ONLY_DIRS])
                            $to_remove[]=$list[$n];
                    }
                }
            }

            if (Count($to_remove)>0)
                $list=array_diff($list,$to_remove);

            if ($options[self::OPTION_CLEAR_PATH])
                $list=str_replace(self::normalizePath($path)."/",'',$list);

            return $list;
        }
    }

    /**
     *
     *  Returns a list of files into a given path, filtered by a mask
     *
     * @param $srcDir               : sanitized path
     * @param array|string $Mask    : Mask, array or string. If array, it should be compatible with grep. If string, it should be a reg. expression
     * @return array|bool           : returns the list if success, FALSE if not
     */
    public static function scanDirForList($srcDir,$Mask=Array()) {
        $error_find=false;

        $listmask=Array("*",".*");
        if (is_array($Mask)) {
            if (Count($Mask)>0)
                $listmask=$Mask;
            $Mask="";
        }
        $groblist=Array();
        foreach($listmask as $globqry) {
            $qryres=@glob($srcDir.DIRECTORY_SEPARATOR.$globqry);
            if ($qryres===FALSE)
                $error_find=true;
            else
                $groblist=array_merge($groblist,$qryres);
        }

        $groblist=array_diff($groblist,Array($srcDir.DIRECTORY_SEPARATOR.".",$srcDir.DIRECTORY_SEPARATOR.".."));

        if ($error_find)
            return FALSE;
        else {
            if ($Mask!='')
                $groblist=preg_grep($Mask,$groblist);

            // Normalize list
            foreach($groblist as $n=>$path)
                $groblist[$n]=self::normalizePath($path); // deal with windows C:/

            return $groblist;
        }


    }

    /**
     *
     *  Creates a ZIP file $dest_file with the contents of $source_dir
     *
     * @param $source_dir       :   path to be added at the ZIP file
     * @param $dest_file        :   file to be created
     * @param $inclusionMask    :   null means all, if its a string : it must compatible to a scandir query, if its a string its a regular expression
     * @param $exclusionMask    :   null means all, if its a string : it must compatible to a scandir query, if its a string its a regular expression
     * @param $error            :   reference to array of error messages
     * @param $success          :   reference to array of success messages
     */
    public static function zipDir($source_dir,$dest_file,$inclusionMask = Array(),$exclusionMask = Array(),&$error,&$success) {
        ini_set('memory_limit', '128M');
        $zipSelection=self::getFilteredDirList($source_dir, $inclusionMask,$exclusionMask,Array(self::OPTION_RECURSIVE=>true));
        $archive = new ZipArchive;
        $error_count=Count($error);

        $source_dir=self::normalizePath($source_dir,true);

        if ($archive->open($dest_file, ZIPARCHIVE::CREATE)===TRUE) {
            foreach($zipSelection as $path) {
                set_time_limit(400);    // reset counter and set timeout 400s
                $dest_path=substr($path,strlen($source_dir));
                if (is_dir($path)) {
                    if ($archive->addEmptyDir($dest_path)===FALSE) {
                        $error[]=XAPP_TEXT_FORMATTED('COULD_NOT_CREATE_FILE',array($dest_path)).
                                XAPP_TEXT_FORMATTED('INTO_ZIP',array($dest_file));
                    }
                } else {
                    if ($archive->addFile($path,$dest_path)===FALSE) {
                        $error[]=XAPP_TEXT_FORMATTED('COULD_NOT_CREATE_FILE',array($dest_path)).
                            XAPP_TEXT_FORMATTED('INTO_ZIP',array($dest_file));
                    } else {
                        $success[]=XAPP_TEXT_FORMATTED('ZIP_FILE_SUCCESS',array($path));
                    }
                }
            }
            if ($archive->close()===TRUE) {
                if ($error_count==Count($error))
                    $success[]=XAPP_TEXT_FORMATTED('ZIP_SUCCESS',array($dest_file));
                else
                    $success[]=XAPP_TEXT_FORMATTED('ZIP_RELATIVE_SUCCESS',array($dest_file));
            }
        } else {
            $error[]=XAPP_TEXT_FORMATTED('COULD_NOT_CREATE_FILE',array($dest_file));

        }
    }
    /**
     * check if the requested path is valid
     *
     * @param string $path
     * @return bool
     */
    public static function isValidPath($path) {
        $path = self::normalizePath($path);
        if (!$path || $path[0] !== '/') {
            $path = '/' . $path;
        }
        if (strstr($path, '/../') || strrchr($path, '/') === '/..') {
            return false;
        }
        return true;
    }
    /**
     * @brief Fix common problems with a file path
     * @param string $path
     * @param bool $stripTrailingSlash
     * @return string
     */
    public static function normalizePath($path, $stripTrailingSlash = true) {
        if ($path == '') {
            return '/';
        }
        //no windows style slashes
        $path = str_replace('\\', '/', $path);

        //add leading slash (only non-windows systems)
        if (($path[0] !== '/') && ($path[1]!=":")) {
            $path = '/' . $path;
        }

        // remove '/./'
        // ugly, but str_replace() can't replace them all in one go
        // as the replacement itself is part of the search string
        // which will only be found during the next iteration
        while (strpos($path, '/./') !== false) {
            $path = str_replace('/./', '/', $path);
        }
        // remove sequences of slashes
        $path = preg_replace('#/{2,}#', '/', $path);

        //remove trailing slash
        if ($stripTrailingSlash and strlen($path) > 1 and substr($path, -1, 1) === '/') {
            $path = substr($path, 0, -1);
        }

        // remove trailing '/.'
        if (substr($path, -2) == '/.') {
            $path = substr($path, 0, -2);
        }
        //normalize unicode if possible, @TODO
        return $path;
    }
    private static function isInList($path,$allowed=array()){

        if(is_string($allowed)){
            $allowed = array($allowed);
        }

        if(count($allowed)>0){
            foreach($allowed as $ext){
                //convert to reqex if not

                if($ext==='*'){
                    $ext = '.*?';
                }

                if(!XApp_Utils_Strings::isRegEx($ext)){
                    $ext = '/\.' . $ext .'$/U';
                }

                if (preg_match($ext, $path)) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function isAllowed($path,$allowed=array(),$forbidden=array()){
        xapp_import('xapp.Utils.Strings');
        $isInAllowed = self::isInList($path,$allowed);
        $isInForbidden = self::isInList($path,$forbidden);
        return
            ($isInAllowed===true && $isInForbidden===true)||
            ($isInAllowed===true && $isInForbidden===false);
    }

    /**
     * Modifies a string to remove all non ASCII characters and spaces.
     *
     * @param string $text
     * @return string
     */
    public static function slugify($text)
    {
        // replace non letter or digits or dots by -
        $text = preg_replace('~[^\\pL\d\.]+~u', '-', $text);

        // trim
        $text = trim($text, '-');

        // transliterate
        if (function_exists('iconv')) {
            $text = iconv('utf-8', 'us-ascii//TRANSLIT//IGNORE', $text);
        }

        // lowercase
        $text = strtolower($text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w\.]+~', '', $text);

        // trim ending dots (for security reasons and win compatibility)
        $text = preg_replace('~\.+$~', '', $text);

        if (empty($text)) {
            return uniqid();
        }
        return $text;
    }
}