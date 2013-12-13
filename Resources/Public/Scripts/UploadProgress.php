<?php
session_start();
$headers = array(
	'Expires' => 'Thu, 01 Jan 1970 00:00:00 GMT', //'Expires: 0' does NOT always produce expected results
	'Cache-Control' => 'no-cache, must-revalidate',
	//losing these enforces use of Expires by user-agents that prefer these or don't play nice with cache-control (e.g. HTTP/1.0)
	'Last-Modified' => NULL,
	'ETag' => NULL
);

foreach($headers as $header => $data) {
	$headerString = $header . ': ' . $data;
	header($headerString);
}

/*
@TODO HTML5
<progress> and XMLHttpRequest Level 2 specs

@TODO file size check
HTML5: http://stackoverflow.com/questions/4112575/client-checking-file-size-using-html5
*/


if(isset($_GET['type']) && isset($_GET['upload_id']) && is_numeric($_GET['upload_id'])) {

	switch ($_GET['type']) {
		case 'session':
			//PHP 5.4+ Session upload progress
			if (version_compare(phpversion(), '5.4', '>=') && (bool) ini_get('session.upload_progress.enabled')) {
				// http://php.net/manual/en/session.upload-progress.php
				// for this method to work, a hidden input field as $name will need to exist in form BEFORE file upload field(s?)
				// $name = ini_get('session.upload_progress.name');

				$key = ini_get('session.upload_progress.prefix') . $_GET['upload_id'];
				$status = isset($_SESSION[$key]) ? $_SESSION[$key] : NULL;
				if ($status !== NULL && count($status['files']) > 0) {
					echo ($status['bytes_processed'] / $status['content_length']) * 100;
				} else {
					echo 'ERROR: session_fail: pid = ' . getmypid() . ', id = ' . $key . ', result = ';
					var_dump($status);
					if ($status === NULL) {
						/*
						 * disabling session.upload_progress.cleanup seems to help in TYPO3 context,
						 * meaning TYPO3 reads it before this script gets to
						 */
						echo "\n", 'Try disabling session.upload_progress.cleanup to see if that helps.';
					}
				}
				exit;
			}
			break;
		case 'apc':
			//APC upload progress
			if (extension_loaded('apc') && intval(ini_get('apc.rfc1867'))) {
				//for this method to work, a hidden input field as $name will need to exist in form BEFORE file upload field(s?)
				//$name = ini_get('apc.rfc1867_name');

				$success = FALSE;
				$fetchKey = ini_get('apc.rfc1867_prefix') . $_GET['upload_id'];
				$status = apc_fetch($fetchKey,$success);
				if ($success) {
					echo ($status['current'] / $status['total']) * 100;
				} else {
					echo 'ERROR: apc_fail: pid = ' . getmypid() . ', id = ' . $fetchKey . ', result = ';
					var_dump($status);
				}
				exit;
			}
			break;
		case 'uploadprogress':
			//PECL uploadprogress package
			if (extension_loaded('uploadprogress')) {
				//for this method to work, a hidden input field as UPLOAD_IDENTIFIER will need to exist in form BEFORE file upload field(s?)

				$status = uploadprogress_get_info($_GET['upload_id']);
				if ($status !== NULL) {
					if (isset($status['bytes_total'])) {
						echo ($status['bytes_uploaded'] / $status['bytes_total']) * 100;
					}
					#@LOW or else.. ?
				} else {
					echo 'ERROR: uploadprogress_fail: pid = ' . getmypid() . ', id = ' . $_GET['upload_id'] . ', result = ';
					var_dump($status);
				}
				exit;
			}
	}

	echo 'ERROR: Your chosen upload-progress type is not supported.';
	exit;
}

#@LOW be a more thorough check, like a referrer that was explicitly set by jslib's ajax call
die('ERROR: This script is not supposed to be called manually.');
?>