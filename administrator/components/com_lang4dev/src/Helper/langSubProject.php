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

use Finnern\Component\Lang4dev\Administrator\Helper\sysFilesContent;
use Finnern\Component\Lang4dev\Administrator\Helper\searchTransIdLocations;

class langSubProject extends langFileNamesSet
{
	public $prjId = "";
	public $prjType = "";
	public $prjRootPath = '';
	public $prjXmlFilePath = '';

	public $prjXmlPathFilename = "";
	public $installPathFilename = "";
    public $componentPrefix = "";

    // !!! ToDo: text_prefix !!!
    // public $text_prefix;

    public $isSysFiles = false;

    protected $langFiles = []; // $langId -> translation file(s)
	protected $transIdLocations = [];
    protected $transIdsClassified;


    // ToDo: MainLangId

	/*   */
	const PRJ_TYPE_NONE = 0;
	const PRJ_TYPE_COMP_BACK_SYS = 1;
	const PRJ_TYPE_COMP_BACK = 2;
	const PRJ_TYPE_COMP_SITE = 3;
	const PRJ_TYPE_MODEL = 4;
	const PRJ_TYPE_PLUGIN = 5;
	// const PRJ_TYPE_TEMPLATE = 1;


	public function __construct($prjId='',
		$prjType= '', // ToDo: enum from sub ?
								$prjRootPath = '',
                                $prjXmlFilePath = '')
    {

	    $this->prjId = $prjId;
	    $this->prjRootPath = $prjRootPath;
	    $this->prjType = $prjType;

	    $this->prjXmlFilePath = $prjXmlFilePath;

//	    $this->prjXmlFile = $prjXmlFile;
//	    $this->prjScriptFile = $prjScriptFile;

		if ($this->prjType == langSubProject::PRJ_TYPE_COMP_BACK_SYS)
		{
			$this->isSysFiles = true;
		}
    }

