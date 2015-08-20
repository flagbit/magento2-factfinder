<?php
namespace Flagbit\FACTFinder\Test\Unit\Model\Handler;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class SearchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * FACTFinder Facade
     *
     * @var \Flagbit\FACTFinder\Model\Facade|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_facade;

    /**
     * Query factory
     *
     * @var \Magento\Search\Model\QueryFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_queryFactory;

    /**
     * Search Helper
     *
     * @var \Flagbit\FACTFinder\Helper\Search|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_searchHelper;

    /**
     * @var \Flagbit\FACTFinder\Model\Handler\Search
     */
    protected $_searchHandler;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->_facade = $this->getMockBuilder('\Flagbit\FACTFinder\Model\Facade')
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_queryFactory = $this->getMockBuilder('\Magento\Search\Model\QueryFactory')
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $queryObject = $this->getMockBuilder('\Magento\Search\Model\Query')
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_queryFactory
            ->expects($this->any())
            ->method('get')
            ->willReturn($queryObject);

        $this->_searchHelper = $this->getMockBuilder('\Flagbit\FACTFinder\Helper\Search')
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();


        $this->_searchHandler = $this->objectManager->getObject(
            'Flagbit\FACTFinder\Model\Handler\Search',
            [
                'facade'        => $this->_facade,
                'queryFactory'  => $this->_queryFactory,
                'searchHelper'  => $this->_searchHelper
            ]
        );
    }

    public function testGetSearchResult()
    {
        $searchResult = $this->getMockBuilder('\FACTFinder\Data\Result')
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_facade
            ->expects($this->once())
            ->method('getSearchResult')
            ->willReturn($searchResult);

        $result = $this->_searchHandler->getSearchResult();

        $this->assertInstanceOf('\FACTFinder\Data\Result', $result);
    }
}