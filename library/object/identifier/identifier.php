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
 * Object Identifier
 *
 * Wraps identifiers of the form type://package.[.path].name in an object, providing public accessors and methods for
 * derived formats.
 *
 * @author  Johan Janssens <http://nooku.assembla.com/profile/johanjanssens>
 * @package Nooku\Library\Object
 */
class ObjectIdentifier implements ObjectIdentifierInterface
{
    /**
     * The object identifier
     *
     * @var string
     */
    protected $_identifier = '';

    /**
     * The identifier type [com|lib]
     *
     * @var string
     */
    protected $_type = '';

    /**
     * The identifier domain
     *
     * @var string
     */
    protected $_domain = '';

    /**
     * The identifier package
     *
     * @var string
     */
    protected $_package = '';

    /**
     * The identifier path
     *
     * @var array
     */
    protected $_path = array();

    /**
     * The identifier object name
     *
     * @var string
     */
    protected $_name = '';

    /**
     * The identifier class
     *
     * @var string
     */
    protected $_class = '';

    /**
     * The object config
     *
     * @var ObjectConfig
     */
    protected $_config = null;

    /**
     * The object mixins
     *
     * @var array
     */
    protected $_mixins = array();

    /**
     * The object decorators
     *
     * @var array
     */
    protected $_decorators = array();

    /**
     * Constructor
     *
     * If the identifier does not have a type set default type to 'lib'. Eg, event.dispatcher is the same as
     * lib:event.dispatcher.
     *
     * @param   string|array $identifier Identifier string or array in type://domain/package.[.path].name format
     * @throws  ObjectExceptionInvalidIdentifier If the identifier cannot be parsed
     */
    public function __construct($identifier)
    {
        //Get the parts
        if(!is_array($identifier))
        {
            //Get the parts
            if(false === $parts = parse_url($identifier)) {
                throw new ObjectExceptionInvalidIdentifier('Identifier cannot be parsed : '.$identifier);
            }

            // Set the type
            $this->type = isset($parts['scheme']) ? $parts['scheme'] : 'lib';

            //Set the domain
            if(isset($parts['host'])) {
                $this->domain = $parts['host'];
            }

            // Set the path
            $this->_path = trim($parts['path'], '/');
            $this->_path = explode('.', $this->_path);

            // Set the extension (first part)
            $this->_package = array_shift($this->_path);

            // Set the name (last part)
            if(count($this->_path)) {
                $this->_name = array_pop($this->_path);
            }
        }
        else
        {
            $parts = $identifier;

            foreach ($parts as $key => $value) {
                $this->$key = $value;
            }

            $identifier = $this->toString();
        }

        //Cache the identifier to increase performance
        $this->_identifier = $identifier;
    }

    /**
     * Serialize the identifier
     *
     * @return string 	The serialised identifier
     */
    public function serialize()
    {
        $data = $this->toArray();
        return serialize($data);
    }

    /**
     * Unserialize the identifier
     *
     * @return string $data	The serialised identifier
     */
    public function unserialize($data)
    {
        $data = unserialize($data);

        foreach($data as $property => $value) {
            $this->{'_'.$property} = $value;
        }
    }

    /**
     * Get the identifier type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Get the identifier domain
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->_domain;
    }

    /**
     * Get the identifier package
     *
     * @return string
     */
    public function getPackage()
    {
        return $this->_package;
    }

    /**
     * Get the identifier package
     *
     * @return array
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * Get the identifier package
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Get the identifier class name
     *
     * @return string
     */
    public function getClass()
    {
        return $this->_class;
    }

    /**
     * Set the identifier class name
     *
     * @param  string $class
     * @return ObjectIdentifier
     */
    public function setClass($class)
    {
        $this->_class = $class;
        return $this;
    }

    /**
     * Get the config
     *
     * This function will lazy create a config object is one does not exist yet.
     *
     * @return ObjectConfig
     */
    public function getConfig()
    {
        if(!isset($this->_config)) {
            $this->_config = new ObjectConfig();
        }

        return $this->_config;
    }

    /**
     * Set the config
     *
     * @param   array    $data   A ObjectConfig object or a an array of configuration options
     * @param   boolean  $merge  If TRUE the data in $config will be merged instead of replaced. Default TRUE.
     * @return  ObjectIdentifierInterface
     */
    public function setConfig($data, $merge = true)
    {
        $config = $this->getConfig();

        if($merge) {
            $config->append($data);
        } else {
            $this->_config = new ObjectConfig($data);
        }

        return $this;
    }

