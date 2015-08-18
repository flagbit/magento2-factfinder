<?php
namespace Flagbit\FACTFinder\Model\Plugin;

class ProductCollection
{
    /**
     * @var \Flagbit\FACTFinder\Model\Handler\Search
     */
    protected $_searchHandler;

    /**
     * @var \Flagbit\FACTFinder\Helper\Config
     */
    protected $_configHelper;

    /**
     * @param \Flagbit\FACTFinder\Model\Handler\Search $_searchHandler,
     * @param \Flagbit\FACTFinder\Helper\Config $configHelper
     */
    public function __construct(
        \Flagbit\FACTFinder\Model\Handler\Search $_searchHandler,
        \Flagbit\FACTFinder\Helper\Config $configHelper
    )
    {
        $this->_searchHandler = $_searchHandler;
        $this->_configHelper = $configHelper;
    }

    /**
     * Overwrite collection size with ff result count
     *
     * @return int
     */
    public function afterGetSize()
    {
        return $this->_searchHandler->getSearchResult()->getFoundRecordsCount();
    }

    /**
     * Set pagesize to false to prevent paging on db query
     *
     * @param \Magento\Eav\Model\Entity\Collection\AbstractCollection $subject
     */
    public function before_loadEntities(\Magento\Eav\Model\Entity\Collection\AbstractCollection $subject)
    {
        $subject->setPageSize(false);
    }
}