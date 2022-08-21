<?php
/**
 * @version
 * @package       Lang4dev
 * @copyright (C) 2022-2022 Lang4dev Team
 * @license
 */

namespace Finnern\Component\Lang4dev\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;

//use Finnern\Component\Lang4dev\Administrator\Helper\sysFilesContent;
//use Finnern\Component\Lang4dev\Administrator\Helper\searchTransIdLocations;

class langSubProject extends langFiles
{
	public $prjId = '';
	public $prjType = 0;
	public $prjRootPath = '';
	public $prjXmlFilePath = '';

	public $prjXmlPathFilename = '';
	public $installPathFilename = '';
	public $langIdPrefix = '';

	// external
	// public $parentId = 0;
	// public $twinId = '';

	// !!! ToDo: text_prefix !!!
	// public $text_prefix;

	public $useLangSysIni = false;
	public $isLangAtStdJoomla = false;

	protected $transIdLocations = [];
	protected $transStringsLocations = [];
	protected $transIdsClassified;

	public function __construct($prjId = '',
		$prjType = projectType::PRJ_TYPE_NONE,
		$prjRootPath = '',
		$prjXmlPathFilename = '')
	{
		parent::__construct();

		$this->prjType            = $prjType;
		$this->prjId              = $prjId;
		$this->prjRootPath        = $prjRootPath;
		$this->prjXmlPathFilename = $prjXmlPathFilename;
		$this->prjXmlFilePath     = dirname($prjXmlPathFilename);

//	    $this->prjXmlFile = $prjXmlFile;
//	    $this->prjScriptFile = $prjScriptFile;

		if ($this->prjType == projectType::PRJ_TYPE_COMP_BACK_SYS)
		{
			$this->useLangSysIni = true;
		}
	}

	private function checkRootPath()
	{
		$isOk = false;

		// continue when path has enough characters
		if (strlen($this->prjRootPath) > 5)
		{
			if (is_dir($this->prjRootPath))
			{
				$isOk = true;
			}
			else
			{
				// try root path of component
				if (str_starts_with($this->prjRootPath, '/',) || str_starts_with($this->prjRootPath, '\\',))
				{
					$testPath = JPATH_ROOT . $this->prjRootPath;
				}
				else
				{
					$testPath = JPATH_ROOT . '/' . $this->prjRootPath;
				}

				if (is_dir($testPath))
				{

					$isOk = true;

					// ToDo: keep root path without JPATH_ROOT part.
					// Needs a access function of the prjRootPath
					// with flag it is on server (instead of PC)
					$this->prjRootPath = $testPath;
				}
			}
		}

		return $isOk;
	}

	private function checkManifestPath()
	{
		$isManifestPathValid = false;

		// continue when path has enough characters
		if (strlen($this->prjXmlPathFilename) > 5)
		{
			if (is_file ($this->prjXmlPathFilename))
			{
				$isManifestPathValid = true;

				// ToDo: create path from ....
				$this->prjXmlFilePath = dirname ($this->prjXmlPathFilename);
			}
			else
			{
				// else is not needed ?
				$this->prjXmlPathFilename = $this->prjXmlPathFilename . "";
			}
		}

		return $isManifestPathValid;
	}

	private function projectFileName()
	{
		$projectFileName = $this->prjId;

		if (   $this->prjType == projectType::PRJ_TYPE_COMP_BACK_SYS
			|| $this->prjType == projectType::PRJ_TYPE_COMP_BACK)
		{
			// $projectFileName = 'com_' . $this->prjId;
			$projectFileName = substr($this->prjId, 4);
		}

		$projectFileName = $projectFileName . '.xml';

		return $projectFileName;
	}

