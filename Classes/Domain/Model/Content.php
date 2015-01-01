<?php
namespace ThinkopenAt\KbNescefe\Domain\Model;

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
 * Domain model for content elements
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */

class Content extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * @var string
	 */
	protected $parentPosition = '';

	/**
	 * @var integer
	 */
	protected $l18nParent = 0;

	/**
	 * @var integer
	 */
	protected $sysLanguageUid = 0;

	/**
	 * @var \ThinkopenAt\KbNescefe\Domain\Model\Content
	 */
	protected $parentElement = NULL;
	
	/**
	 * @var \ThinkopenAt\KbNescefe\Domain\Model\Layout
	 */
	protected $layout;


	/**
	 * Sets the parent position
	 *
	 * @param string $parentPosition
	 * @return void
	 * @api
	 */
	public function setParentPosition($parentPosition) {
		$this->parentPosition = $parentPosition;
	}

	/**
	 * Returns the parentPosition
	 *
	 * @return string
	 * @api
	 */
	public function getParentPosition() {
		return $this->parentPosition;
	}

	/**
	 * Sets the L18N parent
	 *
	 * @param integer $l18nParent
	 * @return void
	 * @api
	 */
	public function setL18nParent($l18nParent) {
		$this->l18nParent = $l18nParent;
	}

	/**
	 * Returns the L18N parent
	 *
	 * @return integer
	 * @api
	 */
	public function getL18nParent() {
		return $this->l18nParent;
	}

	/**
	 * Sets the language uid
	 *
	 * @param integer $sysLanguageUid
	 * @return void
	 * @api
	 */
	public function setSysLanguageUid($sysLanguageUid) {
		$this->sysLanguageUid = $sysLanguageUid;
	}

	/**
	 * Returns the language uid
	 *
	 * @return integer
	 * @api
	 */
	public function getSysLanguageUid() {
		return $this->sysLanguageUid;
	}

	/**
	 * Sets the layout object
	 *
	 * @param \ThinkopenAt\KbNescefe\Domain\Model\Layout $layout
	 * @return void
	 * @api
	 */
	public function setLayout(\ThinkopenAt\KbNescefe\Domain\Model\Layout $layout) {
		$this->layout = $layout;
	}

	/**
	 * Returns the layout object
	 *
	 * @return \ThinkopenAt\KbNescefe\Domain\Model\Layout
	 * @api
	 */
	public function getLayout() {
		return $this->layout;
	}

	/**
	 * Sets the parent element
	 *
	 * @param \ThinkopenAt\KbNescefe\Domain\Model\Content $parentElement
	 * @return void
	 * @api
	 */
	public function setParentElement(\ThinkopenAt\KbNescefe\Domain\Model\Content $parentElement) {
		$this->parentElement = $parentElement;
	}

	/**
	 * Returns the parent element
	 *
	 * @return \ThinkopenAt\KbNescefe\Domain\Model\Content
	 * @api
	 */
	public function getParentElement() {
		return $this->parentElement;
	}

}

