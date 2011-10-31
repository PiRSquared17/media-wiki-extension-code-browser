<?php
/**
 * CodeBrowser
 * This extension allows for browsing of source code within a
 * specified project directory.
 *
 * This code is heavily leveraged from the following sources:
 * Encode Explorer - http://encode-explorer.siineiolekala.net/
 * Extension:Include - http://www.mediawiki.org/wiki/Extension:Include
 *
 * To activate the functionality of this extension include the following in your
 * LocalSettings.php file:
 * require_once("$IP/extensions/CodeBrowser.php");
 *
 * Author: Dan Riedler
 */

/***************************************************************************
 *
*             Encode Explorer
*
*             Author / Autor : Marek Rei (marek ÃƒÆ’Ã‚Â¤t siineiolekala dot net)
*
*             Version / Versioon : 6.3
*
*             Last change / Viimati muudetud: 23.09.2011
*
*             Homepage / Koduleht: encode-explorer.siineiolekala.net
*
*
*             NB!: Comments are in english.
*                  Comments needed for configuring are in both estonian and english.
*                  If you change anything, save with UTF-8! Otherwise you may
*                  encounter problems, especially when displaying images.
*
*             NB!: Kommentaarid on inglise keeles.
*                  Seadistamiseks vajalikud kommentaarid on eesti ja inglise keeles.
*                  Kui midagi muudate, salvestage UTF-8 formaati! Vastasel juhul
*                  vÃƒÆ’Ã‚Âµivad probleemid tekkida, eriti piltide kuvamisega.
*
*   This is free software and it's distributed under GPL Licence.
*
*   Encode Explorer is written in the hopes that it can be useful to people.
*   It has NO WARRANTY and when you use it, the author is not responsible
*   for how it works (or doesn't).
*
*   The icon images are designed by Mark James (http://www.famfamfam.com)
*   and distributed under the Creative Commons Attribution 3.0 License.
*
***************************************************************************/

if(! defined( 'MEDIAWIKI' ) ) {
	echo( "This is an extension to the MediaWiki package and cannot be run standalone.\n" );
	die( -1 );
} 





/***************************************************************************/
/*                                                                         */
/*   Default Configuration                                                 */
/*																		   */
/***************************************************************************/



$wg_codebrowser_config = array();
$_ERROR = "";
$_START_TIME = microtime(TRUE);

//
// GENERAL SETTINGS
//
global $wgScriptPath;
$wg_codebrowser_config['install_path'] = $wgScriptPath."/extensions/CodeBrowser";

// Choose a language. See below in the language section for options.
// Default: $wg_codebrowser_config['lang'] = "en";
//
$wg_codebrowser_config['lang'] = "en";


//
// USER INTERFACE
//

// Will the files be opened in a new window? true/false
// Default: $wg_codebrowser_config['open_in_new_window'] = false;
//
$wg_codebrowser_config['open_in_new_window'] = false;

// How deep in subfolders will the script search for files?
// Set it larger than 0 to display the total used space.
// Default: $wg_codebrowser_config['calculate_space_level'] = 0;
//
$wg_codebrowser_config['calculate_space_level'] = 0;


// Display breadcrumbs (relative path of the location).
// Default: $wg_codebrowser_config['show_path'] = true;
//
$wg_codebrowser_config['show_path'] = true;

// Display the time it took to load the page.
// Default: $wg_codebrowser_config['show_load_time'] = true;
//
$wg_codebrowser_config['show_load_time'] = true;

// The time format for the "last changed" column.
// Default: $wg_codebrowser_config['time_format'] = "d.m.y H:i:s";
//
$wg_codebrowser_config['time_format'] = "d.m.y H:i:s";

// Charset. Use the one that suits for you.
// Default: $wg_codebrowser_config['charset'] = "UTF-8";
//
$wg_codebrowser_config['charset'] = "UTF-8";

//
// PERMISSIONS
//

// The array of folder names that will be hidden from the list.
// Default: $wg_codebrowser_config['hidden_dirs'] = array();
//
$wg_codebrowser_config['hidden_dirs'] = array();

// Filenames will only be shown
//
$wg_codebrowser_config['supported_files'] = array(

"asp", 
"aspx", 
"bat",
"c",
"cpp",
"cs",
"h",
"htm",
"html",
"java",
"js",
"m",
"php",
"py",
"rtf",
"txt"
);



