<?
/**
 * Kodekit Platform - http://www.timble.net/kodekit
 *
 * @copyright	Copyright (C) 2011 - 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		MPL v2.0 <https://www.mozilla.org/en-US/MPL/2.0>
 * @link		https://github.com/timble/kodekit-platform for the canonical source repository
 */
?>

<?= helper('behavior.kodekit'); ?>
<?= helper('behavior.keepalive') ?>
<?= helper('behavior.validator') ?>

<ktml:block prepend="actionbar">
    <ktml:toolbar type="actionbar">
</ktml:block>

<form action="" method="post" id="comment-form" class="-koowa-form">
    <?= object('com:ckeditor.controller.editor')->render(array('name' => 'text', 'toolbar' => 'basic', 'text' => $comment->text)) ?>
</form>