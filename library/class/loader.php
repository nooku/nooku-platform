<?php
/**
 * Nooku Platform - http://www.nooku.org/platform
 *
 * @copyright	Copyright (C) 2007 - 2014 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		git://git.assembla.com/nooku-framework.git for the canonical source repository
 */

namespace Nooku\Library;

require_once dirname(__FILE__).'/interface.php';
require_once dirname(__FILE__).'/locator/interface.php';
require_once dirname(__FILE__).'/locator/abstract.php';
require_once dirname(__FILE__).'/locator/library.php';
require_once dirname(__FILE__).'/registry/interface.php';
require_once dirname(__FILE__).'/registry/registry.php';
require_once dirname(__FILE__).'/registry/cache.php';

/**
 * Class Loader
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package Nooku\Library\Class
 */
class ClassLoader implements ClassLoaderInterface
{
    /**
     * The class registry
     *
     * @var array
     */
    private $__registry = null;

    /**
     * The class locators
     *
     * @var array
     */
    protected $_locators = array();

    /**
     * Global namespaces
     *
     * @var array
     */
    protected $_namespaces = array();

    /**
     * The active global namespace
     *
     * @var  string
     */
    protected $_namespace = 'nooku';

    /**
     * Debug
     *
     * @var boolean
     */
    protected $_debug = false;

    /**
     * Constructor
     *
     * @param array $config Array of configuration options.
     */
    final private function __construct($config = array())
    {
        //Create the class registry
        if(isset($config['cache_enabled']) && $config['cache_enabled'])
        {
            $this->__registry = new ClassRegistryCache();

            if(isset($config['cache_namespace'])) {
                $this->__registry->setNamespace($config['cache_namespace']);
            }
        }
        else $this->__registry = new ClassRegistry();

        //Set the debug mode
        if(isset($config['debug'])) {
            $this->setDebug($config['debug']);
        }

        //Register the library locator
        $this->registerLocator(new ClassLocatorLibrary($config));

        //Register the Nooku\Library namesoace
        $this->getLocator('library')->registerNamespace(__NAMESPACE__, dirname(dirname(__FILE__)));

        //Register the loader with the PHP autoloader
        $this->register();
    }

    /**
     * Clone
     *
     * Prevent creating clones of this class
     */
    final private function __clone()
    {
        throw new \Exception("An instance of ".get_called_class()." cannot be cloned.");
    }

    /**
     * Force creation of a singleton
     *
     * @param  array  $config An optional array with configuration options.
     * @return ClassLoader
     */
    final public static function getInstance($config = array())
    {
        static $instance;

        if ($instance === NULL) {
            $instance = new self($config);
        }

        return $instance;
    }

    /**
     * Registers the loader with the PHP autoloader.
     *
     * @param Boolean $prepend Whether to prepend the autoloader or not
     * @see \spl_autoload_register();
     */
    public function register($prepend = false)
    {
        spl_autoload_register(array($this, 'load'), true, $prepend);
    }

    /**
     * Unregisters the loader with the PHP autoloader.
     *
     * @see \spl_autoload_unregister();
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'load'));
    }

    /**
     * Load a class based on a class name
     *
     * @param string  $class    The class name
     * @throws \RuntimeException If debug is enabled and the class could not be found in the file.
     * @return boolean  Returns TRUE if the class could be loaded, otherwise returns FALSE.
     */
    public function load($class)
    {
        $result = false;

        //Get the path
        $path = $this->getPath( $class, $this->_namespace);

        if ($path !== false)
        {
            if (!in_array($path, get_included_files()) && file_exists($path))
            {
                require $path;

                if($this->_debug)
                {
                    if(!$this->isDeclared($class))
                    {
                        throw new \RuntimeException(sprintf(
                            'The autoloader expected class "%s" to be defined in file "%s".
                            The file was found but the class was not in it, the class name
                            or namespace probably has a typo.', $class, $path
                        ));
                    }
                }
            }
            else $result = false;
        }

        return $result;
    }