//
// SYSTEM
//

// Location in the server. Usually this does not have to be set manually.
// Default: $wg_codebrowser_config['basedir'] = "";
//
$wg_codebrowser_config['basedir'] = "";


// Big files. If you have some very big files (>4GB), enable this for correct
// file size calculation.
// Default: $wg_codebrowser_config['large_files'] = false;
//
$wg_codebrowser_config['large_files'] = false;





/***************************************************************************/
/*                                                                         */
/*   Encode Explorer Code                                                  */
/*																		   */
/***************************************************************************/



/***************************************************************************/
/*                                                                         */
/*   TRANSLATIONS.                                                         */
/***************************************************************************/

$_TRANSLATIONS = array();

// English
$_TRANSLATIONS["en"] = array(
	"file_name" => "File name",
	"size" => "Size",
	"last_changed" => "Last changed",
	"total_used_space" => "Total used space",
	"free_space" => "Free space",
	"password" => "Password",
	"upload" => "Upload",
	"failed_upload" => "Failed to upload the file!",
	"failed_move" => "Failed to move the file into the right directory!",
	"wrong_password" => "Wrong password",
	"make_directory" => "New dir",
	"new_dir_failed" => "Failed to create directory",
	"chmod_dir_failed" => "Failed to change directory rights",
	"unable_to_read_dir" => "Unable to read directory",
	"location" => "Location",
	"root" => "Root",
	"log_file_permission_error" => "The script does not have permissions to write the log file.",
	"upload_not_allowed" => "The script configuration does not allow uploading in this directory.",
	"upload_dir_not_writable" => "This directory does not have write permissions.",
	"mobile_version" => "Mobile view",
	"standard_version" => "Standard view",
	"page_load_time" => "Page loaded in %.2f ms",
	"wrong_pass" => "Wrong username or password",
	"username" => "Username",
	"log_in" => "Log in",
	"upload_type_not_allowed" => "This file type is not allowed for uploading.",
	"del" => "Del", // short for Delete
	"log_out" => "Log out"
);



//
// Dir class holds the information about one directory in the list
//
class Dir
{
	var $name;
	var $location;

	//
	// Constructor
	//
	function Dir($name, $location)
	{
		$this->name = $name;
		$this->location = $location;
	}

	function getName()
	{
		return $this->name;
	}

	function getNameHtml()
	{
		return htmlspecialchars($this->name);
	}

	function getNameEncoded()
	{
		return rawurlencode($this->name);
	}

	//
	// Debugging output
	//
	function debug()
	{
		print("Dir name (htmlspecialchars): ".$this->getName()."\n");
		print("Dir location: ".$this->location->getDir(true, false, false, 0)."\n");
	}
}


//
// File class holds the information about one file in the list
//
class sFile
{
	var $name;
	var $location;
	var $size;
	//var $extension;
	var $type;
	var $modTime;

	//
	// Constructor
	//
	function sFile($name, $location)
	{
		$this->name = $name;
		$this->location = $location;

		$this->type = sFile::getFileType($this->location->getFullPath()."/".$this->getName());
		$this->size = sFile::getFileSize($this->location->getFullPath()."/".$this->getName());
		$this->modTime = filemtime($this->location->getFullPath()."/".$this->getName());
	}

	function getName()
	{
		return $this->name;
	}

	function getNameEncoded()
	{
		return rawurlencode($this->name);
	}

	function getNameHtml()
	{
		return htmlspecialchars($this->name);
	}

	function getSize()
	{
		return $this->size;
	}

	function getType()
	{
		return $this->type;
	}

	function getModTime()
	{
		return $this->modTime;
	}

	//
	// Determine the size of a file
	//
	public static function getFileSize($file)
	{
		$sizeInBytes = filesize($file);

		// If filesize() fails (with larger files), try to get the size from unix command line.
		if (EncodeExplorer::getConfig("large_files") == true || !$sizeInBytes || $sizeInBytes < 0) {
			$sizeInBytes=exec("ls -l '$file' | awk '{print $5}'");
		}
		return $sizeInBytes;
	}

	public static function getFileType($filepath)
	{
		return sFile::getFileExtension($filepath);
	}

