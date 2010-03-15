<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006-2010 Bernhard Kraft <kraftb@think-open.at>
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
 * Plugin 'Content element container' for the 'kb_nescefe' extension.
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('kb_nescefe').'class.tx_kbnescefe_func.php');

class tx_kbnescefe_pi1 extends tslib_pibase {
	var $prefixId = 'tx_kbnescefe_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_kbnescefe_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'kb_nescefe';	// The extension key.
	var $pi_checkCHash = TRUE;
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		$this->container = $GLOBALS['TSFE']->sys_page->getRawRecord('tx_kbnescefe_containers', $this->cObj->data['container']);
		if (is_array($this->container)) {
			$file = t3lib_div::getFileAbsFileName($this->container['fetemplate']);
			if (@is_file($file)) {
				$this->func = t3lib_div::makeInstance('tx_kbnescefe_func');
				$this->func->init($GLOBALS['TSFE']->id, $this->cObj->data['uid'], $GLOBALS['TSFE']->sys_language_content, $this->container, array(), $this->cObj);
				$template = t3lib_div::getURL($file);
				$contentAreas = $this->func->getContentAreas($template);
				$paths = $this->func->getContentElementPaths($contentAreas);
				$contentElements = $this->getContentElements();
				return $this->func->renderContentAreas($template, $contentElements, $contentAreas, $this);
			} else {
				return $this->pi_getLL('no_template_file');
			}
		} else {
			return $this->pi_getLL('no_container');
		}
	}

	function getContentElements() {
		$showLanguage = ($GLOBALS['TSFE']->sys_language_content==0) ? ' AND sys_language_uid IN (0,-1)' : ' AND sys_language_uid='.$GLOBALS['TSFE']->sys_language_content;
		$ef = $GLOBALS['TSFE']->sys_page->enableFields('tt_content');
		$pid = $this->cObj->data['pid'];
		$rows = $GLOBALS['TSFE']->sys_page->getRecordsByField('tt_content', 'pid', $pid, ' AND parentPosition LIKE \''.$this->cObj->data['uid'].'\_\_%\''.$ef, '', 'sorting');
		$storage = array();
		if (is_array($rows)) {
			foreach ($rows as $row) {
				$OLrow = $GLOBALS['TSFE']->sys_page->versionOL('tt_content', $row);
				if ($OLrow) {
					$storage[$row['parentPosition']][] = $OLrow;
				} else {
					$storage[$row['parentPosition']][] = $row;
				}
			}
		}
		$this->func->getSectionMax($storage);
		return $storage;
	}

	function func_CONTENT($contentElements, $nkey, &$callObj) {
		$id = $this->cObj->data['uid'];
		$code = '';
		if (is_array($contentElements[$id.'__'.$nkey])) {
			$localCObj = clone($this->cObj);

			$parts = explode('_', $nkey);
			$c = 0;
			$res = array();
			foreach ($parts as $part) {
				if (($c+1)%2) {
					$res[] = $part;
				}
				$c++;
			}
			$ckey = preg_replace('/[^a-zA-Z0-9_]/', '_', implode('_', $res));

			$conf = false;
			$conf = trim($this->conf['renderObj.'][$this->container['uid'].'.'][$ckey.'.'][$GLOBALS['TSFE']->lang]);
			$confArr = $this->conf['renderObj.'][$this->container['uid'].'.'][$ckey.'.'][$GLOBALS['TSFE']->lang.'.'];
			if (!$conf) {
				$conf = trim($this->conf['renderObj.'][$this->container['uid'].'.'][$ckey]);
				$confArr = $this->conf['renderObj.'][$this->container['uid'].'.'][$ckey.'.'];
			}
			if (!$conf) {
				$conf = trim($this->conf['renderObj.'][$this->container['uid']]);
				$confArr = $this->conf['renderObj.'][$this->container['uid'].'.'];
			}
			if (!$conf) {
				$conf = trim($this->conf['renderObj']);
				$confArr = $this->conf['renderObj.'];
			}
			if (!$conf) {
				$conf = $GLOBALS['TSFE']->tmpl->setup['tt_content'];
				$confArr = $GLOBALS['TSFE']->tmpl->setup['tt_content.'];
			}

			foreach ($contentElements[$id.'__'.$nkey] as $row) {
				$localCObj->start($row, 'tt_content');
				$code .= $localCObj->cObjGetSingle($conf, $confArr, 'tx_kbnescefe_pi1:'.$id.'__'.$nkey);
			}
		}
		return $code;
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/kb_nescefe/pi1/class.tx_kbnescefe_pi1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/kb_nescefe/pi1/class.tx_kbnescefe_pi1.php']);
}

?>
