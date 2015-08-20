<?php
/**
 * Csv.php
 *
 * @category Mage
 * @package magento2
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
 * @license GPL
 * @link http://www.flagbit.de
 */
namespace Flagbit\FACTFinder\Model\Export\Adapter;

use \Magento\Framework\App\Filesystem\DirectoryList;

class Csv extends \Magento\ImportExport\Model\Export\Adapter\Csv
{

    /**
     * Constructor
     *
     * @param \Magento\Framework\Filesystem $filesystem
     * @param string|null $destination
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(\Magento\Framework\Filesystem $filesystem, $destination = null)
    {
        $this->_directoryHandle = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);

        if (!$destination) {
            $destination = uniqid('importexport_');
            $this->_directoryHandle->touch($destination);
        }

        if (!is_string($destination)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The destination file path must be a string.')
            );
        }

        if (!$this->_directoryHandle->isWritable()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The destination directory is not writable.'));
        }

        if ($this->_directoryHandle->isFile($destination) && !$this->_directoryHandle->isWritable($destination)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Destination file is not writable'));
        }

        $this->_destination = $destination;

        $this->_init();
    }
}