	public static function getFileMime($filepath)
	{
		$fhandle = finfo_open(FILEINFO_MIME);
		$mime_type = finfo_file($fhandle, $filepath);
		$mime_type_chunks = preg_split('/\s+/', $mime_type);
		$mime_type = $mime_type_chunks[0];
		$mime_type_chunks = explode(";", $mime_type);
		$mime_type = $mime_type_chunks[0];
		return $mime_type;
	}

	public static function getFileExtension($filepath)
	{
		return strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
	}

	//
	// Debugging output
	//
	function debug()
	{
		print("File name: ".$this->getName()."\n");
		print("File location: ".$this->location->getDir(true, false, false, 0)."\n");
		print("File size: ".$this->size."\n");
		print("File modTime: ".$this->modTime."\n");
	}

}




class Location
{
	var $path;
	var $startDir;
	
	//
	// Split a file path into array elements
	//
	public static function splitPath($dir)
	{
		$dir = stripslashes($dir);
		$path1 = preg_split("/[\\\\\/]+/", $dir);
		$path2 = array();
		for($i = 0; $i < count($path1); $i++)
		{
			if($path1[$i] == ".." || $path1[$i] == "." || $path1[$i] == "")
			continue;
			$path2[] = $path1[$i];
		}
		return $path2;
	}

	//
	// Get the current directory.
	// Options: Include the prefix ("./"); URL-encode the string; HTML-encode the string; return directory n-levels up
	//
	function getDir($prefix, $encoded, $html, $up)
	{
		$dir = "";
		if($prefix == true)
		$dir .= "./";
		for($i = 0; $i < ((count($this->path) >= $up && $up > 0)?count($this->path)-$up:count($this->path)); $i++)
		{
			$temp = $this->path[$i];
			if($encoded)
			$temp = rawurlencode($temp);
			if($html)
			$temp = htmlspecialchars($temp);
			$dir .= $temp."/";
		}
		return $dir;
	}

	function getPathLink($i, $html)
	{
		if($html)
		return htmlspecialchars($this->path[$i]);
		else
		return $this->path[$i];
	}

	function getFullPath()
	{
		$p = "";
		$p .= rtrim(EncodeExplorer::getConfig('basedir'), "\\/")."/";
		$p .= $this->startDir."/";
		$p .= $this->getDir(true, false, false, 0);
		
		return $p;
	}

	//
	// Debugging output
	//
	function debug()
	{
		print("Dir with prefix: ".$this->getDir(true, false, false, 0)."\n");
		print("Dir without prefix: ".$this->getDir(false, false, false, 0)."\n");
		print("Upper dir with prefix: ".$this->getDir(true, false, false, 1)."\n");
		print("Upper dir without prefix: ".$this->getDir(false, false, false, 1)."\n");
	}


	//
	// Set the current directory
	//
	function init($start)
	{
		$this->startDir = rtrim($start,"/\\");
		
		if(!isset($_POST['codebrowser_dir']) || strlen($_POST['codebrowser_dir']) == 0)
		{
			$this->path =  $this->splitPath(".");
		}
		else
		{
			$this->path = $this->splitPath($_POST['codebrowser_dir']);
		}
		
		
	}

	//
	// Checks if the current directory is below the input path
	//
	function isSubDir($checkPath)
	{
		for($i = 0; $i < count($this->path); $i++)
		{
			if(strcmp($this->getDir(true, false, false, $i), $checkPath) == 0)
			return true;
		}
		return false;
	}


	function isWritable()
	{
		return is_writable($this->getDir(true, false, false, 0));
	}

	public static function isDirWritable($dir)
	{
		return is_writable($dir);
	}

	public static function isFileWritable($file)
	{
		if(file_exists($file))
		{
			if(is_writable($file))
			return true;
			else
			return false;
		}
		else if(Location::isDirWritable(dirname($file)))
		return true;
		else
		return false;
	}
}


class EncodeExplorer
{
	var $location;
	var $dirs;
	var $files;
	var $sort_by;
	var $sort_as;
	var $spaceUsed;
	var $lang;
	var $refpage;

