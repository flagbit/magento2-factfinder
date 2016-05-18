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

namespace Flagbit\FACTFinder\Model\Autocomplete;

use Magento\Search\Model\Autocomplete\DataProviderInterface;

class DataProvider implements DataProviderInterface
{

    /**
     * @var \Flagbit\FACTFinder\Model\Handler\Suggest
     */
    protected $_suggestHandler;

    /**
     * @var \Magento\Search\Model\Autocomplete\ItemFactory
     */
    protected $_itemFactory;


    /**
     * @param \Flagbit\FACTFinder\Model\Handler\Suggest      $handler
     * @param \Magento\Search\Model\Autocomplete\ItemFactory $itemFactory
     */
    public function __construct(
        \Flagbit\FACTFinder\Model\Handler\Suggest      $handler,
        \Magento\Search\Model\Autocomplete\ItemFactory $itemFactory
    ) {
        $this->_suggestHandler = $handler;
        $this->_itemFactory = $itemFactory;
    }


    /**
     * @return \Magento\Search\Model\Autocomplete\ItemInterface[]
     */
    public function getItems()
    {
        /** @var \FACTFinder\Data\SuggestQuery[] $suggestions */
        $suggestions = $this->_suggestHandler->getSuggestions();

        $result = [];
        if (empty($suggestions)) {
            return $result;
        }

        foreach ($suggestions as $suggestion) {
            $attributes = $suggestion->getAttributes();
            $result[] = $this->_itemFactory->create([
                'title' => $suggestion->getLabel(),
                'image' => $suggestion->getImageUrl(),
                'link'  => isset($attributes['deeplink']) ? $attributes['deeplink'] : '',
            ]);
        }

        return $result;
    }
}