<?php
namespace Flagbit\FACTFinder\Model;

use Magento\Framework\App\ObjectManager;
use Psr\Log\LoggerInterface;

class Logger implements \FACTFinder\Util\LoggerInterface
{
    /**
     * @var LoggerInterface
     */
    protected static $_logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        self::$_logger = $logger;
    }

    /**
     * Returns a new logger with the given name.
     *
     * @param string $name Name of the logger. This should be the fully
     *                     qualified name of the class using this instance,
     *                     so that different sub-namespaces can be configured
     *                     differently. Note that in the configuration file, the
     *                     loggers need to be qualified with periods instead of
     *                     backslashes.
     *
     * @return LoggerInterface
     */
    public static function getLogger($name)
    {
        if (self::$_logger === null) {
            $objectManager = ObjectManager::getInstance();
            self::$_logger = $objectManager->get('\Psr\Log\LoggerInterface');
        }

        return self::$_logger;
    }

    /**
     * Log message with prefix TRACE
     *
     * @param mixed $message
     * @param array $context
     *
     * @return bool Whether the record has been processed
     */
    public function trace($message, array $context = array())
    {
        return $this->getLogger(__CLASS__)->info($message, $context);
    }


    /**
     * Log message with prefix TRACE
     *
     * @param mixed $message
     * @param array $context
     *
     * @return bool Whether the record has been processed
     */
    public function debug($message, array $context = array())
    {
        return $this->getLogger(__CLASS__)->debug($message, $context);
    }


    /**
     * Log message with prefix INFO
     *
     * @param mixed $message
     * @param array $context
     *
     * @return bool Whether the record has been processed
     */
    public function info($message, array $context = array())
    {
        return $this->getLogger(__CLASS__)->info($message, $context);
    }


    /**
     * Log message with prefix WARNING
     *
     * @param mixed $message
     * @param array $context
     *
     * @return bool Whether the record has been processed
     */
    public function warn($message, array $context = array())
    {
        return $this->getLogger(__CLASS__)->warning($message, $context);
    }


    /**
     * Log message with prefix ERROR
     *
     * @param mixed $message
     * @param array $context
     *
     * @return bool Whether the record has been processed
     */
    public function error($message, array $context = array())
    {
        return $this->getLogger(__CLASS__)->error($message, $context);
    }


    /**
     * Log message with prefix FATAL ERROR
     *
     * @param mixed $message
     * @param array $context
     *
     * @return bool Whether the record has been processed
     */
    public function fatal($message, array $context = array())
    {
        return $this->getLogger(__CLASS__)->emergency($message, $context);
    }
}