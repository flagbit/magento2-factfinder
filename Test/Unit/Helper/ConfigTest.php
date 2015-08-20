<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Flagbit\FACTFinder\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ConfigTest extends \PHPUnit_Framework_TestCase
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
     * @var \Flagbit\FACTFinder\Helper\Config
     */
    private $helper;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->config = $this->getMockBuilder('\Magento\Framework\App\Config\ScopeConfigInterface')
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->helper = $this->objectManager->getObject(
            'Flagbit\FACTFinder\Helper\Config',
            [
                'scopeConfig' => $this->config
            ]
        );
    }

    public function testIsEngineEnabled()
    {
        $this->config
            ->expects($this->once())
            ->method('getValue')
            ->willReturn('factfinder');

        $this->assertTrue($this->helper->isEngineEnabled());
    }

    public function testIsNotEngineEnabled()
    {
        $this->config
            ->expects($this->once())
            ->method('getValue')
            ->willReturn('someOtherEngine');

        $this->assertFalse($this->helper->isEngineEnabled());
    }

    public function testExportImagesAndDeeplinks()
    {
        $this->config
            ->expects($this->once())
            ->method('getValue')
            ->willReturn('textValue');

        $this->assertInternalType('bool', $this->helper->exportImagesAndDeeplinks());
    }

    public function testRemoveTagsOnExport()
    {
        $this->config
            ->expects($this->once())
            ->method('getValue')
            ->willReturn('textValue');

        $this->assertInternalType('bool', $this->helper->removeTagsOnExport());
    }

    public function testGetExportImageType()
    {
        $this->config
            ->expects($this->once())
            ->method('getValue')
            ->willReturn('base_image');

        $this->assertNotEmpty($this->helper->getExportImageType());
    }

    public function testGetExportImageSize()
    {
        $this->config
            ->expects($this->once())
            ->method('getValue')
            ->willReturn('300x300');

        $this->assertInternalType('integer', $this->helper->getExportImageSize());
    }

}
