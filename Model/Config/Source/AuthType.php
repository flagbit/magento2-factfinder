<?php
/**
 * E-Commerce Kickstarter for Magento Enterprise
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Flagbit Kickstarter License
 * that is bundled with this package in the file LICENSE_KICKSTARTER.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.flagbit.de/license/kickstarter
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@flagbit.de so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento and / or
 * the Ecommerce-Kickstarter to newer versions in the future. If you wish to
 * customize Magento for your needs please refer to
 * http://www.magentocommerce.com for more information.
 *
 * PHP Version 5.3+
 *
 * @category  Magento
 * @package   Flagbit_Checkout
 * @author    drlrdsen
 * @copyright 2014 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license   http://www.flagbit.de/license/kickstarter Flagbit Kickstarter License
 * @link      http://www.flagbit.de/
 */
namespace Flagbit\FACTFinder\Model\Config\Source;

class AuthType implements \Magento\Framework\Option\ArrayInterface
{


    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'http', 'label' => __('HTTP')],
            ['value' => 'simple', 'label' => __('Simple')],
            ['value' => 'advanced', 'label' => __('Advanced')],
        ];
    }


}
