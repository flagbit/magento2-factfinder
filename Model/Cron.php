<?php
/**
 * Cron.php
 *
 * @category Mage
 * @package magento2
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
 * @license GPL
 * @link http://www.flagbit.de
 */

namespace Flagbit\FACTFinder\Model;
require_once __DIR__ . '/../../../../../app/autoload.php';

class Cron
{
    /**
     * @var \Flagbit\FACTFinder\Model\Export\Product
     */
    protected $_products;

    /**
     * @var \Flagbit\FACTFinder\Model\Export\Adapter\Csv
     */
    protected $_csv;


    /**
     * @param \Flagbit\FACTFinder\Model\Export\Product $products
     * @param \Flagbit\FACTFinder\Model\Export\Adapter\Csv $csv
     */
    public function __construct(
        Export\Product $products,
        Export\Adapter\Csv $csv
    ) {
        $this->_products = $products;
        $this->_csv = $csv;
    }


    public function runExport()
    {
        $data = $this->_products->export();

        if (!count($data)) {
            return;
        }

        $this->_csv->setHeaderCols($this->_products->getHeader());
        foreach ($data as $row) {
            $this->_csv->writeRow($row);
        }
    }


}
