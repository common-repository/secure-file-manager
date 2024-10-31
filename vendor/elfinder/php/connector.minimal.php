<?php

require( preg_replace('/wp-content(?!.*wp-content).*/','',__DIR__) .'/wp-load.php' );

set_time_limit(0); // just in case it too long, not recommended for production
error_reporting(E_ALL | E_STRICT); // Set E_ALL for debuging
// error_reporting(0);
ini_set('max_file_uploads', 50);   // allow uploading up to 50 files at once

// needed for case insensitive search to work, due to broken UTF-8 support in PHP
if (ini_get("mbstring.internal_encoding")) ini_set("mbstring.internal_encoding", 'UTF-8');
ini_set('mbstring.func_overload', 2);

if (function_exists('date_default_timezone_set')) {
	date_default_timezone_set('Europe/Moscow');
}

// elFinder autoload
require_once './autoload.php';
// ===============================================

// Enable FTP connector netmount
elFinder::$netDrivers['ftp'] = 'FTP';
// ===============================================

// define('ELFINDER_ONLINE_CONVERT_APIKEY', '');
define('ELFINDER_DISABLE_ONLINE_CONVERT', true); // set `true` to disable Online converter
// ===============================================

// // Zip Archive editor
// // Installation by composer
// // `composer require barryvdh/elfinder-flysystem-driver league/flysystem-ziparchive`
// define('ELFINDER_DISABLE_ZIPEDITOR', false); // set `true` to disable zip editor
// ===============================================

function debug() {
	$arg = func_get_args();
	ob_start();
	foreach($arg as $v) {
		var_dump($v);
	}
	$o = ob_get_contents();
	ob_end_clean();
	file_put_contents('.debug.txt', $o, FILE_APPEND);
}

/**
 * Smart logger function
 * Demonstrate how to work with elFinder event api
 *
 * @param  string   $cmd       command name
 * @param  array    $result    command result
 * @param  array    $args      command arguments from client
 * @param  elFinder $elfinder  elFinder instance
 * @return void|true
 * @author Troex Nevelin
 **/
function logger($cmd, $result, $args, $elfinder) {

	
	$log = sprintf("[%s] %s: %s \n", date('r'), strtoupper($cmd), var_export($result, true));
	$logfile = '../files/temp/log.txt';
	$dir = dirname($logfile);
	if (!is_dir($dir) && !mkdir($dir)) {
		return;
	}
	if (($fp = fopen($logfile, 'a'))) {
		fwrite($fp, $log);
		fclose($fp);
	}
	return;

	foreach ($result as $key => $value) {
		if (empty($value)) {
			continue;
		}
		$data = array();
		if (in_array($key, array('error', 'warning'))) {
			array_push($data, implode(' ', $value));
		} else {
			if (is_array($value)) { // changes made to files
				foreach ($value as $file) {
					$filepath = (isset($file['realpath']) ? $file['realpath'] : $elfinder->realpath($file['hash']));
					array_push($data, $filepath);
				}
			} else { // other value (ex. header)
				array_push($data, $value);
			}
		}
		$log .= sprintf(' %s(%s)', $key, implode(', ', $data));
	}
	$log .= "\n";

	$logfile = '../files/temp/log.txt';
	$dir = dirname($logfile);
	if (!is_dir($dir) && !mkdir($dir)) {
		return;
	}
	if (($fp = fopen($logfile, 'a'))) {
		fwrite($fp, $log);
		fclose($fp);
	}
}


/**
 * Simple logger function.
 * Demonstrate how to work with elFinder event api.
 *
 * @package elFinder
 * @author Dmitry (dio) Levashov
 **/
class elFinderSimpleLogger {
	
	/**
	 * Log file path
	 *
	 * @var string
	 **/
	protected $file = '';
	
	/**
	 * constructor
	 *
	 * @return void
	 * @author Dmitry (dio) Levashov
	 **/
	public function __construct($path) {
		$this->file = $path;
		$dir = dirname($path);
		if (!is_dir($dir)) {
			mkdir($dir);
		}
	}
	
