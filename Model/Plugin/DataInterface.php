<?php
namespace Flagbit\FACTFinder\Model\Plugin;
class DataInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(\Magento\Framework\App\RequestInterface $request) {
        $this->_request = $request;
    }

    public function beforeGetValue(\Magento\Framework\App\Config\DataInterface $subject, $path)
    {
        if($path == 'catalog/search/engine'
            && !in_array($this->_request->getModuleName(), ['catalogsearch','search'])) {
            $path = 'factfinder/search/fallback_engine';
        }
        return array($path);
    }

}