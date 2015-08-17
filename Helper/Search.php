<?php
namespace Flagbit\FACTFinder\Helper;

class Search
{
    /**
     * Parent layout of the block
     *
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * Toolbar Block
     *
     * @var \Magento\Catalog\Block\Product\ProductList\Toolbar
     */
    protected $_toolbarBlock;

    /**
     * @param \Magento\Framework\View\Element\Context $context
     */
    public function __construct(\Magento\Framework\View\Element\Context $context)
    {
        $this->_layout = $context->getLayout();
        $this->_toolbarBlock = $this->_layout->getBlock('product_list_toolbar');
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->_toolbarBlock->getCurrentPage();
    }

    /**
     * @return string
     */
    public function getLimitPerPage()
    {
        return $this->_toolbarBlock->getLimit();
    }
}