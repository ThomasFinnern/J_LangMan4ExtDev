<?php
/**
 * @package     Lang4dev
 * @subpackage
 *
 * @version
 * @copyright (C) 2022-2022 Lang4dev Team
 * @license   GPL2
 */

namespace Finnern\Component\Lang4dev\Administrator\Helper;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;

// no direct access
\defined('_JEXEC') or die;

/**
 * Reads and writes user selected main lang ID ...
 *
 *
 *
 *
 *
 * id and subproject id from / to session
 *
 * The initial ID is the highest project id in db and zero for the sub id
 * Zero on sub id tells that all subprojects are displayed to the user
 *
 * @package Lang4dev
 */

class sessionTransLangIds
{
	protected $mainLangId = '??-??';
	protected $transLangId = '??-??';

	public function clear() {

		$mainLangId = '??-??';
		$transLangId = '??-??';

		return;
	}

	/**
	 * setIds
	 *
	 * @return
	 * @since __BUMP_VERSION__
	 */
	public function setIds($mainLangId = '??-??', $transLangId = '??-??')
	{

		$this->mainLangId = $mainLangId;
		$this->transLangId = $transLangId;

		$session = Factory::getSession();
		$data = $session->set('_lang4dev.mainLangId', $mainLangId);
		$data = $session->set('_lang4dev.transLangId', $transLangId);

		return;
	}

	public function setMainIds($mainLangId = '??-??')
	{
		$this->mainLangId = $mainLangId;

		$session = Factory::getSession();
		$data = $session->set('_lang4dev.mainLangId', $mainLangId);

		return;
	}

	public function setTransIds($transLangId = '??-??')
	{
		$this->transLangId = $transLangId;

		$session = Factory::getSession();
		$data = $session->set('_lang4dev.transLangId', $transLangId);

		return;
	}

	public function resetIds()
	{
		// default values
		//$this->setIds();

		$this->clear ();

		$session = Factory::getSession();
		$session->clear('_lang4dev.mainLangId');
		$session->clear('_lang4dev.transLangId');

		return;
	}

	/**
	 * getIds
	 *
	 * @return string [] project id, subproject id
	 * @since __BUMP_VERSION__
	 */
	public function getIds()
	{
		//--- already set in class ? ---------------------

		$mainLangId = $this->mainLangId;
		$transLangId = $this->transLangId;

		// Is not set
		if ($mainLangId == '??-??')
		{
			//--- try session if set ---------------------------------

			$session = Factory::getSession();
			$mainLangId   = $session->get('_lang4dev.mainLangId', null);
			if ($mainLangId != null)
			{
				$transLangId = $session->get('_lang4dev.transLangId', '0');
			}

			// Is not set (in control) => use config
			if (empty($mainLangId))
			{
				//--- retrieve default from config ---------------------------------

				$l4dConfig = ComponentHelper::getComponent('com_lang4dev')->getParams();

				$mainLangId = $l4dConfig->get('main_langId');
				$transLangId = $l4dConfig->get('trans_langId');
			}
		}

		return [$mainLangId, $transLangId];
	}


}