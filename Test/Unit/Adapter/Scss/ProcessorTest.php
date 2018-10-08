<?php

namespace ClawRock\SassPreprocessor\Test\Unit\Adapter\Scss;

use ClawRock\SassPreprocessor\Adapter\Scss\Processor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class ProcessorTest extends TestCase
{
    const TEST_FILES = ['extend', 'import', 'mixins', 'nesting', 'operators', 'variables'];

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $appStateMock;

    /**
     * @var \Leafo\ScssPhp\CompilerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $compilerFactoryMock;

    /**
     * @var \ClawRock\SassPreprocessor\Adapter\Scss\Processor
     */
    private $processor;

    /**
     * @var \Magento\Framework\View\Asset\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    private $assetSourceMock;

    /**
     * @var \Leafo\ScssPhp\Compiler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $compilerMock;

    /**
     * @var \Magento\Framework\View\Asset\File|\PHPUnit_Framework_MockObject_MockObject
     */
    private $assetMock;

    /**
     * @var \Leafo\ScssPhp\Compiler
     */
    private $compiler;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $directoryListMock;

    protected function setUp()
    {
        parent::setUp();

        $this->appStateMock = $this->getMockBuilder(\Magento\Framework\App\State::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->compilerMock = $this->getMockBuilder(\Leafo\ScssPhp\Compiler::class)->getMock();

        $this->compiler = new \Leafo\ScssPhp\Compiler();

        $this->directoryListMock = $this->getMockBuilder(\Magento\Framework\App\Filesystem\DirectoryList::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->compilerFactoryMock = $this->getMockBuilder(\Leafo\ScssPhp\CompilerFactory::class)
            ->setMethods(['create'])
            ->getMock();

        $this->assetSourceMock = $this->getMockBuilder(\Magento\Framework\View\Asset\Source::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assetMock = $this->getMockBuilder(\Magento\Framework\View\Asset\File::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)->getMockForAbstractClass();

        $this->processor = (new ObjectManager($this))->getObject(Processor::class, [
            'logger' => $this->loggerMock,
            'appState' => $this->appStateMock,
            'assetSource' => $this->assetSourceMock,
            'directoryList' => $this->directoryListMock,
            'compilerFactory' => $this->compilerFactoryMock
        ]);
    }

    /**
     * @dataProvider compressedDataProvider
     */
    public function testProcessContentCompressed($input, $output)
    {
        $this->compilerFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($this->compiler);

        $this->appStateMock->expects(self::once())
            ->method('getMode')
            ->willReturn(\Magento\Framework\App\State::MODE_PRODUCTION);

        $this->directoryListMock->expects(self::once())
            ->method('getPath')
            ->with(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR)
            ->willReturn(__DIR__ . '/_files');

        $this->assetSourceMock->expects(self::once())
            ->method('getContent')
            ->with($this->assetMock)
            ->willReturn($input);

        $this->assertEquals($output, $this->processor->processContent($this->assetMock));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testProcessContent($input, $output)
    {
        $this->compilerFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($this->compiler);

        $this->appStateMock->expects(self::once())
            ->method('getMode')
            ->willReturn(\Magento\Framework\App\State::MODE_DEVELOPER);

        $this->directoryListMock->expects(self::once())
            ->method('getPath')
            ->with(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR)
            ->willReturn(__DIR__ . '/_files');

        $this->assetSourceMock->expects(self::once())
            ->method('getContent')
            ->with($this->assetMock)
            ->willReturn($input);

        // Assert without whitespaces
        $this->assertEquals(
            preg_replace('/\s+/', ' ', trim($output)),
            preg_replace('/\s+/', ' ', trim($this->processor->processContent($this->assetMock)))
        );
    }

    public function testProcessEmptyContent()
    {
        $this->compilerFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($this->compilerMock);

        $this->compilerMock->expects(self::never())
            ->method('compile');

        $this->assetSourceMock->expects(self::once())
            ->method('getContent')
            ->with($this->assetMock)
            ->willReturn('');

        $this->assertEmpty($this->processor->processContent($this->assetMock));
    }

    public function testProcessContentEmptyResult()
    {
        $this->compilerFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($this->compiler);

        $this->assetSourceMock->expects(self::once())
            ->method('getContent')
            ->with($this->assetMock)
            ->willReturn('@mixin empty() {}');

        $this->loggerMock->expects(self::once())
            ->method('warning');

        $this->assertEmpty($this->processor->processContent($this->assetMock));
    }

    public function testProcessContentException()
    {
        $this->expectException(\Magento\Framework\View\Asset\ContentProcessorException::class);

        $this->compilerFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($this->compiler);

        $this->assetSourceMock->expects(self::once())
            ->method('getContent')
            ->with($this->assetMock)
            ->willReturn('.invalid# {');

        $this->processor->processContent($this->assetMock);
    }

    public function compressedDataProvider()
    {
        return $this->scssProvider(true);
    }

    public function dataProvider()
    {
        return $this->scssProvider(false);
    }

    public function scssProvider($compress)
    {
        $files = [];
        foreach (self::TEST_FILES as $file) {
            $files[] = [
                rtrim(file_get_contents(__DIR__ . '/_files/' . $file . '.scss')),
                rtrim(file_get_contents(__DIR__ . '/_files/' . $file . ($compress ? '.min' : '') . '.css')),
            ];
        }

        return $files;
    }
}
