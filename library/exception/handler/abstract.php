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
 * Exception Handler
 *
 * @author  Johan Janssens <http://nooku.assembla.com/profile/johanjanssens>
 * @package Nooku\Library\Exception
 */
class ExceptionHandlerAbstract extends Object implements ExceptionHandlerInterface
{
    /**
     * The exception callbacks
     *
     * @var array
     */
    private $__handlers = array();

    /**
     * The error level.
     *
     * @var int
     */
    protected $_error_level;

    /**
     * Exception types
     *
     * @var int
     */
    protected $_exception_type;

    /**
     * Constructor.
     *
     * @param ObjectConfig $config  An optional ObjectConfig object with configuration options
     */
    public function __construct(ObjectConfig $config)
    {
        parent::__construct($config);

        //Set the error level
        $this->setErrorLevel($config->error_level);

        //Add handlers
        foreach($config->exception_handlers as $handler) {
            $this->addHandler($handler);
        }

        if($config->exception_type) {
            $this->enable($config->exception_type);
        }
    }

    /**
     * Initializes the default configuration for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param  ObjectConfig $config An optional ObjectConfig object with configuration options.
     * @return void
     */
    protected function _initialize(ObjectConfig $config)
    {
        $config->append(array(
            'exception_handlers' => array(),
            'exception_type'     => self::TYPE_ALL,
            'exception_level'    => self::ERROR_REPORTING,
        ));

        parent::_initialize($config);
    }

    /**
     * Enable exception handling
     *
     * @param integer $type The type of exceptions to enable
     * @return ExceptionHandler
     */
    public function enable($type = self::TYPE_ALL)
    {
        if($type && self::TYPE_EXCEPTION && !($this->_exception_type & self::TYPE_EXCEPTION))
        {
            set_exception_handler(array($this, 'handleException'));
            $this->_exception_type |= self::TYPE_EXCEPTION;
        }

        if($type && self::TYPE_ERROR && !($this->_exception_type & self::TYPE_ERROR))
        {
            set_error_handler(array($this, '_handleError'));
            $this->_exception_type |= self::TYPE_ERROR;
        }

        if($type && self::TYPE_FATAL_ERROR && !($this->_exception_type & self::TYPE_FATAL_ERROR)) {
            register_shutdown_function(array($this, '_handleFatalError'));
        }

        return $this;
    }

    /**
     * Disable exception handling
     *
     * @param integer $type The type of exceptions to disable
     * @return ExceptionHandler
     */
    public function disable($type = self::TYPE_ALL)
    {
        if(($type & self::TYPE_EXCEPTION) && ($this->_exception_type & self::TYPE_EXCEPTION))
        {
            restore_exception_handler();
            $this->_exception_type ^= self::TYPE_EXCEPTION;
        }

        if(($type & self::TYPE_ERROR) && ($this->_exception_type & self::TYPE_ERROR))
        {
            restore_error_handler();
            $this->_exception_type ^= self::TYPE_ERROR;
        }

        if(($type & self::TYPE_FATAL_ERROR) && ($this->_exception_type & self::TYPE_FATAL_ERROR))
        {
            //Cannot unregister shutdown functions. Check in handler to see if it's enabled.
            $this->_exception_type ^= self::TYPE_FATAL_ERROR;
        }

        return $this;
    }

    /**
     * Add an exception handler
     *
     * @param  callable $callback
     * @param  bool $prepend If true, the handler will be prepended instead of appended.
     * @throws \InvalidArgumentException If the callback is not a callable
     * @return ExceptionHandler
     */
    public function addHandler($callback, $prepend = false )
    {
        if (!is_callable($callback))
        {
            throw new \InvalidArgumentException(
                'The handler must be a callable, "'.gettype($callback).'" given.'
            );
        }

        if($prepend) {
            array_unshift($this->__handlers, $callback);
        } else {
            array_push($this->__handlers, $callback);
        }

        return $this;
    }

