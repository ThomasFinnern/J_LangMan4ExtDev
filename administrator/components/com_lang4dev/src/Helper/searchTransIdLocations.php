<?php
/**
 * This class handles version management for Lang4dev
 *
 * @version
 * @package       Lang4dev
 * @copyright (C) 2022-2022 Lang4dev Team
 * @license
 */

namespace Finnern\Component\Lang4dev\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;

use Finnern\Component\Lang4dev\Administrator\Helper\transIdLocations;

// no direct access
\defined('_JEXEC') or die;

/**
 * Search language constants (Items) in given folders
 * The files uses is limited as *.ini are not useful
 *
 * @package Lang4dev
 */
class searchTransIdLocations
{
	public $fileTypes = 'php, xml';
	public $langIdPrefix = '';
	public $searchPaths = [];
	public $transIdLocations;

	public $useLangSysIni = false;
	public $prjXmlPathFilename = "";
	public $installPathFilename = "";

	protected $name = 'Lang4dev';

	/**
	 * @since __BUMP_VERSION__
	 */
	public function __construct($searchPaths = array(), $langIdPrefix = 'COM_LANG4DEV_')
	{
		// ToDO: check for uppercase and trailing '_'

		$this->transIdLocations       = new transIdLocations();
		$this->langIdPrefix = $langIdPrefix;

		// if ( !empty ($searchPaths)) ... ???
		$this->searchPaths = $searchPaths;

	}

	// Attention the removing of comments may lead to wrong
	// Index in line for found '*/'
	public function findAllTranslationIds()
	{
		// ToDo: log $langIdPrefix, $searchPaths

		$this->transIdLocations = new transIdLocations();

		try
		{
			$hasErr = false;

			/*--------------------------------------------------------------
			checks
			--------------------------------------------------------------*/

			//--- prefix --------------------------------------------------

			if (strlen($this->langIdPrefix) < 5) {
				$hasErr = true;

				$OutTxt = 'findAllTranslationIds: langIdPrefix is not set or too smalll: "' . $this->langIdPrefix . '"';

				$app = Factory::getApplication();
				$app->enqueueMessage($OutTxt, 'error');
			}

			//--- search paths given --------------------------------------------------

			if (count($this->searchPaths) < 1) {
				$hasErr = true;

				$OutTxt = 'findAllTranslationIds: search paths not given' ;

				$app = Factory::getApplication();
				$app->enqueueMessage($OutTxt, 'error');
			}

			/**
			//--- ???? --------------------------------------------------

			if (strlen($this->langIdPrefix) < 5) {
				$hasErr = true;

				$OutTxt = 'findAllTranslationIds: langIdPrefix is not set or too small: "' . $this->langIdPrefix . '"';

				$app = Factory::getApplication();
				$app->enqueueMessage($OutTxt, 'error');
			}
			/**/

			//--- exit on error -----------------------------------------

			if ($hasErr == true)
			{
				return;
			}

			//--- All paths --------------------------------------------------

			foreach ($this->searchPaths as $searchPath)
			{
				//--- paths exist --------------------------------------------------

				$isPathsExisting = is_dir($searchPath);

				if ($isPathsExisting)
				{
					//--- search in path -------------------------------

					$this->searchLangIds_in_Path($searchPath, $this->langIdPrefix);
				}
				else
				{
					$hasErr = true;

					$OutTxt = 'findAllTranslationIds: search path does not exist: "' . $searchPath . '"';

					$app = Factory::getApplication();
					$app->enqueueMessage($OutTxt, 'error');
				}
			}

		}
		catch (\RuntimeException $e)
		{
			$OutTxt = '';
			$OutTxt .= 'Error executing findAllTranslationIds: "' . '<br>';
			$OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

			$app = Factory::getApplication();
			$app->enqueueMessage($OutTxt, 'error');
		}

		return $this->transIdLocations; // ? a lot to return ?
	}

	public function searchLangIds_in_Path($searchPath)
	{
		try
		{
			#--- All files (*.php, *.xml) in folder -------------------------------------

			foreach ($this->filesInDir($searchPath) as $fileName)
			{
				$filePath = $searchPath . DIRECTORY_SEPARATOR . $fileName;
				$ext = pathinfo($filePath, PATHINFO_EXTENSION);

                //--- prevent project sys files -----------------------------------

                if ($ext == 'php' && $filePath == $this->installPathFilename)
                {
                    continue;
                }
                if ($ext == 'xml' && $filePath == $this->prjXmlPathFilename)
                {
                    continue;
                }

                //--- scan content of valid  files -----------------------------------

                if ($ext == 'php')
				{
					$this->searchTransIds_in_PHP_file($fileName, $searchPath);
				}
				if ($ext == 'xml')
				{
					$this->searchTransIds_in_XML_file($fileName, $searchPath);
				}
			}

			#--- All sub folders in folder -------------------------------------

			foreach ($this->folderInDir($searchPath) as $folderName)
			{
				$subFolder = $searchPath . DIRECTORY_SEPARATOR . $folderName;
				$this->searchLangIds_in_Path($subFolder);
			}

		}
		catch (\RuntimeException $e)
		{
			$OutTxt = '';
			$OutTxt .= 'Error executing searchLangIdsInPath: "' . '<br>';
			$OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

			$app = Factory::getApplication();
			$app->enqueueMessage($OutTxt, 'error');
		}
	}

