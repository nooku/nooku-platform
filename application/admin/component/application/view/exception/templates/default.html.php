<?
/**
 * Nooku Framework - http://www.nooku.org
 *
 * @copyright	Copyright (C) 2011 - 2013 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		git://git.assembla.com/nooku-framework.git for the canonical source repository
 */
?>

<!DOCTYPE HTML>
<html lang="<?= $language; ?>" dir="<?= $direction; ?>">
<head>
    <link rel="stylesheet" href="media://application/stylesheets/error.css" type="text/css" />
    <title><?= @text('Error').': '.$code; ?></title>
</head>
<body>
<table width="550" align="center" class="outline">
    <tr>
        <td align="center">
            <h1><?= $code ?> - <?= @text('An error has occurred') ?></h1>
        </td>
    </tr>
    <tr>
        <td width="39%" align="center">
            <p><?= $message ?></p>
            <p>
                <? if(count($trace)) : ?>
                <?= @template('default_backtrace.html'); ?>
                <? endif; ?>
            </p>
        </td>
    </tr>
</table>
</body>
</html>