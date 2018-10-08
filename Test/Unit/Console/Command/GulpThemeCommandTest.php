<?php

namespace ClawRock\SassPreprocessor\Test\Unit\Console\Command;

use ClawRock\SassPreprocessor\Console\Command\GulpThemeCommand;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class GulpThemeCommandTest extends TestCase
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystemMock;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $directoryListMock;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrarInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $componentRegistrarMock;

    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle|\PHPUnit_Framework_MockObject_MockObject
     */
    private $styleMock;

    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyleFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $styleFactoryMock;

    /**
     * @var \Symfony\Component\Console\Question\QuestionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $questionFactoryMock;

    /**
     * @var \Symfony\Component\Console\Question\Question|\PHPUnit_Framework_MockObject_MockObject
     */
    private $questionMock;

    /**
     * @var \ClawRock\SassPreprocessor\Helper\File|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileHelperMock;

    /**
     * @var \Symfony\Component\Console\Application
     */
    private $application;

    /**
     * @var \Symfony\Component\Console\Command\Command
     */
    private $command;

    /**
     * @var \Symfony\Component\Console\Tester\CommandTester
     */
    private $commandTester;

    /**
     * @var \ClawRock\SassPreprocessor\Console\Command\GulpThemeCommand
     */
    private $commandObject;

    protected function setUp()
    {
        parent::setUp();

        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->getMockForAbstractClass();

        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->filesystemMock = $this->getMockBuilder(\Magento\Framework\Filesystem\Driver\File::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->directoryListMock = $this->getMockBuilder(\Magento\Framework\App\Filesystem\DirectoryList::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->questionMock = $this->getMockBuilder(\Symfony\Component\Console\Question\Question::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->componentRegistrarMock = $this->getMockBuilder(ComponentRegistrarInterface::class)
            ->getMockForAbstractClass();

        $this->styleMock = $this->getMockBuilder(\Symfony\Component\Console\Style\SymfonyStyle::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->styleFactoryMock = $this->getMockBuilder(\Symfony\Component\Console\Style\SymfonyStyleFactory::class)
            ->setMethods(['create'])
            ->getMock();

        $this->questionFactoryMock = $this->getMockBuilder(\Symfony\Component\Console\Question\QuestionFactory::class)
            ->setMethods(['create'])
            ->getMock();

        $this->fileHelperMock = $this->getMockBuilder(\ClawRock\SassPreprocessor\Helper\File::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->application = new Application();
        $this->commandObject = (new ObjectManager($this))->getObject(GulpThemeCommand::class, [
            'storeManager' => $this->storeManagerMock,
            'scopeConfig' => $this->scopeConfigMock,
            'filesystem' => $this->filesystemMock,
            'directoryList' => $this->directoryListMock,
            'componentRegistrar' => $this->componentRegistrarMock,
            'styleFactory' => $this->styleFactoryMock,
            'questionFactory' => $this->questionFactoryMock,
            'fileHelper' => $this->fileHelperMock,
        ]);

        $this->application->add($this->commandObject);
        $this->command = $this->application->find('dev:gulp:theme');
        $this->commandTester = new CommandTester($this->command);
    }

    public function testExecute()
    {
        $this->expectThemeConfig();

        $this->fileHelperMock->expects(self::once())
            ->method('readFileAsArray')
            ->willReturn(['module.exports = {', '"blank":', '{}', '}']);

        $this->filesystemMock->expects(self::once())
            ->method('isExists')
            ->willReturn(false);

        $this->filesystemMock->expects(self::once())->method('filePutContents');

        $this->commandTester->execute(['command' => $this->command->getName()]);
    }

    public function testExecuteAlternativeConfig()
    {
        $this->expectThemeConfig();

        $this->fileHelperMock->expects(self::once())
            ->method('readFileAsArray')
            ->willReturn(['module.exports = {', '"blank":', '{}', '}']);

        $this->filesystemMock->expects(self::once())
            ->method('isExists')
            ->willReturn(true);

        $this->filesystemMock->expects(self::once())
            ->method('fileGetContents')
            ->willReturn('{"themes": "path/to/config/file"}');

        $this->filesystemMock->expects(self::once())->method('filePutContents');

        $this->commandTester->execute(['command' => $this->command->getName()]);
    }

    public function testExecuteCorruptedConfig()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);

        $this->expectThemeConfig();

        $this->fileHelperMock->expects(self::never())->method('readFileAsArray');
        $this->filesystemMock->expects(self::once())->method('isExists')->willReturn(true);
        $this->filesystemMock->expects(self::once())->method('fileGetContents')->willReturn('{}');
        $this->filesystemMock->expects(self::never())->method('filePutContents');

        $this->commandTester->execute(['command' => $this->command->getName()]);
    }

    public function testExecuteEmptyConfig()
    {
        $this->expectException(\Magento\Framework\Exception\FileSystemException::class);

        $this->expectThemeConfig();

        $this->fileHelperMock->expects(self::once())->method('readFileAsArray')->willReturn([]);
        $this->filesystemMock->expects(self::once())->method('isExists')->willReturn(false);
        $this->filesystemMock->expects(self::never())->method('filePutContents');

        $this->commandTester->execute(['command' => $this->command->getName()]);
    }

    public function testValidateEmpty()
    {
        $this->assertEquals('lipsum', $this->commandObject->validateEmpty('lipsum'));
    }

    public function testValidateEmptyFailure()
    {
        $this->expectException(\RuntimeException::class);
        $this->commandObject->validateEmpty(null);
    }

    private function expectThemeConfig()
    {
        $this->styleFactoryMock->expects(self::once())->method('create')->willReturn($this->styleMock);

        $this->styleMock->expects(self::exactly(2))
            ->method('ask')
            ->willReturnOnConsecutiveCalls('themeKey', 'css/styles, css/print');

        $this->styleMock->expects(self::exactly(2))
            ->method('choice')
            ->willReturnOnConsecutiveCalls('frontend', 'scss');

        $this->questionFactoryMock->expects(self::exactly(2))
            ->method('create')
            ->willReturn($this->questionMock);

        $this->styleMock->expects(self::exactly(2))
            ->method('askQuestion')
            ->with($this->questionMock)
            ->willReturnOnConsecutiveCalls('test', 'en_US');

        $this->componentRegistrarMock->expects(self::once())
            ->method('getPaths')
            ->with(\Magento\Framework\Component\ComponentRegistrar::THEME)
            ->willReturn([
                'frontend/Magento/blank' => [],
                'frontend/Magento/Luma' => [],
                'frontend/ClawRock/blank' => []
            ]);

        $this->storeManagerMock->expects(self::once())
            ->method('getStores')
            ->willReturn([new DataObject(['id' => 1]), new DataObject(['id' => 2]), new DataObject(['id' => 3])]);

        $this->scopeConfigMock->expects(self::exactly(3))
            ->method('getValue')
            ->willReturnOnConsecutiveCalls('en_US', 'pl_PL', 'en_US');
    }
}
