<?php
/**
 * CategoryCollection.php
 *
 * @category Mage
 * @package magento2
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
 * @license GPL
 * @link http://www.flagbit.de
 */

namespace Flagbit\FACTFinder\Model\Export\Resource;

class Category
{
    /**
     * @var \Magento\Catalog\Model\Resource\Category\CollectionFactory
     */
    protected $_collection;

    /**
     * @var array
     */
    protected $_categoryPaths;

    /**
     * @param \Magento\Catalog\Model\Resource\Category\CollectionFactory $colFactory
     */
    public function __construct(
        \Magento\Catalog\Model\Resource\Category\CollectionFactory $colFactory
    ) {
        $this->_collection = $colFactory;
    }


    /**
     * Get a sting with path of all product categories
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return string
     */
    public function getCategoryPath($product)
    {
        if ($this->_categoryPaths === null) {
            $this->_initCategories();
        }

        $paths = [];
        /** @var \Magento\Catalog\Model\Category $category */

        foreach ($product->getCategoryIds() as $categoryId) {
            if (isset($this->_categoryPaths[$categoryId])) {
                $paths[] = $this->_categoryPaths[$categoryId];
            }
        }

        return implode('|', $paths);
    }


    /**
     * Initialize categories ID to text-path hash.
     *
     * @return $this
     */
    protected function _initCategories()
    {
        $collection = $this->_collection->create()->addNameToResult();
        /* @var $collection \Magento\Catalog\Model\Category */
        foreach ($collection as $category) {
            $structure = preg_split('#/+#', $category->getPath());
            // remove root and "default" categories
            $structure = array_slice($structure, 2);
            $path = [];
            foreach ($structure as $pathId) {
                $name = $collection->getItemById($pathId)->getName();
                $name = urlencode($name);
                $name = str_replace('+', '%20', $name);
                $path[] = $name;
            }

            $this->_categoryPaths[$category->getId()] = implode('/', $path);
        }

        return $this;
    }


}
