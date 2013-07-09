<?php
/**
 * @package        Nooku_Server
 * @subpackage     Articles
 * @copyright      Copyright (C) 2009 - 2012 Timble CVBA and Contributors. (http://www.timble.net)
 * @license        GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link           http://www.nooku.org
 */
?>

<?= @helper('behavior.mootools'); ?>
<?= @helper('behavior.keepalive'); ?>

<!--
<script src="media://js/koowa.js"/>
-->

<div class="btn-toolbar">
    <?= @helper('com:base.toolbar.render', array('toolbar' => $toolbar));?>
</div>

<article <?= !$article->published ? 'class="article-unpublished"' : '' ?>>
    <div class="page-header">
        <h1 id="title" contenteditable="<?= $article->editable ? 'true':'false';?>"><?= $article->title ?></h1>
        <?= @helper('date.timestamp', array('row' => $article, 'show_modify_date' => false)); ?>
        <? if (!$article->published) : ?>
            <span class="label label-info"><?= @text('Unpublished') ?></span>
        <? endif ?>
        <? if ($article->access) : ?>
            <span class="label label-important"><?= @text('Registered') ?></span>
        <? endif ?>
    </div>

    <? if($article->thumbnail): ?>
        <img class="thumbnail" src="<?= $article->thumbnail ?>" align="right" style="margin:0 0 20px 20px;" />
    <? endif; ?>

    <? if($article->fulltext) : ?>
        <div id="introtext" class="article_introtext" contenteditable="<?= $article->editable ? 'true':'false';?>">
            <?= $article->introtext ?>
        </div>
    <? else : ?>
        <div id="introtext" contenteditable="<?= $article->editable ? 'true':'false';?>" >
            <?= $article->introtext ?>
        </div>
    <? endif ?>

    <div id="fulltext" contenteditable="<?= $article->editable?  'true':'false';?>">
        <?= $article->fulltext ?>
    </div>

    <?= @template('com:tags.view.tags.default.html') ?>
    <?= @template('com:attachments.view.attachments.default.html', array('attachments' => $attachments, 'exclude' => array($article->image))) ?>
</article>


<form method="post" action="" class="-koowa-form form-horizontal">
    <input type="hidden" name="published" value="0" />
    <input type="hidden" name="access" value="0" />

    <fieldset>
        <legend><?= @text('Publishing'); ?></legend>
        <div class="control-group">
            <label class="control-label" for="title"><?= @text('Published'); ?></label>
            <div class="controls">
                <input type="checkbox" name="published" value="1" <?= $article->published ? 'checked="checked"' : '' ?> />
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="access"><?= @text('Registered'); ?></label>
            <div class="controls">
                <input type="checkbox" name="access" value="1" <?= $article->access ? 'checked="checked"' : '' ?> />
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="publish_on"><?= @text('Publish on'); ?></label>
            <div class="controls">
                <input type="datetime-local" name="publish_on" value="<?= $article->publish_on ?>" />
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="unpublish_on"><?= @text('Unpublish on'); ?></label>
            <div class="controls">
                <input type="datetime-local" name="unpublish_on" value="<?= $article->unpublish_on ?>" />
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="categories_category_id"><?= @text('Category'); ?></label>
            <div class="controls">
                <?= @helper('com:categories.listbox.categories', array('table' => 'articles', 'name' => 'categories_category_id', 'category' => $article->categories_category_id)) ?>
            </div>
        </div>
    </fieldset>
</form>

<? if ($article->editable) : ?>
    <script src="media://application/js/jquery.js" /></script>

    <script src="media://ckeditor/ckeditor/ckeditor.js" />
        <script type='text/javascript' language='javascript'>

        CKEDITOR.on( 'instanceCreated', function( event ) {
            var editor = event.editor,
                element = editor.element;

            if ( element.is( 'h1', 'h2', 'h3' ) || element.getAttribute( 'id' ) == 'taglist' ) {
                editor.on( 'configLoaded', function() {
                    editor.config.toolbar = 'title';
                });
            }else{
                editor.on( 'configLoaded', function() {
                    editor.config.toolbar = 'standard';
                });
            }
            editor.on('blur', function (ev) {
                jQuery.post('<?=@route();?>', {
                    id: <?=$article->id;?>,
                    introtext : CKEDITOR.instances.introtext.getData(),
                    fulltext : CKEDITOR.instances.fulltext.getData(),
                    title : CKEDITOR.instances.title.getData(),
                    _token:'<?= @object('user')->getSession()->getToken() ?>'
                });
            });
        });

    </script>
<? endif;?>
