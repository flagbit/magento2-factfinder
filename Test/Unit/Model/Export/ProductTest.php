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

namespace Flagbit\FACTFinder\Test\Unit\Model\Export;

class ProductTest extends \PHPUnit_Framework_TestCase
{
    protected $_product;

    protected $_productRepository;

    protected $_searchCriteriaBuilder;

    protected $_filterBuilder;

    protected $_categoryColFactory;

    protected $_attributeColFactory;

    protected $_configurable;

    protected $_config;

    protected function setUp()
    {
        $this->_productRepository = $this->getMock(
            '\Magento\Catalog\Api\ProductRepositoryInterface',
            [],
            [],
            ''
        );

        $this->_searchCriteriaBuilder = $this->getMock(
            '\Magento\Framework\Api\SearchCriteriaBuilder',
            [],
            [],
            '',
            false
        );

        $this->_filterBuilder = $this->getMock(
            '\Magento\Framework\Api\FilterBuilder',
            [],
            [],
            '',
            false
        );

        $this->_categoryColFactory = $this->getMock(
            '\Magento\Catalog\Model\Resource\Category\CollectionFactory',
            [],
            [],
            '',
            false
        );

        $this->_attributeColFactory = $this->getMock(
            '\Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory',
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

        $this->_config = $this->getMock(
            '\Flagbit\FACTFinder\Helper\Config',
            ['exportImagesAndDeeplinks'],
            [],
            '',
            false
        );

        $this->_product = $this->getMock(
            '\Flagbit\FACTFinder\Model\Export\Product',
            null,
            [],
            '',
            false
        );

        $this->_product->__construct(
            $this->_productRepository,
            $this->_searchCriteriaBuilder,
            $this->_filterBuilder,
            $this->_categoryColFactory,
            $this->_attributeColFactory,
            $this->_configurable,
            $this->_config
        );

    }

    public function testGetHeader()
    {
        $this->_config->expects($this->once())
            ->method('exportImagesAndDeeplinks');

        $header = $this->_product->getHeader();

        $this->assertNotEmpty($header);
        $this->assertInternalType('array', $header);
    }

    public function testNothingToExport()
    {
        $product = $this->getMock(
            '\Flagbit\FACTFinder\Model\Export\Product',
            [
                '_getProductList',
                '_buildExportRow',
            ],
            [],
            '',
            false
        );

        $product->expects($this->once())
            ->method('_getProductList')
            ->willReturn([]);

        $product->expects($this->never())->method('_buildExportRow');
        $product->expects($this->never())->method('_getChildrenProducts');

        $result = $product->export();

        $this->assertEquals([], $result);
    }

}