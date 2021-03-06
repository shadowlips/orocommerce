<?php

namespace Oro\Bundle\ShippingBundle\Tests\Unit\Form\Type;

use Genemu\Bundle\FormBundle\Form\JQuery\Type\Select2Type;
use Oro\Bundle\AddressBundle\Form\Type\CountryType;
use Oro\Bundle\AddressBundle\Form\Type\RegionType;
use Oro\Bundle\CurrencyBundle\Form\Type\CurrencySelectionType;
use Oro\Bundle\CurrencyBundle\Provider\CurrencyProviderInterface;
use Oro\Bundle\CurrencyBundle\Rounding\RoundingServiceInterface;
use Oro\Bundle\CurrencyBundle\Utils\CurrencyNameHelper;
use Oro\Bundle\FormBundle\Form\Extension\AdditionalAttrExtension;
use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Oro\Bundle\RuleBundle\Entity\Rule;
use Oro\Bundle\ShippingBundle\Entity\ShippingMethodConfig;
use Oro\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRule;
use Oro\Bundle\ShippingBundle\Entity\ShippingMethodTypeConfig;
use Oro\Bundle\ShippingBundle\Form\Type\ShippingMethodConfigCollectionType;
use Oro\Bundle\ShippingBundle\Form\Type\ShippingMethodConfigType;
use Oro\Bundle\ShippingBundle\Form\Type\ShippingMethodsConfigsRuleDestinationType;
use Oro\Bundle\ShippingBundle\Form\Type\ShippingMethodsConfigsRuleType;
use Oro\Bundle\ShippingBundle\Form\Type\ShippingMethodTypeConfigCollectionType;
use Oro\Bundle\ShippingBundle\Method\ShippingMethodRegistry;
use Oro\Bundle\ShippingBundle\Provider\ShippingMethodChoicesProviderInterface;
use Oro\Bundle\ShippingBundle\Tests\Unit\Form\EventSubscriber\MethodConfigCollectionSubscriberProxy;
use Oro\Bundle\ShippingBundle\Tests\Unit\Form\EventSubscriber\MethodConfigSubscriberProxy;
use Oro\Bundle\ShippingBundle\Tests\Unit\Form\EventSubscriber\MethodTypeConfigCollectionSubscriberProxy;
use Oro\Bundle\ShippingBundle\Tests\Unit\Provider\Stub\ShippingMethodProviderStub;
use Oro\Bundle\ShippingBundle\Validator\Constraints\EnabledTypeConfigsValidationGroup;
use Oro\Bundle\ShippingBundle\Validator\Constraints\EnabledTypeConfigsValidationGroupValidator;
use Oro\Bundle\ShippingBundle\Validator\Constraints\ShippingRuleEnable;
use Oro\Bundle\ShippingBundle\Validator\Constraints\ShippingRuleEnableValidator;
use Oro\Bundle\TranslationBundle\Form\Type\TranslatableEntityType;
use Oro\Component\Testing\Unit\Form\EventListener\Stub\AddressCountryAndRegionSubscriberStub;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Translation\TranslatorInterface;

class ShippingMethodsConfigsRuleTypeTest extends FormIntegrationTestCase
{
    /**
     * @var ShippingMethodsConfigsRuleType
     */
    protected $formType;

    /**
     * @var MethodTypeConfigCollectionSubscriberProxy
     */
    protected $methodTypeConfigCollectionSubscriber;

    /**
     * @var MethodConfigCollectionSubscriberProxy
     */
    protected $methodConfigCollectionSubscriber;

    /**
     * @var MethodConfigSubscriberProxy
     */
    protected $methodConfigSubscriber;

    /**
     * @var ShippingMethodRegistry
     */
    protected $methodRegistry;

    /**
     * @var ShippingMethodChoicesProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $choicesProvider;

    protected function setUp()
    {
        $this->methodRegistry = new ShippingMethodRegistry();
        $this->methodRegistry->addProvider(new ShippingMethodProviderStub());
        $this->methodTypeConfigCollectionSubscriber = new MethodTypeConfigCollectionSubscriberProxy();
        $this->methodConfigSubscriber = new MethodConfigSubscriberProxy();
        $this->methodConfigCollectionSubscriber = new MethodConfigCollectionSubscriberProxy();
        parent::setUp();
        $this->methodTypeConfigCollectionSubscriber
            ->setFactory($this->factory)->setMethodRegistry($this->methodRegistry);
        $this->methodConfigSubscriber->setFactory($this->factory)->setMethodRegistry($this->methodRegistry);
        $this->methodConfigCollectionSubscriber
            ->setFactory($this->factory)->setMethodRegistry($this->methodRegistry);

        /** @var TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject $translator */
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects(static::any())
            ->method('trans')
            ->will(static::returnCallback(function ($message) {
                return $message.'_translated';
            }));

        $this->choicesProvider = $this->createMock(ShippingMethodChoicesProviderInterface::class);

