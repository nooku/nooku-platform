<?php
/**
 * @package     Koowa_Controller
 * @subpackage 	Toolbar
 * @copyright   Copyright (C) 2007 - 2012 Johan Janssens. All rights reserved.
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.nooku.org
 */

namespace Nooku\Library;

/**
 * Controller Toolbar Command Class
 *
 * @author      Johan Janssens <johan@nooku.org>
 * @package     Koowa_Controller
 * @subpackage 	Toolbar
 */
class ControllerToolbarCommand extends ObjectConfig implements ControllerToolbarInterface
{
 	/**
     * The command name
     *
     * @var string
     */
    protected $_name;

    /**
     * The commands
     *
     * @var string|object
     */
    protected $_commands = null;

    /**
     * Toolbar command object
     *
     * @var object
     */
    protected $_parent = null;

    /**
     * Toolbar object
     *
     * @var object
     */
    protected $_toolbar = null;

    /**
     * Constructor.
     *
     * @param	string 			The command name
     * @param   array|ObjectConfig 	An associative array of configuration settings or a ObjectConfig instance.
     */
    public function __construct( $name, $config = array() )
    {
        parent::__construct($config);

        $this->append(array(
            'icon'       => 'icon-32-'.$name,
            'id'         => $name,
            'label'      => ucfirst($name),
            'disabled'   => false,
            'title'		 => '',
            'href'       => null,
            'attribs'    => array(
                'class'  => array(),
            ),
        ));

        //Create the children array
        $this->_commands = array();

        //Set the command name
        $this->_name = $name;
    }

    /**
     * Get the command name
     *
     * @return string	The command name
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Add a command
     *
     * @param   string	$command The command name
     * @param	mixed	$config  Parameters to be passed to the command
     * @return  ControllerToolbarCommand  The command that was added
     */
    public function addCommand($command, $config = array())
    {
        if (!($command instanceof  ControllerToolbarCommand)) {
            $command = $this->getCommand($command, $config);
        }

        //Set the command parent
        $command->setParent($this);

        $this->_commands[] = $command;
        return $command;
    }

    /**
     * Get a command by name
     *
     * @param string $name  The command name
     * @param array $config An optional associative array of configuration settings
     * @return mixed ControllerToolbarCommand if found, false otherwise.
     */
    public function getCommand($name, $config = array())
    {
        return $this->getToolbar()->getCommand($name, $config);
    }

    /**
     * Get the list of commands
     *
     * @return  array
     */
    public function getCommands()
    {
        return $this->_commands;
    }

    /**
     * Get a new iterator
     *
     * @return  \RecursiveArrayIterator
     */
    public function getIterator()
    {
        return new \RecursiveArrayIterator($this->getCommands());
    }

    /**
     * Returns the number of elements in the collection.
     *
     * Required by the Countable interface
     *
     * @return int
     */
    public function count()
    {
        return count($this->_commands);
    }

    /**
     * Set a configuration item
     *
     * @param  string $name
     * @param  mixed  $value
     * @return void
     */
    public function set($name, $value)
    {
        if (is_array($value)) {
            $this->_data[$name] = new ObjectConfig($value);
        } else {
            $this->_data[$name] = $value;
        }
    }

    /**
     * Get the parent node
     *
     * @return	ControllerToolbarCommand
     */
    public function getParent()
    {
        return $this->_parent;
    }

    /**
     * Set the parent command
     *
     * @param object $node The parent command
     * @return ControllerToolbarCommand
     */
    public function setParent(ControllerToolbarCommand $command )
    {
        $this->_parent = $command;
        return $this;
    }

    /**
     * Get the toolbar object
     *
     * @return ControllerToolbarInterface
     */
    public function getToolbar()
    {
        return $this->_toolbar;
    }

    /**
     * Set the parent node
     *
     * @param object $node The toolbar this command belongs too
     * @return ControllerToolbarCommand
     */
    public function setToolbar(ControllerToolbarInterface $toolbar )
    {
        $this->_toolbar = $toolbar;
        return $this;
    }

    /**
     * Add a command by it's name
     *
     * @param   string  Method name
     * @param   array   Array containing all the arguments for the original call
     * @return mixed
     * @see addCommand()
     */
    public function __call($method, $args)
    {
        $parts = StringInflector::explode($method);

        if($parts[0] == 'add' && isset($parts[1]))
        {
            $config = isset($args[0]) ? $args[0] : array();
            $command = $this->addCommand(strtolower($parts[1]), $config);
            return $command;
        }

        return parent::__call($method, $args);
    }
}