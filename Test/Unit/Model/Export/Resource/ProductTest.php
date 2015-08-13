<?php
/**
 * ProductTest.php
 *
 * @category Mage
 * @package magento2
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
 * @license GPL
 * @link http://www.flagbit.de
 */

namespace Flagbit\FACTFinder\Test\Unit\Model\Export\Resource;

class ProductTest extends \PHPUnit_Framework_TestCase
{
    protected $_repository;
    protected $_configurable;
    protected $_filterBuilder;
    protected $_searchCriteriaBuilder;
    protected $_imageHelper;
    protected $_config;

    protected $_resource;

    protected function setUp()
    {
        $this->_repository = $this->getMock(
            '\Magento\Catalog\Model\ProductRepository',
            [],
            [],
            '',
            false
        );
        $this->_configurable = $this->getMock(
            '\Magento\ConfigurableProduct\Model\Product\Type\Configurable',
            [],
            [],
            '',
            false
        );
        $this->_filterBuilder = $this->getMock(
            '\Magento\Framework\Api\FilterBuilder',
            ['setField', 'setValue', 'setConditionType', 'create'],
            [],
            '',
            false
        );
        $this->_searchCriteriaBuilder = $this->getMock(
            '\Magento\Framework\Api\SearchCriteriaBuilder',
            ['addFilters', 'create'],
            [],
            '',
            false
        );
        $this->_imageHelper = $this->getMock(
            '\Magento\Catalog\Helper\Image',
            [],
            [],
            '',
            false
        );
        $this->_config = $this->getMock(
            '\Flagbit\FACTFinder\Helper\Config',
            [],
            [],
            '',
            false
        );
        $this->_resource = $this->getMock(
            '\Flagbit\FACTFinder\Model\Export\Resource\Product',
            null,
            [],
            '',
            false
        );

        $this->_resource->__construct(
            $this->_repository,
            $this->_configurable,
            $this->_filterBuilder,
            $this->_searchCriteriaBuilder,
            $this->_imageHelper,
            $this->_config
        );
    }

    public function testGetProductUrl()
    {
        $product = $this->_getProductMock();

        $product->expects($this->once())
            ->method('getProductUrl')
            ->willReturn('http://example.com/example-url');

        $result = $this->_resource->getProductDeeplink($product);

        $this->assertInternalType('string', $result);
    }

    public function testGetProductList()
    {
        $this->_filterBuilder
            ->method('setField')
            ->will($this->returnSelf());

        $this->_filterBuilder
            ->method('setValue')
            ->will($this->returnSelf());

        $this->_filterBuilder
            ->method('setConditionType')
            ->will($this->returnSelf());

        $this->_filterBuilder
            ->method('create')
            ->willReturn($this->getMock('\Magento\Framework\Api\FilterInterface'));

        $this->_searchCriteriaBuilder
            ->method('addFilters')
            ->will($this->returnSelf());

        $this->_searchCriteriaBuilder
            ->method('create')
            ->willReturn($this->getMock('\Magento\Framework\Api\SearchCriteriaInterface'));

        $resultMock = $this->getMock('\Magento\Catalog\Api\Data\ProductSearchResultsInterface');
        $resultMock->method('getItems')
            ->willReturn([]);

        $this->_repository
            ->expects($this->once())
            ->method('getList')
            ->willReturn($resultMock);

        $result = $this->_resource->getProductList();

        $this->assertInternalType('array', $result);
    }

    public function testGetChildrenProductsOfSimple()
    {
        $product = $this->_getProductMock();
        $product->expects($this->once())
            ->method('getTypeId')
            ->willReturn('simple');

        $this->_configurable->expects($this->never())
            ->method('getChildrenIds');

        $result = $this->_resource->getChildrenProducts($product);

        $this->assertEmpty($result);
        $this->assertInternalType('array', $result);
    }

