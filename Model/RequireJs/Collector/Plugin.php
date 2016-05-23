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

use Flagbit\FACTFinder\Helper\Config;
use Magento\Framework\RequireJs\Config\File\Collector\Aggregated;

class Plugin
{

    /**
     * @var Config
     */
    protected $_configHelper;


    /**
     * @param Config $configHelper
     */
    public function __construct(Config $configHelper)
    {
        $this->_configHelper = $configHelper;
    }


    /**
     * We need a way to disable our js in case the module isn't enabled
     * As there's no way to pass a condition to requirejs thing, here we just remove our file(s) from the merge list
     *
     * @param Aggregated $subject
     * @param \Magento\Framework\View\File[]                                $result
     *
     * @return array
     */
    public function afterGetFiles(Aggregated $subject, $result)
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