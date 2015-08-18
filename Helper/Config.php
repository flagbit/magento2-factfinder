<?php
/**
 * Config.php
 *
 * @category Mage
 * @package magento2
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
 * @license GPL
 * @link http://www.flagbit.de
 */

namespace Flagbit\FACTFinder\Helper;

use \Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function isEngineEnabled()
    {
        return $this->_scopeConfig->getValue('catalog/search/engine') == 'factfinder';
    }

    /**
     * @return bool
     */
    public function exportImagesAndDeeplinks()
    {
        return (bool) $this->_scopeConfig->getValue('factfinder/export/images_deeplinks');
    }

    /**
     * @return bool
     */
    public function removeTagsOnExport()
    {
        return (bool) $this->_scopeConfig->getValue('factfinder/export/remove_tags');
    }

    /**
     * @return string
     */
    public function getExportImageType()
    {
        return $this->_scopeConfig->getValue('factfinder/export/image_type');
    }

    public function getExportImageSize()
    {
        return intval($this->_scopeConfig->getValue('factfinder/export/image_size'));
    }

    /**
     * @param string $path
     *
     * @return mixed
     */
    public function getValue($path)
    {
        return $this->_scopeConfig->getValue($path);
    }

}