<?php
/**
 * @package       Joomla.Administrator
 * @subpackage    com_lang4dev
 *
 * @copyright  (c)  2022-2024 Lang4dev Team
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Finnern\Component\Lang4dev\Administrator\Controller;

defined('_JEXEC') or die;

use JInput;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Router\Router;
use Joomla\CMS\Session\Session;
use Joomla\Component\Associations\Administrator\Helper\AssociationsHelper;
use Joomla\Component\Menus\Administrator\Model\MenuModel;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

// ???? use Symfony\Component\Yaml\Yaml;

use Finnern\Component\Lang4dev\Administrator\Helper\langSubProject;
use Finnern\Component\Lang4dev\Administrator\Helper\projectType;
use Lang4dev\Component\Lang4dev\Administrator\Model\GalleryModel;

use function defined;

/**
 * The Gallery Controller
 *
 * @since __BUMP_VERSION__
 */
class subprojectController extends FormController
{
    /**
     * The extension for which the galleries apply.
     *
     * @var    string
     * @since __BUMP_VERSION__
     */
    protected $extension;

    /**
     * Constructor.
     *
     * @param   array                $config   An optional associative array of configuration settings.
     * @param   MVCFactoryInterface  $factory  The factory.
     * @param   CMSApplication       $app      The JApplication for the dispatcher
     * @param   JInput               $input    Input
     *
     * @since  __BUMP_VERSION__
     * @see    \JControllerLegacy
     */
    public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
    {
        parent::__construct($config, $factory, $app, $input);

        if (empty($this->extension)) {
            $this->extension = $this->input->get('extension', 'com_lang4dev');
        }
    }

