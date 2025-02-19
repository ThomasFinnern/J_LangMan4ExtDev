<?php
/**
 * @package         LangMan4Dev
 * @subpackage      com_lang4dev
 * @author          Thomas Finnern <InsideTheMachine.de>
 * @copyright  (c)  2022-2025 Lang4dev Team
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Finnern\Component\Lang4dev\Administrator\View\PrjTexts;

defined('_JEXEC') or die;

require_once(__DIR__ . '/../../Helper/selectProject.php');

use Finnern\Component\Lang4dev\Administrator\Helper\langPathFileName;
use Finnern\Component\Lang4dev\Administrator\Model\PrjTextsModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

use Finnern\Component\Lang4dev\Administrator\Helper\sessionProjectId;
use Finnern\Component\Lang4dev\Administrator\Helper\sessionTransLangIds;

use function defined;
use function Finnern\Component\Lang4dev\Administrator\Helper\selectProject;

/**
 * View class for a list of lang4dev.
 *
 * @since __BUMP_VERSION__
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The \Form object
     *
     * @var  Form
     */
    protected $form;

    /**
     * @var
     * @since version
     */
    protected $project;

    protected $isDebugBackend;
    protected $isDevelop;
    protected $isDoCommentIds;

    protected $mainLangId;
    protected $transIdsClassified;
    protected $subProjectDatas;

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
        //--- config --------------------------------------------------------------------

        $l4dConfig            = ComponentHelper::getComponent('com_lang4dev')->getParams();
        $this->isDebugBackend = $l4dConfig->get('isDebugBackend');
        $this->isDevelop      = $l4dConfig->get('isDevelop');

        $this->isDoCommentIds = $l4dConfig->get('isDoComment_prepared_missing_ids');

        //--- session (config) ----------------------------------------------------------

        // main / translation language id
        $sessionTransLangIds = new sessionTransLangIds ();
        [$mainLangId, $transLangId] = $sessionTransLangIds->getIds();
        $this->mainLangId = $mainLangId;

        // selection of project and subproject
        $sessionProjectId = new sessionProjectId();
        [$prjId, $subPrjActive] = $sessionProjectId->getIds();

        //--- Form ----------------------------------------------------------------------

        $this->form = $this->get('Form');

        $errors = $this->get('Errors');

//        // Check for errors.
//        if (count($errors = $this->get('Errors')))
//        {
//            throw new GenericDataException(implode("\n", $errors), 500);
//        }

        //--- Main and target lang file --------------------------------------------------------------

        $sessionTransLangIds = new sessionTransLangIds ();
        [$mainLangId, $transLangId] = $sessionTransLangIds->getIds();

        $this->form->setValue('selectSourceLangId', null, $mainLangId);

        //--- define projectTexts ------------------------------------------------------------

        /** @var PrjTextsModel $model */
        $model             = $this->getModel();
        $this->langProject = $model->getProject($prjId, $subPrjActive);
        $project           = $this->langProject;

        /* test projects *
        $project =
        $this->projectTexts = selectProject('lang4dev');
        // $this->projectTexts = selectProject('joomgallery');
        // $this->projectTexts = selectProject('rsgallery2');
        // $this->projectTexts = selectProject('joomla4x');
        /**/


        //--- collect content ---------------------------------------------------

        // read translations
        $project->readLangFiles($this->mainLangId);

        // scan code for occurrences of
        $project->scanCode4TransIds();
        $project->scanCode4TransStrings();

        //--- sort by types -----------------------------------------

        // ['missing', same, notUsed, doubles']
        $this->transIdsClassified = $project->getTransIdsClassified($this->mainLangId);

        // ToDo: write to log and prepare for email to be send

        //--- Collect display data from subProjects ----------------------------------

        $this->subProjectDatas = [];

        if (count($project->subProjects) > 0) {
            foreach ($project->subProjects as $subProject) {

                $subProjectData = new \stdClass();
                $subProjectData->prjIdAndTypeText = $subProject->getPrjIdAndTypeText();
                $subProjectData->transStringsLocations = $subProject->getTransStringsLocations();
                $subProjectData->langFileNames = $subProject->getLangFileNames($this->mainLangId);
                // create dummy lang name for path extract
                $subProjectData->componentLangPath = $this->getComponentLangPath($subProject);

                $this->subProjectDatas[] = $subProjectData;
            }
        }

        /**
         * $Layout = Factory::getApplication()->input->get('layout');
         * Lang4devHelper::addSubmenu('config');
         * $this->sidebar = \JHtmlSidebar::render();
         **/

        $Layout = Factory::getApplication()->input->get('layout');
        //echo '$Layout: ' . $Layout . '<br>';

        $this->addToolbar($Layout);

        parent::display($tpl);

        return;
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
        if (!empty ($this->isDevelop)) {
            echo '<span style="color:red">'
                . '<b>Tasks:</b> <br>'
                . '* use mainLangId from (config / session) used in files view ? <br>'
                . '* Use OutText / ? outtxt for AD HOC texts<br>'
				. '* Project (sub) data: === langProject: show on debug backend or No subprojects defined   <br>'
//				. '* <br>'
//				. '* <br>'
//				. '* <br>'
//				. '* <br>'
                . '</span><br><br>';
        }

        switch ($Layout) {
            /**
             * case 'RawView':
             * ToolBarHelper::title(Text::_('COM_Lang4dev_MAINTENANCE')
             * . ': ' . Text::_('COM_Lang4dev_CONFIGURATION_RAW_VIEW'), 'screwdriver');
             * ToolBarHelper::cancel('config.cancel_rawView', 'JTOOLBAR_CLOSE');
             *
             *
             * break;
             *
             * case 'RawEdit':
             * ToolBarHelper::title(Text::_('COM_Lang4dev_MAINTENANCE')
             * . ': ' . Text::_('COM_Lang4dev_CONFIGURATION_RAW_EDIT'), 'screwdriver');
             * ToolBarHelper::apply('config.apply_rawEdit');
             * ToolBarHelper::save('config.save_rawEdit');
             * ToolBarHelper::cancel('config.cancel_rawEdit', 'JTOOLBAR_CLOSE');
             * break;
             * /**/
            default:
                ToolBarHelper::cancel('lang4dev.cancel', 'JTOOLBAR_CLOSE');
                break;
        }

        // Set the title
        ToolBarHelper::title(Text::_('COM_LANG4DEV_SUBMENU_PROJECTS_TEXTS'), 'list');

        // Options button.
        if (Factory::getApplication()->getIdentity()->authorise('core.admin', 'com_lang4dev')) {
            $toolbar->preferences('com_lang4dev');
        }
    }

    /**
     * @param   \Finnern\Component\Lang4dev\Administrator\Helper\langSubProject  $subProject
     *
     * @return string
     *
     * @since version
     */
    public function getComponentLangPath(\Finnern\Component\Lang4dev\Administrator\Helper\langSubProject $subProject): string
    {
        $dummyLangFilePathName = $subProject->langBasePath . '/' . $this->mainLangId . '/' . 'dumyyName.ini';
        $oLangPathFileName     = new langPathFileName ($dummyLangFilePathName);

        $postId = ""; //  = " (C) ";
        // lang files outside component
        if ($oLangPathFileName->isLangAtStdJoomla()) {
            $postId = " (J) ";  // lang files inside joomla base lang path
        }

        $componentLangPath = dirname($oLangPathFileName->getlangSubPrjPathFileName()) . $postId;

        if ($this->isDebugBackend) {
            $componentLangPath = $subProject->langBasePath . '/' . $this->mainLangId . $postId;
        }

        return $componentLangPath;
    }

}

