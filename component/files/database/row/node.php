<?php
/**
 * Nooku Framework - http://www.nooku.org
 *
 * @copyright	Copyright (C) 2011 - 2013 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		git://git.assembla.com/nooku-framework.git for the canonical source repository
 */

namespace Nooku\Component\Files;

use Nooku\Library;

/**
 * Node Database Row
 *
 * @author  Ercan Ozkaya <http://nooku.assembla.com/profile/ercanozkaya>
 * @package Nooku\Component\Files
 */
class DatabaseRowNode extends Library\DatabaseRowAbstract
{
    /**
     * The file adapter
     *
     * @var FilesAdapterLocalInterface
     */
    protected $_adapter;

    /**
     * The file container
     *
     * @var string
     */
    protected $_container;

    /**
     * Chain of command object
     *
     * @var Library\CommandChain
     */
    protected $_command_chain;

	public function __construct(Library\ObjectConfig $config)
	{
		parent::__construct($config);

		$this->mixin('lib:command.mixin', $config);

		if ($config->validator !== false)
		{
			if ($config->validator === true) {
				$config->validator = 'com:files.database.validator.'.$this->getIdentifier()->name;
			}

			$this->getCommandChain()->enqueue($this->getObject($config->validator));
		}
	}

	protected function _initialize(Library\ObjectConfig $config)
	{
		$config->append(array(
			'command_chain'     => 'lib:command.chain',
			'dispatch_events'   => false,
			'event_dispatcher'  => 'event.dispatcher',
			'enable_callbacks'  => true,
			'validator' 		=> true
		));

		parent::_initialize($config);
	}

	public function copy()
	{
		$context = $this->getContext();
		$context->result = false;

		if ($this->getCommandChain()->run('before.copy', $context, false) !== false)
		{
			$context->result = $this->_adapter->copy($this->destination_fullpath);
			$this->getCommandChain()->run('after.copy', $context);
        }

		if ($context->result !== false)
		{
            if ($this->destination_folder) {
                $this->folder = $this->destination_folder;
            }
            if ($this->destination_name) {
                $this->name = $this->destination_name;
            }

            $this->setStatus($this->overwritten ? Library\Database::STATUS_UPDATED : Library\Database::STATUS_CREATED);
		}
		else $this->setStatus(Library\Database::STATUS_FAILED);

		return $context->result;
	}

	public function move()
	{
		$context = $this->getContext();
		$context->result = false;

		if ($this->getCommandChain()->run('before.move', $context, false) !== false)
		{
			$context->result = $this->_adapter->move($this->destination_fullpath);
			$this->getCommandChain()->run('after.move', $context);
        }

		if ($context->result !== false)
		{
            if ($this->destination_folder) {
                $this->folder = $this->destination_folder;
            }

            if ($this->destination_name) {
                $this->name = $this->destination_name;
            }

            $this->setStatus($this->overwritten ? Library\Database::STATUS_UPDATED : Library\Database::STATUS_CREATED);
		}
		else $this->setStatus(Library\Database::STATUS_FAILED);

		return $context->result;
	}

	public function delete()
	{
		$context = $this->getContext();
		$context->result = false;

		if ($this->getCommandChain()->run('before.delete', $context, false) !== false)
		{
			$context->result = $this->_adapter->delete();
			$this->getCommandChain()->run('after.delete', $context);
        }

		if ($context->result === false) {
			$this->setStatus(Library\Database::STATUS_FAILED);
		} else {
            $this->setStatus(Library\Database::STATUS_DELETED);
        }

		return $context->result;
	}

	public function __get($column)
	{
		if ($column == 'fullpath' && !isset($this->_data['fullpath'])) {
			return $this->getFullpath();
		}

		if ($column == 'path') {
			return trim(($this->folder ? $this->folder.'/' : '').$this->name, '/\\');
		}
		
		if ($column == 'display_name' && empty($this->_data['display_name'])) {
			return $this->name;
		}

		if ($column == 'destination_path')
		{
			$folder = !empty($this->destination_folder) ? $this->destination_folder.'/' : (!empty($this->folder) ? $this->folder.'/' : '');
			$name   = !empty($this->destination_name) ? $this->destination_name : $this->name;

			return trim($folder.$name, '/\\');
		}

		if ($column == 'destination_fullpath') {
			return $this->getContainer()->path.'/'.$this->destination_path;
		}

		if ($column == 'adapter') {
			return $this->_adapter;
		}


		return parent::__get($column);
	}

	public function __set($column, $value)
	{
		parent::__set($column, $value);

		if (in_array($column, array('container', 'folder', 'name'))) {
			$this->setAdapter();
		}
	}

    public function getContainer()
    {
        if(!isset($this->_container))
        {
            //Set the container
            $container = $this->getObject('com:files.model.containers')->slug($this->container)->getRow();

            if (!is_object($container) || $container->isNew()) {
                throw new \UnexpectedValueException('Invalid container');
            }

            $this->_container = $container;
        }

        return $this->_container;
    }

    public function getContext()
    {
        $context = new Library\Command();
        $context->setSubject($this);

        return $context;
    }

	public function setAdapter()
	{
		$type      = $this->getIdentifier()->name;
        $container = $this->getContainer();

		$this->_adapter = $container->getAdapter($type, array(
			'path' => $container->path.'/'.($this->folder ? $this->folder.'/' : '').$this->name
		));

		unset($this->_data['fullpath']);
		unset($this->_data['metadata']);

		return $this;
	}

    /**
     * Get the chain of command object
     *
     * To increase performance the a reference to the command chain is stored in object scope to prevent slower calls
     * to the CommandChain mixin.
     *
     * @return  CommandChainInterface
     */
    public function getCommandChain()
    {
        if(!$this->_command_chain instanceof Library\CommandChainInterface)
        {
            //Ask the parent the relay the call to the mixin
            $this->_command_chain = parent::getCommandChain();

            if(!$this->_command_chain instanceof Library\CommandChainInterface)
            {
                throw new \UnexpectedValueException(
                    'CommandChain: '.get_class($this->_command_chain).' does not implement CommandChainInterface'
                );
            }
        }

        return $this->_command_chain;
    }

	public function setData($data, $modified = true)
	{
		$result = parent::setData($data, $modified);

		if (isset($data['container'])) {
			$this->setAdapter();
		}

		return $result;
	}

	public function getFullpath()
	{
		return $this->_adapter->getRealPath();
	}

    public function toArray()
    {
        $data = parent::toArray();

        unset($data['_token']);
        unset($data['action']);
        unset($data['option']);
        unset($data['format']);
        unset($data['view']);

		$data['container'] = $this->getContainer()->slug;
		$data['type']      = $this->getIdentifier()->name;

        return $data;
    }

    public function isLockable()
    {
    	return false;
    }

    public function isNew()
    {
        return empty($this->name) || !$this->_adapter->exists();
    }
}
