<?php
/**
 * @package         LangMan4Dev
 * @subpackage      com_lang4dev
 * @author          Thomas Finnern <InsideTheMachine.de>
 * @copyright  (c)  2022-2025 Lang4dev Team
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Finnern\Component\Lang4dev\Administrator\View\Project;

defined('_JEXEC') or die;

use JObject;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

//use Finnern\Component\Lang4dev\Administrator\Helper\Lang4devHelper;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;

use function defined;

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
     * An array of items
     *
     * @var  array
     */
    protected $items;

    /**
     * The model state
     *
     * @var  JObject
     */
    protected $state;

    /**
     * Flag if an association exists
     *
     * @var  boolean
     */
    protected $assoc;

    /**
     * The actions the user is authorised to perform
     *
     * @var  JObject
     */
    protected $canDo;

    /**
     * Is there a content type associated with this gallery alias
     *
     * @var    boolean
     * @since __BUMP_VERSION__
     */
    protected $checkTags = false;

    /**
     * @var
     * @since version
     */
    protected $isDebugBackend;
    protected $isDevelop;

    // protected $subProjects;

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

        $l4dConfig = ComponentHelper::getComponent('com_lang4dev')->getParams();
        //$compo_params = ComponentHelper::getComponent('com_lang4dev')->getParams();
        $this->isDebugBackend = $l4dConfig->get('isDebugBackend');
        $this->isDevelop      = $l4dConfig->get('isDevelop');

        //--- Form --------------------------------------------------------------------

        $this->form  = $this->get('Form');
        $this->item  = $this->get('Item');
        $this->state = $this->get('State');

        $subProjects = $this->item->subProjects;

        //$section = $this->state->get('gallery.section') ? $this->state->get('gallery.section') . '.' : '';
        //$this->canDo = ContentHelper::getActions($this->state->get('gallery.component'), $section . 'gallery', $this->item->id);
        $this->canDo = ContentHelper::getActions('com_lang4dev', 'project', $this->item->id);
//        $this->assoc = $this->get('Assoc');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // different toolbar on different layouts
        $Layout = Factory::getApplication()->input->get('layout');
        $this->addToolbar($Layout, $this->item->id);

        Factory::getApplication()->input->set('hidemainmenu', true);

        return parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since __BUMP_VERSION__
     */
    protected function addToolbar($Layout, $itemId)
    {
        //$canDo = \Joomla\Component\Content\Administrator\Helper\ContentHelper::getActions('com_content', 'category', $this->state->get('filter.category_id'));
        $canDo = true;
        $user  = Factory::getUser();

        // Get the toolbar object instance
        $toolbar = Toolbar::getInstance('toolbar');

        // on develop show open tasks if existing
        if (!empty ($this->isDevelop)) {
            echo '<span style="color:red">'
                . '<b>Tasks:</b> <br>'
                . '* description to each input parameter "_DESC"<br>'
                . '* fill out Project Info<br>'
                . '* <br>'
//				. '* <br>'
//				. '* <br>'
//				. '* <br>'
//				. '* <br>'
                . '</span><br><br>';
        }

        switch ($Layout) {
            case 'edit':
            default:
                ToolBarHelper::title(Text::_('COM_LANG4DEV_EDIT_PROJECT', 'project'));

                //--- apply, save and close ... -----------------------------------

                ToolBarHelper::apply('project.apply');
                ToolBarHelper::save('project.save');
                ToolBarHelper::save2new('project.save2new');
                ToolBarHelper::save2copy('project.save2copy');

                $toolbar->delete('projects.delete')
                    ->text('JTOOLBAR_DELETE')
                    ->message('JGLOBAL_CONFIRM_DELETE')
                    ->listCheck(true);

                //--- cancel  -----------------------------------

                //ToolBarHelper::save2new('image.save2new');
                if (empty($this->item->id)) {
                    ToolBarHelper::cancel('project.cancel', 'JTOOLBAR_CLOSE');
                } else {
                    ToolBarHelper::cancel('project.cancel', 'JTOOLBAR_CLOSE');
                }

                // item is saved already
                if ($itemId>0) {
                    ToolbarHelper::custom(
                        'project.detectSubProjects',
                        'icon-refresh',
                        '',
                        'COM_LANG4DEV_DETECT_DETAILS',
                        false
                    );
                }

                // Options button.
                if (Factory::getApplication()->getIdentity()->authorise('core.admin', 'com_lang4dev')) {
                    $toolbar->preferences('com_lang4dev');
                }

                break;
        }  // switch

    }

}

