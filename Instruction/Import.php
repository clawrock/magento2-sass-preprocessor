<?php

namespace ClawRock\SassPreprocessor\Instruction;

use Magento\Framework\View\Asset\LocalInterface;

class Import extends \Magento\Framework\Css\PreProcessor\Instruction\Import
{
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepository;

    /**
     * @var \ClawRock\SassPreprocessor\Helper\File
     */
    private $fileHelper;

    public function __construct(
        \Magento\Framework\View\Asset\NotationResolver\Module $notationResolver,
        \Magento\Framework\Css\PreProcessor\FileGenerator\RelatedGenerator $relatedFileGenerator,
        \Magento\Framework\View\Asset\Repository $assetRepository,
        \ClawRock\SassPreprocessor\Helper\File $fileHelper
    ) {
        parent::__construct($notationResolver, $relatedFileGenerator);
        $this->assetRepository = $assetRepository;
        $this->fileHelper = $fileHelper;
    }

    /**
     * @inheritdoc
     */
    protected function replace(array $matchedContent, LocalInterface $asset, $contentType)
    {
        $matchedFileId = $this->fileHelper->fixFileExtension($matchedContent['path'], $contentType);
        $relatedAsset = $this->assetRepository->createRelated($matchedFileId, $asset);

        if ($this->fileHelper->assetFileExists($relatedAsset)) {
            return parent::replace($matchedContent, $asset, $contentType);
        }

        $matchedContent['path'] = $this->fileHelper->getUnderscoreNotation($matchedContent['path']);

        return parent::replace($matchedContent, $asset, $contentType);
    }
}
