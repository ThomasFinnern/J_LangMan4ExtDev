<?php
/**
 * @package     Finnern\Component\Lang4dev\Administrator\Helper
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace Finnern\Component\Lang4dev\Administrator\Helper;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
//use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use RuntimeException;

use function defined;

// no direct access
defined('_JEXEC') or die;

// https://www.php.net/manual/de/simplexml.examples-basic.php

class manifestData
{
    public $prjXmlFilePath = '';
    public $prjXmlPathFilename = '';

    public $defaultLangPath = "";
    public $adminLangPath = "";

    // is also admin
    public $prjDefaultPath = '';
    public $prjAdminPath = '';
    public $isDefaultPathDefined = '';
    public $isAdminPathDefined = '';

    // local development folder or installed component
    public $isInstalled = false;
    /** @var bool */
    public $isValidXml;

    protected $manifest = false; // XML: false or SimpleXMLElement

    /**
     * @since __BUMP_VERSION__
     */
    public function __construct($prjXmlPathFilename = '')
    {
        $this->prjXmlPathFilename = $prjXmlPathFilename;
        $this->prjXmlFilePath     = ""; // dirname($prjXmlPathFilename);

        // filename given
        if ($prjXmlPathFilename != '') {
            $this->isValidXml = $this->readManifestData();
        }

        return;
    }

    /**
     * @param $prjXmlPathFilename
     *
     * @return bool
     *
     * @throws Exception
     * @since version
     */
    public function readManifestData($prjXmlPathFilename = '')
    {
        $isValidXml = false;

        try {
            // use new file
            if ($prjXmlPathFilename != '') {
                $this->prjXmlPathFilename = $prjXmlPathFilename;
                $this->prjXmlFilePath     = dirname($prjXmlPathFilename);
                // ToDo: clear old data
            } else {
                // use given path name
                $prjXmlPathFilename = $this->prjXmlPathFilename;
            }

            // file exists
            if (File::exists($prjXmlPathFilename)) {
                //// keep as alternative example, used in RSG" installer . Can't remeber why simplexml_load_file was not used
                //$context = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));
                //$this->manifest = $xml = file_get_contents($prjXmlPathFilename, false, $context);

                // Read the file to see if it's a valid component XML file
                $this->manifest = simplexml_load_file($prjXmlPathFilename);

                // error reading ?
                if (!empty($this->manifest)) {
                    $isValidXml = true;
                } else {
                    $OutTxt = Text::_('COM_LANG4DEV_FILE_IS_NOT_AN_XML_DOCUMENT' . ': ' . $prjXmlPathFilename);
                    $app    = Factory::getApplication();
                    $app->enqueueMessage($OutTxt, 'error');
                }

                //--- developer folder or installed in joomla  -----------------------------------------------------------

	            $this->isInstalled = $this->isPathOnJxServer($prjXmlPathFilename);

                //--- extract values -----------------------------------------------------------

                $xml = $this->manifest;

                //--- default main (site) path -------------------------------

                // $this->prjDefaultPath = ' >> initial::(Site_not_defined)';
                $this->prjDefaultPath = '';
                $this->isDefaultPathDefined = false;

                if (isset($xml->files)) {
                    $files = $xml->files;
                    if (isset ($files['folder'])) {
                        $this->prjDefaultPath = $files['folder'][0];
                        $this->isDefaultPathDefined = true;
                    }
                }

                //--- default admin path -------------------------------

                $this->prjAdminPath = ">>Admin_not_defined";
                $this->isAdminPathDefined = false;

                if (isset($xml->administration->files)) {
                    $files = $xml->administration->files;
                    if (isset ($files['folder'])) {
                        $this->prjAdminPath = $files['folder'][0];
                        $this->isAdminPathDefined = true;
                    }
                }

                //--- defaultLangPath -------------------------------

                $this->defaultLangPath = '';
                if (isset($xml->files)) {
                    // add languages folder
                    $this->defaultLangPath = $this->prjDefaultPath . '/language';
                }

                //--- adminLangPath -------------------------------

                $this->adminLangPath = '';
                if (isset($xml->administration->files)) {
                    // add languages folder
                    $this->adminLangPath = $this->prjAdminPath . '/language';
                }
            } else {
                $OutTxt = Text::_('COM_LANG4DEV_FILE_DOES_NOT_EXIST' . ': ' . $prjXmlPathFilename);
                $app    = Factory::getApplication();
                $app->enqueueMessage($OutTxt, 'error');
            }
        } catch (RuntimeException $e) {
            $OutTxt = '';
            $OutTxt .= 'Error executing readManifestData: "' . $prjXmlPathFilename . '"<br>';
            $OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

            $app = Factory::getApplication();
            $app->enqueueMessage($OutTxt, 'error');
        }

        return $isValidXml;
    }

    // info cast to string / int .. when using it (otherwise array is returned)

    /**
     * @param $name
     * @param $default
     *
     * @return mixed
     *
     * @since version
     */
    public function get($name, $default)
    {
//		return isset($this->manifest->$name) ? $this->manifest->$name : $default;
        $result = $this->manifest->$name;

        // return isset($this->manifest->$name) ? $this->manifest->$name : $default;
        return $result;
    }

    // return null on wrong path

    /**
     * @param $names
     * @param $default
     *
     * @return bool|mixed
     *
     * @since version
     */
    public function getByPath($names, $default)
    {
        $result = $default;

        if (!is_array($names)) {
            $name = array($names);
        }

        $base = $this->manifest;
        foreach ($names as $name) {
            $base = isset($this->manifest->$name) ? $this->manifest->$name : null;

            if ($base == null) {
                break;
            }
        }

        if ($base != null) {
            $result = $base;
        }

        return $result;
    }

    /**
     *
     * @return string
     *
     * @since version
     */
    public function getSriptFile()
    {
        return (string)$this->get('scriptfile', '');
    }

    /**
     *
     * @return string
     *
     * @since version
     */
    public function getName()
    {
        return (string)$this->get('name', '');
    }

    // info cast to string / int .. when using it (otherwise array is returned)

    /**
     * @param $name
     *
     * @return null
     *
     * @since version
     */
    public function getXml($name)
    {
        return isset($this->manifest->$name) ? $this->manifest->$name : null;
    }


	public function isPathOnJxServer($prjPathFilename)
	{
		$isPathOnJxServer = false;

		$lowerJxPath = strtolower (JPATH_ROOT);
		$lowerPrjPath = strtolower ($prjPathFilename);

		$slashJxPath = str_replace('\\', '/', $lowerJxPath);;
		$slashPrjPath = str_replace('\\', '/', $lowerPrjPath);;

		// project path starts with root path
		if (str_starts_with($slashPrjPath, $slashJxPath)) {
			$isPathOnJxServer = true;
		}

		return $isPathOnJxServer;
	}




