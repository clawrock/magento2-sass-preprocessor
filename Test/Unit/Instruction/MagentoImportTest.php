<?php

namespace ClawRock\SassPreprocessor\Test\Unit\Instruction;

use ClawRock\SassPreprocessor\Instruction\MagentoImport;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class MagentoImportTest extends TestCase
{
    /**
     * @var \Magento\Framework\View\DesignInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $designMock;

    /**
     * @var \Magento\Framework\View\File\CollectorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileSourceMock;

    /**
     * @var \Magento\Framework\Css\PreProcessor\ErrorHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $errHandlerMock;

    /**
     * @var \Magento\Framework\View\Asset\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $assetRepositoryMock;

    /**
     * @var \Magento\Framework\View\Design\Theme\ThemeProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $themeProviderMock;

    /**
     * @var \ClawRock\SassPreprocessor\Helper\File|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileHelperMock;

    /**
     * @var \Magento\Framework\View\Asset\PreProcessor\Chain|\PHPUnit_Framework_MockObject_MockObject
     */
    private $chainMock;

    /**
     * @var \Magento\Framework\View\Asset\LocalInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $assetMock;

    /**
     * @var \Magento\Framework\View\Asset\File|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileMock;

    /**
     * @var \Magento\Framework\View\Design\ThemeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $themeMock;

    /**
     * @var \ClawRock\SassPreprocessor\Instruction\MagentoImport
     */
    private $instruction;

    protected function setUp()
    {
        parent::setUp();

        $this->designMock = $this->getMockBuilder(\Magento\Framework\View\DesignInterface::class)
            ->getMockForAbstractClass();

        $this->fileSourceMock = $this->getMockBuilder(\Magento\Framework\View\File\CollectorInterface::class)
            ->getMockForAbstractClass();

        $this->errHandlerMock = $this->getMockBuilder(\Magento\Framework\Css\PreProcessor\ErrorHandlerInterface::class)
            ->getMockForAbstractClass();

        $this->assetRepositoryMock = $this->getMockBuilder(\Magento\Framework\View\Asset\Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->themeProviderMock = $this
            ->getMockBuilder(\Magento\Framework\View\Design\Theme\ThemeProviderInterface::class)
            ->getMockForAbstractClass();

        $this->fileHelperMock = $this->getMockBuilder(\ClawRock\SassPreprocessor\Helper\File::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->chainMock = $this->getMockBuilder(\Magento\Framework\View\Asset\PreProcessor\Chain::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assetMock = $this->getMockBuilder(\Magento\Framework\View\Asset\LocalInterface::class)
            ->getMockForAbstractClass();

        $this->chainMock->expects(self::once())
            ->method('getAsset')
            ->willReturn($this->assetMock);

        $this->chainMock->expects(self::once())
            ->method('getContentType')
            ->willReturn('scss');

        $this->fileMock = $this->getMockBuilder(\Magento\Framework\View\Asset\File::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->themeMock = $this->getMockBuilder(\Magento\Framework\View\Design\ThemeInterface::class)
            ->getMockForAbstractClass();

        $this->instruction = (new ObjectManager($this))->getObject(MagentoImport::class, [
            'design' => $this->designMock,
            'fileSource' => $this->fileSourceMock,
            'errorHandler' => $this->errHandlerMock,
            'assetRepository' => $this->assetRepositoryMock,
            'themeProvider' => $this->themeProviderMock,
            'fileHelper' => $this->fileHelperMock,
        ]);
    }

    public function testProcess()
    {
        $this->expectMagentoImport();

        $this->designMock->expects(self::once())
            ->method('getDesignTheme')
            ->willReturn($this->themeMock);

        $this->chainMock->expects(self::once())
            ->method('setContent')
            ->with("@import 'Vendor_Module::source/_module.scss';\n@import 'Another_Module::source/_module.scss';");

        $this->instruction->process($this->chainMock);
    }

    public function testProcessFallbackTheme()
    {
        $this->expectMagentoImport();

        $contextMock = $this->getMockBuilder(\Magento\Framework\View\Asset\File\FallbackContext::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileMock->expects(self::once())
            ->method('getContext')
            ->willReturn($contextMock);

        $contextMock->expects(self::once())
            ->method('getAreaCode')
            ->willReturn('frontend');

        $contextMock->expects(self::once())
            ->method('getThemePath')
            ->willReturn('ClawRock/blank');

        $this->themeProviderMock->expects(self::once())
            ->method('getThemeByFullPath')
            ->with('frontend/ClawRock/blank')
            ->willReturn($this->themeMock);

        $this->chainMock->expects(self::once())
            ->method('setContent')
            ->with("@import 'Vendor_Module::source/_module.scss';\n@import 'Another_Module::source/_module.scss';");

        $this->instruction->process($this->chainMock);
    }

    public function testProcessWithoutMagentoImport()
    {
        $css = '.class { color: #f00; }';

        $this->chainMock->expects(self::atLeastOnce())
            ->method('getContent')
            ->willReturn($css);

        $this->instruction->process($this->chainMock);
        $this->assertEquals($css, $this->chainMock->getContent());
    }

    public function testProcessException()
    {
        $this->errHandlerMock->expects(self::once())->method('processException');

        $this->chainMock->expects(self::atLeastOnce())
            ->method('getContent')
            ->willReturn('//@magento_import \'source/module\';');

        $this->fileHelperMock->expects(self::once())
            ->method('isPartial')
            ->willThrowException(new \Exception());

        $this->instruction->process($this->chainMock);
    }

    private function expectMagentoImport()
    {
        $this->errHandlerMock->expects(self::never())->method('processException');

        $this->chainMock->expects(self::atLeastOnce())
            ->method('getContent')
            ->willReturn('//@magento_import \'source/module\';');

        $this->fileHelperMock->expects(self::once())
            ->method('isPartial')
            ->with('source/module')->willReturn(false);

        $this->fileHelperMock->expects(self::once())
            ->method('getUnderscoreNotation')
            ->with('source/module')
            ->willReturn('source/_module');

        $this->fileHelperMock->expects(self::once())
            ->method('fixFileExtension')
            ->with('source/_module')
            ->willReturn('source/_module.scss');

        $this->assetRepositoryMock->expects(self::once())
            ->method('createRelated')
            ->with('source/_module.scss', $this->assetMock)
            ->willReturn($this->fileMock);

        $this->fileMock->expects(self::once())
            ->method('getFilePath')
            ->willReturn('source/_module.scss');

        $fileMock1 = $this->getMockBuilder(\Magento\Framework\View\File::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fileMock2 = $this->getMockBuilder(\Magento\Framework\View\File::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fileMock1->expects(self::atLeastOnce())
            ->method('getModule')
            ->willReturn('Vendor_Module');

        $fileMock2->expects(self::atLeastOnce())
            ->method('getModule')
            ->willReturn('Another_Module');

        $this->fileSourceMock->expects(self::once())
            ->method('getFiles')
            ->with($this->themeMock, 'source/_module.scss')
            ->willReturn([$fileMock1, $fileMock2]);
    }
}
