<?php
namespace Flagbit\FACTFinder\Model\Handler;

class Search
{
    /**
     * FACTFinder Facade
     *
     * @var \Flagbit\FACTFinder\Model\Facade
     */
    protected $_facade;

    /**
     * Query factory
     *
     * @var \Magento\Search\Model\QueryFactory
     */
    protected $_queryFactory;

    /**
     * Search Helper
     *
     * @var \Flagbit\FACTFinder\Helper\Search
     */
    protected $_searchHelper;

    /**
     * @param \Flagbit\FACTFinder\Model\Facade $facade
     * @param \Magento\Search\Model\QueryFactory $queryFactory
     * @param \Flagbit\FACTFinder\Helper\Search $searchHelper
     */
    public function __construct(
        \Flagbit\FACTFinder\Model\Facade $facade,
        \Magento\Search\Model\QueryFactory $queryFactory,
        \Flagbit\FACTFinder\Helper\Search $searchHelper
    ) {
        $this->_facade = $facade;
        $this->_queryFactory = $queryFactory;
        $this->_searchHelper = $searchHelper;

        $this->_configureFacade();
    }

    protected function _configureFacade()
    {
        $this->_facade->configureSearchAdapter([
            'query'             => $this->_queryFactory->get()->getQueryText(),
            'page'              => $this->_searchHelper->getCurrentPage(),
            'productsPerPage'   => $this->_searchHelper->getLimitPerPage(),
            'idsOnly'           => 'true'
        ]);
    }

    public function getSearchResult()
    {
        return $this->_facade->getSearchResult();
    }
}