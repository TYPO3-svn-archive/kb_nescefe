<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006-2010 Bernhard Kraft (kraftb@think-open.at)
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
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */

require_once(t3lib_extMgm::extPath('kb_nescefe').'class.tx_kbnescefe_func.php');
require_once(t3lib_extMgm::extPath('cms').'tslib/class.tslib_content.php');
class tx_kbnescefe_contentPreview	{
	var $container = false;
	var $template = false;
	var $idx = 1;
	var $sectionIdx = 1;
	var $sectionElementMaxIdx = array();
	var $containerElementColPos = 0;


	/*
	 * Constructor: Initializes this content preview instance
	 *
	 * @return void
	 */
	function tx_kbnescefe_contentPreview() {
		$this->containerElementColPos = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_nescefe']['containerElementColPos'];
	}


	/*
	 * Renders the content preview for a kb_nescefe plugin: It will show the columns of the container and its contents
	 * This is the hook method called from within the page module (tx_cms_layout)
	 *
	 * @param array Contains some parameters passed to this method by the page module
	 * @param tx_cms_layout The parent class from which this hook is called
	 * @return string The rendered preview content
	 * @see: EXT:cms/layout/class.tx_cms_layout.php:tx_cms_layout->tt_content_drawItem (search for "list_type_Info" / hook)
	 */
	function renderPluginPreview($params, &$parentObject)	{
		if (get_class($parentObject) === 'tx_templavoila_module1') {
			return 'The extension "kb_nescefe" is not compatible with TemplaVoila. Use TemplaVoila Flexible Content Elements instead.';
		}
		if ($parentObject->defLangBinding && ($parentObject->tt_contentConfig['sys_language_uid'] == 0)) {
			$lP = 0;
		} else {
			$lP = $parentObject->tt_contentConfig['sys_language_uid'];
		}
		return $this->renderPreview($parentObject->table, $params['row'], 0, $lP, $parentObject);
	}


	/*
	 * Does the real rendering of the plugin content (container and contents)
	 *
	 * @param string The table for which the preview should get rendered (should usually always be "tt_content")
	 * @param array The database row of the plugin/container record (tt_content row) being rendered
	 * @param boolean Not used here
	 * @param integer The language for which to render the output - this determines which container content will get fetched
	 * @param tx_cms_layout A pointer to the parent object instance (page module)
	 * @return string The rendered content
	 */
	function renderPreview($table, $row, $isRTE, $lP, &$pObj)	{
		global $LANG;
		if (($row['CType']=='kb_nescefe_pi1') || (($row['CType'] == 'list') && ($row['list_type'] == 'kb_nescefe_pi1'))) {
			if ($row['container'])	{
				$this->cObj = t3lib_div::makeInstance('tslib_cObj');
				$this->pObj = &$pObj;

				$langField = $GLOBALS['TCA'][$table]['ctrl']['languageField'];
				if ($langField) {
					$this->lP = $row[$langField];
				} else {
					$this->lP = $lP;
				}

				$this->RTE = $GLOBALS['BE_USER']->isRTE();
				$this->pageID = $row['pid'];
				$this->tsConfig = t3lib_BEfunc::getModTSconfig($this->pageID, 'mod.tx_kbnescefe');
				$this->pageTitleParamForAltDoc = '&recTitle='.rawurlencode(t3lib_BEfunc::getRecordTitle('pages', tx_kbnescefe_func::getRecord('pages',$this->pageID), 1));
				$this->id = $row['uid'];
				$this->newSection = t3lib_div::_GP('newSection');
				$this->container = tx_kbnescefe_func::getRecord('tx_kbnescefe_containers', $row['container']);
				$this->containerUid = $row['container'];
				$this->func = t3lib_div::makeInstance('tx_kbnescefe_func');
				$this->func->init($this->pageID, $this->id, $this->lP, $this->container, $this->tsConfig, $this->cObj);
				if (is_array($this->container))	{
					$file = t3lib_div::getFileAbsFileName($this->container['betemplate']);
					if (@is_file($file))	{
						$template = t3lib_div::getURL($file);
						$template = $this->processTemplate($template);
						$contentAreas = $this->func->getContentAreas($template);
						$contentElements = $this->getContentElements($contentAreas);
						$this->func->getContentElementPaths($contentAreas);
						$output = $this->func->renderContentAreas($template, $contentElements, $contentAreas, $this);
						return $output;
					} else	{
						return $LANG->sL('LLL:EXT:kb_nescefe/locallang.xml:no_betemplatefile');
					}
				} else	{
					return $LANG->sL('LLL:EXT:kb_nescefe/locallang.xml:container_invalid');
				}
			} else	{
				return '<strong>'.$LANG->sL('LLL:EXT:kb_nescefe/locallang.xml:select_container').'</strong>';
			}
		}
	}

