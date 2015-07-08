<?php
namespace ThinkopenAt\KbNescefe\AlternateImplementations;

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
 * Extends "Typo3DbBackend"
 * Is required until the change gets incorporated into extbase or
 * another extbase solution is provided for direct access to 
 * records.
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */


class Typo3DbBackend extends \TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbBackend {

	/**
	 * Performs workspace and language overlay on the given row array. The language and workspace id is automatically
	 * detected (depending on FE or BE context). You can also explicitly set the language/workspace id.
	 *
	 * @todo: Mostly all methods being called in this method and this method itself should get moved to a class of its own for handling
	 * TYPO3 language and workspace overlay stuff. Thus a factory could get used to determine whether and how overlay should be done.
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\Qom\SourceInterface $source The source (selector od join)
	 * @param array $rows
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface $querySettings The TYPO3 CMS specific query settings
	 * @param null|integer $workspaceUid
	 * @return array
	 */
	protected function doLanguageAndWorkspaceOverlay(\TYPO3\CMS\Extbase\Persistence\Generic\Qom\SourceInterface $source, array $rows, \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface $querySettings, $workspaceUid = NULL) {
		if (!$querySettings instanceof \ThinkopenAt\KbNescefe\AlternateImplementations\Typo3QuerySettings) {
			throw new \Exception('The passed querySettings must be an alternate implementation!');
		}
		if (! ($querySettings->getEnableLanguageOverlay() || $querySettings->getEnableWorkspaceOverlay())) {
			// Don't overlay!
			return $rows;
		}
		if ($source instanceof \TYPO3\CMS\Extbase\Persistence\Generic\Qom\SelectorInterface) {
			$tableName = $source->getSelectorName();
		} elseif ($source instanceof \TYPO3\CMS\Extbase\Persistence\Generic\Qom\JoinInterface) {
			$tableName = $source->getRight()->getSelectorName();
		} else {
			// No proper source, so we do not have a table name here
			// we cannot do an overlay and return the original rows instead.
			return $rows;
		}

		// Altough "getPageRepository" returns the repository instance we will use the member variable instead.
		$this->getPageRepository($workspaceUid);

		$rows = $this->getMovePlaceholder($tableName, $rows, $querySettings);

		$overlaidRows = array();
		foreach ($rows as $row) {
			// If current row is a translation select its parent
			$row = $this->getTranslationOriginal($tableName, $row, $querySettings);

			// Do workspace/versioning overlay
			$row = $this->getWorkspaceVersion($tableName, $row, $querySettings);

			$row = $this->getTranslatedRecord($tableName, $row, $querySettings);

			if ($row !== NULL && is_array($row)) {
				$overlaidRows[] = $row;
			}
		}
		return $overlaidRows;
	}

	/**
	 * Allows to retrieve a pageRepository instance and sets the correct workspace to use.
	 *
	 * @param null|integer $workspaceUid The workspace which should get used for retrieving records (if applicable)
	 * @return \TYPO3\CMS\Frontend\Page\PageRepository
	 */
	protected function getPageRepository($workspaceUid = NULL) {
		if (!$this->pageRepository instanceof \TYPO3\CMS\Frontend\Page\PageRepository) {
			if ($this->environmentService->isEnvironmentInFrontendMode() && is_object($GLOBALS['TSFE'])) {
				$this->pageRepository = $GLOBALS['TSFE']->sys_page;
			} else {
				$this->pageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
			}
		}
		// Set the passed workspaceUid in the repository
		if (is_object($GLOBALS['TSFE'])) {
			if ($workspaceUid !== NULL) {
				$this->pageRepository->versioningWorkspaceId = $workspaceUid;
			}
		} else {
			if ($workspaceUid === NULL) {
				$workspaceUid = $GLOBALS['BE_USER']->workspace;
			}
			$this->pageRepository->versioningWorkspaceId = $workspaceUid;
		}

		return $this->pageRepository;
	}

