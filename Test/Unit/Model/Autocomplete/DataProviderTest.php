<?php
/**
 * DataProvider.php
 *
 * @category Mage
 * @package magento2
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
 * @license GPL
 * @link http://www.flagbit.de
 */

namespace Flagbit\FACTFinder\Test\Unit\Model\Autocomplete;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Flagbit\FACTFinder\Model\Autocomplete\DataProvider;

class DataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataProvider
     */
    private $model;

    /**
     * @var \Magento\Search\Model\Autocomplete\ItemFactory |\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemFactory;

    /**
     * @var \Flagbit\FACTFinder\Model\Handler\Suggest
     */
    private $handler;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->handler = $this->getMockBuilder('Flagbit\FACTFinder\Model\Handler\Suggest')
            ->disableOriginalConstructor()
            ->setMethods(['getSuggestions'])
            ->getMock();

        $this->itemFactory = $this->getMockBuilder('Magento\Search\Model\Autocomplete\ItemFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->model = $helper->getObject(
            '\Flagbit\FACTFinder\Model\Autocomplete\DataProvider',
            [
                'handler' => $this->handler,
                'itemFactory' => $this->itemFactory
            ]
        );
    }

    /**
     * This method was mainly copied and adjusted from the core data provider test
     * It is unknown what it actually tests so todo it should be changed to test the actual code
     */
    public function testGetItems()
    {
        $queryString = 'string';
        $expected = ['title' => $queryString, 'num_results' => 100500];
        $collection = [
            ['label' => 'string1', 'num_results' => 1],
            ['label' => 'string2', 'num_results' => 2],
            ['label' => 'string11', 'num_results' => 11],
            ['label' => 'string100', 'num_results' => 100],
            ['label' => $queryString, 'num_results' => 100500]
        ];

        $this->handler->expects($this->once())
            ->method('getSuggestions')
            ->willReturn($this->buildCollection($collection));

        $itemMock =  $this->getMockBuilder('Magento\Search\Model\Autocomplete\Item')
            ->disableOriginalConstructor()
            ->setMethods(['getTitle', 'toArray'])
            ->getMock();
        $itemMock->expects($this->any())
            ->method('getTitle')
            ->will($this->onConsecutiveCalls(
                $queryString,
                'string1',
                'string2',
                'string11',
                'string100'
            ));
        $itemMock->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue($expected));

        $this->itemFactory->expects($this->any())
            ->method('create')
            ->willReturn($itemMock);

        $result = $this->model->getItems();
        $this->assertEquals($expected, $result[0]->toArray());
    }

    private function buildCollection(array $data)
    {
        $collectionData = [];
        foreach ($data as $collectionItem) {
            $collectionData[] = new \Magento\Framework\DataObject($collectionItem);
        }

        return $collectionData;
    }
}
