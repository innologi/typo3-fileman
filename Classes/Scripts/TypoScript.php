<?php
namespace Innologi\Fileman\Scripts;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * TypoScript helper library
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class TypoScript {

	/**
	 * Retrieves 'max_file_uploads' from php.ini
	 *
	 * Used to place a limit in the jslib.
	 *
	 * @param string $content
	 * @param array $conf
	 * @return string
	 */
	public function getMaxFileUploads($content, $conf) {
		return ini_get('max_file_uploads');
	}

	/**
	 * Retrieves 'apc.rfc1867_name' from php.ini
	 *
	 * Used to set the field name in the jslib.
	 *
	 * @param string $content
	 * @param array $conf
	 * @return string
	 */
	public function getApcFieldName($content, $conf) {
		return ini_get('apc.rfc1867_name');
	}

	/**
	 * Retrieves 'session.upload_progress.name' from php.ini
	 *
	 * Used to set the field name in the jslib.
	 *
	 * @param string $content
	 * @param array $conf
	 * @return string
	 */
	public function getSesFieldName($content, $conf) {
		return ini_get('session.upload_progress.name');
	}
}
?>