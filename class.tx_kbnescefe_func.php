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
 * Miscellaneouse methods.
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */

if (!class_exists('tslib_cObj'))	{
	require_once(t3lib_extMgm::extPath('cms').'tslib/class.tslib_content.php');
}
class tx_kbnescefe_func	{
	var $idx = 1;
	var $sectionIdx = 1;
	var $sectionElementMaxIdx = array();
	var $sectionLabels = array();


	function init($pageId, $id, $lP, $container, $tsconfig, &$cObj)	{
		$this->pageID = $pageId;
		$this->id = $id;
		$this->lP = $lP;
		$this->containerUid = $container['uid'];
		$this->tsConfig = $tsconfig;
		$this->cObj = &$cObj;
		$this->newSection = t3lib_div::_GP('newSection');
	}
		

	function getContentAreas($template = '')	{
		$contentAreas = array();
		if (preg_match_all('/###(HEADER|CONTENT|SECTION|SECTIONCOUNT|NEWSECTION)_([a-zA-Z0-9_]+)###/s', $template, $matches, PREG_SET_ORDER)>0)	{
			foreach ($matches as $match)	{
				if ($match[1]=='SECTION')	{
					if (!is_array($contentAreas[$match[2]][$match[1]]))	{
						$tmpl = $this->cObj->getSubpart($template, '###SECTION_'.$match[2].'###');
						$ca = false;
						$ca = $this->getContentAreas($tmpl);
						$contentAreas[$match[2]][$match[1]] = array(
							'template' => $tmpl,
							'contentAreas' => $ca,
						);
					}
				} else	{
					$contentAreas[$match[2]][$match[1]] = true;
				}
			}
		}
		return $contentAreas;
	}
	
	function getContentElementPaths($contentAreas, $curKey = '')	{
		global $LANG;
		$keys = array();
		foreach ($contentAreas as $key => $typeArr)	{	
			$ckey = preg_replace('/[^a-zA-Z0-9_]/', '_', (strlen($curKey)?($curKey.'_'):'').$key);
			$label = true;
			if ($this->tsConfig['properties']['labels.'][$this->containerUid.'.'][$ckey.'.'][$LANG->lang])	{
				$label = $this->tsConfig['properties']['labels.'][$this->containerUid.'.'][$ckey.'.'][$LANG->lang];
			} elseif ($this->tsConfig['properties']['labels.'][$this->containerUid.'.'][$ckey])	{
				$label = $this->tsConfig['properties']['labels.'][$this->containerUid.'.'][$ckey];
			}
			if ($this->tsConfig['properties']['labels.'][$this->containerUid.'.']['__'.$ckey.'.'][$LANG->lang])	{
				$slabel = $this->tsConfig['properties']['labels.'][$this->containerUid.'.']['__'.$ckey.'.'][$LANG->lang];
			} elseif ($this->tsConfig['properties']['labels.'][$this->containerUid.'.']['__'.$ckey])	{
				$slabel = $this->tsConfig['properties']['labels.'][$this->containerUid.'.']['__'.$ckey];
			}
			if ($typeArr['HEADER'])	{
				$keys[$key] = $label;
				$this->sectionLabels[(strlen($curKey)?($curKey.'_'):'').$key] = $slabel;
			}
			if ($typeArr['CONTENT'])	{
				$keys[$key] = $label;
				$this->sectionLabels[(strlen($curKey)?($curKey.'_'):'').$key] = $slabel;
			}
			if ($typeArr['SECTIONCOUNT'])	{
				$keys[$key] = $label;
			}
			if ($typeArr['NEWSECTION'])	{
				$keys[$key] = $label;
			}
			if ($typeArr['SECTION'])	{
				$keys[$key] = $label;
				$this->sectionLabels[(strlen($curKey)?($curKey.'_'):'').$key] = $slabel;
				$subkeys = $this->getContentElementPaths($typeArr['SECTION']['contentAreas'], (strlen($curKey)?($curKey.'_'):'').$key);
				foreach ($subkeys as $skey => $sublabel)	{
					$keys[$key.'_*_'.$skey] = $sublabel;
				}
			}
		}
		$this->paths = $keys;
		return $keys;
	}

	function getSectionMax($storage)	{
		foreach ($storage as $pos => $rows)	{
			list($pUid, $pPos) = explode('__', $pos, 2);
			$pParts = explode('_', $pPos);
			if ((count($pParts)>1)&&(count($pParts)%2)==1)	{
				// We have a section element
				$tmpParts = $pParts;
				$ca = array_pop($tmpParts);
				$idx = array_pop($tmpParts);
				$key = implode('_', $tmpParts);
				if ($idx>intval($this->sectionElementMaxIdx[$key]))	{
					$this->sectionElementMaxIdx[$key] = intval($idx);
				}
			}
		}
	}



	function renderContentAreas($template, $contentElements, $contentAreas, &$procObj, $curKey = '')	{
		global $LANG;
		foreach ($contentAreas as $key => $typeArr)	{
			$nkey = (strlen($curKey)?$curKey.'_':'').$key;
			if ($typeArr['SECTION'])	{
				$code = '';
				$max = $this->sectionElementMaxIdx[$nkey]+(!strcmp($this->newSection, $nkey)?1:0);
				for ($x=0; $x <= $max; $x++)	{
					$code .= $this->renderContentAreas($typeArr['SECTION']['template'], $contentElements, $typeArr['SECTION']['contentAreas'], $procObj, $nkey.'_'.$x);
				}
				$template = $this->cObj->substituteSubpart($template, '###SECTION_'.$key.'###', $code);
			}
			if ($typeArr['HEADER'])	{
				$code = '';
				if (method_exists($procObj, 'func_HEADER'))	{
					$code .= $procObj->func_HEADER($contentElements, $nkey, $this);
				} 
				$template = $this->cObj->substituteMarker($template, '###HEADER_'.$key.'###', $code);
				
			}
			if ($typeArr['CONTENT'])	{
				$code = '';
				if (method_exists($procObj, 'func_CONTENT'))	{
					$code .= $procObj->func_CONTENT($contentElements, $nkey, $this);
				} 
				$template = $this->cObj->substituteMarker($template, '###CONTENT_'.$key.'###', $code);
			}
			if ($typeArr['NEWSECTION'])	{
				if (method_exists($procObj, 'func_NEWSECTION'))	{
					$code .= $procObj->func_NEWSECTION($contentElements, $nkey, $this);
				} 
				$template = $this->cObj->substituteMarker($template, '###NEWSECTION_'.$key.'###', $code);
			}
			if ($typeArr['SECTIONCOUNT'])	{
				$code = '';
				if (method_exists($procObj, 'func_SECTIONCOUNT'))	{
					$code .= $procObj->func_SECTIONCOUNT($contentElements, $nkey, $this);
				} 
				$template = $this->cObj->substituteMarker($template, '###SECTIONCOUNT_'.$key.'###', $code);
			}
		}
		return $template;
	}


	function getRecord($table, $uid)	{
		if (method_exists('t3lib_BEfunc', 'getRecordWSOL'))	{
			return t3lib_BEfunc::getRecordWSOL($table, $uid);
		} else	{
			return t3lib_BEfunc::getRecord($table, $uid);
		}
	}

}




if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['kb_nescefe/class.tx_kbnescefe_func.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['kb_nescefe/class.tx_kbnescefe_func.php']);
}

?>
