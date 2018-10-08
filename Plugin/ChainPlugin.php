<?php

namespace ClawRock\SassPreprocessor\Plugin;

use Magento\Developer\Console\Command\SourceThemeDeployCommand;
use Magento\Framework\View\Asset\PreProcessor\Chain;

/**
 * Plugin which fixes issue when deployed symlink is overwritten by temporary file during source theme deploy
 * Entry files are skipped in order to apply @magento_import directive
 * https://github.com/magento/magento2/issues/6943
 */
class ChainPlugin
{
    /**
     * @var \Magento\Developer\Console\Command\SourceThemeDeployCommand
     */
    private $sourceThemeDeployCommand;

    /**
     * @var \ClawRock\SassPreprocessor\Helper\File
     */
    private $fileHelper;

    /**
     * @var \ClawRock\SassPreprocessor\Helper\Cli
     */
    private $cliHelper;

    public function __construct(
        \Magento\Developer\Console\Command\SourceThemeDeployCommand $sourceThemeDeployCommand,
        \ClawRock\SassPreprocessor\Helper\File $fileHelper,
        \ClawRock\SassPreprocessor\Helper\Cli $cliHelper
    ) {
        $this->sourceThemeDeployCommand = $sourceThemeDeployCommand;
        $this->fileHelper = $fileHelper;
        $this->cliHelper = $cliHelper;
    }

    public function afterIsChanged(Chain $subject, $result)
    {
        if (!$this->cliHelper->isCli() || !$this->cliHelper->isCommand('dev:source-theme:deploy')) {
            return $result;
        }

        if (!$this->isEntryFile($subject->getAsset()->getFilePath())) {
            return false;
        }

        return $result;
    }

    private function isEntryFile($path)
    {
        $input = $this->cliHelper->getInput($this->sourceThemeDeployCommand->getDefinition());
        $files = $input->getArgument(SourceThemeDeployCommand::FILE_ARGUMENT);
        $contentType = $input->getOption(SourceThemeDeployCommand::TYPE_ARGUMENT);

        foreach ($files as $file) {
            if ($file === $path || $this->fileHelper->fixFileExtension($file, $contentType) === $path) {
                return true;
            }
        }

        return false;
    }
}
