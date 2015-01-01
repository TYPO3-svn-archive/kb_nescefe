<?php
namespace ThinkopenAt\KbNescefe\Tests\Functional\DataHandling\Regular\Copy;

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
 * Functional "copy" tests for kb_nescefe hooks for the DataHandler
 */
class ActionTest extends \ThinkopenAt\KbNescefe\Tests\Functional\DataHandling\Regular\AbstractActionTestCase {

	/**
	 * @var string
	 */
	protected $assertionDataSetDirectory = 'typo3conf/ext/kb_nescefe/Tests/Functional/DataHandling/Regular/Copy/DataSet/';

	/**
	 * @test
	 * @see DataSet/copyContentFromContainerToOutsideAsFirstElement.csv
	 */
	public function copyContentFromContainerToOutsideAsFirstElement() {
		parent::copyContentFromContainerToOutsideAsFirstElement();
		$this->assertAssertionDataSet('copyContentFromContainerToOutsideAsFirstElement');

		$responseSections = $this->getFrontendResponse(self::VALUE_PageId)->getResponseSections();
		$this->assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Contained Element #3 (copy 1)'));

		$responseSections = $this->getFrontendResponse(self::VALUE_PageId)->getResponseSections('ContainerColumn');
		$this->assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Contained Element #3'));
	}

	/**
	 * @test
	 * @see DataSet/copyContentFromContainerToOutsideAfterAnotherElement.csv
	 */
	public function copyContentFromContainerToOutsideAfterAnotherElement() {
		parent::copyContentFromContainerToOutsideAfterAnotherElement();
		$this->assertAssertionDataSet('copyContentFromContainerToOutsideAfterAnotherElement');

		$responseSections = $this->getFrontendResponse(self::VALUE_PageId)->getResponseSections();
		$this->assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Contained Element #3 (copy 1)'));

		$responseSections = $this->getFrontendResponse(self::VALUE_PageId)->getResponseSections('ContainerColumn');
		$this->assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Contained Element #3'));
	}

	/**
	 * @test
	 * @see DataSet/copyContentFromOutsideToAfterElementInContainer.csv
	 */
	public function copyContentFromOutsideToAfterElementInContainer() {
		parent::copyContentFromOutsideToAfterElementInContainer();
		$this->assertAssertionDataSet('copyContentFromOutsideToAfterElementInContainer');

		$responseSections = $this->getFrontendResponse(self::VALUE_PageId)->getResponseSections();
		$this->assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #1'));

		$responseSections = $this->getFrontendResponse(self::VALUE_PageId)->getResponseSections('ContainerColumn');
		$this->assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #1 (copy 1)'));
	}

	/**
	 * @test
	 * @see DataSet/copyContentFromOutsideToAfterElementInContainer.csv
	 */
	public function copyContentFromOutsideAsFirstElementIntoContainer() {
		parent::copyContentFromOutsideAsFirstElementIntoContainer();
		$this->assertAssertionDataSet('copyContentFromOutsideAsFirstElementIntoContainer');

		$responseSections = $this->getFrontendResponse(self::VALUE_PageId)->getResponseSections();
		$this->assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #1'));

		$responseSections = $this->getFrontendResponse(self::VALUE_PageId)->getResponseSections('ContainerColumn');
		$this->assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #1 (copy 1)'));
	}

	/**
	 * @test
	 * @see DataSet/copyContainer.csv
	 */
	public function copyContainer() {
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_nescefe']['copyRecursive'] = FALSE;
		parent::copyContainer();
		$this->assertAssertionDataSet('copyContainer');

		$responseSections = $this->getFrontendResponse(self::VALUE_PageId)->getResponseSections();
		$this->assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Container Element #2', 'Container Element #2 (copy 1)'));

		$responseSections = $this->getFrontendResponse(self::VALUE_PageId)->getResponseSections('ContainerColumn');
		$this->assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Contained Element #1', 'Contained Element #2', 'Contained Element #3'));
		$this->assertThat($responseSections, $this->getRequestSectionDoesNotHaveRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Contained Element #1 (copy 1)', 'Contained Element #2 (copy 1)', 'Contained Element #3 (copy 1)'));
	}

	/**
	 * @test
	 * @see DataSet/copyContainer.csv
	 */
	public function copyContainerRecursive() {
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_nescefe']['copyRecursive'] = TRUE;
		parent::copyContainer();
		$this->assertAssertionDataSet('copyContainerRecursive');

		$responseSections = $this->getFrontendResponse(self::VALUE_PageId)->getResponseSections();
		$this->assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Container Element #2', 'Container Element #2 (copy 1)'));

		$responseSections = $this->getFrontendResponse(self::VALUE_PageId)->getResponseSections('ContainerColumn');
		$this->assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
			->setTable(self::TABLE_Content)->setField('header')->setValues('Contained Element #1', 'Contained Element #2', 'Contained Element #3', 'Contained Element #1 (copy 1)', 'Contained Element #2 (copy 1)', 'Contained Element #3 (copy 1)'));
	}

}

