<?php
/**
 * Product.php
 *
 * @category Mage
 * @package magento2
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
 * @license GPL
 * @link http://www.flagbit.de
 */
namespace Flagbit\FACTFinder\Model\Export\Resource;

use Magento\Framework\Api;
use Magento\Catalog;

class Product
{

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $_repository;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $_configurable;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $_filterBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @var \Flagbit\FACTFinder\Helper\Config
     */
    protected $_config;


    /**
     * @param \Magento\Catalog\Model\ProductRepository                     $repository
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable
     * @param \Magento\Framework\Api\FilterBuilder                         $filterBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder                 $searchCriteriaBuilder
     * @param \Magento\Catalog\Helper\Image                                $imageHelper
     * @param \Flagbit\FACTFinder\Helper\Config                            $config
     */
    public function __construct(
        Catalog\Model\ProductRepository $repository,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        Api\FilterBuilder $filterBuilder,
        Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        Catalog\Helper\Image $imageHelper,
        \Flagbit\FACTFinder\Helper\Config $config
    ) {
        $this->_repository = $repository;
        $this->_configurable = $configurable;
        $this->_filterBuilder = $filterBuilder;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_imageHelper = $imageHelper;
        $this->_config = $config;
    }


    /**
     * Get list of products for export
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function getProductList()
    {
        $filters = [];

        $filters[] = $this->_filterBuilder
            ->setField(\Magento\Catalog\Model\Product::VISIBILITY)
            ->setValue(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
            ->setConditionType('eq')
            ->create();

        $searchCriteria = $this->_searchCriteriaBuilder->addFilters($filters)
            ->create();

        $products = $this->_repository->getList($searchCriteria)
            ->getItems();

        return $products;
    }


    /**
     * Get list children products for a configurable product
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return array|\Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function getChildrenProducts($product)
    {
        if ($product->getTypeId() !== 'configurable') {
            return [];
        }

        $childrenIds = [];
        foreach ($this->_configurable->getChildrenIds($product->getEntityId()) as $group) {
            $childrenIds = array_merge($childrenIds, $group);
        }

        if (empty($childrenIds)) {
            return [];
        }

        $childrenFilter = $this->_filterBuilder
            ->setField('entity_id')
            ->setValue($childrenIds)
            ->setConditionType('in')
            ->create();
        $childrenSearchCriteria = $this->_searchCriteriaBuilder->addFilters([$childrenFilter])
            ->create();

        $children = $this->_repository->getList($childrenSearchCriteria)
            ->getItems();

        return $children;
    }

    /**
     * Get product deeplink (direct link to product page)
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return string
     */
    public function getProductDeeplink($product)
    {
        return $product->getProductUrl();
    }


    /**
     * Get product image url
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return string
     */
    public function getProductImage($product)
    {
        $type = $this->_config->getExportImageType();

        $image = $this->_imageHelper->init($product, $type);

        if ($this->_config->getExportImageSize() > 0) {
            $image->resize($this->_config->getExportImageSize());
        }

        return (string) $image;
    }


}