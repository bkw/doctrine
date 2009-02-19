<?php

namespace Doctrine\Common;

class DoctrineException extends \Exception
{
    private $_innerException;
    private static $_messages = array();

    public function __construct($message = "", Exception $innerException = null)
    {
        parent::__construct($message);
        $this->_innerException = $innerException;
    }
    
    public function getInnerException()
    {
        return $this->_innerException;
    }
    
    public static function notImplemented($method, $class)
    {
        return new self("The method '$method' is not implemented in class '$class'.");
    }

    public static function __callStatic($method, $arguments)
    {
        $end = end($arguments);
        if ($end instanceof Exception) {
            $this->_innerException = $end;
            unset($arguments[count($arguments) - 1]);
        }

        if (isset(self::$_messages[$method])) {
            $message = sprintf(self::$_messages[$method], $arguments);
        } else {
            $message  = strtolower(preg_replace('~(?<=\\w)([A-Z])~', '_$1', $method));
            $message  = ucfirst(str_replace('_', ' ', $message));
            $args = array();
            foreach ($arguments as $argument) {
                $args[] = var_export($argument, true);
            }
            $message .= ' (' . implode(', ', $args) . ')';
        }
        return new self($message);
    }
}