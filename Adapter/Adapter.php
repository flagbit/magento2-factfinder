<?php
namespace Flagbit\FACTFinder\Adapter;

use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\RequestInterface;

/**
 * MySQL Search Adapter
 */
class Adapter implements AdapterInterface
{
    /**
     * Response Factory
     *
     * @var \Magento\Framework\Search\Adapter\Mysql\ResponseFactory
     */
    protected $_responseFactory;

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
     * @param \Magento\Framework\Search\Adapter\Mysql\ResponseFactory $responseFactory
     * @param \Flagbit\FACTFinder\Model\Facade $facade
     * @param \Magento\Search\Model\QueryFactory $queryFactory
     */
    public function __construct(
        \Magento\Framework\Search\Adapter\Mysql\ResponseFactory $responseFactory,
        \Flagbit\FACTFinder\Model\Facade $facade,
        \Magento\Search\Model\QueryFactory $queryFactory
    ) {
        $this->_responseFactory = $responseFactory;
        $this->_facade = $facade;
        $this->_queryFactory = $queryFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function query(RequestInterface $request)
    {
        $this->_facade->configureSearchAdapter([
            'query' => $this->_queryFactory->get()->getQueryText(),
            'idsOnly' => 'true'
        ]);

        $response = [
            'documents' => $this->_transformSearchResult(
                $this->_facade->getSearchResult()
            ),
            'aggregations' => [
                'category_bucket' => []
            ],
        ];
        return $this->_responseFactory->create($response);
    }

    /**
     * Transform FACTFinder search result to fit catalog search format
     *
     * @param \FACTFinder\Data\Result $searchResult
     * @return array
     */
    protected function _transformSearchResult(\FACTFinder\Data\Result $searchResult)
    {
        $result = [];
        foreach($searchResult as $record) {
            $result[$record->getID()] = [
                'entity_id' => $record->getID(),
                'relevance' => $record->getSimilarity()
            ];
        }

        return $result;
    }
}