//	protected function loadManifestFromData(\SimpleXMLElement $xml)
//	{
//		$test              = new stdClass();
//		$test->name        = (string) $xml->name;
//		$test->packagename = (string) $xml->packagename;
//		$test->update      = (string) $xml->update;
//		$test->authorurl   = (string) $xml->authorUrl;
//		$test->author      = (string) $xml->author;
//		$test->authoremail = (string) $xml->authorEmail;
//		$test->description = (string) $xml->description;
//		$test->packager    = (string) $xml->packager;
//		$test->packagerurl = (string) $xml->packagerurl;
//		$test->scriptfile  = (string) $xml->scriptfile;
//		$test->version     = (string) $xml->version;
//
////		if (isset($xml->files->file) && \count($xml->files->file)) {
////			foreach ($xml->files->file as $file) {}
////		}
//
////		// Handle cases where package contains folders
////		if (isset($xml->files->folder) && \count($xml->files->folder))
////		{
////			foreach ($xml->files->folder as $folder) {}
////		}
//	}
//
//	/**
//	 * Apply manifest data from a \SimpleXMLElement to the object.
//	 *
//	 * @param   \SimpleXMLElement  $xml  Data to load
//	 *
//	 * @return  void
//	 *
//	 * @since   3.1
//	 */
//	protected function loadManifestFromData2(\SimpleXMLElement $xml)
//	{
//		$test               = new stdClass();
//		$test->name         = (string) $xml->name;
//		$test->libraryname  = (string) $xml->libraryname;
//		$test->version      = (string) $xml->version;
//		$test->description  = (string) $xml->description;
//		$test->creationdate = (string) $xml->creationDate;
//		$test->author       = (string) $xml->author;
//		$test->authoremail  = (string) $xml->authorEmail;
//		$test->authorurl    = (string) $xml->authorUrl;
//		$test->packager     = (string) $xml->packager;
//		$test->packagerurl  = (string) $xml->packagerurl;
//		$test->update       = (string) $xml->update;
//
//		if (isset($xml->files) && isset($xml->files->file) && \count($xml->files->file))
//		{
//			foreach ($xml->files->file as $file)
//			{
//				$test->filelist[] = (string) $file;
//			}
//		}
//	}


    public function __toTextItem($name = '')
    {
        return $name . '="' . $this->get($name, '') . '"';
    }

    /**
     *
     * @return array
     *
     * @since version
     */
    public function __toText()
    {
        $lines = [];

        $lines[] = '--- manifest file ---------------------------';

        $lines[] = $this->__toTextItem('name');

        //$test->name         = (string) $xml->name;

        $lines[] = $this->__toTextItem('author');
        $lines[] = $this->__toTextItem('authorEmail');
        $lines[] = $this->__toTextItem('authorUrl');
        $lines[] = $this->__toTextItem('creationDate');
        $lines[] = $this->__toTextItem('description');
        $lines[] = $this->__toTextItem('libraryname');
        $lines[] = $this->__toTextItem('packagename');
        $lines[] = $this->__toTextItem('packager');
        $lines[] = $this->__toTextItem('packagerurl');
        $lines[] = $this->__toTextItem('scriptfile');
        $lines[] = $this->__toTextItem('update');
        $lines[] = $this->__toTextItem('version');

        $lines[] = '';
        if ($this->isInstalled) {
            $lines[] = '( Manifest is within joomla ) ';
        } else {
            $lines[] = '( Manifest on development path ) ';
        }

        $lines[] = 'default (site) path: ' . $this->prjDefaultPath;
        $lines[] = 'admin path: ' . $this->prjAdminPath;

        $lines[] = 'defaultLangPath (site): ' . $this->defaultLangPath;
        $lines[] = 'adminLangPath: ' . $this->adminLangPath;

        $lines[] = '';

        return $lines;
    }

} // class




