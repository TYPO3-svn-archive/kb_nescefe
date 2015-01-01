<?php
namespace ThinkopenAt\KbNescefe\Tests\Functional\DataHandling\Regular\Modify;

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

require_once dirname(dirname(__FILE__)) . '/AbstractActionTestCase.php';

/**
 * Miscellaneous functional tests for kb_nescefe hooks for the DataHandler
 */
class ActionTest extends \ThinkopenAt\KbNescefe\Tests\Functional\DataHandling\Regular\AbstractActionTestCase {

	/**
	 * @var string
	 */
	protected $assertionDataSetDirectory = 'typo3conf/ext/kb_nescefe/Tests/Functional/DataHandling/Regular/Modify/DataSet/';

	/**
	 * Content records
	 */

	/**
	 * @test
	 * @see DataSet/createContents.csv
	 */
	public function createContents() {
		parent::createContents();
		$this->assertAssertionDataSet('createContents');

		$responseSections = $this->getFrontendResponse(self::VALUE_PageId)->getResponseSections('ContainerColumn');
		$this->assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Testing #1', 'Testing #2'));
	}

// Is such a test necessary?
// public function modifyContent()

	/**
	 * @test
	 * @see DataSet/deleteContainer.csv
	 */
	public function deleteContainer() {
		parent::deleteContainer();
		$this->assertAssertionDataSet('deleteContainer');

		$responseSections = $this->getFrontendResponse(self::VALUE_PageId)->getResponseSections();
		$this->assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #1', 'Regular Element #3'));
		$this->assertThat($responseSections, $this->getRequestSectionDoesNotHaveRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Container Element #2'));

		$responseSections = $this->getFrontendResponse(self::VALUE_PageId)->getResponseSections('ContainerColumn');
		$this->assertThat($responseSections, $this->getRequestSectionDoesNotHaveRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Contained Element #1', 'Contained Element #2', 'Contained Element #3'));
	}

	/**
	 * @test
	 * @see DataSet/deleteContained.csv
	 */
	public function deleteContained() {
		parent::deleteContained();
		$this->assertAssertionDataSet('deleteContained');

		$responseSections = $this->getFrontendResponse(self::VALUE_PageId)->getResponseSections();
		$this->assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #1', 'Regular Element #3', 'Container Element #2'));

		$responseSections = $this->getFrontendResponse(self::VALUE_PageId)->getResponseSections('ContainerColumn');
		$this->assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Contained Element #1', 'Contained Element #3'));
		$this->assertThat($responseSections, $this->getRequestSectionDoesNotHaveRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Contained Element #2'));
	}

	/**
	 * @test
	 * @see DataSet/deletePage.csv
	 */
	public function deletePage() {
		parent::deletePage();
		$this->assertAssertionDataSet('deletePage');

		$response = $this->getFrontendResponse(self::VALUE_PageId, 0, 0, 0, FALSE);
		$this->assertContains('PageNotFoundException', $response->getError());
	}

	/**
	 * @test
	 * @see DataSet/copyPage.csv
	 */
	public function copyPage() {
		parent::copyPage();
		$this->assertAssertionDataSet('copyPage');

		$responseSections = $this->getFrontendResponse($this->recordIds['newPageId'])->getResponseSections();
		$this->assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
			->setTable(self::TABLE_Page)->setField('title')->setValues('Relations'));
	}

	/**
	 * @test
	 * @see DataSet/localizeContainer.csv
	 */
	public function localizeContainer() {
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_nescefe']['localizeRecursive'] = FALSE;
		parent::localizeContainer();
		$this->assertAssertionDataSet('localizeContainer');

		$responseSections = $this->getFrontendResponse(self::VALUE_PageId, self::VALUE_LanguageId)->getResponseSections();
		$this->assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('[Translate to Dansk:] Container Element #2'));

		$responseSections = $this->getFrontendResponse(self::VALUE_PageId, self::VALUE_LanguageId)->getResponseSections('ContainerColumn');
		$this->assertThat($responseSections, $this->getRequestSectionDoesNotHaveRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('[Translate to Dansk:] Contained Element #1', '[Translate to Dansk:] Contained Element #2', '[Translate to Dansk:] Contained Element #3'));
	}

	/**
	 * @test
	 * @see DataSet/localizeContainerRecursive.csv
	 */
	public function localizeContainerRecursive() {
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_nescefe']['localizeRecursive'] = TRUE;
		parent::localizeContainer();
		$this->assertAssertionDataSet('localizeContainerRecursive');

		$responseSections = $this->getFrontendResponse(self::VALUE_PageId, self::VALUE_LanguageId)->getResponseSections();
		$this->assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('[Translate to Dansk:] Container Element #2'));

		$responseSections = $this->getFrontendResponse(self::VALUE_PageId, self::VALUE_LanguageId)->getResponseSections('ContainerColumn');
		$this->assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('[Translate to Dansk:] Contained Element #1', '[Translate to Dansk:] Contained Element #2', '[Translate to Dansk:] Contained Element #3'));
	}

}