	//
	// Determine sorting, calculate space.
	//
	function init()
	{
		$this->sort_by = "";
		$this->sort_as = "";
		if( isset($_POST['codebrowser_sort_by']) && isset($_POST['codebrowser_sort_as']) )
		{
			if($_POST['codebrowser_sort_by'] == "name" || $_POST['codebrowser_sort_by'] == "size" || $_POST['codebrowser_sort_by'] == "mod")
			if($_POST['codebrowser_sort_as'] == "asc" || $_POST['codebrowser_sort_as'] == "desc")
			{
				$this->sort_by = $_POST['codebrowser_sort_by'];
				$this->sort_as = $_POST['codebrowser_sort_as'];
			}
		}
		if(strlen($this->sort_by) <= 0 || strlen($this->sort_as) <= 0)
		{
			$this->sort_by = "name";
			$this->sort_as = "desc";
		}


		global $_TRANSLATIONS;
		if(isset($_POST['codebrowser_lang']) && isset($_TRANSLATIONS[$_POST['codebrowser_lang']]))
		$this->lang = $_POST['codebrowser_lang'];
		else
		$this->lang = EncodeExplorer::getConfig("lang");

		$this->refpage = "";
	}

	//
	// Read the file list from the directory
	//
	function readDir()
	{
		global $encodeExplorer;
		//
		// Reading the data of files and directories
		//
		if($open_dir = @opendir($this->location->getFullPath()))
		{
			$this->dirs = array();
			$this->files = array();
			while ($object = readdir($open_dir))
			{
				if($object != "." && $object != "..")
				{
					$absPath = $this->location->getFullPath()."/".$object;
					if(is_dir($absPath))
					{
						if(!in_array($object, EncodeExplorer::getConfig('hidden_dirs')))
						$this->dirs[] = new Dir($object, $this->location);
					}
					else 
					{
						if(in_array(sFile::getFileExtension($absPath), EncodeExplorer::getConfig('supported_files'))) 
						{
							$this->files[] = new sFile($object, $this->location);
						}
					}
				}
			}
			closedir($open_dir);
		}
		else
		{
			echo "failed to open directory: ".$this->location->getFullPath()."<br />";
			echo "either a bad base and/or starting directory path was specified <br />";
			echo "if using apache, did you remember the leading slash?";
			$encodeExplorer->setErrorString("unable_to_read_dir");;
		}
	}

	//
	// A recursive function for calculating the total used space
	//
	function sum_dir($start_dir, $ignore_files, $levels = 1)
	{
		if ($dir = opendir($start_dir))
		{
			$total = 0;
			while ((($file = readdir($dir)) !== false))
			{
				if (!in_array($file, $ignore_files))
				{
					if ((is_dir($start_dir . '/' . $file)) && ($levels - 1 >= 0))
					{
						$total += $this->sum_dir($start_dir . '/' . $file, $ignore_files, $levels-1);
					}
					elseif (is_file($start_dir . '/' . $file))
					{
						$total += sFile::getFileSize($start_dir . '/' . $file) / 1024;
					}
				}
			}

			closedir($dir);
			return $total;
		}
	}

	function calculateSpace()
	{
		if(EncodeExplorer::getConfig('calculate_space_level') <= 0)
		return;
		$ignore_files = array('..', '.');
		$start_dir = getcwd();
		$spaceUsed = $this->sum_dir($start_dir, $ignore_files, EncodeExplorer::getConfig('calculate_space_level'));
		$this->spaceUsed = round($spaceUsed/1024, 3);
	}

	function sort()
	{
		if(is_array($this->files)){
			usort($this->files, "EncodeExplorer::cmp_".$this->sort_by);
			if($this->sort_as == "desc")
			$this->files = array_reverse($this->files);
		}

		if(is_array($this->dirs)){
			usort($this->dirs, "EncodeExplorer::cmp_name");
			if($this->sort_by == "name" && $this->sort_as == "desc")
			$this->dirs = array_reverse($this->dirs);
		}
	}

	function makeArrow($sort_by)
	{

		if($this->sort_by == $sort_by && $this->sort_as == "asc")
		{
			$sort_as = "desc";
			$img = EncodeExplorer::getConfig('install_path')."/images/up.png";
		}
		else
		{
			$sort_as = "asc";
			$img = EncodeExplorer::getConfig('install_path')."/images/down.png";
		}

		if($sort_by == "name")
		$text = $this->getString("file_name");
		else if($sort_by == "size")
		$text = $this->getString("size");
		else if($sort_by == "mod")
		$text = $this->getString("last_changed");

		$retval = "<a ".$this->makeLink($sort_by, $sort_as, $this->location->getDir(false, true, false, 0), null)."> ";
		$retval .= $text." <img style=\"border:0;\" alt=\"".$sort_as."\" src=\"".$img."\" /></a>";

		return $retval;
	}

