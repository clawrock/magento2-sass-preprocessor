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
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Developer\Console\Command\SourceThemeDeployCommand
     */
    private $sourceThemeDeployCommand;

    /**
     * @var \Symfony\Component\Console\Input\ArgvInputFactory
     */
    private $argvInputFactory;

    /**
     * @var \ClawRock\SassPreprocessor\Helper\File
     */
    private $fileHelper;

    /**
     * @var \Symfony\Component\Console\Input\ArgvInput
     */
    private $input;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Developer\Console\Command\SourceThemeDeployCommand $sourceThemeDeployCommand,
        \Symfony\Component\Console\Input\ArgvInputFactory $argvInputFactory,
        \ClawRock\SassPreprocessor\Helper\File $fileHelper
    ) {
        $this->request = $request;
        $this->sourceThemeDeployCommand = $sourceThemeDeployCommand;
        $this->argvInputFactory = $argvInputFactory;
        $this->fileHelper = $fileHelper;
    }

    public function afterIsChanged(Chain $subject, $result)
    {
        if (!$this->isSourceThemeDeployCommand()) {
            return $result;
        }

        $this->bindInput();

        if (!$this->isEntryFile($subject->getAsset()->getFilePath())) {
            return false;
        }

        return $result;
    }

    private function isEntryFile($path)
    {
        $files = $this->input->getArgument(SourceThemeDeployCommand::FILE_ARGUMENT);
        $contentType = $this->input->getOption(SourceThemeDeployCommand::TYPE_ARGUMENT);

        foreach ($files as $file) {
            if ($file === $path || $this->fileHelper->fixFileExtension($file, $contentType) === $path) {
                return true;
            }
        }

        return false;
    }

    private function isSourceThemeDeployCommand()
    {
        return PHP_SAPI === 'cli' && in_array('dev:source-theme:deploy', $this->request->getServerValue('argv', []));
    }

    private function bindInput()
    {
        if ($this->input === null) {
            $this->input = $this->argvInputFactory->create();
            $this->input->bind($this->sourceThemeDeployCommand->getDefinition());
        }
    }
}
