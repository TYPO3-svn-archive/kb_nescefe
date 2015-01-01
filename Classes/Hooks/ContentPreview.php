<?php
namespace ThinkopenAt\KbNescefe\Hooks;

/***************************************************************
*  Copyright notice
*
*  (c) 2006-2015 Bernhard Kraft (kraftb@think-open.at)
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
 * Content preview hook
 * This class is just a wrapper for \thinkopenAt\KbNescefe\Controller\ContentPreviewController which
 * uses extbase. This class ist just a wrapper for getting called by the core.
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */

use \TYPO3\CMS\Core\Utility\GeneralUtility;

class ContentPreview {

	/*
	 * Renders the content preview for a kb_nescefe plugin: It will show the columns of the container and its contents
	 * This is the hook method called from within the page module (PageLayoutView)
	 *
	 * @param array $params: Contains some parameters passed to this method by the page module
	 * @param \TYPO3\CMS\Backend\View\PageLayoutView $parentObject: The parent class from which this hook is called
	 * @return string The rendered preview content
	 * @see: EXT:backend/Classes/View/PageLayoutView.php:PageLayoutView->tt_content_drawItem (search for "list_type_Info" / hook)
	 */
	public function renderPluginPreview($params, $parentObject) {
		$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		$contentPreviewController = $objectManager->get('ThinkopenAt\KbNescefe\Controller\ContentPreviewController');
		return $contentPreviewController->renderPluginPreview($params, $parentObject);
	}

}