	function makeLink($sort_by, $sort_as, $dir, $file)
	{
		return "href=\"javascript: codebrowser_submitform('".$sort_by."', '".$sort_as."', '".$dir."', '".$file."')\"";
	}

	function makeIcon($ext)
	{
		$ext = strtolower($ext);
		return EncodeExplorer::getConfig('install_path')."/images/".$ext.".png";
	}

	function formatModTime($time)
	{
		$timeformat = "d/m/y H:i:s";
		if(EncodeExplorer::getConfig("time_format") != null && strlen(EncodeExplorer::getConfig("time_format")) > 0)
		$timeformat = EncodeExplorer::getConfig("time_format");
		return date($timeformat, $time);
	}

	function formatSize($size)
	{
		$sizes = Array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB');
		$y = $sizes[0];
		for ($i = 1; (($i < count($sizes)) && ($size >= 1024)); $i++)
		{
			$size = $size / 1024;
			$y  = $sizes[$i];
		}
		return round($size, 2)." ".$y;
	}

	//
	// Debugging output
	//
	function debug()
	{
		print("Explorer location: ".$this->location->getDir(true, false, false, 0)."\n");
		for($i = 0; $i < count($this->dirs); $i++)
		$this->dirs[$i]->output();
		for($i = 0; $i < count($this->files); $i++)
		$this->files[$i]->output();
	}

	//
	// Comparison functions for sorting.
	//

	public static function cmp_name($b, $a)
	{
		return strcasecmp($a->name, $b->name);
	}

	public static function cmp_size($a, $b)
	{
		return ($a->size - $b->size);
	}

	public static function cmp_mod($b, $a)
	{
		return ($a->modTime - $b->modTime);
	}

	//
	// The function for getting a translated string.
	// Falls back to english if the correct language is missing something.
	//
	public static function getLangString($stringName, $lang)
	{
		global $_TRANSLATIONS;
		if(isset($_TRANSLATIONS[$lang]) && is_array($_TRANSLATIONS[$lang])
		&& isset($_TRANSLATIONS[$lang][$stringName]))
		return $_TRANSLATIONS[$lang][$stringName];
		else if(isset($_TRANSLATIONS["en"]))// && is_array($_TRANSLATIONS["en"])
		//&& isset($_TRANSLATIONS["en"][$stringName]))
		return $_TRANSLATIONS["en"][$stringName];
		else
		return "Translation error";
	}

	function getString($stringName)
	{
		return EncodeExplorer::getLangString($stringName, $this->lang);
	}

	//
	// The function for getting configuration values
	//
	public static function getConfig($name)
	{
		global $wg_codebrowser_config;
		if(isset($wg_codebrowser_config) && isset($wg_codebrowser_config[$name]))
		return $wg_codebrowser_config[$name];
		return null;
	}

	public static function setError($message)
	{
		global $_ERROR;
		if(isset($_ERROR) && strlen($_ERROR) > 0)
		;// keep the first error and discard the rest
		else
		$_ERROR = $message;
	}

	function setErrorString($stringName)
	{
		EncodeExplorer::setError($this->getString($stringName));
	}
	
	//
	// Generate Breadcrumbs
	//
	function generateBreadCrumbs($fileName) {
		$output = "";

		if(EncodeExplorer::getConfig("show_path") == true)
		{
			$output .= "<a ".$this->makeLink(null, null, "", null).">".$this->getString("root")."</a>\n";

			for($i = 0; $i < count($this->location->path); $i++)
			{
				$output .= "&gt; <a ".$this->makeLink(null, null, $this->location->getDir(false, true, false, count($this->location->path) - $i - 1), null).">";
				$output .= $this->location->getPathLink($i, true);
				$output .= "</a>\n";
			}
			
			if($fileName != null) {
				$output .= "&gt; <a ".$this->makeLink(null, null, $this->location->getDir(false, true, false, count($this->location->path) - $i - 1), $fileName).">";
				$output .= $fileName;
				$output .= "</a>\n";
			}
		}
	
		return $output;
	}
	
