<?php
/**
 * @package         LangMan4Dev
 * @subpackage      com_lang4dev
 * @author          Thomas Finnern <InsideTheMachine.de>
 * @copyright  (c)  2022-2025 Lang4dev Team
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die;

use Finnern\Component\Lang4dev\Administrator\Helper\langPathFileName;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

use Finnern\Component\Lang4dev\Administrator\Helper\langFile;

$this->document->getWebAssetManager()->useStyle('com_lang4dev.backend.translate');

?>

	?>
	<form action="<?php
    echo Route::_('index.php?option=com_lang4dev&view=translate'); ?>" method="post" name="adminForm"
	      id="item-form" class="form-validate">

<!--        --><?php
//        renderProjectSelection($this->form); ?>
<!---->
<!--        --><?php
//        renderLangIdTexts($this->form); ?>
<!---->


		<div class="project_selection_container">

            <?php renderProjectSelection($this->form); ?>
            <?php renderLangIdTexts($this->form); ?>
		</div>




		<?php
        renderCheckAll($this->form); ?>

        <?php

        $subProjects = $this->project->subProjects;

        foreach ($subProjects as $subProject) {
            $title = $subProject->prjId . ': ' . $subProject->getPrjTypeText();
            // style="width: 18rem; bg-light .bg-transparent bg-secondary text-white

            ?>
			<div class="card ">
				<h2 class="card-header " style="background-color: #ced4da;">
                    <?php
                    echo $title; ?>
				</h2>

				<div class="card-body">
                    <?php

                    $editIdx = 0;

                    //--- all main files ----------------------------------

                    $mainLangFiles = $subProject->getLangFilesData($this->mainLangId);

                    foreach ($mainLangFiles as $mainLangFile) {
                        $subPrjPath = $mainLangFile->getlangSubPrjPathFileName();

                        //--- render main file first -------------------------------------------------------

                        renderLangFileEditText(
                            $this->mainLangId,
                            $mainLangFile,
                            $subPrjPath,
                            true,
                            $this->isEditAndSaveMainTranslationFile,
                            $editIdx
                        );
                        $editIdx++;

                        //--- all matching translation lang files ----------------------------------

                        $mainLangFileName = $mainLangFile->getlangBaseFileName();

                        foreach ($subProject->getLangIds() as $langId) {
                            // main is already rendered
                            if ($langId != $this->mainLangId) {
                                // matches translation or show all
                                if ($langId == $this->transLangId || $this->isShowTranslationOfAllIds) {
                                    $transLangFiles = $subProject->getLangFilesData($langId);

                                    //--- find matching name with actual lang ID -------------------

                                    foreach ($transLangFiles as $transLangFile) {
                                        $transLangFileName = $transLangFile->getlangBaseFileName();

                                        if ($transLangFileName == $mainLangFileName) {
                                            $subPrjPath = $transLangFile->getlangSubPrjPathFileName();

                                            //--- render translation files -------------------------------------------------------

                                            renderLangFileEditText(
                                                $langId,
                                                $transLangFile,
                                                $subPrjPath,
                                                false,
                                                $this->isEditAndSaveMainTranslationFile,
                                                $editIdx
                                            );
                                            $editIdx++;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    renderMainfileList($mainLangFiles);

                    ?>
				</div>

			</div>
            <?php
        }

        ?>

        <?php

        if ($this->isDebugBackend)
        {
            renderDebug($this->project);
        }

        ?>

		<input type="hidden" name="task" value=""/>
        <?php
        echo HTMLHelper::_('form.token'); ?>
	</form>

<?php

function renderProjectSelection($form)
{
    ?>
	<div class='project_selection'>
		<div class='project_selection_setting'>
            <?php echo $form->renderField('selectProject'); ?>
		</div>
		<div class='project_selection_setting'>
            <?php echo $form->renderField('selectSubproject'); ?>
		</div>
	</div>
    <?php

    return;
}

function renderLangIdTexts($form)
{
    // mx-2 py-0, mx-2 py-0 px-2
    ?>
	<div class='project_selection'>
		<div class='project_selection_setting'>
            <?php echo $form->renderField('selectSourceLangId'); ?>
		</div>
		<div class='project_selection_setting'>
            <?php echo $form->renderField('selectTargetLangId'); ?>
		</div>
	</div>
    <?php

    return;
}

function renderCheckAll($form)
{
	// ToDo own scss here
    ?>
	<div class="d-flex flex-row">
		<div class="mx-2 p-2">

			<input class="form-check-input" id="checkall-toggle" type="checkbox" name="checkall-toggle" value=""
			       title="<?php
                   echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)">
			<label for="checkall-toggle"><?php
                echo Text::_('COM_LANG4DEV_CHECK_ALL_LANG_FILE_EDITS'); ?></label>
			<br>

		</div>
	</div>
    <?php

    return;
}

function renderCheckLangEdited($subPrjActive, $idx, $checked = false)
{
    ?>
	<div class="d-flex flex-row">
		<div class="py-2">

            <?php
            // public static function id($rowNum, $recId, $checkedOut = false, $name = 'cid', $stub = 'cb', $title = '', $formId = null)
            ?>

            <?php
            echo HTMLHelper::_('grid.id', $idx, $subPrjActive, false, 'cid', 'cb', $subPrjActive); ?>

			<!--input class="form-check-input cache-entry" type="checkbox"
		       id="cb<?php
            echo $idx; ?>" name="cid[]" value="<?php
            echo $subPrjActive; ?>"-->
			<label class="form-check-label" for="cb<?php
            echo $idx; ?>">
                <?php
                echo Text::_('COM_LANG4DEV_CHECK_FOR_SAVE_OF_EDIT_FILE'); ?>
			</label>

		</div>
	</div>
    <?php

    return;
}

function renderLangFileEditText(
    $langId,
    $langFile,
    $subPrjPath,
    $isMain = false,
    $isEditAndSaveMainTranslationFile = false,
    $editIdx = 0
) {
    ?>
	<div class="card bg-light border">
		<h3 class="card-header bg-white">
            <?php echo $langId; ?> &nbsp;
			<div class="fs-4 fw-normal">
                <?php echo $subPrjPath; ?>
			</div>
		</h3>

		<div class="card-body">

			<div class="card-text">
                <?php
                // ToDo: enable edit of main language by config
                if (!$isMain || $isEditAndSaveMainTranslationFile) {
                    $subPrjActive = $langId;
                    renderCheckLangEdited($subPrjActive, $editIdx, $checked = false);
                }

                $linesArray = $langFile->translationLinesArray();
                $langText   = '';

                // ksort($linesArray);
                foreach ($linesArray as $line) {
                    // ToDo: use implode
                    $langText .= $line . '&#10;';
                    //$langText .= $line . '\n';
                }

                if ($isMain) {
                    //--- source (edit) text -------------------------------------------

                    // readonly if not deselected on config
                    $readonly = $isEditAndSaveMainTranslationFile ? '' : "readonly";
                    ?>

					<textarea id="<?php echo $langId . '_' . $editIdx . '_main'; ?>"
					          name="langsEdited[]" rows="12" class="bg-primary  text-white textarea_main"
					          style="overflow-x: scroll; min-width: 100%; overflow-wrap: normal; "
			                  <?php echo $readonly; ?>
			        ><?php echo $langText; ?></textarea>

                    <?php
                    if ($isEditAndSaveMainTranslationFile): ?>
						<input type="text" name="langPathFileNames[]"
						       value="<?php echo $langFile->getLangPathFileName(); ?>"
                               hidden
                        />
                    <?php
                    endif;
                    ?>

                    <?php
                } else {
                    //--- target edit text -------------------------------------------
                    ?>
					<textarea id="<?php echo $langId . '_' . $editIdx . '_target'; ?>"
					          name="langsText[]" rows="12" class="bg-white text-dark textarea_target"
					          style="overflow-x: scroll; min-width: 100%; "
					><?php echo $langText;?></textarea>

					<input type="text" name="langPathFileNames[]"
					       value="<?php echo $langFile->getLangPathFileName();?>"
                           hidden
                    />

                    <?php
                }
                ?>
			</div>
		</div>
	</div>


    <?php

    return;
}

function renderMainfileList($mainLangFiles = [])
{
    foreach ($mainLangFiles as $mainLangFile) {
        $langPathFileName = str_replace('\\', '/', $mainLangFile->getlangPathFileName());
        echo '<input type="hidden" name="mainLangFiles[]" value="' . $langPathFileName . '">';
    }
}

/*-----------------------------------------------------------------
Debug lines
-----------------------------------------------------------------*/

//--- show found file list -----------------------------------------
function renderDebug($project) {

    //--- all projects filenames by lang ID  -----------------------------------------

    $langFileSetsPrjs = $project->LangFileNamesCollection();

?>
    <hr>
    <br>
    <!--div class="row g-2"-->
    <div class="row">
        <h3>Lang file list</h3><br>

        <div class="d-flex align-items-center">
            <div class="p-2 flex-grow-1">

                <?php
                foreach ($langFileSetsPrjs as $prjId => $langFileSets) {
                ?>
                    [<?php echo $prjId; ?>]<br>

                    <?php
                    foreach ($langFileSets as $langId => $langFiles) {
                    ?>
                        &nbsp;&nbsp;&nbsp;[<?php echo $langId; ?>]<br>

                        <?php
                        foreach ($langFiles as $langFile) {
                        ?>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;<?php echo $langFile; ?><br>

        				<?php
                        }
                    }
                }
				?>

            </div>
        </div>
    </div>
    <br>

	<?php
	return;
}
/*-----------------------------------------------------------------
HTML code
-----------------------------------------------------------------*/

