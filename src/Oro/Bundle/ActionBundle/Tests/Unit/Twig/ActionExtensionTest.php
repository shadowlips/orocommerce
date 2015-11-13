<?php

namespace Oro\Bundle\ActionBundle\Tests\Unit\Twig;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

use Oro\Bundle\ActionBundle\Model\ActionManager;
use Oro\Bundle\ActionBundle\Twig\ActionExtension;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class ActionExtensionTest extends \PHPUnit_Framework_TestCase
{
    const ROUTE = 'test_route';

    /** @var \PHPUnit_Framework_MockObject_MockObject|ActionManager */
    protected $actionManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject|DoctrineHelper */
    protected $doctrineHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject|RequestStack */
    protected $requestStack;

    /** @var ActionExtension */
    protected $extension;

    protected function setUp()
    {
        $this->actionManager = $this->getMockBuilder('Oro\Bundle\ActionBundle\Model\ActionManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestStack = $this->getMockBuilder('Symfony\Component\HttpFoundation\RequestStack')
            ->disableOriginalConstructor()
            ->getMock();

        $this->extension = new ActionExtension($this->actionManager, $this->doctrineHelper, $this->requestStack);
    }

    protected function tearDown()
    {
        unset($this->extension, $this->actionManager, $this->doctrineHelper, $this->requestStack);
    }

    public function testGetName()
    {
        $this->assertEquals(ActionExtension::NAME, $this->extension->getName());
    }

    public function testGetFunctions()
    {
        $functions = $this->extension->getFunctions();
        $this->assertCount(2, $functions);

        $expectedFunctions = [
            'oro_action_widget_parameters' => true,
            'has_actions' => false,
        ];

        /** @var \Twig_SimpleFunction $function */
        foreach ($functions as $function) {
            $this->assertInstanceOf('\Twig_SimpleFunction', $function);
            $this->assertArrayHasKey($function->getName(), $expectedFunctions);
            $this->assertEquals($expectedFunctions[$function->getName()], $function->needsContext());
        }
    }

    /**
     * @dataProvider getWidgetParametersDataProvider
     *
     * @param array $context
     * @param array $expected
     */
    public function testGetWidgetParameters(array $context, array $expected)
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Request $request */
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $request->expects($this->once())
            ->method('get')
            ->with('_route')
            ->willReturn(self::ROUTE);

        $this->requestStack->expects($this->once())
            ->method('getMasterRequest')
            ->willReturn($request);

        if (array_key_exists('entity', $context)) {
            $this->doctrineHelper->expects($this->once())
                ->method('getEntityIdentifier')
                ->with($context['entity'])
                ->willReturn(['id' => 42]);
        }

        $result = $this->extension->getWidgetParameters($context);

        $this->assertInternalType('array', $result);

        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $result);
            $this->assertEquals($value, $result[$key]);
        }
    }

    /**
     * @return array
     */
    public function getWidgetParametersDataProvider()
    {
        return [
            [
                'context' => [],
                'expected' => ['route' => self::ROUTE],
            ],
            [
                'context' => ['entity_class' => '\stdClass'],
                'expected' => ['route' => self::ROUTE, 'entityClass' => '\stdClass'],
            ],
            [
                'context' => ['entity' => new \stdClass],
                'expected' => ['route' => self::ROUTE, 'entityClass' => 'stdClass', 'entityId' => ['id' => 42]],
            ],
            [
                'context' => ['entity' => new \stdClass, 'entity_class' => 'testClass'],
                'expected' => ['route' => self::ROUTE, 'entityClass' => 'stdClass', 'entityId' => ['id' => 42]],
            ],
        ];
    }

    /**
     * @dataProvider hasActionsDataProvider
     *
     * @param bool $result
     */
    public function testHasActions($result)
    {
        $params = ['test_param' => 'test_param_value'];

        $this->actionManager->expects($this->once())
            ->method('hasActions')
            ->with($params)
            ->willReturn($result);

        $this->assertEquals($result, $this->extension->hasActions($params));
    }

    /**
     * @return array
     */
    public function hasActionsDataProvider()
    {
        return [
            [true],
            [false],
        ];
    }
}