	//
	// Time to generate page
	//	
	function getTimeToGenerate()
	{
		global $_START_TIME;
		
		return 	sprintf($this->getString("page_load_time"), (microtime(TRUE) - $_START_TIME)*1000);
	}
		

	//
	// Main function, activating tasks
	//
	function run($argv, $parser)
	{
		$output = "";
		
		if( !isset($argv['startdir']) || strlen($argv['startdir']) == 0)
		{
			$output = "<div id=\"error\">ERROR, parameter: startdir must be specified</div>\n";
		}
		else
		{
			if( isset($argv['refpage']) && strlen($argv['refpage']) > 0)
			{
				$this->refpage = $argv['refpage'];
			}
			
			$this->location = new Location();
			$this->location->init($argv['startdir']);
			$output = $this->generateHtmlOutput($parser);
		}

		
		return $output;
	}

	//
	// Generate HTML for either CodeBrowserPortal or file
	//
	function generateHtmlOutput($parser) {
		global $_ERROR;
		global $wgTitle;
		global $wgArticlePath;

		$output = "";

		// <!-- START: CodeBrowser area -->
		$output .= "<div id=\"codebrowser_frame\">\n";
		
		// <!-- START: CodeBrowser_Form area -->
		$output .= "<form id = \"codebrowser_form\" action=\"".$wgTitle->getFullURL()."\" method=\"post\">\n";

		//
		// Print the error (if there is something to print)
		//
		if(isset($_ERROR) && strlen($_ERROR) > 0)
		{
			$output .= "<div id=\"error\">".$_ERROR."</div>\n";
		}

		
        # <!-- START: CodeBrowser_table -->
		$output .= "<table class=\"codebrowser_table\">\n";

		// display breadcrumbs
		$output .= "<caption>\n";
		$output .= $this->generateBreadCrumbs(null);
		$output .= "</caption>\n";

		
		// generated syntax-highlighted file
		if( isset($_POST['codebrowser_file']) && strlen($_POST['codebrowser_file']) > 0) {
			$output .= $this->outputFileHtml($_POST['codebrowser_file'], $parser);
		}
		// else generate code browsing portal
		else {
			$this->calculateSpace();
			$this->readDir();
			$this->sort();
			$output .= $this->outputCodeBrowserPortalHtml();
		}
	

		// display time to generate page
		$output .= "<tfoot>\n";
		$output .= "<tr>\n";	
		if($this->refpage != null)
		{
			$url = str_replace('$1',  $this->refpage, $wgArticlePath);

			$output .= "<td colspan=\"2\" align=\"left\">\n";
			$output .= $this->getTimeToGenerate();
			$output .= "</td>\n";
			$output .= "<td colspan=\"2\" align=\"right\">\n";
			$output .= "Return to <a class=\"refpage\" href=\"".$url."\">".str_replace("_", " ", $this->refpage)."</a>\n";
			$output .= "</td>\n";
		} else {
			$output .= "<td colspan=\"4\" align=\"left\">\n";
			$output .= $this->getTimeToGenerate();
			$output .= "</td>\n";
				
		}	
		$output .= "</tr>\n";
		$output .= "</tfoot>\n";			
	
		$output .= "</table>\n";
		// <!-- END: CodeBrowser_table -->		
		

		$output .= "<input type=\"hidden\" id=\"codebrowser_dir\" name=\"codebrowser_dir\" value=\"\" />\n";
		$output .= "<input type=\"hidden\" id=\"codebrowser_file\" name=\"codebrowser_file\" value=\"\" />\n";
		$output .= "<input type=\"hidden\" id=\"codebrowser_sort_by\" name=\"codebrowser_sort_by\" value=\"\" />\n";
		$output .= "<input type=\"hidden\" id=\"codebrowser_sort_as\" name=\"codebrowser_sort_as\" value=\"\" />\n";
		$output .= "<input type=\"hidden\" id=\"codebrowser_lang\" name=\"codebrowser_lang\" value=\"\" />\n";
		$output .= "</form>\n";
		// <!-- END: CodeBrowser_Form area -->

		
		$output .= "</div>\n";
		// <!-- END: CodeBrowser area -->
		
	
		return $output;
	}

	
	//
	// Generate the syntax high-lighted file
	//
	function outputFileHtml($fileName, $parser) {
		$output = "";
		
		//<!-- START: file source code -->
		$output .= "<tbody>\n";
		$output .= "<tr class=\"source_code\" width =\100%\"><td>\n";

		if ( ! isset($wg_include_secure_include_path) )
		$wg_include_secure_include_path = "$IP/extensions/secure-include.php";

		@include $wg_include_secure_include_path;

		if( !function_exists('ef_include_render') ) {
			$output .= "<div id=\"error\">ERROR: Extension:Include not installed</div>\n";
		}
		else
		{
			# Including local paths requires to uncomment the following line
			# $wg_include_allowed_features['local'] = true;
			global $wg_include_allowed_parent_paths;
			$wg_include_allowed_parent_paths = $_SERVER['DOCUMENT_ROOT'];
			global $wg_include_disallowed_regex;
			$wg_include_disallowed_regex = array('/.*LocalSettings.php/', '/.*\.conf/', '/.*\/\.ht/');
			 
			# Including remote URLs requires to uncomment the following line
			# $wg_include_allowed_features['remote'] = true;
			global $wg_include_allowed_url_regexp;
			$wg_include_allowed_url_regexp = array('/^http:\/\/.*$/');
			global $wg_include_disallowed_url_regexp;
			$wg_include_disallowed_url_regexp = array('/^.*:\/\/intranet/');
			global $wg_include_allowed_features;
			$wg_include_allowed_features['highlight'] = true;
			$wg_include_allowed_features['local'] = true;
			$wg_include_allowed_features['remote'] = true;

	#		$output .= "<div id=\"#codebrowser_breadcrumbs\">\n";
	#		$output .= $this->generateBreadCrumbs($fileName);
	#		$output .= "</div>\n";
			
			$filePath = $this->location->getFullPath().$fileName;

			$argv = array();
			$argv['src'] = $filePath;
			$argv['highlight'] = sFile::getFileType($filePath);
			$argv['linenums'] = "GESHI_NORMAL_LINE_NUMBERS";
			// call secure-include extension
			$output .= ef_include_render(null, $argv, $parser);

		}
		
		$output .= "</td></tr>\n";
		$output .= "</tbody>\n";
		//<!-- END: file source code -->
		
		
		return $output;
	}