    /**
     * Remove an exception handler
     *
     * @param  callable $callback
     * @throws \InvalidArgumentException If the callback is not a callable
     * @return ExceptionHandler
     */
    public function removeHandler($callback)
    {
        if (!is_callable($callback))
        {
            throw new \InvalidArgumentException(
                'The handler must be a callable, "'.gettype($callback).'" given.'
            );
        }

        if($key = array_search($callback, $this->__handlers)) {
            unset($this->__handlers[$key]);
        }

        return $this;
    }

    /**
     * Get the registered handlers
     *
     * @return array An array of callables
     */
    public function getHandlers()
    {
        return $this->__handlers;
    }

    /**
     * Set the error level
     *
     * @param int $level If NULL, will reset the level to the system default.
     */
    public function setErrorLevel($level)
    {
        $this->_error_level = null === $level ? error_reporting() : $level;
    }

    /**
     * Get the error level
     *
     * @return int The error level
     */
    public function getErrorLevel()
    {
        return $this->_error_level;
    }

    /**
     * Handle an exception by calling all handlers that have registered to receive it.
     *
     * If an exception handler returns TRUE the exception handling will be aborted, otherwise the next handler will be
     * called, until all handlers have gotten a change to handle the exception.
     *
     * @param   \Exception  $exception  The exception to be handled
     * @return  void
     */
    public function handleException(\Exception $exception)
    {
        try
        {
            $handled = false;

            //Try to handle the exception
            foreach($this->getHandlers() as $handler)
            {
                if(true === $handled = call_user_func($handler, $exception)) {
                    break;
                };
            }

            //Unhandled exception, rethrow it.
            if(!$handled) {
                throw $exception;
            }
        }
        catch (\Exception $e) {
            $this->_handleFatalException($e);
        }
    }

    /**
     * Check if an exception type is enabled
     *
     * @param $type
     * @return bool
     */
    public function isEnabled($type)
    {
        if($this->_exception_type & $type) {
            return true;
        }

        return false;
    }

    /**
     * Fatal Exception Handler
     *
     * @return void
     */
    protected function _handleFatalException(\Exception $exception)
    {
        $message = "<strong>Exception</strong> '%s' thrown while dispatching error: %s in <strong>%s</strong> on line <strong>%s</strong> %s";
        $message = sprintf($message,
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );

        if (ini_get('display_errors')) {
            echo $message;
        }

        if (ini_get('log_errors')) {
            error_log($message);
        }

        exit(0);
    }

    /**
     * Error Handler
     *
     * Do not call this method directly. Function visibility is public because set_error_handler does not allow for
     * protected method callbacks.
     *
     * @param int    $level      The level of the error raised
     * @param string $message    The error message
     * @param string $file       The filename that the error was raised in
     * @param int    $line       The line number the error was raised at
     * @param array  $context    An array that points to the active symbol table at the point the error occurred
     * @return bool
     */
    public function _handleError($level, $message, $file, $line, $context = null)
    {
        if($this->isEnabled(self::TYPE_ERROR))
        {
            $error_level = $this->getErrorLevel();

            if (0 !== $level)
            {
                if (error_reporting() & $level && $error_level & $level)
                {
                    $exception = new ExceptionError($message, HttpResponse::INTERNAL_SERVER_ERROR, $level, $file, $line);
                    $this->handleException($exception);
                }

                //Let the normal error flow continue
                return false;
            }
        }

        return false;
    }

    /**
     * Fatal Error Handler
     *
     * Do not call this method directly. Function visibility is public because register_shutdown_function does not
     * allow for protected method callbacks.
     *
     * @return bool
     */
    public function _handleFatalError()
    {
        if($this->isEnabled(self::TYPE_FATAL_ERROR))
        {
            $error_level = $this->getErrorLevel();

            $error = error_get_last();
            $level = $error['type'];

            if (error_reporting() & $level && $error_level & $level)
            {
                $exception = new ExceptionError($error['message'], HttpResponse::INTERNAL_SERVER_ERROR, $level, $error['file'], $error['line']);
                $this->handleException($exception);
            }
        }

        return true;
    }
}