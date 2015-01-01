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


class Typo3QuerySettings extends \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings {

	/**
	 * Flag if the language overlay should be enabled (default is TRUE).
	 *
	 * @var boolean
	 */
	protected $enableLanguageOverlay = TRUE;

	/**
	 * Flag if the workspace overlay should be enabled (default is TRUE).
	 *
	 * @var boolean
	 */
	protected $enableWorkspaceOverlay = TRUE;

	/*
	 * Sets the flag if a language overlay should be enabled.
	 *
	 * @param boolean $enableLanguageOverlay FALSE if a language overlay should be prevented.
	 * @return \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface
	 * @api
	 */
	public function setEnableLanguageOverlay($enableLanguageOverlay) {
		$this->enableLanguageOverlay = $enableLanguageOverlay;
		return $this;
	}

	/**
	 * Returns the state, if a language overlay should be enabled.
	 *
	 * @return boolean TRUE, if a language overlay should be enabled; otherwise FALSE.
	 */
	public function getEnableLanguageOverlay() {
		return $this->enableLanguageOverlay;
	}

	/**
	 * Sets the flag if a workspace overlay should be enabled.
	 *
	 * @param boolean $enableWorkspaceOverlay FALSE if a workspace overlay should be prevented.
	 * @return \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface
	 * @api
	 */
	public function setEnableWorkspaceOverlay($enableWorkspaceOverlay) {
		$this->enableWorkspaceOverlay = $enableWorkspaceOverlay;
		return $this;
	}

	/**
	 * Returns the state, if a workspace overlay should be enabled.
	 *
	 * @return boolean TRUE, if a workspace overlay should be enabled; otherwise FALSE.
	 */
	public function getEnableWorkspaceOverlay() {
		return $this->enableWorkspaceOverlay;
	}

}

