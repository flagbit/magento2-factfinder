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
namespace Flagbit\FACTFinder\Model\Export;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Catalog\Model\Resource\Category;
use Magento\Catalog\Model\Resource\Product\Attribute;

class Product
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_products;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $_filterBuilder;

    /**
     * @var \Magento\Catalog\Model\Resource\Category\CollectionFactory
     */
    protected $_categoryColFactory;

    protected $_attributeColFactory;

    /**
     * @var array
     */
    protected $_defaultFields = [
        'id',
        'parent_id',
        'sku',
        'category',
    ];

    protected $_requiredAttributes = [
        'name',
        'description',
        'short_description',
        'price'
    ];

    /**
     * @var array
     */
    protected $_attributeFields = [
        'filterable_attributes',
        'searchable_attributes',
        'numerical_attributes',
    ];

    /**
     * @var array
     */
    protected $_categoryPaths;

    protected $_filterableAttributes = [];

    protected $_searchableAttributes = [];

    protected $_numericalAttributes = [];

    protected $_attributeOptionLabels;


    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface                     $productRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder                        $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder                                $filterBuilder
     * @param \Magento\Catalog\Model\Resource\Category\CollectionFactory          $categoryColFactory
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $attributeColFactory
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        Category\CollectionFactory $categoryColFactory,
        Attribute\CollectionFactory $attributeColFactory
    ) {
        $this->_products = $productRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_filterBuilder = $filterBuilder;
        $this->_categoryColFactory = $categoryColFactory;
        $this->_attributeColFactory = $attributeColFactory;
    }


    /**
     * @return string[]
     */
    public function export()
    {
        $filters = [];

        $filters[] = $this->_filterBuilder
            ->setField(\Magento\Catalog\Model\Product::VISIBILITY)
            ->setValue(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
            ->setConditionType('eq')
            ->create()
        ;

        $searchCriteria = $this->_searchCriteriaBuilder->addFilters($filters)
            ->create();

        $products = $this->_products->getList($searchCriteria)
            ->getItems();

        $result = [];
        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($products as $product) {
            $row = [
                'id'                    => $product->getEntityId(),
                'parent_id'             => $product->getEntityId(), //this might be sku as well, needs to me configured
                'sku'                   => $product->getSku(),
                'category'              => $this->_getCategoryPath($product),
                'filterable_attributes' => $this->_getFilterableAttributeValues($product),
                'searchable_attributes' => $this->_getSearchableAttributeValues($product),
                'numerical_attributes'  => $this->_getNumericalAttributeValues($product),
                'name'                  => $product->getName(),
                'description'           => $product->getDescription(),
                'short_description'     => $product->getShortDescription(),
                'price'                 => $product->getPrice(),
            ];
            // todo: additional attributes

            $result[] = $this->_formatRowValues($row);
        }

        return $result;
    }


    /**
     * @param string[] $row
     *
     * @return string[]
     */
    protected function _formatRowValues(array $row)
    {
        foreach ($row as $key => $value) {
            $row[$key] = $this->_sanitizeValue($value);
        }

        return $row;
    }


    /**
     * Get header for the csv file
     *
     * @return array
     */
    public function getHeader()
    {
        return array_merge(
            $this->_defaultFields,
            $this->_attributeFields,
            $this->_requiredAttributes,
            $this->_getAdditionalAttributeCodes()
        );
    }


    /**
     * Sanitize values in the certain way
     *
     * @param $value
     *
     * @return string
     */
    protected function _sanitizeValue($value)
    {
        // Add spaces before HTML Tags, so that strip_tags() does not join word
        $value = preg_replace('/</u', ' <', $value);
        $value = preg_replace("#\s+#siu", ' ', trim(strip_tags($value)));

        $value = addslashes($value);

        // remove html entities
        $value = preg_replace("/&(?:[a-z\d]|#\d|#x[a-f\d]){2,8};/i", '', $value);

        return $value;
    }


    /**
     * Get attributes used in search
     *
     * @return array
     */
    protected function _getSearchableAttributes()
    {
        if (empty($this->_searchableAttributes)) {
            $collection = $this->_attributeColFactory->create();
            /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
            foreach ($collection as $attribute) {
                if ($attribute->getIsSearchable()
                    && !in_array($attribute->getAttributeCode(), $this->_requiredAttributes)
                    && $attribute->getIsUserDefined()
                    && !in_array($attribute->getAttributeCode(), array_keys($this->_getNumericalAttributes()))
                ) {
                    $this->_searchableAttributes[$attribute->getAttributeCode()] = $attribute;
                }
            }
        }

        return $this->_searchableAttributes;
    }


    /**
     * Get searchable attribute values string for a product
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return string
     */
    protected function _getSearchableAttributeValues($product)
    {
        $result = [];
        /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
        foreach ($this->_getSearchableAttributes() as $attribute) {
            $values = $this->_getAttributeValue($product, $attribute);
            foreach ($values as $value) {
                $result[] = $value;
            }
        }

        return implode(',', $result);
    }


    /**
     * Get a sting with path of all product categories
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return string
     */
    protected function _getCategoryPath($product)
    {
        if ($this->_categoryPaths === null) {
            $this->_initCategories();
        }

        $paths = [];
        /** @var \Magento\Catalog\Model\Category $category */
        foreach ($product->getCategoryCollection() as $category) {
            if (isset($this->_categoryPaths[$category->getId()])) {
                $paths[] = $this->_categoryPaths[$category->getId()];
            }
        }

        return implode('|', $paths);
    }


    /**
     * @return array
     */
    protected function _getAdditionalAttributeCodes()
    {
        $result = [];
//        foreach ($this->_getAdditionalAttributes() as $attribute) {
//            $result[] = $attribute->getCode();
//        }

        return $result;
    }


    /**
     * Retrieves attributes that should be exported separately
     *
     * @return array
     */
    protected function _getAdditionalAttributes()
    {
        return [];
    }


    /**
     * Initialize categories ID to text-path hash.
     *
     * @return $this
     */
    protected function _initCategories()
    {
        $collection = $this->_categoryColFactory->create()->addNameToResult();
        /* @var $collection \Magento\Catalog\Model\Resource\Category\Collection */
        foreach ($collection as $category) {
            $structure = preg_split('#/+#', $category->getPath());
            $path = [];
            foreach ($structure as $pathId) {
                $path[] = $collection->getItemById($pathId)->getName();
            }

            $this->_categoryPaths[$category->getId()] = implode('/', $path);
        }

        return $this;
    }


    /**
     * Get filterable attribute values string for a product
     *
     * @param $product
     *
     * @return string
     */
    protected function _getFilterableAttributeValues($product)
    {
        $result = [];
        /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
        foreach ($this->_getFilterableAttributes() as $attribute) {
            $code = $attribute->getAttributeCode();
            $values = $this->_getAttributeValue($product, $attribute);
            foreach ($values as $value) {
                $result[] = $code . '=' . $value;
            }
        }

        return implode('|', $result);
    }


    /**
     * Get all attributes that can be used as filters
     *
     * @return array
     */
    protected function _getFilterableAttributes()
    {
        if (empty($this->_filterableAttributes)) {
            $collection = $this->_attributeColFactory->create();
            /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
            foreach ($collection as $attribute) {
                if ($attribute->getIsFilterable()
                    && !in_array($attribute->getAttributeCode(), $this->_requiredAttributes)
                    && !in_array($attribute->getAttributeCode(), array_keys($this->_getSearchableAttributes()))
                ) {
                    $this->_filterableAttributes[$attribute->getAttributeCode()] = $attribute;
                }
            }
        }

        return $this->_filterableAttributes;
    }


    /**
     * Get all numerical attributes
     *
     * @return array
     */
    protected function _getNumericalAttributes()
    {
        if (empty($this->_numericalAttributes)) {
            $collection = $this->_attributeColFactory->create();
            /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
            foreach ($collection as $attribute) {
                if ($attribute->getBackendType() == 'decimal'
                    && $attribute->getIsFilterable()
                    && !in_array($attribute->getAttributeCode(), $this->_requiredAttributes)
                ) {
                    $this->_numericalAttributes[$attribute->getAttributeCode()] = $attribute;
                }
            }
        }

        return $this->_numericalAttributes;
    }


    /**
     * Get numerical attribute values string for a product
     *
     * @param $product
     *
     * @return string
     */
    protected function _getNumericalAttributeValues($product)
    {
        $result = [];
        /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
        foreach ($this->_getNumericalAttributes() as $attribute) {
            $values = $this->_getAttributeValue($product, $attribute);
            foreach ($values as $value) {
                $result[] = $attribute->getAttributeCode() . '=' . round($value, 2);
            }
        }

        return implode('|', $result);
    }


    /**
     * Retrieve attribute value from a product
     *
     * @param \Magento\Catalog\Model\Product                        $product
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     *
     * @return array
     */
    protected function _getAttributeValue($product, $attribute)
    {
        $code = $attribute->getAttributeCode();
        $result = [];

        // select, multiselect
        if (in_array($attribute->getBackendType(), ['int', 'varchar'])) {
            if (!isset($this->_attributeOptionLabels[$code])) {
                $this->_loadAttributeValues($attribute);
            }

            $values = explode(',', $product->getData($code));
            foreach ($values as $value) {
                if (isset($this->_attributeOptionLabels[$code][$value])) {
                    $result[] = $this->_attributeOptionLabels[$code][$value];
                }
            }
        } else {
            $result[] = $product->getData($code);
        }

        return $result;
    }


    /**
     * Load all option values for an attribute to the cache variable
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     *
     * @return $this
     */
    protected function _loadAttributeValues($attribute)
    {
        $code = $attribute->getAttributeCode();
        $options = $attribute->getOptions();
        foreach ($options as $option) {
            if ($option->getValue()) {
                $this->_attributeOptionLabels[$code][$option->getValue()] = $option->getLabel();
            }
        }

        return $this;
    }


}