        $this->formType = new ShippingMethodsConfigsRuleType(
            $this->choicesProvider,
            $translator
        );
    }

    public function testGetBlockPrefix()
    {
        $this->assertEquals(ShippingMethodsConfigsRuleType::BLOCK_PREFIX, $this->formType->getBlockPrefix());
    }

    /**
     * @dataProvider submitDataProvider
     *
     * @param array|null $data
     */
    public function testSubmit($data)
    {
        $form = $this->factory->create($this->formType, $data);

        $this->assertSame($data, $form->getData());

        $form->submit([
            'rule' => [
                'name' => 'new rule',
                'sortOrder' => '1',
            ],
            'currency' => 'USD',
            'methodConfigs' => [
                [
                    'method' => ShippingMethodProviderStub::METHOD_IDENTIFIER,
                    'options' => [],
                    'typeConfigs' => [
                        [
                            'enabled' => true,
                            'type' => ShippingMethodProviderStub::METHOD_TYPE_IDENTIFIER,
                            'options' => [
                                'price' => 12,
                                'type' => 'per_item',
                            ],
                        ]
                    ]
                ]
            ]
        ]);

        $shippingRule = (new ShippingMethodsConfigsRule())
            ->setRule(
                (new Rule())
                    ->setName('new rule')
                    ->setSortOrder(1)
                    ->setEnabled(false)
            )
            ->setCurrency('USD')
            ->addMethodConfig(
                (new ShippingMethodConfig())
                    ->setMethod(ShippingMethodProviderStub::METHOD_IDENTIFIER)
                    ->setOptions([])
                    ->addTypeConfig(
                        (new ShippingMethodTypeConfig())
                            ->setEnabled(true)
                            ->setType(ShippingMethodProviderStub::METHOD_TYPE_IDENTIFIER)
                            ->setOptions([
                                'price' => 12,
                                'type' => 'per_item',
                            ])
                    )
            );

        $this->assertTrue($form->isValid());
        $this->assertEquals($shippingRule, $form->getData());
    }

    /**
     * @return array
     */
    public function submitDataProvider()
    {
        return [
            [new ShippingMethodsConfigsRule()],
            [
                (new ShippingMethodsConfigsRule())
                    ->setRule(
                        (new Rule())
                            ->setName('old name')
                            ->setSortOrder(0)
                    )
                    ->setCurrency('EUR')
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidators()
    {
        $constraint = new EnabledTypeConfigsValidationGroup();

        return [
            $constraint->validatedBy() => new EnabledTypeConfigsValidationGroupValidator(),
            (new ShippingRuleEnable())->validatedBy() => $this->createMock(ShippingRuleEnableValidator::class),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions()
    {
        $roundingService = $this->createMock(RoundingServiceInterface::class);
        $roundingService->expects($this->any())
            ->method('getPrecision')
            ->willReturn(4);
        $roundingService->expects($this->any())
            ->method('getRoundType')
            ->willReturn(RoundingServiceInterface::ROUND_HALF_UP);

        $currencyProvider = $this->getMockBuilder(CurrencyProviderInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $currencyProvider->expects($this->any())
            ->method('getCurrencyList')
            ->willReturn(['USD']);

        /** @var \PHPUnit_Framework_MockObject_MockObject|TranslatableEntityType $registry */
        $translatableEntity = $this->getMockBuilder('Oro\Bundle\TranslationBundle\Form\Type\TranslatableEntityType')
            ->setMethods(['setDefaultOptions', 'buildForm'])
            ->disableOriginalConstructor()
            ->getMock();

        return [
            new PreloadedExtension(
                [
                    ShippingMethodConfigCollectionType::class
                    => new ShippingMethodConfigCollectionType($this->methodConfigCollectionSubscriber),
                    ShippingMethodConfigType::class
                    => new ShippingMethodConfigType($this->methodConfigSubscriber, $this->methodRegistry),
                    ShippingMethodTypeConfigCollectionType::class =>
                        new ShippingMethodTypeConfigCollectionType($this->methodTypeConfigCollectionSubscriber),
                    CurrencySelectionType::NAME => new CurrencySelectionType(
                        $currencyProvider,
                        $this->getMockBuilder(LocaleSettings::class)->disableOriginalConstructor()->getMock(),
                        $this->getMockBuilder(CurrencyNameHelper::class)->disableOriginalConstructor()->getMock()
                    ),
                    CollectionType::NAME => new CollectionType(),
                    ShippingMethodsConfigsRuleDestinationType::NAME => new ShippingMethodsConfigsRuleDestinationType(
                        new AddressCountryAndRegionSubscriberStub()
                    ),
                    'oro_country' => new CountryType(),
                    'genemu_jqueryselect2_translatable_entity' => new Select2Type('translatable_entity'),
                    'translatable_entity' => $translatableEntity,
                    'oro_region' => new RegionType(),
                ],
                ['form' => [new AdditionalAttrExtension()]]
            ),
            $this->getValidatorExtension(true)
        ];
    }
}
