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
 * Domain model for a layout
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */

class Layout extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * @var string
	 */
	protected $name = '';
	
	/**
	 * @var string
	 */
	protected $fetemplate;

	/**
	 * @var string
	 */
	protected $betemplate;


	/**
	 * Sets the name
	 *
	 * @param string $name
	 * @return void
	 * @api
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Returns the name
	 *
	 * @return string
	 * @api
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets the FE-Template
	 *
	 * @param string $fetemplate
	 * @return void
	 * @api
	 */
	public function setFetemplate($fetemplate) {
		$this->fetemplate= $fetemplate;
	}

	/**
	 * Returns the FE-Template
	 *
	 * @return string
	 * @api
	 */
	public function getFetemplate() {
		return $this->fetemplate;
	}

	/**
	 * Sets the BE-Template
	 *
	 * @param string $betemplate
	 * @return void
	 * @api
	 */
	public function setBetemplate($betemplate) {
		$this->betemplate= $betemplate;
	}

	/**
	 * Returns the BE-Template
	 *
	 * @return string
	 * @api
	 */
	public function getBetemplate() {
		return $this->betemplate;
	}

}