	public function filesInDir($folder)
	{
		$files = [];

		try
		{
			// php, xml
			//$regEx = '\.xml$|\.html$';
			$regEx = '\.php|\.xml$';
			$files = Folder::files($folder, $regEx);
		}
		catch (\RuntimeException $e)
		{
			$OutTxt = '';
			$OutTxt .= 'Error executing filesInDir: "' . '<br>';
			$OutTxt .= 'Error: "' . $e->getMessaxge() . '"' . '<br>';

			$app = Factory::getApplication();
			$app->enqueueMessage($OutTxt, 'error');
		}

		return $files;
	}

	// Multiple items in one line

	public function searchTransIds_in_PHP_file($fileName, $path)
	{
		$isInComment = false;
		$lines = [];

		try
		{
			$lineNr = 0;

			// Read all lines
			$filePath = $path . DIRECTORY_SEPARATOR . $fileName;

			$lines = file($filePath);

			// content found
			// ToDo: 		foreach ($lines as $lineNr => $line)
			foreach ($lines as $line)
			{
				$lineNr = $lineNr + 1;

				//--- remove comments --------------

				$bareLine = $this->removeCommentPHP($line, $isInComment);

				//--- find items --------------

				if (strlen($bareLine) > 0)
				{

					$items = $this->searchLangIdsInLinePHP($bareLine);

					//--- add items
					foreach ($items as $item)
					{
						$item->file    = $fileName;
						$item->path    = $path;
						$item->lineNr = $lineNr;

						$this->transIdLocations->addItem($item);
					}
				}

			}
		}
		catch (\RuntimeException $e)
		{
			$OutTxt = 'Error executing searchTransIdsIn_PHP_file: "' . '<br>';
			$OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

			$app = Factory::getApplication();
			$app->enqueueMessage($OutTxt, 'error');
		}

		// return $this->transIdLocations;
	}

	public function removeCommentPHP($line, &$isInComment)
	{
		$bareLine = $line;

		try
		{
			// No inside a '/*' comment
			if ( ! $isInComment)
			{
				//--- check for comments ---------------------------------------

				$doubleSlash = '//';
				$slashAsterisk = '/*';

				$doubleSlashIdx = strpos ($line, $doubleSlash);
				$slashAsteriskIdx = strpos ($line, $slashAsterisk);

				// comment exists, keep start of string
				if ($doubleSlashIdx != false || $slashAsteriskIdx != false)
				{
					if ($doubleSlashIdx != false && $slashAsteriskIdx == false) {
						$bareLine =  strstr ($line, $doubleSlash, true);
					}
					else
					{
						if ($doubleSlashIdx == false && $slashAsteriskIdx != false)
						{
							$bareLine = strstr($line, $slashAsterisk, true);
							$isInComment = true;
						}
						else
						{
							//--- both found ---------------------------------

							// which one is first
							if ($doubleSlashIdx < $slashAsteriskIdx) {
								$bareLine =  strstr ($line, $doubleSlash, true);
							} else {
								$bareLine = strstr($line, $slashAsterisk, true);
								$isInComment = true;
							}

						}

					}


				} // No comment indicator

			} else {
				//--- Inside a '/*' comment

				$bareLine = '';

				$asteriskSlash = '*/';
				$asteriskSlashIdx = strpos ($line, $asteriskSlash);

				// end found ?
				if ($asteriskSlashIdx != false)
				{
					// Keep end of string
					$bareLine = strstr($line, $asteriskSlash);

					// handle rest of string
					$isInComment = false;
					$bareLine = $this->removeCommentPHP($bareLine, $isInComment);
				}
			}

		}
		catch (\RuntimeException $e)
		{
			$OutTxt = '';
			$OutTxt .= 'Error executing removeCommentPHP: "' . '<br>';
			$OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

			$app = Factory::getApplication();
			$app->enqueueMessage($OutTxt, 'error');
		}

		return $bareLine;
	}

