<?php
namespace Flagbit\FACTFinder\Controller\Export;

use \Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Webapi\Exception;

class Schedule extends \Flagbit\FACTFinder\Controller\Export
{
    /**
     * @var \Magento\Cron\Model\Schedule
     */
    protected $_schedule;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_timezone;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(\Magento\Framework\App\Action\Context $context)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $this->_schedule = $objectManager->get('\Magento\Cron\Model\Schedule');
        $this->_timezone = $objectManager->get('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');

        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $this->_schedule
                ->setJobCode('factfinder_product_export')
                ->setCreatedAt($this->_timezone->scopeTimeStamp())
                ->setScheduledAt($this->_timezone->scopeTimeStamp() + 60)
                ->setStatus(\Magento\Cron\Model\Schedule::STATUS_PENDING)
                ->save();
            $this->messageManager->addSuccess('Export was successfully scheduled', 'backend');
        } catch (\Exception $e) {
            $this->messageManager->addError('There was an error in scheduling the export', 'backend');
        } finally {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

            return $resultRedirect->setRefererOrBaseUrl();
        }

    }
}