    public function findPrjFiles () {

        $isFilesFound = false;

        try {

            //--- pre check type

            if ($this->prjType == langSubProject::PRJ_TYPE_COMP_BACK_SYS) {
                $this->isSysFiles = true;
            }

	        $hasSysFiles = ! ($this->prjType == langSubProject::PRJ_TYPE_COMP_BACK
		        || $this->prjType == langSubProject::PRJ_TYPE_COMP_SITE);

	        // On sys file receive componentPrefix
	        if($hasSysFiles)
	        {

		        //--- Assign from function call variables ------------------------------------

		        $finder = new sysFilesContent();

		        $finder->prjId       = $this->prjId;
		        $finder->prjType     = $this->prjType;
		        $finder->prjRootPath = $this->prjRootPath;

		        // use sysFilesContent
		        // new ...;

		        $isFilesFound = $finder->findPrjFiles();

		        // take results
		        if ($isFilesFound)
		        {
			        // Path and name
			        if ($this->isSysFiles)
			        {
				        $this->prjXmlFilePath = $finder->prjXmlFilePath;
			        }
			        else
			        {
				        $this->prjXmlFilePath = $this->prjRootPath;
			        }
			        $this->prjXmlPathFilename  = $finder->prjXmlPathFilename;
			        $this->installPathFilename = $finder->installPathFilename;
			        $this->componentPrefix     = $finder->componentPrefix;
		        }

		        $this->detectLangBasePath($this->prjXmlFilePath, $this->isSysFiles);
	        }
			else
			{
				$this->detectLangBasePath($this->prjRootPath, $this->isSysFiles);
			}

            //$this->detectLangBasePath($this->prjRootPath);
            $this->searchLangFiles();

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
    public function getLangFile ($langId='en-GB', $isReadOriginal=false)
    {

        // if not cached or $isReadOriginal
        if (empty($this->langFiles [$langId]) || $isReadOriginal) {

            return $this->readLangFile ($langId='en-GB', $isReadOriginal=false);
        }

        return $this->langFiles [$langId];
    }


    // read content of language file  ==> get translation in langFiles
    public function readLangFile ($langId='en-GB') {


        $langFileName =  $this->langFileNames [$langId];

        // $langFile = new langFile ($langFileName);
        $langFile = new langFile ();
        $langFile->readFileContent($langFileName, $langId);

        $this->langFiles [$langId] = $langFile;

        // if (empty($langFiles [$langId]) 0=> return empty ? ...

        return $this->langFiles [$langId];
    }

    public function scanCode4TransIdsLocations ($isSysFiles=false) {

		$searchTransIdLocations = new searchTransIdLocations ();

	    $searchTransIdLocations->isSysFiles = $this->isSysFiles;
	    $searchTransIdLocations->prjXmlPathFilename = $this->prjXmlPathFilename;
        $searchTransIdLocations->installPathFilename = $this->installPathFilename;

        $searchTransIdLocations->componentPrefix = $this->componentPrefix;
        // sys file selected
        if ($isSysFiles || $this->isSysFiles) {

            //--- scan project files  ------------------------------------

            // scan project XML
            $searchTransIdLocations->searchTransIdsIn_XML_file(
                baseName($this->prjXmlPathFilename), dirname($this->prjXmlPathFilename));

            // scan install file
            $searchTransIdLocations->searchTransIdsIn_PHP_file(
                baseName($this->installPathFilename), dirname($this->installPathFilename));
        }
        else {
            //--- scan all not project files ------------------------------------

            // start path
            $searchPath = $this->prjXmlFilePath;
            if (empty($searchPath)) {
                $searchPath = $this->prjRootPath;
            }
            $searchTransIdLocations->searchPaths = array ($searchPath);

            //--- do scan all not project files ------------------------------------

            $searchTransIdLocations->findAllTranslationIds();
        }

        $this->transIdLocations = $searchTransIdLocations->transIdLocations->items;

        return $this->transIdLocations;
    }

    public function getPrjTransIdNames ()
    {
        $names = [];

        try {

            foreach ($this->transIdLocations as $name => $val) {

                $names [] = $name;
            }

        }
        catch (\RuntimeException $e)
        {
            $OutTxt = 'Error executing getPrjTransIdNames: "' . '<br>';
            $OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

            $app = Factory::getApplication();
            $app->enqueueMessage($OutTxt, 'error');
        }

        return $names;
    }

    public function getTransIdLocations ($isScanOriginal=false)
    {
        // if not cached or $isReadOriginal
        if (empty($this->transIdLocations) || $isScanOriginal) {

            return $this->scanCode4TransIdsLocations ($this->isSysFiles);
        }

        return $this->transIdLocations;
    }

    public function classifyTransIds (){

        $codeTransIds = $this->getPrjTransIdNames();

        // ToDo: MainLangId
        $this->MainLangId = 'en-GB';
        $langId = $this->MainLangId;

        $langFile = $this->langFiles [$langId];
		[$missing, $same, $notUsed] = $langFile->separateByTransIds($codeTransIds);

        $transIdsClassified = [];
		$transIdsClassified['missing'] = $missing;
		$transIdsClassified['same'] = $same;
		$transIdsClassified['notUsed'] = $notUsed;

	    $transIdsClassified['doubles'] = $this->collectDoubles();

        $this->transIdsClassified = $transIdsClassified;

        return $this->transIdsClassified;
    }

    public function getTransIdsClassified ($isClassifyTransIds=false){

        if (empty($this->transIdsClassified) || $isClassifyTransIds) {

            return $this->classifyTransIds ();
        }

        return $this->transIdsClassified;
    }

	private function collectDoubles()
	{
		$doubles = [];

		$langId = $this->MainLangId;

		$langFile = $this->langFiles [$langId];
		$doubles = $langFile->collectDoubles();

		return $doubles;
	}

	public function getPrjTypeText () {
		$typename = '? type' ;

		switch ($this->prjType){

			case self::PRJ_TYPE_NONE:
				$typename = 'type-none' ;
				break;

			case self::PRJ_TYPE_COMP_BACK_SYS:
				$typename = 'type-backend-sys' ;
				break;

			case self::PRJ_TYPE_COMP_BACK:
				$typename = 'type-backend' ;
				break;

			case self::PRJ_TYPE_COMP_SITE:
				$typename = 'type-site' ;
				break;

			case self::PRJ_TYPE_MODEL:
				$typename = 'type-model' ;
				break;

			case self::PRJ_TYPE_PLUGIN:
				$typename = 'type-plugin' ;
				break;

		}

		return $typename;
	}


} // class
