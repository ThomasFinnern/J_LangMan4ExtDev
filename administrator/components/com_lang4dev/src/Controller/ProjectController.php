<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_lang4dev
 *
 * @copyright (C) 2022-2022 Lang4dev Team
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Finnern\Component\Lang4dev\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Router;
use Joomla\CMS\Session\Session;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Symfony\Component\Yaml\Yaml;

/**
 * The Gallery Controller
 *
 * @since __BUMP_VERSION__
 */
class projectController extends FormController
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
	 * @param   \JInput              $input    Input
	 *
	 * @since __BUMP_VERSION__
	 * @see    \JControllerLegacy
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

		if (empty($this->extension))
		{
			$this->extension = $this->input->get('extension', 'com_lang4dev');
		}
	}

	/**
	 * Standard cancel, back to list view
	 *
	 * @param null $key
	 *
	 * @return bool
	 *
	 * @since __BUMP_VERSION__
	 */
	public function cancel($key = null)
	{
		Session::checkToken() or die(Text::_('JINVALID_TOKEN'));
		$link = Router::_('index.php?option=com_lang4dev&view=projects');
		$this->setRedirect($link);

		return true;
	}

	/**
     * Remove an item.
     *
     * @return  void
     *
     * @since   1.6
     */
    /**
    public function delete()
    {
        // Check for request forgeries
        $this->checkToken();

        $user = $this->app->getIdentity();
        $cids = (array) $this->input->get('cid', array(), 'array');

        if (count($cids) < 1)
        {
            $this->setMessage(Text::_('COM_LANG4DEV_NO_IMAGE_SELECTED'), 'warning');
        }
        else
        {
            // Access checks.
            foreach ($cids as $i => $id)
            {
                if (!$user->authorise('core.delete', 'com_menus.menu.' . (int) $id))
                {
                    // Prune items that you can't change.
                    unset($cids[$i]);
                    $this->app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), 'error');
                }
            }

            if (count($cids) > 0)
            {
                // Get the model.
                /** @var \Joomla\Component\Menus\Administrator\Model\MenuModel $model *
                $model = $this->getModel();

                // Make sure the item ids are integers
                $cids = ArrayHelper::toInteger($cids);

                // Remove the items.
                if (!$model->delete($cids))
                {
                    $this->setMessage($model->getError(), 'error');
                }
                else
                {
                    // Delete image files physically

                    /** ToDo: folowing
                    $IsDeleted = false;

                    try
                    {

                        // ToDo: handle deleting of files like in menu (m-controller -> m-model -> m-table)

                        $filename          = $this->name;

                        //$imgFileModel = JModelLegacy::getInstance('imageFile', 'ProjectModel');
                        $imgFileModel = $this->getModel ('imageFile');

                        $IsFilesAreDeleted = $imgFileModel->deleteImgItemImages($filename);
                        if (! $IsFilesAreDeleted)
                        {
                            // Remove from database
                        }

                        $IsDeleted = parent::delete($pk);
                    }
                    catch (\RuntimeException $e)
                    {
                        $OutTxt = '';
                        $OutTxt .= 'Error executing image.table.delete: "' . $pk . '<br>';
                        $OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

                        $app = Factory::getApplication();
                        $app->enqueueMessage($OutTxt, 'error');
                    }

                    return $IsDeleted;
                    /**









                    $this->setMessage(Text::plural('COM_LANG4DEV_N_ITEMS_DELETED', count($cids)));
                }
            }
        }

        $this->setRedirect('index.php?option=com_menus&view=menus');
    }
    /**/


	/**
	 * Method to check if you can add a new record.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since __BUMP_VERSION__
	 *
	protected function allowAdd($data = array())
	{
        $app  = Factory::getApplication();
        $user = $app->getIdentity();

		return ($user->authorise('core.create', $this->extension) || count($user->getAuthorisedGalleries($this->extension, 'core.create')));
	}
	/**/

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
	protected function allowEdit($data = array(), $key = 'parent_id')
	{
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
        $app  = Factory::getApplication();
        $user = $app->getIdentity();

		// Check "edit" permission on record asset (explicit or inherited)
		if ($user->authorise('core.edit', $this->extension . '.gallery.' . $recordId))
		{
			return true;
		}

		// Check "edit own" permission on record asset (explicit or inherited)
		if ($user->authorise('core.edit.own', $this->extension . '.gallery.' . $recordId))
		{
			// Need to do a lookup from the model to get the owner
			$record = $this->getModel()->getItem($recordId);

			if (empty($record))
			{
				return false;
			}

			$ownerId = $record->created_user_id;

			// If the owner matches 'me' then do the test.
			if ($ownerId == $user->id)
			{
				return true;
			}
		}

		return false;
	}
	/**/

	/**
	 * Method to run batch operations.
	 *
	 * @param   object  $model  The model.
	 *
	 * @return  boolean  True if successful, false otherwise and internal error is set.
	 *
	 * @since __BUMP_VERSION__
	 *
	public function batch($model = null)
	{
	Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

		// Set the model
		/** @var \Lang4dev\Component\Lang4dev\Administrator\Model\GalleryModel $model *
		$model = $this->getModel('Gallery');

		// Preset the redirect
		$this->setRedirect('index.php?option=com_lang4dev&view=galleries&extension=' . $this->extension);

		return parent::batch($model);
	}
	/**/

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
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$append = parent::getRedirectToItemAppend($recordId);
		$append .= '&extension=' . $this->extension;

		return $append;
	}
	/**/

	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since __BUMP_VERSION__
	 *
	protected function getRedirectToListAppend()
	{
		$append = parent::getRedirectToListAppend();
		$append .= '&extension=' . $this->extension;

		return $append;
	}
	/**/

	/**
	 * Function that allows child controller access to model data after the data has been saved.
	 *
	 * @param   \Joomla\CMS\MVC\Model\BaseDatabaseModel  $model      The data model object.
	 * @param   array                                    $validData  The validated data.
	 *
	 * @return  void
	 *
	 * @since __BUMP_VERSION__
	 *
	protected function postSaveHook(BaseDatabaseModel $model, $validData = array())
	{
		$item = $model->getItem();

		if (isset($item->params) && is_array($item->params))
		{
			$registry = new Registry($item->params);
			$item->params = (string) $registry;
		}

		if (isset($item->metadata) && is_array($item->metadata))
		{
			$registry = new Registry($item->metadata);
			$item->metadata = (string) $registry;
		}
	}
	/**/
}
