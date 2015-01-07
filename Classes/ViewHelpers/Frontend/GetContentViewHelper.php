<?php
namespace ThinkopenAt\KbNescefe\ViewHelpers\Frontend;

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
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * ViewHelper that retrieves the content of a kb_nescefe column.
 * By default the content gets rendered like when styles.content.get is
 * used which uses the TypoScript root level object "tt_content" for rendering.
 *
 * The kb_nescefe "getContent" view helper extends the default fluid view
 * helper "f:cObject" so by setting the "typoscriptObjectPath" another TypoScript
 * than "tt_content" can get used for rendering.
 *
 * == Examples ==
 *
 * <code title="Render column content">
 * <h1><nescefe:getContent index="1" /></h1>
 * </code>
 * <output>
 * <div id="c123" class="csc-default">
 *    ... kb_nescefe column #1 content elements rendered by "tt_content" TypoScript like if styles.content.get was used ...
 * <div >
 * </output>
 *
 *
 * <code title="Render column content using alternative TypoScript">
 *
 * In your TypoScript setup:
 * ----------------------------------------------
 * lib.myRenderScript < tt_content
 * lib.myRenderScript.stdWrap.innerWrap >
 * ----------------------------------------------
 *
 * <h1><nescefe:getContent index="1" typoscriptObjectPath="lib.myRenderScript" /></h1>
 * </code>
 * <output>
 *  ... kb_nescefe column #1 content elements rendered by "lib.myRenderScript" ...
 *  ... All content elements will get rendered without any "section_frame" wrapping (<div id="c123" class="csc-default"> | </div>) ...
 * </output>
 *
 * @api
 */
class GetContentViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\CObjectViewHelper {

	/**
	 * @var \ThinkopenAt\KbNescefe\Domain\Repository\ContentRepository
	 * @inject
	 */
	protected $contentRepository;

	/**
	 * Renders a content column.
	 *
	 * @param integer $index: The index of the column which to render.
	 * @param string $typoscriptObjectPath: The TypoScript setup path of the TypoScript which use to render the content elements
	 * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
	 * @return string The rendered column content
	 * @api
	 */
	public function render($index, $typoscriptObjectPath = '') {
		$contentElements = $this->contentRepository->findByParent($this->templateVariableContainer->get('element'), $index, TRUE);
		$content = '';
		$typoscriptObjectPath = $typoscriptObjectPath ? : 'tt_content';
		foreach ($contentElements as $contentElement) {
			$content .= parent::render($typoscriptObjectPath, $contentElement, NULL, 'tt_content');
		}
		return $content;
	}

}

