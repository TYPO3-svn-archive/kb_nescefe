<?php
namespace ThinkopenAt\KbNescefe\Hooks;

/***************************************************************
*  Copyright notice
*
*  (c) 2010-2015 Bernhard Kraft (kraftb@think-open.at)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Hook class for the import/export module
 *
 * @author Bernhard Kraft <kraftb@think-open.at>
 */


class ImportExport {

	/**
	 * Update the "colPos" value for imported kb_nescefe elements before writing them to the database.
	 * This is necessary as "containerElementColPos" could be different on some systems.
	 *
	 * @param array $params: The parameters passed to the hook from the import/export module
	 * @param \TYPO3\CMS\Impexp\ImportExport $parentObject: The import/export module instance
	 * @return void
	 */
	public function updateColPos(array $params, \TYPO3\CMS\Impexp\ImportExport $parentObject) {
		if (is_array($params) && is_array($params['data']) && is_array($params['data']['tt_content'])) {
			foreach ($params['data']['tt_content'] as $key => $data) {
				$params['data']['tt_content'][$key] = $this->updateColPos_record($data);
			}
		}
	}

	/**
	 * Update the "colPos" of the passed data array
	 *
	 * @param array $data: The data fields (record) which will get written to DB
	 * @return array The modified data fields
	 */
	protected function updateColPos_record(array $data) {
		if ($data['kbnescefe_parentElement'] && strlen($data['kbnescefe_parentPosition'])) {
			$data['colPos'] = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_nescefe']['containerElementColPos'];
		}
		return $data;
	}

}