	//
	// Generate the code browsing page
	//
	function outputCodeBrowserPortalHtml()
	{
		$output = "";
		
		$output .= "<thead>\n";
		$output .= "<tr>\n";
		$output .= "<th scope=\"col\">&nbsp;</th>\n";
		$output .= "<th scope=\"col\">".$this->makeArrow("name")."</th>\n";
		$output .= "<th scope=\"col\">".$this->makeArrow("size")."</th>\n";
		$output .= "<th scope=\"col\">".$this->makeArrow("mod")."</th>\n";
		$output .= "</tr>\n";
		$output .= "</thead>\n";
	
		
		$output .= "<tbody>\n";
	
		$output .= "<tr>\n";
		$output .= "<td class=\"icon\"><img alt=\"dir\" src=\"".$this->makeIcon("folder")."\" /></td>\n";
		$output .= "<td ><a class=\"name\"";
		$output .= $this->makeLink(null, null, $this->location->getDir(false, true, false, 1), null).">..</a>\n";
		$output .= "</td>\n";
		$output .= "<td class=\"size\">&nbsp;</td><td class=\"changed\">&nbsp;</td>\n";
		$output .= "</tr>\n";


		//
		// Ready to display folders and files.
		//
		$row = 1;

		//
		// Folders first
		//
		if($this->dirs)
		{
			foreach ($this->dirs as $dir)
			{
				$row_style = ($row ? "class=\"odd\"" : "");
				$output .= "<tr ".$row_style.">\n";
				$output .= "<td class=\"icon\"><img alt=\"dir\" src=\"".$this->makeIcon("folder")."\" /></td>\n";
				$output .= "<td class=\"name\">\n";
				$output .= "<a ".$this->makeLink(null, null, $this->location->getDir(false, true, false, 0).$dir->getNameEncoded(), null)." >";
				$output .= $dir->getNameHtml();
				$output .= "</a>\n";
				$output .= "</td>\n";
				$output .= "<td class=\"size\">&nbsp;</td><td class=\"changed\">&nbsp;</td>\n";
				$output .= "</tr>\n";
				$row =! $row;
			}
		}

		//
		// Now the files
		//
		if($this->files)
		{
			$count = 0;
			foreach ($this->files as $file)
			{
				$row_style = ($row ? "class=\"odd\"" : "");
				$output .= "<tr ".$row_style.">\n";
				$output .= "<td class=\"icon\"><img alt=\"".$file->getType()."\" src=\"".$this->makeIcon($file->getType())."\" /></td>\n";
				$output .= "<td class=\"name\">\n";
				$output .= "<a ".$this->makeLink(null,null,$this->location->getDir(false, true, false, 0),$file->getNameEncoded());
				if(EncodeExplorer::getConfig('open_in_new_window') == true)
				$output .= " target=\"_blank\"";
				$output .= ">";
				$output .= $file->getNameHtml();
				$output .= "</a>\n";
				$output .= "</td>\n";
				$output .= "<td class=\"size\">".$this->formatSize($file->getSize())."</td>\n";
				$output .= "<td class=\"changed\">".$this->formatModTime($file->getModTime())."</td>\n";

				$output .= "</tr>\n";
				$row =! $row;
			}
		}

		$output .= "</tbody>\n";

		return ($output);
	}

}


