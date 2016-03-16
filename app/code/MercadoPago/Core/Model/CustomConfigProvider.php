<?php

namespace MercadoPago\Core\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;

/**
 * Return configs to Standard Method
 *
 * Class StandardConfigProvider
 *
 * @package MercadoPago\Core\Model
 */
class CustomConfigProvider
    implements ConfigProviderInterface
{
    protected $methodInstance;

    protected $methodCode = Custom\Payment::CODE;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    protected $_request;


    protected $_assetRepo;
    protected $_context;

    /**
     * @param PaymentHelper $paymentHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        PaymentHelper $paymentHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Asset\Repository $assetRepo
    )
    {
        $this->_request = $context->getRequest();
        $this->methodInstance = $paymentHelper->getMethodInstance($this->methodCode);
        $this->_scopeConfig = $scopeConfig;
        $this->_checkoutSession = $checkoutSession;
        $this->_storeManager = $storeManager;
        $this->_assetRepo = $assetRepo;
        $this->_context = $context;
    }


    public function getConfig()
    {
        return $this->methodInstance->isAvailable() ? [
            'payment' => [
                $this->methodCode => [
                    'bannerUrl'     => $this->methodInstance->getConfigData('banner_checkout'),
                    'type_checkout' => $this->methodInstance->getConfigData('type_checkout'),
                    'country'       => strtoupper($this->_scopeConfig->getValue('payment/mercadopago/country')),
                    'grand_total' => $this->_checkoutSession->getQuote()->getGrandTotal(),
                    'base_url' =>    $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK),
                    'success_url' =>    $this->_context->getUrl()->getUrl('mercadopago/custom/success', ['_secure' => true]),
                    'logEnabled' => $this->_scopeConfig->getValue('payment/mercadopago/logs'),
                    'discount_coupon' => $this->_scopeConfig->getValue('payment/mercadopago_custom/coupon_mercadopago'),
                    'route' => $this->_request->getRouteName(),
                    'public_key' => $this->methodInstance->getConfigData('public_key'),
                    'customer' => $this->methodInstance->getCustomerAndCards(),
                    'loading_gif' => $this->_assetRepo->getUrl('MercadoPago_Core::images/loading.gif'),
                    'text-currency' => __('$'),
                    'text-choice' => __('Choice'),
                    'default-issuer' => __('Default issuer'),
                    'text-installment' => __('Enter the card number')
                ],
            ],
        ] : [];
    }

}