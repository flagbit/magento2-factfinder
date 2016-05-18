<?php
/**
 * AttributeTest.php
 *
 * @category Mage
 * @package magento2
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
 * @license GPL
 * @link http://www.flagbit.de
 */

namespace Flagbit\FACTFinder\Test\Unit\Model\Export\Resource;


use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class AttributeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Flagbit\FACTFinder\Model\Export\Resource\Attribute
     */
    protected $_resource;

    protected $_collectionFactory;

    /**
     * @var ObjectManagerHelper
     */
    protected $_objectManagerHelper;

    public function setUp()
    {
        $this->_collectionFactory = $this->getMock(
            '\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory',
            [],
            [],
            '',
            false
        );

        $this->_resource = $this->getMock(
            '\Flagbit\FACTFinder\Model\Export\Resource\Attribute',
            null,
            [],
            '',
            false
        );

        $this->_resource->__construct(
            $this->_collectionFactory
        );

        $this->_objectManagerHelper = new ObjectManagerHelper($this);


    }

    public function testGetAdditionalAttributeCodes()
    {
        $result = $this->_resource->getAdditionalAttributeCodes();
        $this->assertInternalType('array', $result);
    }

    public function testGetAdditionalAttributes()
    {
        $result = $this->_resource->getAdditionalAttributes();
        $this->assertInternalType('array', $result);
    }

    public function testGetRequiredAttributes()
    {
        $result = $this->_resource->getRequiredAttributes();
        $this->assertInternalType('array', $result);
    }

    public function testGetSearchableAttributesEmpty()
    {
        $this->prepareEmptyCollection();

        $result = $this->_resource->getSearchableAttributes();

        $this->assertEmpty($result);
        $this->assertInternalType('array', $result);
    }

    public function testGetFilterableAttributesEmpty()
    {
        $this->prepareEmptyCollection();

        $result = $this->_resource->getFilterableAttributes();

        $this->assertEmpty($result);
        $this->assertInternalType('array', $result);
    }

    public function testGetNumericalAttributesEmpty()
    {
        $this->prepareEmptyCollection();

        $result = $this->_resource->getNumericalAttributes();

        $this->assertEmpty($result);
        $this->assertInternalType('array', $result);
    }

    public function testGetNumericalAttributes()
    {
        $this->prepareAttributeCollection();

        $result = $this->_resource->getNumericalAttributes();

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
    }

    public function testGetFilterableAttributes()
    {
        $this->prepareAttributeCollection();

        $result = $this->_resource->getFilterableAttributes();

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertCount(3, $result);
    }

    public function testGetSearchableAttributes()
    {
        $this->prepareAttributeCollection();

        $result = $this->_resource->getSearchableAttributes();

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertCount(3, $result);
    }

    protected function prepareEmptyCollection()
    {
        $collection = $this->_objectManagerHelper->getCollectionMock(
            '\Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection',
            []
        );

        $this->_collectionFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($collection);
    }

    protected function prepareAttributeCollection()
    {
        $methods = ['getBackendType', 'getIsFilterable', 'getAttributeCode', 'getIsSearchable', 'getIsUserDefined'];
        $dataSet = [
            // numerical
            ['decimal', true, 'test_numerical_1', true, true],
            ['decimal', false, 'test_numerical_2', true, false],
            ['varchar', true, 'test_numerical_3', false, false],
            // searchable
            ['varchar', true, 'test_searchable_1', true, true],
            ['text', false, 'test_searchable_2', true, true],
            ['decimal', true, 'test_numerical_1', true, false],
            // filterable
            ['varchar', true, 'test_filterable_1', false, false],
            ['text', false, 'test_filterable_2', true, true],
            ['decimal', true, 'test_numerical_1', true, false],
        ];

        $data = [];
        foreach ($dataSet as $values) {
            $values = array_combine($methods, $values);
            $attribute = $this->getMock(
                '\Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
                $methods,
                [],
                '',
                false
            );

            foreach ($values as $method => $value) {
                $attribute->expects($this->any())
                    ->method($method)
                    ->willReturn($value);
            }

            $data[] = $attribute;
        }

        $this->_collectionFactory
            ->expects($this->any())
            ->method('create')
            ->will($this->returnCallback(function () use ($data) {
                return $this->_objectManagerHelper->getCollectionMock(
                    '\Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection',
                    $data
                );
            }));
    }

    /**
     * @dataProvider initializeGetAttributeValueDataProvider
     */
    public function testGetAttributeValue($product, $attribute)
    {
        $result = $this->_resource->getAttributeValue($product, $attribute);

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
    }

    public function initializeGetAttributeValueDataProvider()
    {
        $optionMock = $this->getMock('\Magento\Eav\Api\Data\AttributeOptionInterface');
        $optionMock->method('getValue')
            ->willReturn(12);
        $optionMock->method('getLabel')
            ->willReturn('Label');

        $objectManagerHelper = new ObjectManagerHelper($this);
        $options = $objectManagerHelper->getCollectionMock(
            '\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection',
            [$optionMock, $optionMock]
        );


        $methods = ['getBackendType', 'getAttributeCode', 'getOptions'];
        $dataSet = [
            ['int', 'test_numerical_1', $options],
            ['text', 'test_numerical_2', $options],
            ['varchar', 'test_numerical_3', $options],
            ['decimal', 'test_numerical_3', $options],

        ];

        $data = [];
        foreach ($dataSet as $values) {
            $values = array_combine($methods, $values);
            $attribute = $this->getMock(
                '\Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
                $methods,
                [],
                '',
                false
            );

            foreach ($values as $method => $value) {
                $attribute->expects($this->any())
                    ->method($method)
                    ->willReturn($value);
            }

            $product = $this->getMock(
                '\Magento\Catalog\Model\Product',
                ['getData'],
                [],
                '',
                false
            );

            $product->expects($this->any())
                ->method('getData')
                ->with($attribute->getAttributeCode())
                ->willReturn('12,14,1');

            $data[] = [$product, $attribute];
        }

        return $data;
    }
}
