<?php

namespace ClawRock\SassPreprocessor\Adapter\Scss;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Phrase;
use Magento\Framework\View\Asset\ContentProcessorException;
use Magento\Framework\View\Asset\ContentProcessorInterface;
use Magento\Framework\View\Asset\File;

class Processor implements ContentProcessorInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var \Magento\Framework\View\Asset\Source
     */
    private $assetSource;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;

    /**
     * @var \Magento\Framework\Css\PreProcessor\Config
     */
    private $config;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $ioFile;

    /**
     * @var \Leafo\ScssPhp\CompilerFactory
     */
    private $compilerFactory;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\View\Asset\Source $assetSource,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Css\PreProcessor\Config $config,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Leafo\ScssPhp\CompilerFactory $compilerFactory
    ) {
        $this->logger = $logger;
        $this->appState = $appState;
        $this->assetSource = $assetSource;
        $this->directoryList = $directoryList;
        $this->config = $config;
        $this->ioFile = $ioFile;
        $this->compilerFactory = $compilerFactory;
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\View\Asset\ContentProcessorException
     */
    public function processContent(File $asset)
    {
        $path = $asset->getPath();
        try {
            /** @var \Leafo\ScssPhp\Compiler $compiler */
            $compiler = $this->compilerFactory->create();

            if ($this->appState->getMode() !== State::MODE_DEVELOPER) {
                $compiler->setFormatter(\Leafo\ScssPhp\Formatter\Compressed::class);
            }

            $compiler->setImportPaths([
                $this->directoryList->getPath(DirectoryList::VAR_DIR)
                . '/' . $this->config->getMaterializationRelativePath()
                . '/' . $this->ioFile->dirname($path)
            ]);

            $content = $this->assetSource->getContent($asset);

            if (trim($content) === '') {
                return '';
            }

            gc_disable();
            $content = $compiler->compile($content);
            gc_enable();

            if (trim($content) === '') {
                $this->logger->warning('Parsed scss file is empty: ' . $path);
                return '';
            }

            return $content;
        } catch (\Exception $e) {
            throw new ContentProcessorException(new Phrase($e->getMessage()));
        }
    }
}
