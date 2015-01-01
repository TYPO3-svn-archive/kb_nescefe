<?php
namespace ThinkopenAt\KbNescefe\Tests\Functional\DataHandling\Regular;

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
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

$basePath = dirname(__FILE__);
while (basename($basePath) !== 'typo3conf') {
	$basePath = dirname($basePath);
}
$basePath = dirname($basePath);

require_once $basePath . '/typo3/sysext/core/Tests/Functional/DataHandling/AbstractDataHandlerActionTestCase.php';

/**
 * Functional test for the kb_nescefe DataHandler hooks
 */
abstract class AbstractActionTestCase extends \TYPO3\CMS\Core\Tests\Functional\DataHandling\AbstractDataHandlerActionTestCase {

	const VALUE_PageId = 89;
	const VALUE_PageIdTarget = 90;
	const VALUE_PageIdWebsite = 1;
	const VALUE_ContentIdFirst = 297;
	const VALUE_ContentIdContainer = 298;
	const VALUE_ContentIdThird = 299;
	const VALUE_ContentIdContained_1 = 300;
	const VALUE_ContentIdContained_2 = 301;
	const VALUE_ContentIdContained_3 = 302;
	const VALUE_LanguageId = 1;

	const TABLE_Page = 'pages';
	const TABLE_Content = 'tt_content';

	/**
	 * @var string
	 */
	protected $scenarioDataSetDirectory = 'typo3conf/ext/kb_nescefe/Tests/Functional/DataHandling/Regular/DataSet/';

	public function setUp() {
		parent::setUp();
		$this->importScenarioDataSet('LiveDefaultPages');
		$this->importScenarioDataSet('LiveDefaultElements');

		$this->setUpFrontendRootPage(1, array('typo3conf/ext/kb_nescefe/Tests/Functional/Fixtures/Frontend/JsonRenderer.ts'));
		$this->backendUser->workspace = 0;
	}

	/**
	 * @var array
	 */
	protected $testExtensionsToLoad = array(
		'typo3conf/ext/kb_nescefe',
	);

	/**
	 * MISCELLANEOUS tests
	 */

	public function createContents() {
		// Creating record in column #2 (index=1) of the container
		$this->actionService->createNewRecord(static::TABLE_Content, static::VALUE_PageId, array('header' => 'Testing #1', 'kbnescefe_parentElement' => static::VALUE_ContentIdContainer, 'kbnescefe_parentPosition' => '1', 'colPos' => 10));
		// Creating record after existing contained record
		$this->actionService->createNewRecord(static::TABLE_Content, -static::VALUE_ContentIdContained_2, array('header' => 'Testing #2'));
	}

	public function deleteContainer() {
		$this->actionService->deleteRecord(static::TABLE_Content, static::VALUE_ContentIdContainer);
	}

	public function deleteContained() {
		$this->actionService->deleteRecord(static::TABLE_Content, static::VALUE_ContentIdContained_2);
	}

	public function localizeContainer() {
		$this->actionService->localizeRecord(self::TABLE_Content, self::VALUE_ContentIdContainer, self::VALUE_LanguageId);
	}

	/**
	 * COPY tests
	 */

	public function copyContentFromContainerToOutsideAsFirstElement() {
		$this->actionService->copyRecord(static::TABLE_Content, static::VALUE_ContentIdContained_3, static::VALUE_PageId, array('colPos' => 0));
	}

	public function copyContentFromContainerToOutsideAfterAnotherElement() {
		$this->actionService->copyRecord(static::TABLE_Content, static::VALUE_ContentIdContained_3, -static::VALUE_ContentIdFirst);
	}

	public function copyContentFromOutsideToAfterElementInContainer() {
		$this->actionService->copyRecord(static::TABLE_Content, static::VALUE_ContentIdFirst, -static::VALUE_ContentIdContained_2);
	}

	public function copyContentFromOutsideAsFirstElementIntoContainer() {
		$this->actionService->copyRecord(self::TABLE_Content, self::VALUE_ContentIdFirst, self::VALUE_PageId, array('colPos' => 10, 'kbnescefe_parentPosition' => '1', 'kbnescefe_parentElement' => static::VALUE_ContentIdContainer));
	}

	public function copyContainer() {
		$this->actionService->copyRecord(self::TABLE_Content, self::VALUE_ContentIdContainer, self::VALUE_PageId);
	}

	/**
	 * MOVE tests
	 */

	public function moveContentFromContainerToOutsideAsFirstElement() {
		$this->actionService->moveRecord(static::TABLE_Content, static::VALUE_ContentIdContained_3, static::VALUE_PageId, array('colPos' => 0));
	}

	public function moveContentFromContainerToOutsideAfterAnotherElement() {
		$this->actionService->moveRecord(static::TABLE_Content, static::VALUE_ContentIdContained_3, -static::VALUE_ContentIdFirst);
	}

	public function moveContentFromOutsideToAfterElementInContainer() {
		$this->actionService->moveRecord(static::TABLE_Content, static::VALUE_ContentIdFirst, -static::VALUE_ContentIdContained_2);
	}

	public function moveContentFromOutsideAsFirstElementIntoContainer() {
		$this->actionService->moveRecord(self::TABLE_Content, self::VALUE_ContentIdFirst, self::VALUE_PageId, array('colPos' => 10, 'kbnescefe_parentPosition' => '1', 'kbnescefe_parentElement' => static::VALUE_ContentIdContainer));
	}

	public function moveContainerAfterAnotherElement() {
		$this->actionService->moveRecord(self::TABLE_Content, self::VALUE_ContentIdContainer, -self::VALUE_ContentIdThird);
	}

	public function moveContainerToDifferentPage() {
		$this->actionService->moveRecord(self::TABLE_Content, self::VALUE_ContentIdContainer, self::VALUE_PageIdTarget);
	}

	/**
	 * PAGE tests
	 */

	public function deletePage() {
		$this->actionService->deleteRecord(self::TABLE_Page, self::VALUE_PageId);
	}

	public function copyPage() {
		$newTableIds = $this->actionService->copyRecord(self::TABLE_Page, self::VALUE_PageId, self::VALUE_PageIdTarget);
		$this->recordIds['newPageId'] = $newTableIds[self::TABLE_Page][self::VALUE_PageId];
	}

}

