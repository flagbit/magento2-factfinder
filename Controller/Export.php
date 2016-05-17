<?php
namespace Flagbit\FACTFinder\Controller;

class Export extends \Magento\Framework\App\Action\Action
{
    const XML_AUTH_PASSWORD_PATH = 'factfinder/search/auth_password';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(\Magento\Framework\App\Action\Context $context)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        parent::__construct($context);
    }

    public function preDispatch()
    {
        $password = md5($this->_scopeConfig->getValue(self::XML_AUTH_PASSWORD_PATH));

        if ($password == '' || $password != $this->getRequest()->getParam('key')) {
            $this->getActionFlag()->set('', self::FLAG_NO_DISPATCH, true);
        }
    }

    public function execute()
    {

    }
}
