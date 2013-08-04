<?php
#@TODO doc
class Tx_Fileman_TypoScript {
	public function getMaxFileUploads($content, $conf) {
		return ini_get('max_file_uploads');
	}
	public function getApcFieldName($content, $conf) {
		return ini_get('apc.rfc1867_name');
	}
}
?>