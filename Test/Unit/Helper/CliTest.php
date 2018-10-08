<?php

namespace ClawRock\SassPreprocessor\Test\Unit\Helper;

use ClawRock\SassPreprocessor\Helper\Cli;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class CliTest extends TestCase
{
    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \Symfony\Component\Console\Input\ArgvInputFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $argvInputFactoryMock;

    /**
     * @var \Symfony\Component\Console\Input\Input|\PHPUnit_Framework_MockObject_MockObject
     */
    private $inputMock;

    /**
     * @var \Symfony\Component\Console\Input\InputDefinition|\PHPUnit_Framework_MockObject_MockObject
     */
    private $inputDefinitionMock;

    /**
     * @var \ClawRock\SassPreprocessor\Helper\Cli
     */
    private $helper;

    protected function setUp()
    {
        parent::setUp();

        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['getServerValue'])
            ->getMockForAbstractClass();

        $this->argvInputFactoryMock = $this->getMockBuilder(\Symfony\Component\Console\Input\ArgvInputFactory::class)
            ->setMethods(['create'])
            ->getMock();

        $this->inputMock = $this->getMockBuilder(\Symfony\Component\Console\Input\Input::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->inputDefinitionMock = $this->getMockBuilder(\Symfony\Component\Console\Input\InputDefinition::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helper = (new ObjectManager($this))->getObject(Cli::class, [
            'request' => $this->requestMock,
            'argvInputFactory' => $this->argvInputFactoryMock
        ]);
    }

    public function testGetInput()
    {
        $this->argvInputFactoryMock->expects(self::once())->method('create')->willReturn($this->inputMock);
        $this->inputMock->expects(self::once())->method('bind')->with($this->inputDefinitionMock);

        $this->assertInstanceOf(
            \Symfony\Component\Console\Input\Input::class,
            $this->helper->getInput($this->inputDefinitionMock)
        );
    }

    public function testIsCli()
    {
        $this->assertTrue($this->helper->isCli());
    }

    public function testIsCommand()
    {
        $this->requestMock->expects(self::once())->method('getServerValue')->willReturn(['php', 'test:command', 'arg']);
        $this->assertTrue($this->helper->isCommand('test:command'));
    }
}
