<?php
/**
 * Nooku Framework - http://www.nooku.org
 *
 * @copyright	Copyright (C) 2011 - 2013 Timble CVBA and Contributors. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		git://git.assembla.com/nooku-framework.git
 */

namespace Nooku\Component\Users;

use Nooku\Library;

/**
 * User Controller Toolbar
 *
 * @author  Gergo Erdosi <http://nooku.assembla.com/profile/gergoerdosi>
 * @package Nooku\Component\Users
 */
class ControllerToolbarUser extends Library\ControllerToolbarModel
{
    public function onAfterControllerBrowse(Library\Event $event)
    {
        parent::onAfterControllerBrowse($event);
        
        $this->addSeparator();
		$this->addEnable();
		$this->addDisable();
		$this->addSeparator();
    }
}