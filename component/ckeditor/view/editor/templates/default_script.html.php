<?
/**
 * Nooku Framework - http://www.nooku.org
 *
 * @copyright	Copyright (C) 2011 - 2013 Timble CVBA and Contributors. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		git://git.assembla.com/nooku-framework.git
 */
?>

<script src="media://ckeditor/ckeditor/ckeditor.js" />

<script>
    jQuery( document ).ready(function() {

        CKEDITOR.replace( <?= $id ?>, {
            toolbar: '<?= $settings->options->toolbar ?>',
            language: '<?= $settings->language ?>',
            height: '<?= $settings->height ?>',
            width: '<?= $settings->width ?>',
            contentsLangDirection: '<?= $settings->directionality ?>',
            scayt_autoStartup: '<?= $settings->scayt_autoStartup ?>',
        });
    });
</script>
