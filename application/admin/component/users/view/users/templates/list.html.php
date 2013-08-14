<?php
/**
 * Nooku Framework - http://www.nooku.org
 *
 * @copyright	Copyright (C) 2011 - 2013 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		git://git.assembla.com/nooku-framework.git for the canonical source repository
 */
?>

<table class="table">
    <thead>
    <tr>
        <th>
            <strong><?= @text( 'Name' ); ?></strong>
        </th>
        <th>
            <strong><?= @text( 'User Group' ); ?></strong>
        </th>
        <th>
            <strong><?= @text( 'Application' ); ?></strong>
        </th>
        <th>
            <strong><?= @text( 'Last Activity' ); ?></strong>
        </th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($users as $user) : ?>
    <tr>
        <td>
            <a href="<?=  @route('option=com_users&view=user&id='. $user->id); ?>" title="<?= @text( 'Edit User' ) ?>">
                <?= $user->name; ?>
            </a>
        </td>
        <td>
            <?= $user->role_name;?>
        </td>
        <td>
            <?= $user->loggedin_application; ?>
        </td>
        <td>
            <?= @helper('date.humanize', array('date' => '@'.$user->loggedin_on));?>
        </td>
    </tr>
        <?php endforeach; ?>
    </tbody>
</table>