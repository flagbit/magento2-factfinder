<?php
/**
 * AutocompletePlugin.php
 *
 * @category Mage
 * @package magento2
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
 * @license GPL
 * @link http://www.flagbit.de
 */

namespace Flagbit\FACTFinder\Model\Autocomplete;

class Plugin
{

    /**
     * @var \Flagbit\FACTFinder\Model\Autocomplete\DataProvider
     */
    protected $_dataProvider;

    /**
     * @var \Flagbit\FACTFinder\Helper\Config
     */
    protected $_configHelper;

    /**
     * @param \Flagbit\FACTFinder\Model\Autocomplete\DataProvider $dataProvider,
     * @param \Flagbit\FACTFinder\Helper\Config                   $configHelper
     */
    public function __construct(
        DataProvider $dataProvider,
        \Flagbit\FACTFinder\Helper\Config $configHelper
    ) {
        $this->_dataProvider = $dataProvider;
        $this->_configHelper = $configHelper;
    }


    /**
     * The original method of the Autocomplete model calls all the data providers
     * which are passed there as in a constructor argument through the dependency injection system.
     * Since we don't need all of them to be called, overriding this method was the only acceptable
     * way to avoid calling unnecessary data providers
     *
     * @param \Magento\Search\Model\Autocomplete $subject
     * @param \Closure                           $proceed
     *
     * @return \Magento\Search\Model\Autocomplete\ItemInterface[]
     */
    public function aroundGetItems(\Magento\Search\Model\Autocomplete $subject, \Closure $proceed)
    {
        if (!$this->_configHelper->isEngineEnabled()) {
            return $proceed();
        }

        return $this->_dataProvider->getItems();
    }


}