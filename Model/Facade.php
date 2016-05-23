<?php
namespace Flagbit\FACTFinder\Model;

use FACTFinder\Loader as FF;
use FACTFinder\Core\IConvEncodingConverter;

class Facade
{
    /**
     * @var \FACTFinder\Util\Pimple
     */
    protected $_dic;

    /**
     * @var \Flagbit\FACTFinder\Model\Logger
     */
    protected $_logger;

    /**
     * @var \Flagbit\FACTFinder\Model\Configuration
     */
    protected $_configuration;

    /**
     * Two-dimensional array of FACT-Finder adapters
     * First-dimension key corresponds to type
     * Second-dimension key corresponds to channel
     *
     * @var array of FACTFinder\Adapter\AbstractAdapter
     */
    protected $_adapters = array();

    /**
     * @param \Flagbit\FACTFinder\Model\Logger $logger
     * @param \Flagbit\FACTFinder\Model\Configuration $configuration
     * @param \FACTFinder\Util\Pimple $dic
     */
    public function __construct(
        \Flagbit\FACTFinder\Model\Logger $logger,
        \Flagbit\FACTFinder\Model\Configuration $configuration,
        \FACTFinder\Util\Pimple $dic
    )
    {
        $this->_logger = $logger;
        $this->_configuration = $configuration;
        $this->_dic = $dic;

        FF::disableCustomClasses();
        $this->_prepareDic();
    }

    protected function _prepareDic()
    {
        $logger = $this->_logger;
        $config = $this->_configuration;

        $this->_dic['loggerClass'] = function ($c) use ($logger) {
            return $logger;
        };

        $this->_dic['configuration'] = function ($c) use ($config) {
            return $config;
        };

        $this->_dic['request'] = $this->_dic->factory(function ($c) {
            return $c['requestFactory']->getRequest();
        });

        $this->_dic['requestFactory'] = function ($c) {
            return FF::getInstance(
                'Core\Server\MultiCurlRequestFactory',
                $c['loggerClass'],
                $c['configuration'],
                $c['requestParser']->getRequestParameters()
            );
        };

        $this->_dic['clientUrlBuilder'] = function ($c) {
            return FF::getInstance(
                'Core\Client\UrlBuilder',
                $c['loggerClass'],
                $c['configuration'],
                $c['requestParser'],
                $c['encodingConverter']
            );
        };

        $this->_dic['serverUrlBuilder'] = function ($c) {
            return FF::getInstance(
                'Core\Server\UrlBuilder',
                $c['loggerClass'],
                $c['configuration']
            );
        };

        $this->_dic['requestParser'] = function ($c) {
            return FF::getInstance(
                'Core\Client\RequestParser',
                $c['loggerClass'],
                $c['configuration'],
                $c['encodingConverter']
            );
        };

        $this->_dic['encodingConverter'] = function ($c) {
            if (extension_loaded('iconv')) {
                $type = 'Core\IConvEncodingConverter';
            } elseif(function_exists('utf8_encode')
                && function_exists('utf8_decode')
            ) {
                $type = 'Core\Utf8EncodingConverter';
            } else {
                throw new \Exception('No encoding conversion available.');
            }

            return FF::getInstance(
                $type,
                $c['loggerClass'],
                $c['configuration']
            );
        };
    }

    /**
     * Get search result object
     *
     * @param string|null $channel
     * @param int|null    $id
     *
     * @return \FACTFinder\Data\Result
     */
    public function getSearchResult($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("search", "getResult", $channel, $id);
    }

    /**
     * Get an object of a specified type
     *
     * @param string $type
     * @param string $objectGetter
     * @param string $channel
     * @param int    $id
     *
     * @return Object|null
     */
    protected function _getFactFinderObject($type, $objectGetter, $channel = null, $id = null)
    {
        $data = null;

        try {
            $adapter = $this->_getAdapter($type, $channel, $id);
            $data = $adapter->$objectGetter();
        } catch (\Exception $e) {
            $this->_logger->error($e);
        }

        return $data;
    }

    /**
     * Get an instance of adapter
     *
     * @param string $type
     * @param string $channel (default: null)
     * @param int    $id      (default: null)
     *
     * @return \FACTFinder\Adapter\AbstractAdapter
     */
    protected function _getAdapter($type, $channel = null, $id = null)
    {
        $hashKey = $this->_getAdapterIdentifier($type, $channel, $id);

        // get the channel after calculating the adapter identifier
        if (!$channel) {
            $channel = $this->_configuration->getChannel();
        }

        if (!isset($this->_adapters[$hashKey][$channel])) {
            $this->_adapters[$hashKey][$channel] = FF::getInstance(
                'Adapter\\' . ucfirst($type),
                $this->_dic['loggerClass'],
                $this->_dic['configuration'],
                $this->_dic['request'],
                $this->_dic['clientUrlBuilder']
            );
        }

        return $this->_adapters[$hashKey][$channel];
    }

    /**
     * Get identifying hash for each adapter based on type, channel and id
     *
     * @param string $type
     * @param string $channel (default: null)
     * @param int    $id      (default: null)
     *
     * @return string
     */
    protected function _getAdapterIdentifier($type, $channel = null, $id = null)
    {
        $args = func_get_args();

        return implode('_', $args);
    }

    /**
     * Configure search adapter
     *
     * @param array  $params
     * @param string $channel
     * @param int    $id
     *
     * @return void
     */
    public function configureSearchAdapter($params, $channel = null, $id = null)
    {
        $this->_configureAdapter($params, "search", $channel, $id);
    }

    /**
     * Configure suggest adapter
     *
     * @param array  $params
     * @param string $channel
     * @param int    $id
     *
     * @return void
     */
    public function configureSuggestAdapter($params, $channel = null, $id = null)
    {
        $this->_configureAdapter($params, "suggest", $channel, $id);
    }

    /**
     * Get suggestions object from adapter
     *
     * @param string $channel
     * @param int    $id
     *
     * @return \FACTFinder\Data\SuggestQuery[]
     */
    public function getSuggestions($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("suggest", "getSuggestions", $channel, $id);
    }

    /**
     * Configure adapter
     *
     * @param array  $params
     * @param string $type
     * @param string $channel
     * @param int    $id
     *
     * @return void
     */
    protected function _configureAdapter($params, $type, $channel = null, $id = null)
    {
        $adapterId = $this->_getAdapterIdentifier($type, $channel, $id);
        $this->_paramHashes[$adapterId] = $this->_createParametersHash($params);

        foreach ($params as $key => $value) {
            $this->_dic['requestParser']->getClientRequestParameters()->set($key, $value);
            $this->_dic['requestParser']->getRequestParameters()->set($key, $value);
        }
    }

    /**
     * Create hash for an array of params
     *
     * @param array $params
     *
     * @return string
     */
    private function _createParametersHash($params)
    {
        $returnValue = '';
        if ($params) {
            ksort($params);
            $returnValue = md5(http_build_query($params));
        }

        return $returnValue;
    }
}