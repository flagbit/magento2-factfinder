<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Search\Test\Unit\Adapter\Mysql;

use Magento\Framework\App\Resource;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class AdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResponseFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseFactory;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Search\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Flagbit\FACTFinder\Model\Handler\Search|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchHandler;

    /**
     * @var \Flagbit\FACTFinder\Adapter\Adapter
     */
    private $adapter;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->request = $this->getMockBuilder('Magento\Framework\Search\RequestInterface')
            ->setMethods(['getAggregation'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->responseFactory = $this->getMockBuilder('\Magento\Framework\Search\Adapter\Mysql\ResponseFactory')
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchHandler = $this->getMockBuilder('\Flagbit\FACTFinder\Model\Handler\Search')
            ->setMethods(['getSearchResult'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->adapter = $this->objectManager->getObject(
            'Flagbit\FACTFinder\Adapter\Adapter',
            [
                'responseFactory' => $this->responseFactory,
                'searchAdapter' => $this->searchHandler
            ]
        );
    }

    public function testQuery()
    {
        $selectResult = [
            'documents' => [
                1 => [
                    'entity_id' => 1,
                    'relevance' => '0.99',
                ],
            ],
            'aggregations' => [
                'category_bucket' => []
            ],
        ];

        $this->responseFactory->expects($this->once())
            ->method('create')
            ->with($selectResult)
            ->will($this->returnArgument(0));

        $searchResult = $this->getMockBuilder('\FACTFinder\Data\Result')
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $resultRecord = $this->getMockBuilder('\FACTFinder\Data\Record')
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $resultRecord
            ->expects($this->any())
            ->method('getID')
            ->willReturn('1');

        $resultRecord
            ->expects($this->any())
            ->method('getSimilarity')
            ->willReturn('0.99');

        $this->_mockIteratorItems($searchResult, [$resultRecord]);

        $searchResult
            ->expects($this->any())
            ->method('getFoundRecordsCount')
            ->willReturn('1');

        $this->searchHandler
            ->expects($this->any())
            ->method('getSearchResult')
            ->willReturn($searchResult);

        $response = $this->adapter->query($this->request);

        $this->assertEquals($selectResult, $response);
    }

    /**
     * Adds expected items to a mocked Iterator.
     */
    protected function _mockIteratorItems(\Iterator $iterator, array $items, $includeCallsToKey = false)
    {
        $iterator->expects($this->at(0))->method('rewind');
        $counter = 1;
        foreach ($items as $k => $v) {
            $iterator->expects($this->at($counter++))->method('valid')->will($this->returnValue(true));
            $iterator->expects($this->at($counter++))->method('current')->will($this->returnValue($v));
            if ($includeCallsToKey) {
                $iterator->expects($this->at($counter++))->method('key')->will($this->returnValue($k));
            }
            $iterator->expects($this->at($counter++))->method('next');
        }
        $iterator->expects($this->at($counter))->method('valid')->will($this->returnValue(false));
    }
}
