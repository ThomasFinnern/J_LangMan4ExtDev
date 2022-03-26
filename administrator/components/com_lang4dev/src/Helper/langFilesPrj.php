<?php
/**
 * This class contains translations file
 *
 *
 * @version
 * @package       Lang4dev
 * @copyright (C) 2022-2022 Lang4dev Team
 * @license
 */


namespace Finnern\Component\Lang4dev\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;

use Finnern\Component\Lang4dev\Administrator\Helper\langFiles;

// no direct access
\defined('_JEXEC') or die;

/**
 * Collect contents of all translation files for one base folder (existing)
 * Write the changes set inlcuding

 * The files uses is limitet as *.ini are not useful
 *
 * @package Lang4dev
 */
class langFilesPrj
{
	public $fileTypes = 'php, xml';







} // class