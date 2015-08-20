<?php
/**
 * Plugin.php
 *
 * @category Mage
 * @package magento2
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
 * @license GPL
 * @link http://www.flagbit.de
 */
namespace Flagbit\FACTFinder\Model\RequireJs\Collector;

class Plugin
{

    /**
     * @var \Flagbit\FACTFinder\Helper\Config
     */
    protected $_configHelper;


    /**
     * @param \Flagbit\FACTFinder\Helper\Config $configHelper
     */
    public function __construct(
        \Flagbit\FACTFinder\Helper\Config $configHelper
    ) {
        $this->_configHelper = $configHelper;
    }


    /**
     * We need a way to disable our js in case the module isn't enabled
     * As there's no way to pass a condition to requirejs thing, here we just remove our file(s) from the merge list
     * 
     * @param \Magento\Framework\RequireJs\Config\File\Collector\Aggregated $subject
     * @param \Magento\Framework\View\File[]                                $result
     *
     * @return array
     */
    public function afterGetFiles(\Magento\Framework\RequireJs\Config\File\Collector\Aggregated $subject, $result)
    {
        if ($this->_configHelper->isEngineEnabled()) {
            return $result;
        }

        foreach ($result as $index => $file) {
            if ($file->getModule() == 'Flagbit_FACTFinder') {
                unset($result[$index]);
            }
        }

        return $result;
    }


}