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
    protected $_searchAdapter;

    /**
     * @param \Magento\Framework\Search\Adapter\Mysql\ResponseFactory $responseFactory
     * @param \Flagbit\FACTFinder\Model\Facade $facade
     */
    public function __construct(
        \Magento\Framework\Search\Adapter\Mysql\ResponseFactory $responseFactory,
        \Flagbit\FACTFinder\Model\Handler\Search $searchAdapter
    ) {
        $this->_responseFactory = $responseFactory;
        $this->_searchAdapter = $searchAdapter;

    }

    /**
     * {@inheritdoc}
     */
    public function query(RequestInterface $request)
    {
        $response = [
            'documents' => $this->_transformSearchResult(
                $this->_searchAdapter->getSearchResult()
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
