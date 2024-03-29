<?php
/**
 * @package       Joomla.Administrator
 * @subpackage    com_lang4dev
 *
 * @copyright (c) 2022-2023 Lang4dev Team
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Session\Session;

//HTMLHelper::_('stylesheet', 'com_lang4dev/backend/maintenance.css', array('version' => 'auto', 'relative' => true));
//HTMLHelper::_('script', 'com_lang4dev/backend/maintenance.js', ['version' => 'auto', 'relative' => true]);

//Text::script('COM_LANG4DEV_PLEASE_CHOOSE_A_GALLERY_FIRST', true);
HTMLHelper::_('stylesheet', 'com_lang4dev/backend/controlPanel.css', array('version' => 'auto', 'relative' => true));

// command buttons
class cmdButton
{
    public $link;
    public $textTitle;
    public $textInfo;
    public $classIcons;
    public $classButton;

    public function __construct(
        $link = '?',
        $textTitle = '?',
        $textInfo = '?',
        $classIcons = array('?', '?'),
        $classButton = '?'
    ) {
        $this->link        = $link;
        $this->textTitle   = $textTitle;
        $this->textInfo    = $textInfo;
        $this->classIcons  = $classIcons;
        $this->classButton = $classButton;
    }

}

$cmdButtons = [];

// projects
$cmdButtons[] = new cmdButton(
    Route::_('index.php?option=com_lang4dev&view=projects'),
    Text::_('COM_LANG4DEV_PROJECTS'),
    Text::_('COM_LANG4DEV_PROJECTS_DESC') . '                        ',
    array('fas fa-tasks', 'icon-flag'),
//    array('icon-equalizer', 'icon-edit'),
    'viewProjects'
);

// project texts
$cmdButtons[] = new cmdButton(
    Route::_('index.php?option=com_lang4dev&view=prjtexts'),
    Text::_('COM_LANG4DEV_PRJ_TEXTS'),
    Text::_('COM_LANG4DEV_PRJ_TEXTS_DESC') . '                        ',
//    array('icon-forward', 'icon-list'),
    array('icon-code', 'icon-list'),
    'viewPrjTexts'
);

// translate
$cmdButtons[] = new cmdButton(
    Route::_('index.php?option=com_lang4dev&view=translate'),
    Text::_('COM_LANG4DEV_TRANSLATE'),
    Text::_('COM_LANG4DEV_TRANSLATE_DESC') . '                        ',
    array('icon-forward', 'icon-flag'),
    'viewTranslate'
);

/** ToDo: all in one joomla (german) translation
 * // translations
 * $cmdButtons[] = new cmdButton(
 * Route::_('index.php?option=com_lang4dev&view=translations'),
 * Text::_('COM_LANG4DEV_TRANSLATIONS'),
 * Text::_('COM_LANG4DEV_TRANSLATIONS_DESC') . '                        ',
 * array('icon-flag', 'icon-book'),
 * 'viewTranslations'
 * );
 * /**/

// translations
$cmdButtons[] = new cmdButton(
    Route::_('index.php?option=com_lang4dev&view=maintenance'),
    Text::_('COM_LANG4DEV_MAINTENANCE'),
    Text::_('COM_LANG4DEV_MAINTENANCE_DESC') . '                        ',
    array('icon-cog', 'icon-equalizer'),
    'viewTranslations'
);

function DisplayButton($button)
{
    global $imageClass;
    $imageClass = 'fas fa-list';
    $imageClass = 'fas fa-image';

    // <button type="button" class="btn btn-primary">Primary</button>
    ?>
	<div class="rsg2-icon-button-container" style="border: #0a53be;">
		<button type="button" class="btn ">

			<a href="<?php
            echo $button->link; ?>" class="<?php
            echo $button->classButton; ?>">
				<figure class="lang4dev-icon">
                    <?php
                    foreach ($button->classIcons as $Idx => $imageClass) {
                        echo '            <span class="' . $imageClass . ' icoMoon icoMoon0' . $Idx . '" style="font-size:30px;"></span>'; // style="font-size:30px;"
                    }
                    ?>
					<figcaption class="rsg2-text">
						<div class="maint-title"><strong><?php
                                echo $button->textTitle; ?></strong></div>
						<div class="maint-text"><?php
                            echo $button->textInfo; ?></div>
					</figcaption>
				</figure>
			</a>

		</button>
	</div>
    <?php
}

