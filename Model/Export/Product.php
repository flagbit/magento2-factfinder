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

use Flagbit\FACTFinder\Model\Export\Resource;
use Magento\Catalog\Model\Resource\Product\Attribute;
use Flagbit\FACTFinder\Helper\Config;
use Magento\Catalog\Helper;

class Product
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productResource;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_categoryResource;

    /**
     * @var \Flagbit\FACTFinder\Helper\Config
     */
    protected $_config;

    /**
     * @var \Flagbit\FACTFinder\Model\Export\Resource\Attribute
     */
    protected $_attributeResource;

    /**
     * @var array
     */
    protected $_defaultFields = [
        'id',
        'parent_id',
        'sku',
        'category',
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
     * @param \Flagbit\FACTFinder\Model\Export\Resource\Product   $productRepository
     * @param \Flagbit\FACTFinder\Model\Export\Resource\Category  $categoryCollection
     * @param \Flagbit\FACTFinder\Model\Export\Resource\Attribute $attributeColFactory
     * @param \Flagbit\FACTFinder\Helper\Config                   $config
     */
    public function __construct(
        Resource\Product $productRepository,
        Resource\Category $categoryCollection,
        Resource\Attribute $attributeColFactory,
        Config $config
    ) {
        $this->_productResource = $productRepository;
        $this->_categoryResource = $categoryCollection;
        $this->_attributeResource = $attributeColFactory;
        $this->_config = $config;
    }


    /**
     * @return string[]
     */
    public function export()
    {
        $result = [];
        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($this->_productResource->getProductList() as $product) {
            $result[] = $this->_buildExportRow($product);
            foreach ($this->_productResource->getChildrenProducts($product) as $child) {
                $child->setParentId($product->getEntityId());
                $result[] = $this->_buildExportRow($child);
            }
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
        $columns = array_merge(
            $this->_defaultFields,
            $this->_attributeFields,
            $this->_attributeResource->getRequiredAttributes(),
            $this->_attributeResource->getAdditionalAttributeCodes()
        );

        if ($this->_config->exportImagesAndDeeplinks()) {
            $columns = array_merge($columns, ['image', 'deeplink']);
        }

        return $columns;
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
     * Compose csv row for export
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return array
     */
    protected function _buildExportRow($product)
    {
        $row = [
            'id'                    => $product->getEntityId(),
            'parent_id'             => $product->getParentId() ? $product->getParentId() : $product->getEntityId(),
            'sku'                   => $product->getSku(),
            'category'              => $this->_categoryResource->getCategoryPath($product),
            'filterable_attributes' => $this->_getFilterableAttributeValues($product),
            'searchable_attributes' => $this->_getSearchableAttributeValues($product),
            'numerical_attributes'  => $this->_getNumericalAttributeValues($product),
            'name'                  => $product->getName(),
            'description'           => $product->getDescription(),
            'short_description'     => $product->getShortDescription(),
            'price'                 => $product->getPrice(),
        ];

        if ($this->_config->exportImagesAndDeeplinks() && !$product->getParentId()) {
            $row['image'] = $this->_productResource->getProductImage($product);
            $row['deeplink'] = $this->_productResource->getProductDeeplink($product);
        }

        return $this->_formatRowValues($row);
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
        foreach ($this->_attributeResource->getSearchableAttributes() as $attribute) {
            $values = $this->_attributeResource->getAttributeValue($product, $attribute);
            foreach ($values as $value) {
                $result[] = $value;
            }
        }

        return implode(',', $result);
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
        foreach ($this->_attributeResource->getNumericalAttributes() as $attribute) {
            $values = $this->_attributeResource->getAttributeValue($product, $attribute);
            foreach ($values as $value) {
                $result[] = $attribute->getAttributeCode() . '=' . round($value, 2);
            }
        }

        return implode('|', $result);
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
        foreach ($this->_attributeResource->getFilterableAttributes() as $attribute) {
            $code = $attribute->getAttributeCode();
            $values = $this->_attributeResource->getAttributeValue($product, $attribute);
            foreach ($values as $value) {
                $result[] = $code . '=' . $value;
            }
        }

        return implode('|', $result);
    }


}