	/**
	 * Create log record
	 *
	 * @param  string   $cmd       command name
	 * @param  array    $result    command result
	 * @param  array    $args      command arguments from client
	 * @param  elFinder $elfinder  elFinder instance
	 * @return void|true
	 * @author Dmitry (dio) Levashov
	 **/
	public function log($cmd, $result, $args, $elfinder) {
		$log = $cmd.' ['.date('d.m H:s')."]\n";
		
		if (!empty($result['error'])) {
			$log .= "\tERROR: ".implode(' ', $result['error'])."\n";
		}
		
		if (!empty($result['warning'])) {
			$log .= "\tWARNING: ".implode(' ', $result['warning'])."\n";
		}
		
		if (!empty($result['removed'])) {
			foreach ($result['removed'] as $file) {
				// removed file contain additional field "realpath"
				$log .= "\tREMOVED: ".$file['realpath']."\n";
			}
		}
		
		if (!empty($result['added'])) {
			foreach ($result['added'] as $file) {
				$log .= "\tADDED: ".$elfinder->realpath($file['hash'])."\n";
			}
		}
		
		if (!empty($result['changed'])) {
			foreach ($result['changed'] as $file) {
				$log .= "\tCHANGED: ".$elfinder->realpath($file['hash'])."\n";
			}
		}
		
		$this->write($log);
	}
	
	/**
	 * Write log into file
	 *
	 * @param  string  $log  log record
	 * @return void
	 * @author Dmitry (dio) Levashov
	 **/
	protected function write($log) {
		
		if (($fp = @fopen($this->file, 'a'))) {
			fwrite($fp, $log."\n");
			fclose($fp);
		}
	}
	
	
} // END class 


/**
 * Simple function to demonstrate how to control file access using "accessControl" callback.
 * This method will disable accessing files/folders starting from '.' (dot)
 *
 * @param  string    $attr    attribute name (read|write|locked|hidden)
 * @param  string    $path    absolute file path
 * @param  string    $data    value of volume option `accessControlData`
 * @param  object    $volume  elFinder volume driver object
 * @param  bool|null $isDir   path is directory (true: directory, false: file, null: unknown)
 * @param  string    $relpath file path relative to volume root directory started with directory separator
 * @return bool|null
 **/
function access($attr, $path, $data, $volume, $isDir, $relpath) {
	$basename = basename($path);
	return $basename[0] === '.'                  // if file/folder begins with '.' (dot)
			 && strlen($relpath) !== 1           // but with out volume root
		? !($attr == 'read' || $attr == 'write') // set read+write to false, other (locked+hidden) set to true
		:  null;                                 // else elFinder decide it itself
}

/**
 * Access control example class
 *
 * @author Dmitry (dio) Levashov
 **/
class elFinderTestACL {
	
	/**
	 * make dotfiles not readable, not writable, hidden and locked
	 *
	 * @param  string  $attr  attribute name (read|write|locked|hidden)
	 * @param  string  $path  file path. Attention! This is path relative to volume root directory started with directory separator.
	 * @param  mixed   $data  data which seted in 'accessControlData' elFinder option
	 * @param  elFinderVolumeDriver  $volume  volume driver
	 * @return bool
	 * @author Dmitry (dio) Levashov
	 **/
	public function fsAccess($attr, $path, $data, $volume) {
		
		if ($volume->name() == 'localfilesystem') {
			return strpos(basename($path), '.') === 0
				? !($attr == 'read' || $attr == 'write')
				: $attr == 'read' || $attr == 'write';
		}
		
		return true;
	}
	
} // END class 

$acl = new elFinderTestACL();

function validName($name) {
	return strpos($name, '.') !== 0;
}


$logger = new elFinderSimpleLogger('../files/temp/log.txt');

