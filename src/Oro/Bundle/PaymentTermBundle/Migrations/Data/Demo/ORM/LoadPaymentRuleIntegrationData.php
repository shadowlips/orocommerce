<?php

namespace Oro\Bundle\PaymentTermBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData;
use Oro\Bundle\PaymentBundle\Entity\PaymentMethodConfig;
use Oro\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRule;
use Oro\Bundle\PaymentTermBundle\Entity\PaymentTermSettings;
use Oro\Bundle\PaymentTermBundle\Integration\PaymentTermChannelType;
use Oro\Bundle\RuleBundle\Entity\Rule;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadPaymentRuleIntegrationData extends AbstractFixture implements ContainerAwareInterface
{
    const PAYMENT_TERM_INTEGRATION_CHANNEL_REFERENCE = 'payment_term_integration_channel';

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        if (!$this->container) {
            return;
        }

        $channel = $this->loadIntegration($manager);

        $this->loadShippingRule($manager, $channel);
    }

    /**
     * @param ObjectManager $manager
     *
     * @return Channel
     */
    private function loadIntegration(ObjectManager $manager)
    {
        $label = (new LocalizedFallbackValue())->setString('Payment Term');

        $transport = new PaymentTermSettings();
        $transport->addLabel($label);

        $channel = new Channel();
        $channel->setType(PaymentTermChannelType::TYPE)
            ->setName((string)$label)
            ->setEnabled(true)
            ->setOrganization($this->getOrganization($manager))
            ->setTransport($transport);

        $this->setReference(self::PAYMENT_TERM_INTEGRATION_CHANNEL_REFERENCE, $channel);

        $manager->persist($channel);
        $manager->flush();

        return $channel;
    }

    /**
     * @param ObjectManager $manager
     * @param Channel       $channel
     */
    private function loadShippingRule(ObjectManager $manager, Channel $channel)
    {
        $methodConfig = new PaymentMethodConfig();
        $methodConfig->setType($this->getPaymentTermIdentifier($channel));

        $rule = new Rule();
        $rule->setName('Default')
            ->setEnabled(true)
            ->setSortOrder(1);

        $shippingRule = new PaymentMethodsConfigsRule();

        $shippingRule->setRule($rule)
            ->setOrganization($this->getOrganization($manager))
            ->setCurrency('USD')
            ->addMethodConfig($methodConfig);

        $manager->persist($shippingRule);
        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     *
     * @return Organization|object
     */
    private function getOrganization(ObjectManager $manager)
    {
        if ($this->hasReference(LoadOrganizationAndBusinessUnitData::REFERENCE_DEFAULT_ORGANIZATION)) {
            return $this->getReference(LoadOrganizationAndBusinessUnitData::REFERENCE_DEFAULT_ORGANIZATION);
        } else {
            return $manager
                ->getRepository('OroOrganizationBundle:Organization')
                ->getFirst();
        }
    }

    /**
     * @param Channel $channel
     *
     * @return int|string
     */
    private function getPaymentTermIdentifier(Channel $channel)
    {
        return $this->container
            ->get('oro_payment_term.config.integration_method_identifier_generator')
            ->generateIdentifier($channel);
    }
}
