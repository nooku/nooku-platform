<?php
/**
 * Nooku Framework - http://www.nooku.org
 *
 * @copyright	Copyright (C) 2011 - 2013 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		git://git.assembla.com/nooku-framework.git for the canonical source repository
 */

use Nooku\Library;

/**
 * Contact Html View
 *
 * @author  Johan Janssens <http://nooku.assembla.com/profile/johanjanssens>
 * @package Component\Contacts
 */
class ContactsViewContactHtml extends Library\ViewHtml
{
    protected function _actionRender(Library\ViewContext $context)
    {
        //Get the contact
        $contact = $this->getModel()->getData();

        //Get the category
        $category = $this->getCategory();

        //Get the parameters of the active menu item
        $page = $this->getObject('application.pages')->getActive();

        //Set the breadcrumbs
        $pathway = $this->getObject('application')->getPathway();

        if($page->getLink()->query['view'] == 'categories' )
        {
            $pathway->addItem($category->title, $this->getTemplate()->getHelper('route')->category(array('row' => $category)));
            $pathway->addItem($contact->name, '');
        }

        if($page->getLink()->query['view'] == 'contacts' ) {
            $pathway->addItem($contact->name, '');
        }

        return parent::_actionRender($context);
    }

    public function setData(Library\ObjectConfigInterface $data)
    {
        $data->params   = $this->getObject('application')->getParams();
        $data->category = $this->getCategory();

        return parent::setData($data);
    }

    public function getCategory()
    {
        //Get the category
        $category = $this->getObject('com:contacts.model.categories')
                         ->table('contacts')
                         ->id($this->getModel()->getState()->category)
                         ->getRow();

        return $category;
    }
}