	// In first version the manifest file was searched,
	// now the location of the manifest file is expected
	// to be assigned already
	//
	// script- / install file, language files as list, transId
	public function findPrjFiles()
	{

		$isFilesFound = false;

		try
		{

			//--- check valid project root path ---------------------------------------------------

			$isRootPathValid = $this->checkRootPath();

			// xml may be in administrator / ... sub path
			if (!$isRootPathValid)
			{
				$projectFileName = $this->projectFileName();

				// sets $prjXmlFilePath
				$isFileFound     = $this->searchXmlProjectFile($projectFileName, $this->prjRootPath); // $this->prjXmlFilePath); //
				$isRootPathValid = $this->checkRootPath();
			}

			// manifest found ?
			if ($isRootPathValid)
			{
				//--- check valid manifest path ---------------------------------------------------

				$isManifestPathValid = $this->checkManifestPath();

				// manifest found ?
				if ($isManifestPathValid)
				{
					//--- open manifest file -------------------------------------------------

					// Manifest tells if files have to be searched inside component or old on joomla standard paths
					$manifestLang = new manifestLangFiles ($this->prjXmlPathFilename);

					//--- project XML and script file -------------------------------------------------

					// files config.xml and  to expect for sub project
					[$isConfigXml, $isInstallPhp] = projectType::enabledByType($this->prjType);

					if ($isInstallPhp)
					{
						$this->installPathFilename = $this->prjXmlFilePath . '/' . $manifestLang->getSriptFile();
						// ToDo: function checkInstallFile ();

					}

//					// ToDo: remove  as config is not needed here and handle otherwise ?
//					if ($isConfigXml)
//					{
//
//					}

					// lang id of project
					$this->langIdPrefix = strtoupper($manifestLang->getName());

					//--- pre check type -----------------

					if ($this->prjType == projectType::PRJ_TYPE_COMP_BACK_SYS)
					{
						$this->useLangSysIni = true;
					}

					// manifest tells about defined list of lang files
					$this->isLangAtStdJoomla  = $manifestLang->isLangAtStdJoomla;

					//--- lang files list by manifest ----------------------------------------

					if ($this->isLangAtStdJoomla) {

						// includes detectLangBasePath
						$this->collectManifestLangFiles($manifestLang, $this->prjType);

						// ToDo: extendManifestLangFilesList()
						// search for late additions not mentioned in manifest
						$this->extendManifestLangFilesList();

					} else {

						$this->prjXmlFilePath = $this->prjRootPath;
						$this->detectLangBasePath($this->prjRootPath, $this->useLangSysIni);

						$this->collectPrjFolderLangFiles();
					}

				}
			}
		}
		catch (\RuntimeException $e)
		{
			$OutTxt = '';
			$OutTxt .= 'Error executing findPrjFiles: "' . '<br>';
			$OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

			$app = Factory::getApplication();
			$app->enqueueMessage($OutTxt, 'error');
		}

		return $isFilesFound;
	}

	// read content of language file  ==> get translation in langFiles
	public function getLangIds()
	{
		$langIds = [];

		foreach ($this->langFilesData as $langId => $langFile)
		{
			$langIds [] = $langId;
		}

		return $langIds;
	}

	// get translations from langFiles (read) and keep file names
	public function getLangFilesData($langId = 'en-GB', $isReadOriginal = false)
	{

		// if not cached or $isReadOriginal
		if (empty($this->langFileNamesSet [$langId]) || $isReadOriginal)
		{
			return $this->readLangFiles($langId = 'en-GB', $isReadOriginal = false);
		}

		return $this->langFilesData [$langId];
	}

	// read translations from langFiles and keep file names
	public function readLangFiles($langId = 'en-GB')
	{
		if ($langId == '')
		{
			$langId = 'en-GB';
		}

		$langFileNames = $this->langFileNamesSet [$langId];

		foreach ($langFileNames as $langFileName)
		{
			$fileName     = basename($langFileName);
			$translations = $this->readLangFile($langFileName);

			$this->langFilesData [$langId][$fileName] = $translations;
		}

		// if (empty($langFiles [$langId]) 0=> return empty ? ...

		return $this->langFilesData [$langId];
	}

	// read translations from langFile and keep file name
	public function readLangFile($langFileName)
	{
		$langFileData = new langFile ();
		$langFileData->readFileContent($langFileName);

		return $langFileData;
	}

	public function scanCode4TransIdsLocations($useLangSysIni = false)
	{

		$searchTransIdLocations = new searchTransIdLocations ($this->langIdPrefix);

		$searchTransIdLocations->useLangSysIni       = $this->useLangSysIni;
		$searchTransIdLocations->prjXmlPathFilename  = $this->prjXmlPathFilename;
		$searchTransIdLocations->installPathFilename = $this->installPathFilename;

		// $searchTransIdLocations->langIdPrefix = $this->langIdPrefix;

		// sys file selected
		if ($useLangSysIni || $this->useLangSysIni)
		{
			//--- scan project files  ------------------------------------

			// scan project XML
			$searchTransIdLocations->searchTransIds_in_XML_file(
				baseName($this->prjXmlPathFilename), dirname($this->prjXmlPathFilename));

			// scan install file
			$searchTransIdLocations->searchTransIds_in_PHP_file(
				baseName($this->installPathFilename), dirname($this->installPathFilename));
		}
		else
		{
			//--- scan all not project files ------------------------------------

			// start path
			$searchPath = $this->prjXmlFilePath;
			if (empty($searchPath))
			{
				$searchPath = $this->prjRootPath;
			}
			$searchTransIdLocations->searchPaths = array($searchPath);

			//--- do scan all not project files ------------------------------------

			$searchTransIdLocations->findAllTranslationIds();
		}

		$this->transIdLocations = $searchTransIdLocations->transIdLocations->items;

		return $this->transIdLocations;
	}

