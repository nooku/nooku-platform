<?php
/**
 * Nooku Framework - http://www.nooku.org
 *
 * @copyright	Copyright (C) 2011 - 2013 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		git://git.assembla.com/nooku-framework.git for the canonical source repository
 */

use Nooku\Library;
use Nooku\Component\Users;

/**
 * User Controller
 *
 * @author  Gergo Erdosi <http://nooku.assembla.com/profile/gergoerdosi>
 * @package Component\Users
 */
class UsersControllerUser extends Users\ControllerUser
{ 
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->addCommandCallback('after.add' , '_resetPassword');
        $this->addCommandCallback('after.edit', '_resetPassword');
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(array(
            'behaviors' => array(
                'com:activities.controller.behavior.loggable' => array('title_column' => 'name'),
            )
        ));

        parent::_initialize($config);
    }

    protected function _resetPassword(Library\ControllerContextInterface $context)
    {
        $entity = $context->result;

        // Expire the user's password if a password reset was requested.
        if ($entity->getStatus() !== Library\Database::STATUS_FAILED)
        {
            if($context->request->data->get('password_reset', 'boolean')) {
                $entity->getPassword()->expire();
            }
        }
    }

    protected function _actionDelete(Library\ControllerContextInterface $context)
    {
        $entity = parent::_actionDelete($context);

        $this->getObject('com:users.model.sessions')
            ->email($entity->email)
            ->getRowset()
            ->delete();

        return $entity;
    }
}