	// Multiple items in one line
	// Must be cleaned from comments first
	public function searchLangIdsInLinePHP($line)
	{
		$items = [];
		$matches = [];

		try
		{
			// find all words then iterate through array
// https://stackoverflow.com/questions/4722007/php-preg-match-to-find-whole-words

			// Python solution
			// py$searchRegex = "\\b" + $this->langIdPrefix + "\\w+";
			// Finds multiple words per line
			$searchRegex = '/' . $this->langIdPrefix . "\w+/";

			// test find all words then iterate through array
			preg_match_all($searchRegex, $line, $matchGroups);

			if (!empty($matchGroups))
				// if (count ($matchGroups) > 0)
			{
				$idx = 0;

				// all items found in line
				foreach ($matchGroups[0] as $name)
				{
					$colIdx = strpos($line, $name, $idx);

					$item = new transIdLocation ($name, '', '', -1, $colIdx);

					// ? same twice ?
					$items [] = $item;

					// search behind last find
					$idx = $colIdx + strlen($name);
				}
			}

		}
		catch (\RuntimeException $e)
		{
			$OutTxt = '';
			$OutTxt .= 'Error executing searchLangIdsInLinePHP: "' . '<br>';
			$OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

			$app = Factory::getApplication();
			$app->enqueueMessage($OutTxt, 'error');
		}

		return $items;
	}

	public function searchTransIds_in_XML_file($fileName, $path)
	{
		$isInComment = false;

		try
		{
			$lineNr = 0;

			// Read all lines
			$filePath = $path . DIRECTORY_SEPARATOR . $fileName;

			$lines = file($filePath);

			// content found
			foreach ($lines as $line)
			{
				$lineNr = $lineNr + 1;

				//--- remove comments --------------

				$bareLine = $this->removeCommentXML($line, $isInComment);

				//--- find items --------------

				if (strlen($bareLine) > 0)
				{

					$items = $this->searchLangIdsInLineXML($bareLine);

					//--- add items
					foreach ($items as $item)
					{
						$item->file    = $fileName;
						$item->path    = $path;
						$item->lineNr = $lineNr;

						$this->transIdLocations->addItem($item);
					}
				}

			}
		}
		catch (\RuntimeException $e)
		{
			$OutTxt = '';
			$OutTxt .= 'Error executing searchTransIdsIn_XML_file: "' . '<br>';
			$OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

			$app = Factory::getApplication();
			$app->enqueueMessage($OutTxt, 'error');
		}

//		return $this->transIdLocations;
	}

	public function removeCommentXML($line, &$isInComment)
	{
		$bareLine = $line;

		try
		{

			// No inside a '<--' comment
			if ( ! $isInComment)
			{
				//--- check for comments ---------------------------------------

				$commStart = '<!--';
				$commStartIdx = strpos ($line, $commStart);

				// comment exists, keep start of string
				if ($commStartIdx != false)
				{
					$bareLine = strstr($line, $commStart, true);
					$isInComment = true;

				} // No comment indicator

			} else {
				//--- Inside a '/*' comment

				$bareLine = '';

				$commEnd = '-->';
				$commEndIdx = strpos ($line, $commEnd);

				// end found ?
				if ($commEndIdx != false)
				{
					// Keep end of string
					$bareLine = strstr($line, $commEnd);

					// handle rest of string
					$isInComment = false;
					$bareLine = $this->removeCommentPHP($bareLine, $isInComment);
				}
			}

		}
		catch (\RuntimeException $e)
		{
			$OutTxt = '';
			$OutTxt .= 'Error executing removeCommentXML: "' . '<br>';
			$OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

			$app = Factory::getApplication();
			$app->enqueueMessage($OutTxt, 'error');
		}

		return $bareLine;
	}

	public function searchLangIdsInLineXML($line)
	{
		$items = [];
		$matches = [];

		try
		{
			// find all words then iterate through array

			// Python solution
			// py$searchRegex = "\\b" + $this->langIdPrefix + "\\w+";
			// Finds multiple words per line
			$searchRegex = '/' . $this->langIdPrefix . "\w+/";

			// test find all words then iterate through array
			preg_match_all($searchRegex, $line, $matchGroups);

			if (!empty($matchGroups))
			// if (count ($matchGroups) > 0)
			{
				$idx = 0;

				// all items found in line
				foreach ($matchGroups[0] as $name)
				{
					$colIdx = strpos($line, $name, $idx);

					$item = new transIdLocation ($name, '', '', -1, $colIdx);

					// ? same twice ?
					$items [] = $item;

					// search behind last find
					$idx = $idx + strlen($name);
				}
			}
		}
		catch (\RuntimeException $e)
		{
			$OutTxt = '';
			$OutTxt .= 'Error executing searchLangIdsInLineXML: "' . '<br>';
			$OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

			$app = Factory::getApplication();
			$app->enqueueMessage($OutTxt, 'error');
		}

		return $items;
	}

	public function folderInDir($folder)
	{
		$folders = [];

		try
		{
			// ToDo: leave out 'language' folder
			// php, xml
			$folders = Folder::folders($folder);

		}
		catch (\RuntimeException $e)
		{
			$OutTxt = '';
			$OutTxt .= 'Error executing folderInDir: "' . '<br>';
			$OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

			$app = Factory::getApplication();
			$app->enqueueMessage($OutTxt, 'error');
		}

		return $folders;
	}

}