function runConnector() {

	$nonce = $_REQUEST['_wpnonce'];
	$currentUserRoles = wp_get_current_user()->roles;

    if ( wp_verify_nonce( $nonce, 'secure-file-manager-pro' ) && ( in_array( get_current_user_id(), get_option( 'sfm_auth_user' ) ) || !empty( array_intersect( $currentUserRoles, get_option( 'sfm_auth_roles' ) ) ) ) ) {
		$connectorOptions = array(
			'driver'     => 'LocalFileSystem',
			'path'       => getcwd().'../../../../../../../',
			'startPath'  => getcwd().'../../../../../../../',
			'URL'        => dirname($_SERVER['PHP_SELF']) . '/../files/',
			'trashHash'  => 't1_Lw',                     // elFinder's hash of trash folder
			'winHashFix' => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
			'mimeDetect' => 'internal',
			'tmbPath'    => '.tmb',
			'utf8fix'    => true,
			'tmbCrop'    => false,
			'tmbBgColor' => 'transparent',
			'accessControl' => 'access',
			'acceptedName'    => '/^[^\.].*$/',
			'attributes' => array(
				array(
					'pattern' => '/\.js$/',
					'read' => true,
					'write' => true
				),
				array(
					'pattern' => '/^\/icons$/',
					'read' => true,
					'write' => false
				)
			),
			'uploadDeny'  => array('all'),                // All Mimetypes not allowed to upload
			'uploadAllow' => array('text/html', 'text/javascript', 'image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain', 'text/x-php', 'application/zip', 'application/pdf', 'text/css'), // Mimetype `image` and `text/plain` allowed to upload
			'uploadOrder' => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
		);
	} else {
		$connectorOptions = array(
			'driver'     => 'LocalFileSystem',
			'path'       => getcwd().'../../../../../../../',
			'startPath'  => getcwd().'../../../../../../../',
			'URL'        => dirname($_SERVER['PHP_SELF']) . '/../files/',
			'trashHash'  => 't1_Lw',                     // elFinder's hash of trash folder
			'winHashFix' => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
			'mimeDetect' => 'internal',
			'tmbPath'    => '.tmb',
			'utf8fix'    => true,
			'tmbCrop'    => false,
			'tmbBgColor' => 'transparent',
			'accessControl' => 'access',
			'acceptedName'    => '/^[^\.].*$/',
			'disabled' => array('abort','archive','callback','chmod','dim','duplicate','editor','extract','file','get','info','ls','mkdir','mkfile','netmount','open','parents','paste','ping','put','rename','resize','rm','search','size','tmb','tree','upload','url','zipdl'),
			'attributes' => array(
				array(
					'pattern' => '/\.js$/',
					'read' => true,
					'write' => false
				),
				array(
					'pattern' => '/^\/icons$/',
					'read' => true,
					'write' => false
				)
			),
			'uploadDeny'  => array('all'),                // All Mimetypes not allowed to upload
			'uploadAllow' => array('image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain', 'text/x-php', 'application/zip', 'application/pdf', 'text/css'), // Mimetype `image` and `text/plain` allowed to upload
			'uploadOrder' => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
		);
	}

	$opts = array(
		'locale' => 'en_US.UTF-8',
		'bind' => array(
			// '*' => 'logger',
			'mkdir mkfile rename duplicate upload rm paste' => 'logger'
		),
		'debug' => true,
		'netVolumesSessionKey' => 'netVolumes',
		'roots' => array(
			$connectorOptions,
			// Trash volume
			array(
				'id'            => '1',
				'driver'        => 'Trash',
				'path'          => '../files/.trash/',
				'tmbURL'        => dirname($_SERVER['PHP_SELF']) . '/../files/.trash/.tmb/',
				'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
				'uploadDeny'    => array('all'),                // Recomend the same settings as the original volume that uses the trash
				'uploadAllow'   => array('image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain', 'text/x-php', 'application/zip'), // Same as above
				'uploadOrder'   => array('deny', 'allow'),      // Same as above
				'accessControl' => 'access',                    // Same as above
			),

		)
			
	);

	// sleep(3);
	header('Access-Control-Allow-Origin: *');
	$connector = new elFinderConnector(new elFinder($opts), true);
	$connector->run();

}

if ( wp_validate_auth_cookie() ){
	runConnector();
}

// echo '<pre>';
// print_r($connector);