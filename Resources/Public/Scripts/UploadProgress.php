<?php
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

/* @TODO Test non APC implementation (so.. 5.4?)
http://php.net/manual/en/session.upload-progress.php

session_start();
//error_reporting(0);

$progress_key = ini_get("session.upload_progress.prefix") . $_POST[ini_get("session.upload_progress.name")]
$current = $_SESSION[$progress_key]["bytes_processed"];
$total = $_SESSION[$progress_key]["content_length"];
echo $current < $total ? ceil($current / $total * 100) : 100;
*/

if(isset($_GET['upload_id']) && is_numeric($_GET['upload_id'])) {

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
			echo 'apc_failed';
		}
		exit;
	}

	echo 'none_supported';
	exit;
}

#@SHOULD be a more thorough check, like a referrer that was explicitly set by jslib's ajax call
die('ERROR: This script is not supposed to be called manually.');
?>