	public function scanCode4TransStringsLocations($useLangSysIni = false)
	{

		$searchTransIdLocations = new searchTransStrings ($this->langIdPrefix);

		$searchTransIdLocations->useLangSysIni       = $this->useLangSysIni;
		$searchTransIdLocations->prjXmlPathFilename  = $this->prjXmlPathFilename;
		$searchTransIdLocations->installPathFilename = $this->installPathFilename;

		// sys file selected
		if ($useLangSysIni || $this->useLangSysIni)
		{

			//--- scan project files  ------------------------------------

			// scan install file
			$searchTransIdLocations->searchTransStrings_in_PHP_file(
				baseName($this->installPathFilename), dirname($this->installPathFilename));
		}
		else
		{
			//--- scan all not project files ------------------------------------

			// start path
			$searchPath = $this->prjXmlFilePath;
			if (empty($searchPath))
			{
				$searchPath = $this->prjRootPath;
			}
			$searchTransIdLocations->searchPaths = array($searchPath);

			//--- do scan all not project files ------------------------------------

			$searchTransIdLocations->findAllTranslationStrings();
		}

		$this->transStringsLocations = $searchTransIdLocations->transStringLocations->items;

		return $this->transStringsLocations;
	}

	public function getPrjTransIdLocations()
	{
		$names = [];

		try
		{

			foreach ($this->transIdLocations as $name => $val)
			{
				$names [] = $name;
			}

		}
		catch (\RuntimeException $e)
		{
			$OutTxt = 'Error executing getPrjTransIdLocations: "' . '<br>';
			$OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

			$app = Factory::getApplication();
			$app->enqueueMessage($OutTxt, 'error');
		}

		return $names;
	}

	public function getTransIdLocations($isScanOriginal = false)
	{
		// if not cached or $isReadOriginal
		if (empty($this->transIdLocations) || $isScanOriginal)
		{

			$this->scanCode4TransIdsLocations($this->useLangSysIni);
		}

		return $this->transIdLocations;
	}

	public function getTransStringsLocations($isScanOriginal = false)
	{
		// if not cached or $isReadOriginal
		if (empty($this->transStringsLocations) || $isScanOriginal)
		{

			$this->scanCode4TransStringsLocations($this->useLangSysIni);
		}

		return $this->transStringsLocations;
	}

	public function getTransIdsClassified($langId = "en-GB", $isDoClassifyTransIds = false)
	{

		if (empty($this->transIdsClassified) || $isDoClassifyTransIds)
		{

			return $this->classifyTransIds($langId);
		}

		return $this->transIdsClassified;
	}

	public function classifyTransIds($langId = "en-GB")
	{
		//
		$codeTransIds = $this->getPrjTransIdLocations();

		[$missing, $same, $notUsed] = $this->matchTranslationsFile2Locations($codeTransIds, $langId);

		$transIdsClassified            = [];
		$transIdsClassified['missing'] = $missing;
		$transIdsClassified['same']    = $same;
		$transIdsClassified['notUsed'] = $notUsed;

		$transIdsClassified['doubles'] = $this->collectDoubles($langId);

		$this->transIdsClassified = $transIdsClassified;

		return $this->transIdsClassified;
	}

	private function collectDoubles($langId = "en-GB")
	{
		$doubles = [];

		// ToDo: each langFilesData[$langId] as $langFile get data not file name
		foreach ($this->langFilesData[$langId] as $langFile)
		{
			$fileName                     = baseName($langFile->getlangPathFileName());
			$doubles[basename($fileName)] = $langFile->collectDoubles();
		}

		return $doubles;
	}

	public function getPrjTypeText()
	{

		return projectType::getPrjTypeText($this->prjType);

	}

