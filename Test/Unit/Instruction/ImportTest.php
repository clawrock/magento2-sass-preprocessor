<?php

namespace ClawRock\SassPreprocessor\Test\Unit\Instruction;

use ClawRock\SassPreprocessor\Instruction\Import;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class ImportTest extends TestCase
{
    /**
     * @var \Magento\Framework\View\Asset\NotationResolver\Module|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleResolverMock;

    /**
     * @var \Magento\Framework\Css\PreProcessor\FileGenerator\RelatedGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $relatedGeneratorMock;

    /**
     * @var \Magento\Framework\View\Asset\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $assetRepositoryMock;

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
    private $assetFileMock;

    /**
     * @var \ClawRock\SassPreprocessor\Instruction\Import
     */
    private $instruction;

    protected function setUp()
    {
        parent::setUp();

        $this->moduleResolverMock = $this->getMockBuilder(\Magento\Framework\View\Asset\NotationResolver\Module::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->relatedGeneratorMock = $this
            ->getMockBuilder(\Magento\Framework\Css\PreProcessor\FileGenerator\RelatedGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assetRepositoryMock = $this->getMockBuilder(\Magento\Framework\View\Asset\Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileHelperMock = $this->getMockBuilder(\ClawRock\SassPreprocessor\Helper\File::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->chainMock = $this->getMockBuilder(\Magento\Framework\View\Asset\PreProcessor\Chain::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assetMock = $this->getMockBuilder(\Magento\Framework\View\Asset\LocalInterface::class)
            ->getMockForAbstractClass();

        $this->assetFileMock = $this->getMockBuilder(\Magento\Framework\View\Asset\File::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->chainMock->expects(self::once())->method('getContentType')->willReturn('scss');
        $this->chainMock->expects(self::once())->method('getAsset')->willReturn($this->assetMock);

        $this->instruction = (new ObjectManager($this))->getObject(Import::class, [
            'notationResolver' => $this->moduleResolverMock,
            'relatedFileGenerator' => $this->relatedGeneratorMock,
            'assetRepository' => $this->assetRepositoryMock,
            'fileHelper' => $this->fileHelperMock,
        ]);

        $this->relatedGeneratorMock->expects(self::once())->method('generate')->with($this->instruction);
    }

    public function testProcessNoImports()
    {
        $this->chainMock->expects(self::once())
            ->method('getContent')
            ->willReturn('.class { color: #f00; }');

        $this->fileHelperMock->expects(self::never())->method('fixFileExtension');
        $this->assetRepositoryMock->expects(self::never())->method('createRelated');
        $this->fileHelperMock->expects(self::never())->method('assetFileExists');
        $this->fileHelperMock->expects(self::never())->method('getUnderscoreNotation');
        $this->chainMock->expects(self::never())->method('setContent');

        $this->instruction->process($this->chainMock);
    }

    public function testProcessImportExistingFile()
    {
        $this->chainMock->expects(self::once())
            ->method('getContent')
            ->willReturn('@import \'_partial\';');

        $this->fileHelperMock->expects(self::once())->method('fixFileExtension');

        $this->assetRepositoryMock->expects(self::once())
            ->method('createRelated')
            ->willReturn($this->assetFileMock);

        $this->fileHelperMock->expects(self::once())->method('assetFileExists')->willReturn(true);
        $this->fileHelperMock->expects(self::never())->method('getUnderscoreNotation');

        $this->moduleResolverMock->expects(self::once())
            ->method('convertModuleNotationToPath')
            ->willReturn('../Module/path');

        $this->chainMock->expects(self::once())
            ->method('setContent')
            ->with('@import \'../Module/path\';');

        $this->instruction->process($this->chainMock);
        $this->assertNotEmpty($this->instruction->getRelatedFiles());
    }

    public function testProcessImportFileWithoutUnderscore()
    {
        $this->chainMock->expects(self::once())
            ->method('getContent')
            ->willReturn('@import \'partial\';');

        $this->fileHelperMock->expects(self::once())->method('fixFileExtension');
        $this->fileHelperMock->expects(self::once())->method('getUnderscoreNotation');

        $this->assetRepositoryMock->expects(self::once())
            ->method('createRelated')
            ->willReturn($this->assetFileMock);

        $this->fileHelperMock->expects(self::once())
            ->method('assetFileExists')
            ->willReturn(false);

        $this->moduleResolverMock->expects(self::once())
            ->method('convertModuleNotationToPath')
            ->willReturn('../Module/path');

        $this->fileHelperMock->expects(self::once())
            ->method('assetFileExists')
            ->willReturn(true);

        $this->instruction->process($this->chainMock);
        $this->assertNotEmpty($this->instruction->getRelatedFiles());
    }
}