	/**
	 * This method will return the original record of a translated one.
	 * When in frontend context this is required for language overlay to work propery.
	 * Setting "enableLanguageOverlay" to FALSE allows to disable language overlay which can be required in backend modules.
	 *
	 * @param string $tableName The table from which to retrieve an original untranslated record.
	 * @param array $row The record for which to retrieves its language parent (untranslated original)
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface $querySettings The TYPO3 CMS specific query settings
	 * @return array The original language version (if available)
	 */
	protected function getTranslationOriginal($tableName, $row, \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface $querySettings) {
		if ($querySettings->getEnableLanguageOverlay() === FALSE) {
			// Don't do any language overlay.
			return $row;
		}
		if ( isset($tableName) && isset($GLOBALS['TCA'][$tableName])
			&& isset($GLOBALS['TCA'][$tableName]['ctrl']['languageField'])
			&& isset($GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'])
		) {
			if ( isset($row[$GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField']])
				&& $row[$GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField']] > 0
			) {
				// What happens if there is no original translation? Shouldn't the result of the "getSingleRow"
				// call get stored in a temporary variable and only get assigned to $row if is is a valid result?
				$row = $this->databaseHandle->exec_SELECTgetSingleRow(
					$tableName . '.*',
					$tableName,
					$tableName . '.uid=' . (integer) $row[$GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField']] .
						' AND ' . $tableName . '.' . $GLOBALS['TCA'][$tableName]['ctrl']['languageField'] . '=0'
				);
			}
		}
		return $row;
	}

	/**
	 * Fetches and returns the translated record.
	 *
	 * @param string $tableName The table from which to retrieve a translated record
	 * @param array $row The record for which to retrieve a translation
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface $querySettings The TYPO3 CMS specific query settings
	 * @return The translated record according to the language in the querySettings
	 */
	protected function getTranslatedRecord($tableName, array $row, \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface $querySettings) {
		if ($querySettings->getEnableLanguageOverlay() === FALSE) {
			return $row;
		}
		if ($tableName == 'pages') {
			$row = $this->pageRepository->getPageOverlay($row, $querySettings->getLanguageUid());
		} elseif ( isset($GLOBALS['TCA'][$tableName]['ctrl']['languageField'])
			&& $GLOBALS['TCA'][$tableName]['ctrl']['languageField'] !== ''
			&& !isset($GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerTable'])
			&& in_array($row[$GLOBALS['TCA'][$tableName]['ctrl']['languageField']], array(-1, 0))
		) {
			$overlayMode = $querySettings->getLanguageMode() === 'strict' ? 'hideNonTranslated' : '';
			$row = $this->pageRepository->getRecordOverlay($tableName, $row, $querySettings->getLanguageUid(), $overlayMode);
		}
		return $row;
	}

	/**
	 * Performs workspace/versioning overlay for the given record.
	 *
	 * Can get disabled by setting "enableWorkspaceOverlay" query (context) setting to FALSE.
	 * This is useful for backend modules who know the exact UID of a record they wish to retrieve from a repository.
	 *
	 * Please take care that there is also a version overlay within "pageRepository->getRecordOverlay" which will get called
	 * when a language overlay of the record is performed. So if you do not want any version overlay at all you will also have
	 * to vent LanguageOverlay.
	 *
	 * @param string $tableName The table from which to retrieve a workspace/versionized record
	 * @param array $row The record for which to retrieve a workspace/versionized variant
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface $querySettings The TYPO3 CMS specific query settings
	 * @return array The versionized/workspace record
	 */
	protected function getWorkspaceVersion($tableName, $row, \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface $querySettings) {
		if ($querySettings->getEnableWorkspaceOverlay() === FALSE) {
			// Don't do any workspace overlay or versioning.
			return $row;
		}
		$this->pageRepository->versionOL($tableName, $row, TRUE);
		return $row;
	}

	/**
	 * Fetches the move-placeholder in case it is supported by the table and if there's only one row in the result set
	 * (applying this to all rows does not work, since the sorting order would be destroyed and possible limits not met anymore)
	 *
	 * @param string $tableName The table on which to operate
	 * @param array $row The row for which to fetch a workspace move-placeholder
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface $querySettings The TYPO3 CMS specific query settings
	 * @return array The move placeholder if available, else the passed row
	 */
	protected function getMovePlaceholder($tableName, array $rows, \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface $querySettings) {
		if ($querySettings->getEnableWorkspaceOverlay() === FALSE) {
			// Don't do any workspace overlay or versioning.
			return $rows;
		}
		if (!empty($this->pageRepository->versioningWorkspaceId)
			&& !empty($GLOBALS['TCA'][$tableName]['ctrl']['versioningWS'])
			&& $GLOBALS['TCA'][$tableName]['ctrl']['versioningWS'] >= 2
			&& count($rows) === 1
		) {
			$movePlaceholder = $this->databaseHandle->exec_SELECTgetSingleRow(
				$tableName . '.*',
				$tableName,
				't3ver_state=3 AND t3ver_wsid=' . $this->pageRepository->versioningWorkspaceId
					. ' AND t3ver_move_id=' . $rows[0]['uid']
			);
			if (!empty($movePlaceholder)) {
				$rows = array($movePlaceholder);
			}
		}
		return $rows;
	}

}

