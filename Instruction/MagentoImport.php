<?php

namespace ClawRock\SassPreprocessor\Instruction;

use Magento\Framework\View\Asset\File\FallbackContext;
use Magento\Framework\View\Asset\LocalInterface;

class MagentoImport implements \Magento\Framework\View\Asset\PreProcessorInterface
{
    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    private $design;

    /**
     * @var \Magento\Framework\View\File\CollectorInterface
     */
    private $fileSource;

    /**
     * @var \Magento\Framework\Css\PreProcessor\ErrorHandlerInterface
     */
    private $errorHandler;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepository;

    /**
     * @var \Magento\Framework\View\Design\Theme\ThemeProviderInterface
     */
    private $themeProvider;

    /**
     * @var \ClawRock\SassPreprocessor\Helper\File
     */
    private $fileHelper;

    public function __construct(
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\View\File\CollectorInterface $fileSource,
        \Magento\Framework\Css\PreProcessor\ErrorHandlerInterface $errorHandler,
        \Magento\Framework\View\Asset\Repository $assetRepository,
        \Magento\Framework\View\Design\Theme\ThemeProviderInterface $themeProvider,
        \ClawRock\SassPreprocessor\Helper\File $fileHelper
    ) {
        $this->design = $design;
        $this->fileSource = $fileSource;
        $this->errorHandler = $errorHandler;
        $this->assetRepository = $assetRepository;
        $this->themeProvider = $themeProvider;
        $this->fileHelper = $fileHelper;
    }

    /**
     * @inheritdoc
     */
    public function process(\Magento\Framework\View\Asset\PreProcessor\Chain $chain)
    {
        $asset = $chain->getAsset();
        $contentType = $chain->getContentType();
        $replaceCallback = function ($matchContent) use ($asset, $contentType) {
            return $this->replace($matchContent, $asset, $contentType);
        };

        $chain->setContent(preg_replace_callback(
            \Magento\Framework\Css\PreProcessor\Instruction\MagentoImport::REPLACE_PATTERN,
            $replaceCallback,
            $chain->getContent()
        ));
    }

    private function replace(array $matchedContent, LocalInterface $asset, $contentType)
    {
        $imports = [];
        try {
            $matchedFileId = $matchedContent['path'];

            if (!$this->fileHelper->isPartial($matchedFileId)) {
                $matchedFileId = $this->fileHelper->getUnderscoreNotation($matchedFileId);
            }

            $matchedFileId = $this->fileHelper->fixFileExtension($matchedFileId, $contentType);
            $relatedAsset = $this->assetRepository->createRelated($matchedFileId, $asset);
            $resolvedPath = $relatedAsset->getFilePath();
            $files = $this->fileSource->getFiles($this->getTheme($relatedAsset), $resolvedPath);

            /** @var \Magento\Framework\View\File */
            foreach ($files as $file) {
                $imports[] = $file->getModule()
                    ? "@import '{$file->getModule()}::{$resolvedPath}';"
                    : "@import '{$matchedFileId}';";
            }
        } catch (\Exception $e) {
            $this->errorHandler->processException($e);
        }

        return implode("\n", $imports);
    }

    private function getTheme(LocalInterface $asset)
    {
        $context = $asset->getContext();
        if ($context instanceof FallbackContext) {
            return $this->themeProvider->getThemeByFullPath(
                $context->getAreaCode() . '/' . $context->getThemePath()
            );
        }
        return $this->design->getDesignTheme();
    }
}
