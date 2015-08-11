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
    protected $_export;

    protected $_productResource;

    protected $_categoryResource;

    protected $_attributeResource;

    protected $_config;

    protected function setUp()
    {
        $this->_productResource = $this->getMock(
            '\Flagbit\FACTFinder\Model\Export\Resource\Product',
            ['getProductList', 'getChildrenProducts'],
            [],
            '',
            false
        );

        $this->_categoryResource = $this->getMock(
            '\Flagbit\FACTFinder\Model\Export\Resource\Category',
            [],
            [],
            '',
            false
        );

        $this->_attributeResource = $this->getMock(
            '\Flagbit\FACTFinder\Model\Export\Resource\Attribute',
            null,
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

        $this->_export = $this->getMock(
            '\Flagbit\FACTFinder\Model\Export\Product',
            ['_buildExportRow'],
            [],
            '',
            false
        );

        $this->_export->__construct(
            $this->_productResource,
            $this->_categoryResource,
            $this->_attributeResource,
            $this->_config
        );

    }

    public function testGetHeader()
    {
        $this->_config->expects($this->once())
            ->method('exportImagesAndDeeplinks');

        $header = $this->_export->getHeader();

        $this->assertNotEmpty($header);
        $this->assertInternalType('array', $header);
    }


    public function testNothingToExport()
    {
        $this->_productResource->expects($this->once())
            ->method('getProductList')
            ->willReturn([]);

        $this->_export->expects($this->never())
            ->method('_buildExportRow');
        $this->_productResource->expects($this->never())
            ->method('getChildrenProducts');

        $result = $this->_export->export();

        $this->assertEquals([], $result);
    }


    public function testExport()
    {
        $products = $this->_getProductList();

        $this->_productResource->expects($this->once())
            ->method('getProductList')
            ->willReturn($products);

        $this->_productResource->expects($this->atLeastOnce())
            ->method('getChildrenProducts')
            ->willReturn([]);

        $this->_export->expects($this->atLeastOnce())
            ->method('_buildExportRow')
            ->willReturn('test string');

        $result = $this->_export->export();

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
    }


    protected function _getProductList()
    {
        $product = $this->getMock(
            'Magento\Catalog\Model\Product',
            [],
            [],
            '',
            false
        );

        return [$product, $product];
    }

}