	/*
	 * Processes the BE template: Checks if there are command statements at the top of the file, performs the requested actions and removes them before returning
	 *
	 * @param string The template as read from file
	 * @return string The template with command statements in the header removed
	 */
	function processTemplate($template) {
		$lines = explode(chr(10), $template);
		do {
			$line = array_shift($lines);
			$processedCommand = $this->processCommand($line);
			if (!$processedCommand) {
				array_unshift($lines, $line);
			}
		} while ($processedCommand);
		return implode(chr(10), $lines);
	}

	/*
	 * Processes template commands
	 *
	 * @param string One of the first lines of a template file (which can contain commands)
	 * @return boolean True when a command was found and processed
	 */
	function processCommand($line) {
		list($command, $params) = explode('=', $line, 2);
		switch (trim($command)) {
			case 'INCLUDE_STYLE':
				$params = trim($params);
				$file = t3lib_div::getFileAbsFileName($params);
				if (is_file($file) && is_readable($file)) {
					$GLOBALS['SOBE']->doc->inDocStylesArray['kb_nescefe_'.md5($params)] = t3lib_div::getURL($file);
				}
				return true;
			break;
			default:
				return false;
			break;
		}
		return false;
	}

	/*
	 * Retrieves the content elements inside the currently rendered container
	 *
	 * @param array The content areas of the current container
	 * @return array All content elements found for the currently rendered container
	 */
	function getContentElements($contentAreas)	{
		$cpospart = '';
		$showHidden = $this->pObj->tt_contentConfig['showHidden']?'':t3lib_BEfunc::BEenableFields('tt_content');
		$showLanguage = $this->pObj->defLangBinding && $this->lP==0 ? ' AND sys_language_uid IN (0,-1)' : ' AND sys_language_uid='.$this->lP;
		$cpospart .= ' AND parentPosition LIKE \''.$this->id.'\_\_%\'';

		$queryParts = $this->pObj->makeQueryArray('tt_content', $this->pageID, $cpospart.$showHidden.$showLanguage);
		$result = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($queryParts);
		$storage = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))	{
			$storage[$row['parentPosition']][] = $row;
		}
		$this->func->getSectionMax($storage);
		return $storage;
	}
	
	/*
	 * Returns the number of sections/content areas for the currently rendered container
	 *
	 * @param array All content elements of the current container
	 * @param string The key for the current content area
	 * @param object A pointer to the object instance from which this callback function is called (usually a instance of "tx_kbnescefe_func")
	 * @return string The number of the last repeatable section for the current content area
	 */
	function func_SECTIONCOUNT($contentElements, $nkey, &$callObj)	{
		$code = intval($this->func->sectionElementMaxIdx[$nkey])+1;
		return $code;
	}


	/*
	 * Shows a button/link for creating a new section
	 *
	 * @param array All content elements of the current container
	 * @param string The key for the current content area
	 * @param object A pointer to the object instance from which this callback function is called (usually a instance of "tx_kbnescefe_func")
	 * @return string An interface element (<input type="button" .../>) for creating a new section
	 */
	function func_NEWSECTION($contentElements, $nkey, &$callObj)	{
		global $LANG;
		if (!$this->sectionIdxArr[$nkey])	{
			$this->sectionIdxArr[$nkey] = $this->sectionIdx++;
		}
		$code = '<input type="button" name="newSection_'.$nkey.'" value="'.htmlspecialchars(str_replace('###LABEL###', is_string($callObj->paths[$nkey])?$callObj->paths[$nkey]:$LANG->sL('LLL:EXT:kb_nescefe/locallang.xml:sectionList'), $LANG->sL('LLL:EXT:kb_nescefe/locallang.xml:newSection'))).'" onclick="document.location.href=\''.t3lib_div::linkThisScript(array('newSection' => $nkey)).'\'; return false;">';
		return $code;
	}

	/*
	 * Renders the content elements inside a kb_nescefe container column / content area
	 *
	 * @param array All content elements of the current container
	 * @param string The key for the current content area
	 * @param object A pointer to the object instance from which this callback function is called (usually a instance of "tx_kbnescefe_func")
	 * @return string The rendered content elements
	 */
	function func_CONTENT($contentElements, $nkey, &$callObj)	{
		$code = '';
		if (is_array($contentElements[$this->id.'__'.$nkey]))	{
			$keys = array_keys($contentElements[$this->id.'__'.$nkey]);
			$cnt = 0;
			foreach ($contentElements[$this->id.'__'.$nkey] as $row)	{
				$row_code = '';
				if ($cnt)		{
					if ($cnt>1)	{
						$this->pObj->tt_contentData['prev'][$row['uid']] = -$contentElements[$this->id.'__'.$nkey][$keys[$cnt-2]]['uid'];
					} else	{
						$this->pObj->tt_contentData['prev'][$row['uid']] = $this->pageID;
					}
				}
				$this->pObj->tt_contentData['next'][$row['uid']] = -$contentElements[$this->id.'__'.$nkey][$keys[$cnt+1]]['uid'];
				for ($x = 0; $x < 1; $x++)	{
					$this->pObj->tt_contentData['nextThree'][$row['uid']] .= $contentElements[$this->id.'__'.$nkey][$keys[$cnt+$x]]['uid']?($contentElements[$this->id.'__'.$nkey][$keys[$cnt+$x]]['uid'].','):'';
				}

				$isRTE = $this->RTE && $this->pObj->isRTEforField('tt_content', $row, 'bodytext');
				$row_code .= $this->pObj->tt_content_drawHeader($row, $this->pObj->tt_contentConfig['showInfo']?15:5, $this->pObj->defLangBinding && $this->lP>0, true);

					// Take care of new 4.4 skin
				if (t3lib_div::compat_version('4.4')) {
					$row_code .= '<div '.($row['_ORIG_uid'] ? ' class="ver-element"' :'').'>'.$this->pObj->tt_content_drawItem($row, $isRTE, $this->lP).'</div>';
					$row_code .= '</div>';
					$statusHidden = ($this->pObj->isDisabled('tt_content', $row) ? ' t3-page-ce-hidden' : '');
					$row_code = '<div class="t3-page-ce' . $statusHidden . '">' . $row_code. '</div>';
				} else {
					$row_code .= $this->pObj->tt_content_drawItem($row, $isRTE, $this->lP);
				}
				$code .= $row_code;
				$cnt++;
			}
		}
		return $code;
	}


	/*
	 * Renders the header for a kb_nescefe content column
	 *
	 * @param array All content elements of the current container
	 * @param string The key for the current content area
	 * @param object A pointer to the object instance from which this callback function is called (usually a instance of "tx_kbnescefe_func")
	 * @return string The rendered column header
	 */
	function func_HEADER($contentElements, $nkey, &$callObj) {
		global $LANG;
		$code = '';
		$this->option_newWizard = $this->pObj->option_newWizard;
		$earr = array();
		if (is_array($contentElements[$nkey]))	{
			foreach ($contentElements[$nkey] as $row) {
				if ($row['uid']) {
					$earr[] = $row['uid'];
				}
			}
		}
		$editUidList = implode(',', $earr);
		$newP = $this->newContentElementOnClick($this->pageID, $this->containerElementColPos, $this->lP, $this->id.'__'.$nkey);
		$skey = preg_replace('/\_([^_]+)_([^_]+)$/', '_$2', $nkey);
		$code .= $this->pObj->tt_content_drawColHeader(str_replace('###IDX###', $this->idx++, is_string($this->paths[$nkey])?$this->paths[$nkey]:(is_string($callObj->sectionLabels[$skey])?$callObj->sectionLabels[$skey]:$LANG->sL('LLL:EXT:kb_nescefe/locallang.php:column'))), ($this->pObj->doEdit&&count($contentElements[$nkey])?'&edit[tt_content]['.$editUidList.']=edit'.$this->pageTitleParamForAltDoc:''), $newP);
		return $code;
	}


	/**
	 * Creates onclick-attribute content for a new content element
	 * Modified copy from: typo3/sysext/cms/layout/class.tx_cms_layout.php
	 *
	 * @param integer Page id where to create the element.
	 * @param integer Preset: Column position value
	 * @param integer Preset: Sys langauge value
	 * @param string The position in the parent container element
	 * @return string String for onclick attribute.
	 * @see getTable_tt_content()
	 */
	function newContentElementOnClick($id,$colPos,$sys_language,$parentPosition) {
		if ($this->option_newWizard)	{
			$onClick="window.location.href='db_new_content_el.php?id=".$id.'&colPos='.$colPos.'&sys_language_uid='.$sys_language.'&parentPosition='.$parentPosition.'&uid_pid='.$id.'&returnUrl='.rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI'))."';";
		} else {
			$onClick=t3lib_BEfunc::editOnClick('&edit[tt_content]['.$id.']=new&defVals[tt_content][colPos]='.$colPos.'&defVals[tt_content][sys_language_uid]='.$sys_language.'&defVals[tt_content][parentPosition]='.$parentPosition,$this->backPath);
		}
		return $onClick;
	}

}




if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['kb_nescefe/class.tx_kbnescefe_contentPreview.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['kb_nescefe/class.tx_kbnescefe_contentPreview.php']);
}

?>
