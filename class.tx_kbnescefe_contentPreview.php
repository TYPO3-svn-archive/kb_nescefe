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

	function tx_kbnescefe_contentPreview() {
		$this->containerElementColPos = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_nescefe']['containerElementColPos'];
	}


	function renderPluginPreview($params, &$parentObject)	{
		if ($parentObject->defLangBinding && ($parentObject->tt_contentConfig['sys_language_uid'] == 0)) {
			$lP = 0;
		} else {
			$lP = $parentObject->tt_contentConfig['sys_language_uid'];
		}
		return $this->renderPreview($parentObject->table, $params['row'], 0, $lP, $parentObject);
	}

	function renderPreview($table, $row, $isRTE, $lP, &$pObj)	{
		global $LANG;
		if (($row['CType']=='kb_nescefe_pi1') || (($row['CType'] == 'list') && ($row['list_type'] == 'kb_nescefe_pi1'))) {
			if ($row['container'])	{
				$this->cObj = t3lib_div::makeInstance('tslib_cObj');
				$this->pObj = &$pObj;
				$this->lP = $lP;
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


	function getContentElements($contentAreas)	{
		$cpospart = '';
		$showHidden = $this->pObj->tt_contentConfig['showHidden']?'':t3lib_BEfunc::BEenableFields('tt_content');
		$showLanguage = $this->pObj->defLangBinding && $this->lP==0 ? ' AND sys_language_uid IN (0,-1)' : ' AND sys_language_uid='.$this->lP;
		$cpospart .= ' AND parentPosition LIKE \''.$this->id.'__%\'';

		$queryParts = $this->pObj->makeQueryArray('tt_content', $this->pageID, $cpospart.$showHidden.$showLanguage);
		$result = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($queryParts);
		$storage = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))	{
			$storage[$row['parentPosition']][] = $row;
		}
		$this->func->getSectionMax($storage);
		return $storage;
	}
	
	function func_SECTIONCOUNT($contentElements, $nkey, &$callObj)	{
		$code = intval($this->func->sectionElementMaxIdx[$nkey])+1;
		return $code;
	}


	function func_NEWSECTION($contentElements, $nkey, &$callObj)	{
		global $LANG;
		if (!$this->sectionIdxArr[$nkey])	{
			$this->sectionIdxArr[$nkey] = $this->sectionIdx++;
		}
		$code = '<input type="button" name="newSection_'.$nkey.'" value="'.htmlspecialchars(str_replace('###LABEL###', is_string($callObj->paths[$nkey])?$callObj->paths[$nkey]:$LANG->sL('LLL:EXT:kb_nescefe/locallang.xml:sectionList'), $LANG->sL('LLL:EXT:kb_nescefe/locallang.xml:newSection'))).'" onclick="document.location.href=\''.t3lib_div::linkThisScript(array('newSection' => $nkey)).'\'; return false;">';
		return $code;
	}

	function func_CONTENT($contentElements, $nkey, &$callObj)	{
		$code = '';
		if (is_array($contentElements[$this->id.'__'.$nkey]))	{
			$keys = array_keys($contentElements[$this->id.'__'.$nkey]);
			$cnt = 0;
			foreach ($contentElements[$this->id.'__'.$nkey] as $row)	{
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
				$code .= $this->pObj->tt_content_drawHeader($row, $this->pObj->tt_contentConfig['showInfo']?15:5, $this->pObj->defLangBinding && $this->lP>0, true);
				$code .= $this->pObj->tt_content_drawItem($row, $isRTE, $this->lP);
				$cnt++;
			}
		}
		return $code;
	}


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
	 * Copy from: typo3/sysext/cms/layout/class.tx_cms_layout.php
	 *
	 * @param	integer		Page id where to create the element.
	 * @param	integer		Preset: Column position value
	 * @param	integer		Preset: Sys langauge value
	 * @return	string		String for onclick attribute.
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
