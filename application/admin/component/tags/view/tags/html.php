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
 * Tags Html View
 *
 * @author  Johan Janssens <http://nooku.assembla.com/profile/johanjanssens>
 * @package Component\Tags
 */
class TagsViewTagsHtml extends Library\ViewHtml
{
    public function setData(Library\ObjectConfigInterface $data)
	{
		//If no row exists assign an empty array
		if($this->getModel()->getState()->row) {
			$data->disabled = false;
		} else {
			$data->disabled = true;
		}
			
		return parent::setData($data);
	}
}
