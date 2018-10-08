<?php

namespace ClawRock\SassPreprocessor\Test\Unit\Plugin;

use ClawRock\SassPreprocessor\Plugin\ChainPlugin;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class ChainPluginTest extends TestCase
{
    /**
     * @var \Magento\Developer\Console\Command\SourceThemeDeployCommand|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sourceThemeDeployCommandMock;

    /**
     * @var \ClawRock\SassPreprocessor\Helper\Cli|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cliHelperMock;

    /**
     * @var \ClawRock\SassPreprocessor\Helper\File|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileHelperMock;

    /**
     * @var \Magento\Framework\View\Asset\PreProcessor\Chain|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectMock;

    /**
     * @var \Magento\Framework\View\Asset|\PHPUnit_Framework_MockObject_MockObject
     */
    private $assetMock;

    /**
     * @var \Symfony\Component\Console\Input\InputDefinition|\PHPUnit_Framework_MockObject_MockObject
     */
    private $inputDefinitionMock;

    /**
     * @var \Symfony\Component\Console\Input\Input|\PHPUnit_Framework_MockObject_MockObject
     */
    private $inputMock;

    /**
     * @var \ClawRock\SassPreprocessor\Plugin\ChainPlugin
     */
    private $plugin;

    protected function setUp()
    {
        parent::setUp();

        $this->sourceThemeDeployCommandMock = $this
            ->getMockBuilder(\Magento\Developer\Console\Command\SourceThemeDeployCommand::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cliHelperMock = $this->getMockBuilder(\ClawRock\SassPreprocessor\Helper\Cli::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileHelperMock = $this->getMockBuilder(\ClawRock\SassPreprocessor\Helper\File::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->subjectMock = $this->getMockBuilder(\Magento\Framework\View\Asset\PreProcessor\Chain::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assetMock = $this->getMockBuilder(\Magento\Framework\View\Asset::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->inputMock = $this->getMockBuilder(\Symfony\Component\Console\Input\Input::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->inputDefinitionMock = $this->getMockBuilder(\Symfony\Component\Console\Input\InputDefinition::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->plugin = (new ObjectManager($this))->getObject(ChainPlugin::class, [
            'sourceThemeDeployCommand' => $this->sourceThemeDeployCommandMock,
            'fileHelper' => $this->fileHelperMock,
            'cliHelper' => $this->cliHelperMock
        ]);
    }

    public function testNotCli()
    {
        $this->subjectMock->expects(self::never())
            ->method('getAsset')
            ->willReturn($this->assetMock);

        $this->cliHelperMock->expects(self::once())
            ->method('isCli')
            ->willReturn(false);

        $this->assertTrue($this->plugin->afterIsChanged($this->subjectMock, true));
    }

    public function testNotSourceThemeDeployCommand()
    {
        $this->subjectMock->expects(self::never())
            ->method('getAsset')
            ->willReturn($this->assetMock);

        $this->cliHelperMock->expects(self::once())
            ->method('isCli')
            ->willReturn(true);

        $this->cliHelperMock->expects(self::once())
            ->method('isCommand')
            ->with('dev:source-theme:deploy')
            ->willReturn(false);

        $this->assertTrue($this->plugin->afterIsChanged($this->subjectMock, true));
    }

    public function testEntryFile()
    {
        $this->subjectMock->expects(self::once())
            ->method('getAsset')
            ->willReturn($this->assetMock);

        $this->assetMock->expects(self::once())
            ->method('getFilePath')
            ->willReturn('filepath.scss');

        $this->expectSourceThemeDeployCommand();
        $this->expectFileFromInput();

        $this->assertTrue($this->plugin->afterIsChanged($this->subjectMock, true));
    }

    public function testPartialFile()
    {
        $this->subjectMock->expects(self::once())
            ->method('getAsset')
            ->willReturn($this->assetMock);

        $this->assetMock->expects(self::once())
            ->method('getFilePath')
            ->willReturn('_partial.scss');

        $this->expectSourceThemeDeployCommand();
        $this->expectFileFromInput();

        $this->assertFalse($this->plugin->afterIsChanged($this->subjectMock, true));
    }

    private function expectSourceThemeDeployCommand()
    {
        $this->cliHelperMock->expects(self::once())
            ->method('isCli')
            ->willReturn(true);

        $this->cliHelperMock->expects(self::once())
            ->method('isCommand')
            ->with('dev:source-theme:deploy')
            ->willReturn(true);
    }

    private function expectFileFromInput()
    {
        $this->sourceThemeDeployCommandMock->expects(self::once())
            ->method('getDefinition')
            ->willReturn($this->inputDefinitionMock);

        $this->cliHelperMock->expects(self::once())
            ->method('getInput')
            ->with($this->inputDefinitionMock)
            ->willReturn($this->inputMock);

        $this->inputMock->expects(self::once())
            ->method('getArgument')
            ->with(\Magento\Developer\Console\Command\SourceThemeDeployCommand::FILE_ARGUMENT)
            ->willReturn(['filepath']);

        $this->inputMock->expects(self::once())
            ->method('getOption')
            ->with(\Magento\Developer\Console\Command\SourceThemeDeployCommand::TYPE_ARGUMENT)
            ->willReturn('scss');

        $this->fileHelperMock->expects(self::once())
            ->method('fixFileExtension')
            ->with('filepath', 'scss')
            ->willReturn('filepath.scss');
    }
}
