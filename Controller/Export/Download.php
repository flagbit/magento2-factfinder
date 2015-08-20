<?php
namespace Flagbit\FACTFinder\Controller\Export;

use \Magento\Framework\App\Filesystem\DirectoryList;
use \Magento\Framework\Controller\ResultFactory;

class Download extends \Flagbit\FACTFinder\Controller\Export
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(\Magento\Framework\App\Action\Context $context)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $this->_filesystem = $objectManager->get('\Magento\Framework\Filesystem');
        $this->_fileFactory = $objectManager->get('\Magento\Framework\App\Response\Http\FileFactory');

        parent::__construct($context);
    }

    public function execute()
    {
        $fileName = 'products.csv';
        $filePath = $this->_filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath('factfinder') . '/';

        if (!file_exists($filePath . $fileName)) {
            /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
            $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);
            $resultRaw->setContents('No export file was generated yet. Please trigger an export through magento\'s backend.');
            return $resultRaw;
        }

        return $this->_fileFactory->create(
            $fileName,
            [
                'type' => 'filename',
                'value' => 'factfinder/' . $fileName,
                'rm' => false
            ],
            DirectoryList::VAR_DIR
        );
    }
}