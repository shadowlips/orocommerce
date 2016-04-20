<?php

namespace OroB2B\Bundle\PaymentBundle\Method\View;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

use OroB2B\Bundle\PaymentBundle\DependencyInjection\Configuration;
use OroB2B\Bundle\PaymentBundle\Entity\PaymentTransaction;
use OroB2B\Bundle\PaymentBundle\Form\Type\CreditCardType;
use OroB2B\Bundle\PaymentBundle\Method\PayflowGateway;
use OroB2B\Bundle\PaymentBundle\PayPal\Payflow\Option\Account;
use OroB2B\Bundle\PaymentBundle\PayPal\Payflow\Response\Response;
use OroB2B\Bundle\PaymentBundle\Provider\PayflowGatewayPaymentTransactionProvider;
use OroB2B\Bundle\PaymentBundle\Traits\ConfigTrait;

class PayflowGatewayView implements PaymentMethodViewInterface
{
    use ConfigTrait;

    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var OptionsResolver */
    private $optionsResolver;

    /** @var PayflowGatewayPaymentTransactionProvider */
    protected $payflowGatewayPaymentTransactionProvider;

    /**
     * @param FormFactoryInterface $formFactory
     * @param ConfigManager $configManager
     * @param PayflowGatewayPaymentTransactionProvider $payflowGatewayPaymentTransactionProvider
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        ConfigManager $configManager,
        PayflowGatewayPaymentTransactionProvider $payflowGatewayPaymentTransactionProvider
    ) {
        $this->formFactory = $formFactory;
        $this->configManager = $configManager;
        $this->payflowGatewayPaymentTransactionProvider = $payflowGatewayPaymentTransactionProvider;
    }

    /** {@inheritdoc} */
    public function getOptions(array $context = [])
    {
        $options = $this->getOptionsResolver()->resolve($context);
        $authorizeTransaction = $this->payflowGatewayPaymentTransactionProvider
            ->getZeroAmountTransaction($options['entity'], $this->getPaymentMethodType());

        $formView = $this->formFactory->create(CreditCardType::NAME)->createView();

        $options = [
            'formView' => $formView,
            'allowedCreditCards' => $this->getAllowedCreditCards(),
        ];

        if ($authorizeTransaction) {
            $options['authorizeTransaction'] = $authorizeTransaction->getId();
            $options['acct'] = $this->getLast4($authorizeTransaction);
        }

        return $options;
    }

    /**
     * @param PaymentTransaction $paymentTransaction
     * @return string|null
     */
    protected function getLast4(PaymentTransaction $paymentTransaction)
    {
        $response = new Response($paymentTransaction->getResponse());

        $acct = $response->getOffset(Account::ACCT);

        return substr($acct, strlen($acct) - 4, 4);
    }

    /** {@inheritdoc} */
    public function getBlock()
    {
        return '_payment_methods_credit_card_widget';
    }

    /** {@inheritdoc} */
    public function getOrder()
    {
        return (int)$this->getConfigValue(Configuration::PAYFLOW_GATEWAY_SORT_ORDER_KEY);
    }

    /** {@inheritdoc} */
    public function getPaymentMethodType()
    {
        return PayflowGateway::TYPE;
    }

    /** {@inheritdoc} */
    public function getLabel()
    {
        return $this->getConfigValue(Configuration::PAYFLOW_GATEWAY_LABEL_KEY);
    }

    /**
     * @return mixed
     */
    public function getAllowedCreditCards()
    {
        return $this->getConfigValue(Configuration::PAYFLOW_GATEWAY_ALLOWED_CC_TYPES_KEY);
    }

    /**
     * @return OptionsResolver
     */
    public function getOptionsResolver()
    {
        if (!$this->optionsResolver) {
            $this->optionsResolver = new OptionsResolver();
            $this->optionsResolver
                ->setRequired('entity')
                ->addAllowedTypes('entity', ['object']);
        }

        return $this->optionsResolver;
    }
}