    /**
     * Standard cancel, back to list view
     *
     * @param   null  $key
     *
     * @return bool
     *
     * @since __BUMP_VERSION__
     */
    public function cancel($key = null)
    {
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));
        // $link = Route::_('index.php?option=com_lang4dev&view=subprojects');
        $link = 'index.php?option=com_lang4dev&view=subprojects';
        $this->setRedirect($link);

        return true;
    }

    /**
     *
     * @return bool|void
     *
     * @since version
     */
    public function detectDetails()
    {
        $result = false;

        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

        // ToDo: form validate https://docs.joomla.org/Joomla_4_Tips_and_Tricks:_Form_Validation_Basics

        // try ...

        // check: does it need $input = ....
        $data = $this->input->post->get('jform', array(), 'array');

        // subproject
        /**/
        $prjId   = $data ['prjId'];
        $prjType = (int)$data ['subPrjType'];

        $rootPath           = trim($data ['root_path']);
        $data ['root_path'] = $rootPath;
        /**/

        //--- retrieve data from paths --------------------------------------------

        $subPrj              = new langSubProject ();
        $subPrj->prjId       = $prjId;
        $subPrj->prjType     = $prjType;
        $subPrj->prjRootPath = $rootPath;

        $isFilesFound = $subPrj->RetrieveBaseManifestData();

        if ($isFilesFound) {
            $data['prjXmlPathFilename']  = $subPrj->prjXmlPathFilename;
            $data['installPathFilename'] = $subPrj->installPathFilename;
            $data['prefix']              = $subPrj->langIdPrefix;
        }

        //--- write to post data for save --------------------------------------

        // Add new data to input before process by parent save()
        $this->input->post->set('jform', $data);

        $result = parent::save($key = null, $urlVar = 'id') && $isFilesFound;

        //--- return to edit --------------------------------

        $id   = (int)$data ['id'];
        $link = 'index.php?option=com_lang4dev&view=subproject&layout=edit&id=' . $id;
        $this->setRedirect($link);

        return $result;
    }

    /**
     * Remove an item.
     *
     * @return  void
     *
     * @since   1.6
     */
    /** @var MenuModel $model *
     * $model = $this->getModel();
     *
     * // Make sure the item ids are integers
     * $cids = ArrayHelper::toInteger($cids);
     *
     * // Remove the items.
     * if (!$model->delete($cids))
     * {
     * $this->setMessage($model->getError(), 'error');
     * }
     * else
     * {
     * // Delete image files physically
     *
     * /** ToDo: following
     * $IsDeleted = false;
     *
     * try
     * {
     *
     * // ToDo: handle deleting of files like in menu (m-controller -> m-model -> m-table)
     *
     * $filename          = $this->name;
     *
     * //$imgFileModel = JModelLegacy::getInstance('imageFile', 'subprojectModel');
     * $imgFileModel = $this->getModel ('imageFile');
     *
     * $IsFilesAreDeleted = $imgFileModel->deleteImgItemImages($filename);
     * if (! $IsFilesAreDeleted)
     * {
     * // Remove from database
     * }
     *
     * $IsDeleted = parent::delete($pk);
     * }
     * catch (\RuntimeException $e)
     * {
     * $OutTxt = '';
     * $OutTxt .= 'Error executing image.table.delete: "' . $pk . '<br>';
                        * $OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';
     *
     * $app = Factory::getApplication();
     * $app->enqueueMessage($OutTxt, 'error');
     * }
     *
     * return $IsDeleted;
     * /**
     *
     *
     *
     *
     *
     *
     *
     *
     *
     * $this->setMessage(Text::plural('COM_LANG4DEV_N_ITEMS_DELETED', count($cids)));
     * }
     * }
     * }
     *
     * $this->setRedirect('index.php?option=com_menus&view=menus');
     * }
     * /**/

    /**
     * Method to check if you can add a new record.
     *
     * @param   array  $data  An array of input data.
     *
     * @return  boolean
     *
     * @since __BUMP_VERSION__
     *
     * protected function allowAdd($data = array())
     * {
     * $app  = Factory::getApplication();
     * $user = $app->getIdentity();
     *
     * return ($user->authorise('core.create', $this->extension) || count($user->getAuthorisedGalleries($this->extension, 'core.create')));
     * }
     * /**/

    /**
     * Method to check if you can edit a record.
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key.
     *
     * @return  boolean
     *
     * @since __BUMP_VERSION__
     *
     * protected function allowEdit($data = array(), $key = 'parent_id')
     * {
     * $recordId = (int) isset($data[$key]) ? $data[$key] : 0;
     * $app  = Factory::getApplication();
     * $user = $app->getIdentity();
     *
     * // Check "edit" permission on record asset (explicit or inherited)
     * if ($user->authorise('core.edit', $this->extension . '.gallery.' . $recordId))
     * {
     * return true;
     * }
     *
     * // Check "edit own" permission on record asset (explicit or inherited)
     * if ($user->authorise('core.edit.own', $this->extension . '.gallery.' . $recordId))
     * {
     * // Need to do a lookup from the model to get the owner
     * $record = $this->getModel()->getItem($recordId);
     *
     * if (empty($record))
     * {
     * return false;
     * }
     *
     * $ownerId = $record->created_user_id;
     *
     * // If the owner matches 'me' then do the test.
     * if ($ownerId == $user->id)
     * {
     * return true;
     * }
     * }
     *
     * return false;
     * }
     * /**/

    /**
     * Method to run batch operations.
     *
     * @param   object  $model  The model.
     *
     * @return  boolean  True if successful, false otherwise and internal error is set.
     *
     * @since __BUMP_VERSION__
     *
     * public function batch($model = null)
     * {
     * Session::checkToken() or die(Text::_('JINVALID_TOKEN'));
     *
     * // Set the model
     * /** @var GalleryModel $model *
     *                          $model = $this->getModel('Gallery');
     *
     * // Preset the redirect
     * $this->setRedirect('index.php?option=com_lang4dev&view=galleries&extension=' . $this->extension);
     *
     * return parent::batch($model);
     * }
     * /**/

    /**
     * Gets the URL arguments to append to an item redirect.
     *
     * @param   integer  $recordId  The primary key id for the item.
     * @param   string   $urlVar    The name of the URL variable for the id.
     *
     * @return  string  The arguments to append to the redirect URL.
     *
     * @since __BUMP_VERSION__
     *
     * protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
     * {
     * $append = parent::getRedirectToItemAppend($recordId);
     * $append .= '&extension=' . $this->extension;
     *
     * return $append;
     * }
     * /**/

    /**
     * Gets the URL arguments to append to a list redirect.
     *
     * @return  string  The arguments to append to the redirect URL.
     *
     * @since __BUMP_VERSION__
     *
     * protected function getRedirectToListAppend()
     * {
     * $append = parent::getRedirectToListAppend();
     * $append .= '&extension=' . $this->extension;
     *
     * return $append;
     * }
     * /**/

    /**
     * Function that allows child controller access to model data after the data has been saved.
     *
     * @param   BaseDatabaseModel  $model      The data model object.
     * @param   array              $validData  The validated data.
     *
     * @return  void
     *
     * @since __BUMP_VERSION__
     *
     * protected function postSaveHook(BaseDatabaseModel $model, $validData = array())
     * {
     * $item = $model->getItem();
     *
     * if (isset($item->params) && is_array($item->params))
     * {
     * $registry = new Registry($item->params);
     * $item->params = (string) $registry;
     * }
     *
     * if (isset($item->metadata) && is_array($item->metadata))
     * {
     * $registry = new Registry($item->metadata);
     * $item->metadata = (string) $registry;
     * }
     * }
     * /**/
}