	public function getPrjIdAndTypeText()
	{

		return $this->prjId . ': ' . $this->getPrjTypeText();
	}

	public function yyy_detectLangFiles()
	{

		try
		{
			//--- pre check type -----------------

			if ($this->prjType == projectType::PRJ_TYPE_COMP_BACK_SYS)
			{
				$this->useLangSysIni = true;
			}

			// Manifest tells if files have to be searched inside component or old on joomla standard paths
			$manifestLang = new manifestLangFiles ($this->prjXmlPathFilename);

			// lang file origins inside component
			if (!$manifestLang->isLangAtStdJoomla)
			{
				//--- search in component path -------------------------------

				parent::collectPrjFolderLangFiles();
			}
			else
			{
				//--- use joomla standard paths ------------------------------

				parent::collectManifestLangFiles($manifestLang, $this->prjType);
			}
		}
		catch (\RuntimeException $e)
		{
			$OutTxt = '';
			$OutTxt .= 'Error executing detectLangFiles: "' . '<br>';
			$OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

			$app = Factory::getApplication();
			$app->enqueueMessage($OutTxt, 'error');
		}

		return; // $isFilesFound;
		// ToDo: ....
	}

	public function alignTranslationsByMain($mainLangId)
	{

		$mainTrans = [];

		try
		{

//			// for each other call
//			foreach ($this->langFilesData as $langId => $temp)
//			{
//				if ($langId != $mainLangId)
//				{
//
//					$this->langFilesData[$langId]->alignTranslationsByMain($mainTrans);
//				}
//			}

			$mainLangFilesData = $this->langFilesData[$mainLangId];

			$transLangIds = $this->getLangIds();

			// all other lang ids
			foreach ($transLangIds as $transLangId)
			{
				// Not main language
				if ($transLangId != $mainLangId)
				{

					//--- all main lang files -----------------------------------------------

					$transFilesData = $this->langFilesData[$transLangId];

					foreach ($mainLangFilesData as $mainFileData)
					{
						//--- create matching translation file name -----------------------------------------------

						$mainLangFileName  = $mainFileData->getlangPathFileName();
						$mainTrans = $mainFileData->translations;

						$matchTransFileName = $this->matchingNameByTransId($mainLangId, $mainLangFileName, $transLangId);

						// look up the matching translation
						foreach ($transFilesData as $transFileData)
						{
							$actTransFileName = $transFileData->getlangPathFileName();

							// toDo: should not be needed
							$actTransFileName = str_replace('\\', '/', $actTransFileName);
							if ($actTransFileName == $matchTransFileName) {

								// align order of items in matching translation
								$transFileData->alignTranslationsByMain($mainTrans);
							}
						}

					} // main files
				}
			} // for translation ids

		}
		catch (\RuntimeException $e)
		{
			$OutTxt = '';
			$OutTxt .= 'Error executing alignTranslationsByMain: "' . '<br>';
			$OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

			$app = Factory::getApplication();
			$app->enqueueMessage($OutTxt, 'error');
		}

		return; // $isFilesFound;
		// ToDo: ....
	}

	public function searchXmlProjectFile ($projectFileName, $searchPath) {

		$isFileFound = false;

		if ($searchPath)
		{
			// expected path and file name
			$prjXmlPathFilename = $searchPath . '/' . $projectFileName;

			try
			{

				//--- ? path to file given ? --------------------------------------
				// d:\Entwickl\2022\_github\LangMan4Dev\administrator\components\com_lang4dev\lang4dev.xml

				if (is_file($prjXmlPathFilename))
				{

					$this->prjXmlFilePath = $searchPath;
					$this->prjXmlPathFilename = $prjXmlPathFilename;
					$isFileFound          = true;

				}
				else
				{
					#--- All sub folders in folder -------------------------------------

					foreach (Folder::folders($searchPath) as $folderName)
					{

						$subFolder = $searchPath . '/' . $folderName;

						$isPathFound = $this->searchXmlProjectFile($projectFileName, $subFolder);

						if ($isPathFound)
						{
							break;
						}
					}

				}
			}
			catch (\RuntimeException $e)
			{
				$OutTxt = '';
				$OutTxt .= 'Error executing searchXmlProjectFile: "' . '<br>';
				$OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

				$app = Factory::getApplication();
				$app->enqueueMessage($OutTxt, 'error');
			}
		}

		return $isFileFound;
	}


} // class