    /**
     * Add a mixin
     *
     *  @param mixed $decorator An object implementing ObjectMixinInterface, an ObjectIdentifier or an identifier string
     * @param array $config     An array of configuration options
     * @return ObjectIdentifierInterface
     * @see Object::mixin()
     */
    public function addMixin($mixin, $config = array())
    {
        if ($mixin instanceof ObjectMixinInterface || $mixin instanceof ObjectIdentifier) {
            $this->_mixins[] = $mixin;
        } else {
            $this->_mixins[$mixin] = $config;
        }

        return $this;
    }

    /**
     * Get the mixin registry
     *
     * @return array
     */
    public function getMixins()
    {
        return $this->_mixins;
    }

    /**
     * Add a decorator
     *
     * @param mixed $decorator An object implementing ObjectDecoratorInterface, an ObjectIdentifier or an identifier string
     * @param array $config    An array of configuration options
     * @return ObjectIdentifierInterface
     * @see Object::decorate()
     */
    public function addDecorator($decorator, $config = array())
    {
        if ($decorator instanceof ObjectDecoratorInterface || $decorator instanceof ObjectIdentifier) {
            $this->_decorators[] = $decorator;
        } else {
            $this->_decorators[$decorator] = $config;
        }

        return $this;
    }

    /**
     * Get the decorators
     *
     *  @return array
     */
    public function getDecorators()
    {
        return $this->_decorators;
    }

    /**
     * Check if the object is a multiton
     *
     * @return boolean Returns TRUE if the object is a singleton, FALSE otherwise.
     */
    public function isMultiton()
    {
        return array_key_exists(__NAMESPACE__.'\ObjectMultiton', class_implements($this->class));
    }

    /**
     * Check if the object is a singleton
     *
     * @return boolean Returns TRUE if the object is a singleton, FALSE otherwise.
     */
    public function isSingleton()
    {
        return array_key_exists(__NAMESPACE__.'\ObjectSingleton', class_implements($this->class));
    }

    /**
     * Formats the identifier as a type://package.[.path].name string
     *
     * @return string
     */
    public function toString()
    {
        if($this->_identifier == '')
        {
            if(!empty($this->_type)) {
                $this->_identifier .= $this->_type;
            }

            if(!empty($this->_domain)) {
                $this->_identifier .= '://'.$this->_domain.'/';
            } else {
                $this->_identifier .= ':';
            }

            if(!empty($this->_package)) {
                $this->_identifier .= $this->_package;
            }

            if(count($this->_path)) {
                $this->_identifier .= '.'.implode('.',$this->_path);
            }

            if(!empty($this->_name)) {
                $this->_identifier .= '.'.$this->_name;
            }
        }

        return $this->_identifier;
    }

    /**
     * Formats the identifier as an associative array
     *
     * @return array
     */
    public function toArray()
    {
        $data = array(
            'domain'      => $this->_domain,
            'type'		  => $this->_type,
            'package'	  => $this->_package,
            'path'		  => $this->_path,
            'name'		  => $this->_name,
            'class'       => $this->_class,
            'identifier'  => $this->_identifier,
        );

        return $data;
    }

    /**
     * Implements the virtual class properties
     *
     * This functions creates a string representation of the identifier.
     *
     * @param   string  $property The virtual property to set.
     * @param   string  $value    Set the virtual property to this value.
     * @throws  ObjectExceptionInvalidIdentifier If the type is unknown
     */
    public function __set($property, $value)
    {
        if(isset($this->{'_'.$property}))
        {
            //Force the path to an array
            if($property == 'path')
            {
                if(is_scalar($value)) {
                     $value = (array) $value;
                }
            }

            //Set the properties
            $this->{'_'.$property} = $value;

            //Reset the properties
            $this->_identifier = '';
            $this->_class      = '';
        }
    }

    /**
     * Implements access to virtual properties by reference so that it appears to be a public property.
     *
     * @param   string  $property The virtual property to return.
     * @return  array   The value of the virtual property.
     */
    public function &__get($property)
    {
        $result = null;
        if(isset($this->{'_'.$property})) {
            $result =& $this->{'_'.$property};
        }

        return $result;
    }

    /**
     * This function checks if a virtual property is set.
     *
     * @param   string  $property The virtual property to return.
     * @return  boolean True if it exists otherwise false.
     */
    public function __isset($property)
    {
        $name = ltrim($property, '_');
        $vars = get_object_vars($this);

        return isset($vars['_'.$name]);
    }

    /**
     * Allow casting of the identfiier to a string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Prevent creating clones of this class
     *
     * @throws Exception
     */
    final private function __clone()
    {
        throw new \Exception("An object identifier is an immutable object and cannot be cloned.");
    }
}