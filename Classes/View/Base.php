<?php
namespace ThinkopenAt\KbNescefe\View;

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
 * This class represents a kb_nescefe template.
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */

use \ThinkopenAt\KbNescefe\Exceptions\InvalidSettingsException;
use \TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class Base extends \TYPO3\CMS\Fluid\View\StandaloneView {

	/**
	 * @var \ThinkopenAt\KbNescefe\Domain\Model\Layout
	 */
	protected $layout = NULL;

	/**
	 * @var string The absolute filename of the template being used
	 */
	protected $fileName = '';

	/**
	 * Sets the layout which is used
	 *
	 * @param \ThinkopenAt\KbNescefe\Domain\Model\Layout $layout: The layout which is being used
	 * @return void
	 */
	public function setLayout(\ThinkopenAt\KbNescefe\Domain\Model\Layout $layout) {
		$this->layout = $layout;
		$templateFile = $this->getTemplateFileFromLayout();
		$this->fileName = GeneralUtility::getFileAbsFileName($templateFile);
		if (!@is_file($this->fileName))	{
			throw new InvalidSettingsException('kb_nescefe: The template file "'.$templateFile.'" does not exist!');
		}
		$this->setTemplatePathAndFilename($this->fileName);
	}


	/**
	 * Shall return the template file which to use by returning the appropriate value from the layout
	 *
	 * @return string The template file name
	 */
	abstract protected function getTemplateFileFromLayout();

}

