<?php

namespace ClawRock\SassPreprocessor\Test\Unit\Console\Command;

use ClawRock\SassPreprocessor\Console\Command\GulpInstallCommand;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class GulpInstallCommandTest extends TestCase
{
    /**
     * @var \Magento\Framework\Filesystem\Driver\File|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystemMock;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $directoryListMock;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrar|\PHPUnit_Framework_MockObject_MockObject
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
     * @var \Symfony\Component\Console\Application
     */
    private $application;

    /**
     * @var \Symfony\Component\Console\Tester\CommandTester
     */
    private $commandTester;

    /**
     * @var \Symfony\Component\Console\Command\Command
     */
    private $command;

    /**
     * @var \ClawRock\SassPreprocessor\Console\Command\GulpInstallCommand
     */
    private $commandObject;

    protected function setUp()
    {
        parent::setUp();

        $this->filesystemMock = $this->getMockBuilder(\Magento\Framework\Filesystem\Driver\File::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->directoryListMock = $this->getMockBuilder(\Magento\Framework\App\Filesystem\DirectoryList::class)
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

        $this->styleFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($this->styleMock);

        $this->application = new Application();
        $this->commandObject = (new ObjectManager($this))->getObject(GulpInstallCommand::class, [
            'filesystem' => $this->filesystemMock,
            'directoryList' => $this->directoryListMock,
            'componentRegistrar' => $this->componentRegistrarMock,
            'styleFactory' => $this->styleFactoryMock,
        ]);

        $this->application->add($this->commandObject);
        $this->command = $this->application->find('dev:gulp:install');
        $this->commandTester = new CommandTester($this->command);
    }

    public function testExecute()
    {
        $this->styleMock->expects(self::once())->method('confirm')->willReturn(true);

        $this->componentRegistrarMock->expects(self::once())
            ->method('getPath')
            ->with(ComponentRegistrar::MODULE, 'ClawRock_SassPreprocessor');

        $this->filesystemMock->expects(self::exactly(2))
            ->method('createDirectory');

        $this->filesystemMock->expects(self::exactly(2))
            ->method('readDirectory')
            ->willReturnOnConsecutiveCalls(['path/to/directory', 'path/to/file1'], ['path/to/directory/file2']);

        $this->filesystemMock->expects(self::exactly(3))
            ->method('isDirectory')
            ->withConsecutive(['path/to/directory'], ['path/to/directory/file2'], ['path/to/file1'])
            ->willReturnOnConsecutiveCalls(true, false, false);

        $this->filesystemMock->expects(self::exactly(count(GulpInstallCommand::ROOT_FILES) + 2))
            ->method('copy');

        $this->styleMock->expects(self::once())
            ->method('success');

        $this->commandTester->execute(['command' => $this->command->getName()]);
    }

    public function testExecuteNoConfirm()
    {
        $this->styleMock->expects(self::once())->method('confirm')->willReturn(false);

        $this->componentRegistrarMock->expects(self::never())
            ->method('getPath')
            ->with(ComponentRegistrar::MODULE, 'ClawRock_SassPreprocessor');

        $this->filesystemMock->expects(self::never())->method('createDirectory');
        $this->filesystemMock->expects(self::never())->method('readDirectory');
        $this->filesystemMock->expects(self::never())->method('isDirectory');
        $this->filesystemMock->expects(self::never())->method('copy');
        $this->styleMock->expects(self::never())->method('success');

        $this->commandTester->execute(['command' => $this->command->getName()]);
    }
}
