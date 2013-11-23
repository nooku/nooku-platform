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
 * Articles HTML View
 *
 * @author  Tom Janssens <http://nooku.assembla.com/profile/tomjanssens>
 * @package Component\Articles
 */
class ArticlesViewArticlesHtml extends Library\ViewHtml
{
    public function setData(Library\ObjectConfigInterface $data)
    {        
        $state = $this->getModel()->getState();
        
        // Enable sortable
        $data->sortable = $state->category && $state->sort == 'ordering' && $state->direction == 'asc';
        
        return parent::setData($data);
    }
}