<?php
namespace Flagbit\FACTFinder\Controller\Export;

use \Magento\Framework\App\Filesystem\DirectoryList;
use \Magento\Framework\Controller\ResultFactory;

class Get extends \Flagbit\FACTFinder\Controller\Export
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(\Magento\Framework\App\Action\Context $context)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $this->_filesystem = $objectManager->get('\Magento\Framework\Filesystem');

        parent::__construct($context);
    }

    public function execute()
    {
        $fileName = '/factfinder/products.csv';
        $filePath = $this->_filesystem->getDirectoryRead(DirectoryList::VAR_DIR);

        if (!is_file($filePath->getAbsolutePath() . $fileName)) {
            /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
            $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);
            $resultRaw->setContents('No export file was generated yet. Please trigger an export through magento\'s backend.');
            return $resultRaw;
        }

        $fileContent = $filePath->readFile($fileName);

        $this->_response->setHeader('Content-type', 'text/plain', true);

        $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $resultRaw->setContents($fileContent);
        return $resultRaw;
    }
}