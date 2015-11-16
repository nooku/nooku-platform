<?
/**
 * Nooku Platform - http://www.nooku.org/platform
 *
 * @copyright	Copyright (C) 2011 - 2014 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		https://github.com/nooku/nooku-platform for the canonical source repository
 */
?>

<ktml:script src="assets://pages/js/pages-list.js" />

<ul class="navigation">
    <? foreach(object('com:pages.model.menus')->sort('title')->application('site')->fetch() as $menu) : ?>
        <? $menu_pages = object('com:pages.model.pages')->fetch()->find(array('pages_menu_id' => $menu->id)) ?>
        <? if(count($menu_pages)) : ?>
            <h3><?= $menu->title ?></h3>
            <? $first = true; $last_depth = 0; ?>

            <? foreach($menu_pages as $page) : ?>
                <li>
                <? $depth = substr_count($page->path, '/') ?>
                    <a class="level<?= $depth ?>" href="<?= route($page->getLink()->getQuery().'&Itemid='.$page->id) ?>">
                        <span><?= $page->title ?></span>
                    </a>
                </li>
            <? endforeach ?>
        <? endif; ?>
    <? endforeach ?>
</ul>