    public function testGetChildrenProductsIfEmpty()
    {
        $product = $this->_getProductMock();
        $product->expects($this->once())
            ->method('getTypeId')
            ->willReturn('configurable');

        $this->_configurable->expects($this->once())
            ->method('getChildrenIds')
            ->willReturn([0 => []]); // no children

        $result = $this->_resource->getChildrenProducts($product);

        $this->assertEmpty($result);
        $this->assertInternalType('array', $result);
    }

    public function testGetChildrenProductsNotEmpty()
    {
        $product = $this->_getProductMock();
        $product->expects($this->once())
            ->method('getTypeId')
            ->willReturn('configurable');

        $this->_configurable->expects($this->once())
            ->method('getChildrenIds')
            ->willReturn([
                1 => [1, 2, 3],
                2 => [3, 123, 43, 11],
                12 => [1]
            ]);

        $this->_filterBuilder
            ->method('setField')
            ->with('entity_id')
            ->will($this->returnSelf());

        $this->_filterBuilder
            ->method('setValue')
            ->will($this->returnSelf());

        $this->_filterBuilder
            ->method('setConditionType')
            ->with('in')
            ->will($this->returnSelf());

        $this->_filterBuilder
            ->method('create')
            ->willReturn($this->getMock('\Magento\Framework\Api\FilterInterface'));

        $this->_searchCriteriaBuilder
            ->method('addFilters')
            ->will($this->returnSelf());

        $this->_searchCriteriaBuilder
            ->method('create')
            ->willReturn($this->getMock('\Magento\Framework\Api\SearchCriteriaInterface'));

        $resultMock = $this->getMock('\Magento\Catalog\Api\Data\ProductSearchResultsInterface');
        $resultMock->method('getItems')
            ->willReturn([$this->getMock('\Magento\Catalog\Api\Data\ProductInterface')]);

        $this->_repository
            ->expects($this->once())
            ->method('getList')
            ->willReturn($resultMock);


        $result = $this->_resource->getChildrenProducts($product);

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertInstanceOf('Magento\Catalog\Api\Data\ProductInterface', array_pop($result));
    }

    /**
     * @dataProvider initializeImageTypeDataProvider
     */
    public function testGetBaseProductImage($type)
    {
        $product = $this->_getProductMock();

        $this->_config
            ->expects($this->once())
            ->method('getExportImageType')
            ->willReturn($type);

        $this->_imageHelper
            ->expects($this->once())
            ->method('init')
            ->with($product, $type)
            ->will($this->returnSelf());

        $result = $this->_resource->getProductImage($product);
        $this->assertInternalType('string', $result);
    }

    /**
     * @dataProvider initializeImageResizeDataProvider
     */
    public function testGetProductImageResize($size)
    {
        $this->_config
            ->expects($this->any())
            ->method('getExportImageSize')
            ->willReturn($size);

        $this->_imageHelper
            ->expects($this->once())
            ->method('init')
            ->will($this->returnSelf());

        if (intval($size) > 0) {
            $this->_imageHelper
                ->expects($this->once())
                ->method('resize')
                ->with($size)
                ->will($this->returnSelf());
        } else {
            $this->_imageHelper
                ->expects($this->never())
                ->method('resize');
        }

        $this->_resource->getProductImage($this->_getProductMock());
    }

    protected function _getProductMock()
    {
        $product = $this->getMock(
            '\Magento\Catalog\Model\Product',
            [
                'getId',
                'getTypeId',
                'getProductUrl',
            ],
            [],
            '',
            false
        );

        $product->method('getId')
            ->willReturn(123);

        return $product;
    }

    public function initializeImageTypeDataProvider()
    {
        return [
            ['image'],
            ['small_image'],
            ['thumbnail'],
        ];
    }

    public function initializeImageResizeDataProvider()
    {
        return [
            [0],
            [50],
            [-25],
            ['ololo'],
            ['40'],
        ];
    }

}