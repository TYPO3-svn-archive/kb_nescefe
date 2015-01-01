<?php
namespace ThinkopenAt\KbNescefe\Hooks;

/***************************************************************
*  Copyright notice
*
*  (c) 2014-2015 Bernhard Kraft (kraftb@think-open.at)
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
 * Hook for "PageLayoutView" which alters the query for
 * "getContentRecordsPerColumn" if requested to do so.
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */

class PageLayoutView implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * @var array|boolean Content elements which should get selected
	 */
	protected $contentElementUids = FALSE;


	/**
	 * This hook method will get called after "makeQueryArray". It replaces the where part by an appropriate
	 * where statement if configured to do so. This is class is a singleton and a list of tt_content elements
	 * which should get retrieved can get set via the "setContentElementUids()" method.
	 *
	 * @param array $queryParts: Passed as reference. The query array as usually returned by makeQueryArray. Has to get modified appropriately.
	 * @param TYPO3\CMS\Backend\View\PageLayoutView $parentObject: The instance from which this hook gets called.
	 * @param string $table: The table for which a query shall get constructed
	 * @param integer $id: Page id (NOT USED! $parentObject->pidSelect is used instead)
	 * @param string $addWhere: Additional part for where clause
	 * @param string $fieldList: Field list to select, * for all (for "SELECT [fieldlist] FROM ...")
	 * @param array $params: Hook parameters. Results of the generated query.
	 * @return void
	 */
	public function makeQueryArray_post(&$queryParts, $parentObject, $table, $id, $addWhere, $fieldList, $params) {
		if (is_array($this->contentElementUids)) {
			$this->contentElementUids[] = '0';
			$queryParts['WHERE'] = preg_replace('/\s+AND\s+colPos\s+IN\s+\(0\)\s+/', ' ', $queryParts['WHERE']);
			$queryParts['WHERE'] .= ' AND uid IN ('.implode(',', $this->contentElementUids).')';
		}
	}

	/**
	 * This method allows to set the content elements which will get selected by setting their UID in the "where" part of the modified query
	 *
	 * @param array $contentElementUids: Will get stored in the class property used in the hook method.
	 * @return void
	 */
	public function setContentElementUids(array $contentElementUids) {
		$this->contentElementUids = $contentElementUids;
	}

	/**
	 * This method resets the internal contentElementUids array to boolean FALSE.
	 * This is required for inhibiting endless recursion.
	 *
	 * @return void
	 */
	public function unsetContentElementUids() {
		$this->contentElementUids = FALSE;
	}

}

