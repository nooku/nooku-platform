<?php
/**
 * Nooku Framework - http://www.nooku.org
 *
 * @copyright	Copyright (C) 2007 - 2013 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		git://git.assembla.com/nooku-framework.git for the canonical source repository
 */

namespace Nooku\Library;

/**
 * Command Interface
 *
 * @author		Johan Janssens <johan@nooku.org>
 * @package     Koowa_Command
 */
interface CommandInterface extends ObjectHandlable
{
	/**
	 * Generic Command handler
	 *
	 * @param 	string 	The command name
	 * @param 	object  The command context
	 * @return	boolean
	 */
	public function execute( $name, CommandContext $context);

	/**
	 * Get the priority of the command
	 *
	 * @return	integer The command priority
	 */
  	public function getPriority();
}
