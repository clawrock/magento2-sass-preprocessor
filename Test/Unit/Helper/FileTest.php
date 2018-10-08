<?php

namespace ClawRock\SassPreprocessor\Test\Unit\Helper;

use ClawRock\SassPreprocessor\Helper\File;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Asset\File\NotFoundException;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
    /**
     * @var \Magento\Framework\Filesystem\Io\File|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ioFileMock;

    /**
     * @var \ClawRock\SassPreprocessor\Helper\File
     */
    private $helper;

    protected function setUp()
    {
        parent::setUp();
        $this->ioFileMock = $this->getMockBuilder(\Magento\Framework\Filesystem\Io\File::class)->getMock();

        $this->helper = (new ObjectManager($this))->getObject(File::class, [
            'ioFile' => $this->ioFileMock
        ]);
    }

    public function testIsPartialTrue()
    {
        $path = 'web/css/source/styles/_hello.scss';

        $this->ioFileMock->expects(self::once())->method('getPathInfo')->willReturn(pathinfo($path));
        $this->assertTrue($this->helper->isPartial($path));
    }

    public function testIsPartialFalse()
    {
        $path = 'web/css/source/styles/hello.scss';

        $this->ioFileMock->expects(self::once())->method('getPathInfo')->willReturn(pathinfo($path));
        $this->assertFalse($this->helper->isPartial($path));
    }

    public function testAssetFileNotExists()
    {
        /** @var \Magento\Framework\View\Asset\File|\PHPUnit_Framework_MockObject_MockObject $asset */
        $asset = $this->getMockBuilder(\Magento\Framework\View\Asset\File::class)
            ->disableOriginalConstructor()
            ->getMock();

        $asset->expects(self::once())->method('getSourceFile')->willThrowException(new NotFoundException());

        $this->assertFalse($this->helper->assetFileExists($asset));
    }

    public function testAssetFileExists()
    {
        /** @var \Magento\Framework\View\Asset\File|\PHPUnit_Framework_MockObject_MockObject $asset */
        $asset = $this->getMockBuilder(\Magento\Framework\View\Asset\File::class)
            ->disableOriginalConstructor()
            ->getMock();

        $asset->expects(self::once())->method('getSourceFile')->willReturn('file_path');

        $this->assertTrue($this->helper->assetFileExists($asset));
    }

    public function testFixFileExtension()
    {
        $path = 'web/css/source/styles/hello';
        $this->ioFileMock->expects(self::once())->method('getPathInfo')->willReturn(pathinfo($path));
        $this->assertEquals('web/css/source/styles/hello.scss', $this->helper->fixFileExtension($path, 'scss'));
    }

    public function testFixFileExtensionNoChange()
    {
        $path = 'web/css/source/styles/hello.scss';
        $this->ioFileMock->expects(self::once())->method('getPathInfo')->willReturn(pathinfo($path));
        $this->assertEquals('web/css/source/styles/hello.scss', $this->helper->fixFileExtension($path, 'scss'));
    }

    public function testGetUnderscoreNotation()
    {
        $path = 'web/css/source/styles/hello.scss';
        $this->ioFileMock->expects(self::once())->method('getPathInfo')->willReturn(pathinfo($path));
        $this->assertEquals('web/css/source/styles/_hello.scss', $this->helper->getUnderscoreNotation($path));
    }

    public function testReadFileAsArray()
    {
        $this->assertEquals([], $this->helper->readFileAsArray('path/to/file', 'js'));
    }
}
