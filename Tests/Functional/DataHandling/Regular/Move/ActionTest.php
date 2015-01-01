<?php
namespace ThinkopenAt\KbNescefe\Tests\Functional\DataHandling\Regular\Move;

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
 * Functional "move" tests for kb_nescefe hooks for the DataHandler
 */
class ActionTest extends \ThinkopenAt\KbNescefe\Tests\Functional\DataHandling\Regular\AbstractActionTestCase {

	/**
	 * @var string
	 */
	protected $assertionDataSetDirectory = 'typo3conf/ext/kb_nescefe/Tests/Functional/DataHandling/Regular/Move/DataSet/';

	/**
	 * @test
	 * @see DataSet/moveContentFromContainerToOutsideAsFirstElement.csv
	 */
	public function moveContentFromContainerToOutsideAsFirstElement() {
		parent::moveContentFromContainerToOutsideAsFirstElement();
		$this->assertAssertionDataSet('moveContentFromContainerToOutsideAsFirstElement');

		$responseSections = $this->getFrontendResponse(self::VALUE_PageId)->getResponseSections();
		$this->assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Contained Element #3'));

		$responseSections = $this->getFrontendResponse(self::VALUE_PageId)->getResponseSections('ContainerColumn');
		$this->assertThat($responseSections, $this->getRequestSectionDoesNotHaveRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Contained Element #3'));
	}

	/**
	 * @test
	 * @see DataSet/moveContentFromContainerToOutsideAfterAnotherElement.csv
	 */
	public function moveContentFromContainerToOutsideAfterAnotherElement() {
		parent::moveContentFromContainerToOutsideAfterAnotherElement();
		$this->assertAssertionDataSet('moveContentFromContainerToOutsideAfterAnotherElement');

		$responseSections = $this->getFrontendResponse(self::VALUE_PageId)->getResponseSections();
		$this->assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Contained Element #3'));

		$responseSections = $this->getFrontendResponse(self::VALUE_PageId)->getResponseSections('ContainerColumn');
		$this->assertThat($responseSections, $this->getRequestSectionDoesNotHaveRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Contained Element #3'));
	}

	/**
	 * @test
	 * @see DataSet/moveContentFromOutsideToAfterElementInContainer.csv
	 */
	public function moveContentFromOutsideToAfterElementInContainer() {
		parent::moveContentFromOutsideToAfterElementInContainer();
		$this->assertAssertionDataSet('moveContentFromOutsideToAfterElementInContainer');

		$responseSections = $this->getFrontendResponse(self::VALUE_PageId)->getResponseSections();
		$this->assertThat($responseSections, $this->getRequestSectionDoesNotHaveRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #1'));

		$responseSections = $this->getFrontendResponse(self::VALUE_PageId)->getResponseSections('ContainerColumn');
		$this->assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #1'));
	}

	/**
	 * @test
	 * @see DataSet/moveContentFromOutsideToAfterElementInContainer.csv
	 */
	public function moveContentFromOutsideAsFirstElementIntoContainer() {
		parent::moveContentFromOutsideAsFirstElementIntoContainer();
		$this->assertAssertionDataSet('moveContentFromOutsideAsFirstElementIntoContainer');

		$responseSections = $this->getFrontendResponse(self::VALUE_PageId)->getResponseSections();
		$this->assertThat($responseSections, $this->getRequestSectionDoesNotHaveRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #1'));

		$responseSections = $this->getFrontendResponse(self::VALUE_PageId)->getResponseSections('ContainerColumn');
		$this->assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #1'));
	}

	/**
	 * @test
	 * @see DataSet/moveContainerAfterAnotherElement.csv
	 */
	public function moveContainerAfterAnotherElement() {
		parent::moveContainerAfterAnotherElement();
		$this->assertAssertionDataSet('moveContainerAfterAnotherElement');

		$responseSections = $this->getFrontendResponse(self::VALUE_PageId)->getResponseSections();
		$this->assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Container Element #2'));

		$responseSections = $this->getFrontendResponse(self::VALUE_PageId)->getResponseSections('ContainerColumn');
		$this->assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Contained Element #1', 'Contained Element #2', 'Contained Element #3'));
	}

	/**
	 * @test
	 * @see DataSet/moveContainerToDifferentPage.csv
	 */
	public function moveContainerToDifferentPage() {
		parent::moveContainerToDifferentPage();
		$this->assertAssertionDataSet('moveContainerToDifferentPage');

		$responseSectionsSource = $this->getFrontendResponse(self::VALUE_PageId)->getResponseSections();
		$this->assertThat($responseSectionsSource, $this->getRequestSectionHasRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #1'));
		$this->assertThat($responseSectionsSource, $this->getRequestSectionDoesNotHaveRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Container Element #2'));
		$responseSectionsSource = $this->getFrontendResponse(self::VALUE_PageId)->getResponseSections('ContainerColumn');
		$this->assertThat($responseSectionsSource, $this->getRequestSectionDoesNotHaveRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Contained Element #1', 'Contained Element #2', 'Contained Element #3'));

		$responseSectionsTarget = $this->getFrontendResponse(self::VALUE_PageIdTarget)->getResponseSections();
		$this->assertThat($responseSectionsTarget, $this->getRequestSectionHasRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Container Element #2'));
		$responseSectionsSource = $this->getFrontendResponse(self::VALUE_PageIdTarget)->getResponseSections('ContainerColumn');
		$this->assertThat($responseSectionsSource, $this->getRequestSectionHasRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Contained Element #1', 'Contained Element #2', 'Contained Element #3'));
	}

}

