<?php

\defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;

?>
<form>

    <?php
    echo 'default.php: ' . realpath(dirname(__FILE__));
    ?>

    <input type="hidden" name="task" value="" />
    <?php echo HTMLHelper::_('form.token'); ?>

</form>