function add_js_css() {
	global $wgOut;
	
	$script = "<script type=\"text/javascript\">\n";
	$script .= "   function codebrowser_submitform(sort_by, sort_as, dir, file)\n";
	$script .= "   {\n";
	$script .= "      if( sort_by != null)\n";
	$script .= "	   document.getElementById(\"codebrowser_sort_by\").value=sort_by;\n";
	$script .= "      if( sort_as != null)\n";
	$script .= "	   document.getElementById(\"codebrowser_sort_as\").value=sort_as;\n";
	$script .= "      if( dir != null)\n";
	$script .= "	   document.getElementById(\"codebrowser_dir\").value=dir;\n";
	$script .= "      if( file != null)\n";
	$script .= "	   document.getElementById(\"codebrowser_file\").value=file;\n";
	$script .= "	   document.forms[\"codebrowser_form\"].submit();\n";
	$script .= "   }\n";
	$script .= "</script>\n";
	
	$wgOut->addScript($script);	
	
	$wgOut->addStyle(EncodeExplorer::getConfig('install_path')."/codebrowser.css");
}





/***************************************************************************/
/*                                                                         */
/*   MediaWiki Extension Code                                              */
/*																		   */
/***************************************************************************/



###### Name used for the extension, tag, and settings page name #######
define("CODEBROWSER_NAME",  "CodeBrowser");            # Name of tag



# CodeBrowser MediaWiki extension
$wgExtensionFunctions[] = "wfCodeBrowserExtension";
$wgExtensionCredits['parserhook'][] = array(
	 'name' => CODEBROWSER_NAME,
	 'version' => '0.1a',
	 'author' =>'Dan Riedler', 
	 'url' => 'http://www.mediawiki.org/wiki/Extension:CodeBrowser',
	 'description' => 'Code source code within a page'
);



# register the extension with the WikiText parser
# the first parameter is the name of the new tag.
# In this case it defines the tag <CodeBrowser> ... </CodeBrowser>
# the second parameter is the callback function for
# processing the text between the tags
function wfCodeBrowserExtension() {
	global $wgParser;
	global $wgHooks;
	$wgParser->setHook( CODEBROWSER_NAME, "renderBrowsingPortal" );
}



# The callback function for rending the browsing portal.
# On a normal invocation, this tag list the file & directories within the
# specified directory. Clicking a folder navigates to within that directory,
# redirecting to the same page and rendering a new browsing portal.
# Clicking a source file redirects to the same page and renders the syntax
# high-lighted file.
function renderBrowsingPortal( $input, $argv, $parser ) {	
	reset($_POST);
	reset($argv);
	
	$parser->disableCache(); # IMPORTANT!!!!!! seriously, i spent 2 hrs figuring out this was necessary
	
	add_js_css();

	
	$encodeExplorer = new EncodeExplorer();
	$encodeExplorer->init();
	
	$output = $encodeExplorer->run($argv, $parser);


	return $output;
}





?>



