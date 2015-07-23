<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Flagbit\FACTFinder\Adapter;

use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Adapter\Mysql\ResponseFactory;

/**
 * MySQL Search Adapter
 */
class Adapter implements AdapterInterface
{
    /**
     * Response Factory
     *
     * @var ResponseFactory
     */
    protected $responseFactory;

    /**
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        ResponseFactory $responseFactory
    ) {
        $this->responseFactory = $responseFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function query(RequestInterface $request)
    {
        $response = [
            'documents' => [
                40 => [
                    'entity_id' => 40,
                    'relevance' => 200
                ],
                44 => [
                    'entity_id' => 44,
                    'relevance' => 200
                ]
            ],
            'aggregations' => [
                'category_bucket' => []
            ],
        ];
        return $this->responseFactory->create($response);
    }
}
