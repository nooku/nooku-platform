<?
/**
 * @package     Nooku_Server
 * @subpackage  Comments
 * @copyright	Copyright (C) 2011 - 2012 Timble CVBA and Contributors. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://www.nooku.org
 */
?>


<?if(object('com:comments.controller.comment')->canAdd()):?>
    <script src="media://files/js/jquery-1.8.0.min.js" />
    <script src="media:///ckeditor/ckeditor/ckeditor.js" />
    <script type='text/javascript' language='javascript'>

        CKEDITOR.on( 'instanceCreated', function( event ) {
            var editor = event.editor;


                editor.config.toolbar = 'title';


                editor.config.removePlugins = 'codemirror,readmore,autosave,images,files';
                editor.config.allowedContent = "";
                editor.config.forcePasteAsPlainText = true;

                editor.on('blur', function(e){
                    if (e.editor.checkDirty()){
                        var id = e.editor.name.split("-");

                        var request = new Request.JSON({
                            url: '?view=comment&id='+id[1],
                            data: {
                                text: e.editor.getData(),
                                _token:'<?= object('user')->getSession()->getToken() ?>'
                            },
                            onComplete: function(response){

                            }
                        }).send();
                    }
                });

        });


        jQuery( document ).ready(function() {
            jQuery('.icon-trash').click(function(){
                var id = jQuery(this).attr('data-id');
                jQuery.ajax({
                    type: "delete",
                    url: '?view=comment&id='+id,
                    data: {
                        id: id,
                        _token:'<?= object('user')->getSession()->getToken() ?>',
                        _action: 'delete'
                    },
                    success: function(response){
                        jQuery('#comment-'+id).remove()
                    }
                });

            });
        });
    </script>
<? endif ?>

<? if(count($comments) || object('com:comments.controller.comment')->canAdd()) : ?>
<div class="comments">
    <?if(object('com:comments.controller.comment')->canAdd()):?>
        <?= include('com:comments.view.comment.form.html'); ?>
    <?endif;?>

    <? foreach($comments as $comment) : ?>

        <div class="comment" id="comment-<?=$comment->id;?>">
            <div class="comment-header">
               <span class="comment-header-author">
                    <?= $comment->created_by == object('user')->id ? translate('You') : $comment->created_by_name ?>&nbsp;<?= translate('wrote') ?>
               </span>
               <span class="comment-header-time">
                    <time datetime="<?= $comment->created_on ?>" pubdate><?= helper('date.humanize', array('date' => $comment->created_on)) ?></time>
                </span>
                <?if(object('com:comments.controller.comment')->id($comment->id)->canDelete()):?>
                    <span class="comment-header-options">
                        <i class="icon-trash" data-id="<?=$comment->id;?>"></i>
                    </span>
                <? endif;?>
            </div>

            <div class="comment-content" id="comment-<?=$comment->id;?>" contenteditable="<?= object('com:comments.controller.comment')->id($comment->id)->canEdit() == 1? "true":"false";?>" >
                <p><?=$comment->text?></p>
            </div>


        </div>
    <? endforeach ?>
</div>
<?endif;?>