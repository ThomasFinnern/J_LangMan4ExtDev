<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_lang4dev
 *
 * @copyright   Copyright (C) 2022 - 2022
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Finnern\Component\Lang4dev\Administrator\View\PrjTexts;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

use Finnern\Component\Lang4dev\Administrator\Helper\searchLangLocations;
use Finnern\Component\Lang4dev\Administrator\Helper\langFile;

/**
 * View class for a list of lang4dev.
 *
 * @since __BUMP_VERSION__
 */
class HtmlView extends BaseHtmlView
{
	protected $isDevelop;
	protected $prjLangLocations;

	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return  mixed  A string if successful, otherwise an \Exception object.
	 *
	 * @since __BUMP_VERSION__
	 */
	public function display($tpl = null)
	{
		$Layout = Factory::getApplication()->input->get('layout');
		//echo '$Layout: ' . $Layout . '<br>';

		$l4dConfig = ComponentHelper::getComponent('com_Lang4dev')->getParams();
		$this->isDevelop = $l4dConfig->get('isDevelop');

		//--- search paths ------------------------------------

		// ToDo: take search paths from somewhere else
		//$file = Path::clean(JPATH_ADMINISTRATOR . '/components/' . $component . '/helpers/' . $eName . '.php');
		//$searchPaths = array (JPATH_ADMINISTRATOR . '/components/com_lang4dev');
		//$searchPaths = array (JPATH_ADMINISTRATOR . '/components/com_lang4dev/tmpl');
		$searchPaths = array (JPATH_ADMINISTRATOR . '/components/com_lang4dev/src/test');
		$oLangLocations = new searchLangLocations ($searchPaths);
		$this->prjLangLocations = $oLangLocations->findAllTranslationIds();

		//--- langFiles ToDo: use other ... ------------------------------------

		// dummy file:
		//$filePath = JPATH_ADMINISTRATOR . '/components/' . 'com_lang4dev/language/en-GB/com_lang4dev.sys.tmp';
		$filePath = JPATH_ADMINISTRATOR . '/components/' . 'com_lang4dev/language/en-GB/com_lang4dev.sys.tmp';
		//$filePath = JPATH_ADMINISTRATOR . '//components/com_lang4dev/src/test/' . 'com_lang4dev.01.tmp';
		$srcPath = File::stripExt($filePath) . '.ini';
		File::copy($srcPath, $filePath);

//		$filePath = JPATH_ADMINISTRATOR . '/components/' . 'com_lang4dev/language/en-GB/com_lang4dev.tmp';
//		File::copy(File::stripExt($filePath) . '.ini', $filePath);

		$testLangFile = new langFile($filePath);
		$testLangFile->translationsToFile();
		$this->testLangFile = $testLangFile;

		//--- not translated --------------------------------------------------------------

		$transIds_new = $this->prjLangLocations->getMissingTransIds($testLangFile->getItemNames());

		$this->transIds_new = $transIds_new;


		/**
		HTMLHelper::_('sidebar.setAction', 'index.php?option=com_Lang4dev&view=config&layout=RawView');
		/**
		$Layout = Factory::getApplication()->input->get('layout');
		Lang4devHelper::addSubmenu('config');
		$this->sidebar = \JHtmlSidebar::render();
		**/

		$this->addToolbar($Layout);
		/**/

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since __BUMP_VERSION__
	 */
	protected function addToolbar($Layout)
	{
		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');

		// on develop show open tasks if existing
		if (!empty ($this->isDevelop))
		{
			echo '<span style="color:red">'
				. 'Tasks: <br>'
				. '* <br>'
				. '* <br>'
//				. '* <br>'
//				. '* <br>'
//				. '* <br>'
//				. '* <br>'
				. '</span><br><br>';
		}

		switch ($Layout)
		{
			/**
			case 'RawView':
				ToolBarHelper::title(Text::_('COM_Lang4dev_MAINTENANCE')
					. ': ' . Text::_('COM_Lang4dev_CONFIGURATION_RAW_VIEW'), 'screwdriver');
				ToolBarHelper::cancel('config.cancel_rawView', 'JTOOLBAR_CLOSE');


				break;

			case 'RawEdit':
				ToolBarHelper::title(Text::_('COM_Lang4dev_MAINTENANCE')
					. ': ' . Text::_('COM_Lang4dev_CONFIGURATION_RAW_EDIT'), 'screwdriver');
				ToolBarHelper::apply('config.apply_rawEdit');
				ToolBarHelper::save('config.save_rawEdit');
				ToolBarHelper::cancel('config.cancel_rawEdit', 'JTOOLBAR_CLOSE');
				break;
			/**/
			default:
				ToolBarHelper::cancel('lang4dev.cancel', 'JTOOLBAR_CLOSE');
				break;
		}

		// Options button.
		if (Factory::getApplication()->getIdentity()->authorise('core.admin', 'com_lang4dev'))
		{
			$toolbar->preferences('com_Lang4dev');
		}
	}



}

