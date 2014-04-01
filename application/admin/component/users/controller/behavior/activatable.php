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
 * Resettable Controller Behavior
 *
 * @author  Arunas Mazeika <http://nooku.assembla.com/profile/arunasmazeika>
 * @package Component\Users
 */
class UsersControllerBehaviorActivatable extends Users\ControllerBehaviorActivatable
{
    protected function _afterAdd(Library\ControllerContextInterface $context)
    {
        $user = $context->result;

        if ($user instanceof Users\DatabaseRowUser && $user->getStatus() == Library\Database::STATUS_CREATED && $user->activation)
        {
            $translator = $this->getObject('translator');

            if (($url = $this->_getActivationUrl()))
            {
                $url = $context->request->getUrl()
                                        ->toString(Library\HttpUrl::SCHEME | Library\HttpUrl::HOST | Library\HttpUrl::PORT) . $url;

                $site = $this->getObject('application')->getCfg('sitename');

                $subject = $translator->translate('User Account Activation');
                $message = $translator->translate('User account activation E-mail',
                    array('name' => $user->name, 'site' => $site, 'url' => $url));

                if (!$user->notify(array('subject' => $subject, 'message' => $message)))
                {
                    $context->reponse->addMessage($translator->translate('Failed to send activation E-mail'), 'error');
                }
            }
            else
            {
                $context->reponse->addMessage($translator->translate('Unable to get a user account activation URL'), 'error');
            }
        }
    }

    protected function _getActivationUrl()
    {
        $url = null;

        $user = $this->getModel()->getRow();
        $page  = $this->getObject('application.pages')->find(array(
            'component' => 'users',
            'access'    => 0,
            'link'      => array(array('view' => 'user'))));

        if ($page)
        {
            $url                      = $page->getLink();
            $url->query['activation'] = $user->activation;
            $url->query['uuid']       = $user->uuid;

            // TODO: This is a frontend URL and we can't get a frontend router. To be solved.
            $this->getObject('application')->getRouter()->build($url);
        }

        return $url;
    }
}