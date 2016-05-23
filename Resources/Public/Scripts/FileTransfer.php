<?php
session_start();
$headers = array(
	'Expires' => 'Thu, 01 Jan 1970 00:00:00 GMT', //'Expires: 0' does NOT always produce expected results
	'Cache-Control' => 'no-cache, must-revalidate',
	//losing these enforces use of Expires by user-agents that prefer these or don't play nice with cache-control (e.g. HTTP/1.0)
	'Last-Modified' => NULL,
	'ETag' => NULL,

	'Content-Type' => 'application/json'
);

foreach($headers as $header => $data) {
	$headerString = $header . ': ' . $data;
	header($headerString);
}

$return = array(
	'success' => 0
);

// @TODO make this an eid script

if(isset($_GET['filename']) && isset($_GET['state'])) {

	$dir = ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir();
	# @TODO once it is eid: $dir = rtrim(t3lib_div::fixWindowsFilePath($dir), '/') . '/tx_fileman_filelist/';
	$dir = rtrim($dir, '/') . '/tx_fileman_filelist/';
	$filename = $_GET['filename'];
	if($_GET['state'] === '0') {
		$tmp_name = md5($filename);
		if (file_exists($dir . $tmp_name)) {
			$tmp_name = getUniqueFilename($tmp_name, $dir);
		}
		$filename = $return['tmp_name'] = $tmp_name;
	}
	$return['output_path'] = $path = $dir . $filename;


	$data = file_get_contents('php://input');
	$dataExp = explode(',', $data);
	if (isset($dataExp[1])) {
		$data = base64_decode($dataExp[1], TRUE);
		if ($data !== FALSE) {
			if (@file_put_contents($path, $data, FILE_APPEND) !== FALSE) {
				$return['success'] = 1;
				$return['message'] = 'Successful transfer.';
			} else {
				$return['message'] = 'Transfer failed.';
			}
		} else {
			$return['message'] = 'Garbage input: ' . $dataExp[1];;
		}
	} else {
		$return['message'] = 'Unexpected input stream: ' . $data;
	}
} else {
	$return['message'] = 'This script was called with erroneous parameters, possibly manually.';
}

#@LOW be a more thorough check, like a referrer that was explicitly set by jslib's ajax call
echo json_encode($return);
ob_flush();
flush();
exit;

function getUniqueFilename($filename, $dir) {
	$i = 0;
	$original = $parts = explode('.', $filename);
	while (file_exists($dir . $filename) && $i < 1000) {
		$parts[0] .= ++$i;
		$filename = join('.', $parts);
		$parts = $original;
	}
	return $filename;
}