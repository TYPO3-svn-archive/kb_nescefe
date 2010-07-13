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
 * Item processing class
 * TODO: This code is probably outdated. AFAIK parentPosition pointers can not have "*" in them.
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */

require_once(t3lib_extMgm::extPath('kb_nescefe').'class.tx_kbnescefe_func.php');
require_once(t3lib_extMgm::extPath('kb_nescefe').'class.tx_kbnescefe_contentPreview.php');
require_once(PATH_t3lib.'class.t3lib_recordlist.php');
require_once(PATH_typo3.'class.db_list.inc');
require_once(t3lib_extMgm::extPath('cms').'layout/class.tx_cms_layout.php');
require_once(t3lib_extMgm::extPath('cms').'tslib/class.tslib_content.php');
class tx_kbnescefe_itemproc {
	var $idx = 1;


	function contentPositions($conf, &$pObj)	{
		global $LANG;
		list($pUid, $pos) = explode('_', $conf['row']['parentPosition'], 2);
		$pUid = intval($pUid);
		if (!$conf['row']['parentPosition']) {
			$conf['items'][] = array($LANG->sL('LLL:EXT:kb_nescefe/locallang.php:no_container'), '');
			return $conf['items'];
		}
		if ($pUid&&strcmp($pos, ''))	{
			$this->func->pObj = t3lib_div::makeInstance('tx_cms_layout');
			$this->func->pObj->start($conf['row']['pid'], 'tt_content', 0);
			$this->func->lP = $conf['row']['sys_language_uid'];
			$this->func->pageID = $conf['row']['pid'];
			$this->func->id = $pUid;
			$parentRec = tx_kbnescefe_func::getRecord('tt_content', $pUid);
			if (is_array($parentRec)&&$parentRec['container'])	{
				$container = tx_kbnescefe_func::getRecord('tx_kbnescefe_containers', $parentRec['container']);
				if (is_array($container))	{
					$tsConfig = t3lib_BEfunc::getModTSconfig($conf['row']['pid'], 'mod.tx_kbnescefe');
					$this->previewObj = t3lib_div::makeInstance('tx_kbnescefe_contentPreview');
					$this->previewObj->pageID = $conf['row']['pid'];
					$this->previewObj->id = $pUid;
					$this->previewObj->lP = $conf['row']['sys_language_uid'];
					$this->previewObj->pObj = t3lib_div::makeInstance('tx_cms_layout');
					$this->previewObj->pObj->start($conf['row']['pid'], 'tt_content', 0);
					$this->cObj = t3lib_div::makeInstance('tslib_cObj');
					$this->func = t3lib_div::makeInstance('tx_kbnescefe_func');
					$this->func->init($conf['row']['pid'], $pUid, $conf['row']['sys_language_uid'], $container, $tsConfig, $this->cObj);
					$this->previewObj->func = &$this->func;
					$file = t3lib_div::getFileAbsFileName($container['betemplate']);
					$conf['items'][] = array($LANG->sL('LLL:EXT:kb_nescefe/locallang.php:no_container'), '');
					if (@is_file($file))	{
						$template = t3lib_div::getURL($file);
						$contentAreas = $this->func->getContentAreas($template);
						$contentElements = $this->previewObj->getContentElements($contentAreas);
						$paths = $this->func->getContentElementPaths($contentAreas);
						$hx = 3;
						foreach ($paths as $path => $label)	{
							$pos = array();
							if (strpos($path, '*')!==false)	{
								$tspath = str_replace('_*_', '_', $path);
								$parts = explode('_', $path);
								$tmp = array($parts);
								do	{
									$hasStars = false;
									$newTmp = array();
									foreach ($tmp as $tmpPath)	{
										if (is_array($tmpPath)&&in_array('*', $tmpPath, 1))	{
											$newTmp = array_merge($newTmp, $this->substitutePathSectionIdx($tmpPath));
											$hasStars = true;
										} else	{
											$newTmp[] = $tmpPath;
										}
									}
									if (!$hx--) {
										die('Too many recursions');
									}
									$tmp = $newTmp;
								} while ($hasStars);
								foreach ($tmp as $tpath)	{
									if (!is_array($tpath))	continue;
									$hp = $tpath;
									$ca = array_pop($hp);
									$se = intval(array_pop($hp));
									$tps = implode('_', $tpath);
									if ($this->func->sectionLabels[$tspath])	{
										$slabel = $this->func->sectionLabels[$tspath];
										$slabel = str_replace('###IDX###', $se+1, $slabel);
									} else	{
										$slabel = $LANG->sL('LLL:EXT:kb_nescefe/locallang.php:column');
										$slabel = str_replace('###IDX###', ($se+1).'/'.$ca, $slabel);
									}
									$conf['items'][] = $h = array($slabel, $pUid.'__'.$tps);
								}
							} else	{
								$conf['items'][] = array(str_replace('###IDX###', $this->idx++, is_string($label)?$label:$LANG->sL('LLL:EXT:kb_nescefe/locallang.php:column')), $pUid.'__'.$path);
							}
						}
					} else	{
						$conf['items'][] = array('No template file', $conf['row']['parentPosition']);
					}
				} else	{
					$conf['items'][] = array('No container record', $conf['row']['parentPosition']);
				}
			} else	{
				$conf['items'][] = array('No parent record', $conf['row']['parentPosition']);
			}
		}
		return $conf['items'];
	}

	function substitutePathSectionIdx($tmpPath)	{
		if (($pos = array_search('*', $tmpPath))!==false)	{
			$pre = array_slice($tmpPath, 0, $pos);
			$post = array_slice($tmpPath, $pos+1);
			$pkey = implode('_', $pre);
			$newk = array();
			$max = $this->func->sectionElementMaxIdx[$pkey]+1;
			for ($x = 0; $x <= $max; $x++)	{
				$newk[] = array_merge(is_array($pre)?$pre:array(), array($x), is_array($post)?$post:array());
			}
			return $newk;
		} else	{
			return array($tmpPath);
		}
	}

}




if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['kb_nescefe/class.tx_kbnescefe_itemproc.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['kb_nescefe/class.tx_kbnescefe_itemproc.php']);
}

?>
