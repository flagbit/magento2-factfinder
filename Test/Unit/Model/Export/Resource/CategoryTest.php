<?php
/**
 * CategoryTest.php
 *
 * @category Mage
 * @package magento2
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
 * @license GPL
 * @link http://www.flagbit.de
 */

namespace Flagbit\FACTFinder\Test\Unit\Model\Export\Resource;


class CategoryTest extends \PHPUnit_Framework_TestCase
{
    protected $_collection;

    protected $_resource;

    public function setUp()
    {
        $this->_collection = $this->getMock(
            '\Magento\Catalog\Model\Resource\Category\CollectionFactory',
            [],
            [],
            '',
            false
        );

        $this->_resource = $this->getMock(
            'Flagbit\FACTFinder\Model\Export\Resource\Category',
            ['_initCategories'],
            [],
            '',
            false
        );

        $this->_resource->__construct(
            $this->_collection
        );
    }

    public function testGetCategoryPath()
    {
        $product = $this->getMock(
            '\Magento\Catalog\Model\Product',
            [],
            [],
            '',
            false
        );
        $product->expects($this->once())
            ->method('getCategoryIds')
            ->willReturn([1,5,17]);

        $result = $this->_resource->getCategoryPath($product);

        $this->assertInternalType('string', $result);
    }

}
