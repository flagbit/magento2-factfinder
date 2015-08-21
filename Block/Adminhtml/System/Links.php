<?php
/**
 * Link.php
 *
 * @category Mage
 * @package magento2
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
 * @license GPL
 * @link http://www.flagbit.de
 */

namespace Flagbit\FACTFinder\Block\Adminhtml\System;

class Links extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * @var string
     */
    protected $_template = 'system/config/links.phtml';

    /**
     * @var \Flagbit\FACTFinder\Helper\Config
     */
    protected $_config;


    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Url                  $url
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Url $url,
        \Flagbit\FACTFinder\Helper\Config $config
    ) {
        parent::__construct($context);

        // By default this block uses backend url builder
        // but we need links for frontend so here we replace it
        $this->_urlBuilder = $url;

        $this->_config = $config;

    }


    /**
     * @inheritdoc
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '<td class="label"><label for="' .
            $element->getHtmlId() .
            '">' .
            $element->getLabel() .
            '</label></td>';
        $html .= $this->toHtml();

        $html .= $this->_renderHint($element);

        return $this->_decorateRowHtml($element, $html);
    }


    /**
     * Returns array of data for rendering links in the template
     *
     * @return array
     */
    public function getLinks()
    {
        $params = [
            'key'   => $this->_config->getPasswordMd5(),
            'store' => $this->getRequest()->getParam('store'),
        ];

        return [
            'schedule' => ['url' => $this->getUrl('factfinder/export/schedule', $params), 'label' => 'Schedule'],
            'download' => ['url' => $this->getUrl('factfinder/export/download', $params), 'label' => 'Download'],
            'link'     => ['url' => $this->getUrl('factfinder/export/get', $params), 'label' => 'Get link'],
        ];
    }


}