    /**
     * Get the path based on a class name
     *
     * @param string $class     The class name
     * @param string $namespace The global namespace. If NULL the active global namespace will be used.
     * @return string|boolean   Returns canonicalized absolute pathname or FALSE of the class could not be found.
     */
    public function getPath($class, $namespace = null)
    {
        $result = false;

        //Switch the namespace
        $prefix = $namespace ? $namespace : $this->_namespace;

        if(!$this->__registry->has($prefix.'-'.$class))
        {
            //Locate the class
            foreach($this->_locators as $locator)
            {
                $basepath = $this->getNamespace($namespace);
                if(false !== $result = $locator->locate($class, $basepath)) {
                    break;
                };
            }

            //Also store if the class could not be found to prevent repeated lookups.
            $this->__registry->set($prefix.'-'.$class, $result);

        } else $result = $this->__registry->get($prefix.'-'.$class);

        return $result;
    }

    /**
     * Get the path based on a class name
     *
     * @param string $class     The class name
     * @param string $path      The class path
     * @param string $namespace The global namespace. If NULL the active global namespace will be used.
     * @return void
     */
    public function setPath($class, $path, $namespace = null)
    {
        $prefix = $namespace ? $namespace : $this->_namespace;
        $this->__registry->set($prefix.'-'.$class, $path);
    }

    /**
     * Register a class locator
     *
     * @param  ClassLocatorInterface $locator
     * @param  bool $prepend If true, the locator will be prepended instead of appended.
     * @return void
     */
    public function registerLocator(ClassLocatorInterface $locator, $prepend = false )
    {
        $array = array($locator->getType() => $locator);

        if($prepend) {
            $this->_locators = $array + $this->_locators;
        } else {
            $this->_locators = $this->_locators + $array;
        }
    }

    /**
     * Get a registered class locator based on his type
     *
     * @param string $type The locator type
     * @return ClassLocatorInterface|null  Returns the object locator or NULL if it cannot be found.
     */
    public function getLocator($type)
    {
        $result = null;

        if(isset($this->_locators[$type])) {
            $result = $this->_locators[$type];
        }

        return $result;
    }

    /**
     * Register an alias for a class
     *
     * @param string  $class The original
     * @param string  $alias The alias name for the class.
     */
    public function registerAlias($class, $alias)
    {
        $alias = trim($alias);
        $class = trim($class);

        $this->__registry->alias($class, $alias);
    }

    /**
     * Get the registered alias for a class
     *
     * @param  string $class The class
     * @return array   An array of aliases
     */
    public function getAliases($class)
    {
        return array_search($class, $this->__registry->getAliases());
    }

    /**
     * Register a global namespace
     *
     * @param  string $namespace
     * @param  string $path The location of the namespace
     * @param  boolean $active Make the namespace active. Default is FALSE.
     * @return  ClassLoaderInterface
     */
    public function registerNamespace($namespace, $path, $active = false)
    {
        $this->_namespaces[$namespace] = $path;

        //Set the active namespace
        if($active) {
            $this->_namespace = $namespace;
        }

        return $this;
    }

    /**
     * Get a global namespace path
     *
     * If no namespace is passed in this method will return the active global namespace.
     *
     * @param string|null $namespace The namespace.
     * @return string|false The namespace path or FALSE if the namespace does not exist.
     */
    public function getNamespace($namespace = null)
    {
        if(!isset($namespace)) {
            $namespace = $this->_namespace;
        }

        return isset($this->_namespaces[$namespace]) ? $this->_namespaces[$namespace] : false;
    }

    /**
     * Set the active global namespace
     *
     * Method can only set a namespace that previously has been registered.
     *
     * @param string $namespace The namespace
     * @return ClassLoader
     */
    public function setNamespace($namespace)
    {
        if(isset($this->_namespaces[$namespace])) {
            $this->_namespace = $namespace;
        }

        return $this;
    }

    /**
     * Get the global namespaces
     *
     * @return array An array with namespaces as keys and path as value
     */
    public function getNamespaces()
    {
        return $this->_namespaces;
    }

    /**
     * Enable or disable class loading
     *
     * If debug is enabled the class loader will throw an exception if a file is found but does not declare the class.
     *
     * @param bool $debug True or false.
     * @return ClassLoader
     */
    public function setDebug($debug)
    {
        return $this->_debug = (bool) $debug;
    }

    /**
     * Check if the loader is running in debug mode
     *
     * @return bool
     */
    public function isDebug()
    {
        return $this->_debug;
    }

    /**
     * Tells if a class, interface or trait exists.
     *
     * @params string $class
     * @return boolean
     */
    public function isDeclared($class)
    {
        return class_exists($class, false)
            || interface_exists($class, false)
            || (function_exists('trait_exists') && trait_exists($class, false));
    }
}