function DisplayControlButtons($cmdButtons)
{
    foreach ($cmdButtons as $Button) {
        DisplayButton($Button);
    }
}

function renderProjectSelection($form)
{
    ?>
	<br>
	<div class="d-flex flex-row py-0 my-0 justify-content-between">
		<div class="mx-2 py-0 flex-fill ">
            <?php
            echo $form->renderField('selectProject'); ?>
		</div>

		<div class="mx-2 py-0 px-2 flex-fill ">
            <?php
            echo $form->renderField('selectSubproject'); ?>
		</div>

	</div>
    <?php

    return;
}

function renderLangIdTexts($form)
{
    // mx-2 py-0, mx-2 py-0 px-2
    ?>
	<br>
	<div class="d-flex flex-row py-0 my-0 justify-content-between">
		<div class="mx-2 py-0 flex-fill ">
            <?php
            echo $form->renderField('selectSourceLangId'); ?>
		</div>

		<div class="mx-2 py-0 flex-fill ">
            <?php
            echo $form->renderField('selectTargetLangId'); ?>
		</div>

	</div>
    <?php

    return;
}

?>

	<form action="<?php
    echo Route::_('index.php?option=com_lang4dev'); ?>"
	      method="post" name="adminForm" id="adminForm" class="form-validate">

		<div class="main-horizontal-bar" style="display: flex; flex-direction: row; justify-content: flex-start;">
            <?php
            //--- Logo -----------------------------
            DisplayLogo();
            ?>

			<div class="main-vertical-stack"
			     style="display: flex; flex-direction: column; justify-content: space-between">
				<div class="vertical-header">
					<h2><?php
                        echo Text::_('COM_LANG4DEV_LANG4DEV'); ?></h2>
					<strong><?php
                        echo Text::_('COM_LANG4DEV_LANG4DEV_DESC'); ?></strong>
				</div>
				<div class="horizontal-buttons"
				     style="display: flex; flex-direction: row; align-content: space-between; ">
                    <?php
                    //--- Control buttons ------------------
                    DisplayControlButtons($cmdButtons);
                    ?>
				</div>
				<div class="vertical-empty-part3" style="min-height: 20px;">
				</div>
			</div>
		</div>

		<br>
		<h2><?php
            echo '&nbsp;&nbsp;' ?>Lang4Dev<?php
            echo '&nbsp;V' . $this->extensionVersion; ?></h2>

        <?php
        renderProjectSelection($this->form); ?>
        <?php
        renderLangIdTexts($this->form); ?>

		<input type="hidden" name="task" value=""/>
        <?php
        echo HTMLHelper::_('form.token'); ?>

	</form>

<?php

//--- Logo -----------------------------

/**
 * Just displays the logo as svg
 *
 * @since __BUMP_VERSION__
 */
function DisplayLogo()
{
    echo '    <div class="lang4dev_logo">';
//	             echo HTMLHelper::_('image', 'com_lang4dev/RSG2_logo.big.png', Text::_('COM_LANG4DEV_MAIN_LOGO_ALT_TEXT'), null, true);
    echo HTMLHelper::_(
        'image',
        'com_lang4dev/Lang4Dev_Logo.svg',
        Text::_('COM_LANG4DEV_MAIN_LOGO_ALT_TEXT'),
        null,
        true
    );
    echo '     </div>';
//	echo '<p class="test">';
//	echo '</p>

    echo '<div class="clearfix"></div>';
}

//--- Control buttons ------------------

