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

<ktml:block prepend="actionbar">
    <ktml:toolbar type="actionbar">
</ktml:block>

<ktml:block prepend="sidebar">
    <?= import('default_sidebar.html'); ?>
</ktml:block>

<form action="" method="get" class="-koowa-grid">
    <?= import('default_scopebar.html') ?>
    <table>
        <thead>
            <tr>
                <th width="1">
                    <?= helper('grid.checkall') ?>
                </th>
                <th>
                    <?= helper('grid.sort', array('column' => 'name', 'url' => route())) ?>
                </th>
                <th width="1">
                    <?= helper('grid.sort', array('column' => 'native_name', 'title' => 'Native Name', 'url' => route())) ?>
                </th>
                <th width="1">
                    <?= helper('grid.sort', array('column' => 'iso_code', 'title' => 'ISO Code', 'url' => route())) ?>
                </th>
                <th width="1">
                    <?= translate('Default') ?>
                </th>
                <th width="1">
                    <?= helper('grid.sort', array('column' => 'slug', 'url' => route())) ?>
                </th>
                <th width="1">
                    <?= helper('grid.sort', array('column' => 'enabled', 'title' => 'Enabled', 'url' => route())) ?>
                </th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="7">
                     <?= helper('com:theme.paginator.pagination', array('url' => route())) ?>
                </td>
            </tr>
        </tfoot>
        <tbody>
            <? foreach($languages as $language) : ?>
            <tr>
                <td align="center">
                    <?= helper('grid.checkbox', array('entity' => $language)) ?>
                </td>
                <td>
                    <a href="<?= route('view=language&id='.$language->id) ?>"><?= $language->name ?></a>
                </td>
                <td>
                    <?= $language->native_name ?>
                </td>
                <td align="center">
                    <?= $language->iso_code ?>
                </td>
                <td align="center">
                    <? if($language->default): ?>
                        <i class="icon-star"></i>
                    <? endif ?>
                </td>
                <td align="center">
                    <?= $language->slug ?>
                </td>
                <td align="center">
                    <? if($language->default) : ?>
                        <?= translate('n/a') ?>
                    <? else: ?>
                        <?= helper('grid.enable', array('entity' => $language)) ?>
                    <? endif ?>
                </td>
            </tr>
            <? endforeach ?>
        </tbody>
    </table>
</form>