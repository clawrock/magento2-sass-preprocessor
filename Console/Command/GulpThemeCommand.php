<?php

namespace ClawRock\SassPreprocessor\Console\Command;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GulpThemeCommand extends Command
{
    const DEFAULT_THEMES_CONFIG = 'dev/tools/grunt/configs/themes.js';
    const GRUNT_CONFIG_FILE = 'grunt-config.json';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrarInterface
     */
    private $componentRegistrar;

    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyleFactory
     */
    private $styleFactory;

    /**
     * @var \Symfony\Component\Console\Question\QuestionFactory
     */
    private $questionFactory;

    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $io;

    /**
     * @var \ClawRock\SassPreprocessor\Helper\File
     */
    private $fileHelper;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem\Driver\File $filesystem,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar,
        \Symfony\Component\Console\Style\SymfonyStyleFactory $styleFactory,
        \Symfony\Component\Console\Question\QuestionFactory $questionFactory,
        \ClawRock\SassPreprocessor\Helper\File $fileHelper
    ) {
        parent::__construct('dev:gulp:theme');
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->filesystem = $filesystem;
        $this->directoryList = $directoryList;
        $this->componentRegistrar = $componentRegistrar;
        $this->styleFactory = $styleFactory;
        $this->questionFactory = $questionFactory;
        $this->fileHelper = $fileHelper;
    }

    protected function configure()
    {
        parent::configure();

        $this->setDescription('Install Theme');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = $this->styleFactory->create(['input' => $input, 'output' => $output]);

        $this->io->title('Gulp theme');
        $this->installTheme();
    }

    private function installTheme()
    {
        $themeConfig = $this->createThemeConfig();
        $gruntConfigPath = $this->directoryList->getRoot() . '/' . self::GRUNT_CONFIG_FILE;
        if ($this->filesystem->isExists($gruntConfigPath)) {
            $gruntConfig = json_decode($this->filesystem->fileGetContents($gruntConfigPath), true);
            if (isset($gruntConfig['themes'])) {
                $this->writeThemeConfig($themeConfig, $gruntConfig['themes']);
                return;
            }
            throw new LocalizedException(new Phrase('Grunt config file corrupted.'));
        }
        $this->writeThemeConfig($themeConfig, $this->directoryList->getRoot() . '/' . self::DEFAULT_THEMES_CONFIG);
    }

    private function writeThemeConfig($config, $file)
    {
        $themeConfigPath = $this->directoryList->getRoot() . '/' . $file;

        $themeConfig = $this->fileHelper->readFileAsArray($themeConfigPath, 'js');

        if (empty($themeConfig)) {
            throw new FileSystemException(new Phrase('Theme config not found'));
        }

        foreach ($themeConfig as $line => $content) {
            if (strpos($content, 'module.exports') !== false) {
                array_splice($themeConfig, $line+1, 0, $this->convertConfigToJson($config));
                $this->filesystem->filePutContents($themeConfigPath, $themeConfig);
                break;
            }
        }
    }

    private function convertConfigToJson($config)
    {
        $jsonConfig = $config['key'] . ': ';
        unset($config['key']);
        $jsonConfig .= json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . ',';
        $lines = explode("\n", $jsonConfig);
        foreach ($lines as &$line) {
            $line = '    ' . $line . PHP_EOL;
        }
        unset($line);

        return $lines;
    }

    private function createThemeConfig()
    {
        $themeKey = $this->io->ask('Theme key', null, [$this, 'validateEmpty']);
        $themeArea = $this->io->choice('Theme area', ['frontend', 'adminhtml'], 'frontend');

        /** @var \Symfony\Component\Console\Question\Question $question */
        $question = $this->questionFactory->create(['question' => 'Theme name']);
        $question->setValidator([$this, 'validateEmpty']);
        $question->setAutocompleterValues($this->getInstalledThemes());
        $themeName = $this->io->askQuestion($question);

        /** @var \Symfony\Component\Console\Question\Question $question */
        $question = $this->questionFactory->create(['question' => 'Theme locale']);
        $question->setValidator([$this, 'validateEmpty']);
        $question->setAutocompleterValues($this->getLocales());
        $themeLocale = $this->io->askQuestion($question);

        $themeDsl = $this->io->choice('Theme DSL', ['less', 'scss'], 'scss');
        $themeFiles = $this->io->ask(
            'Theme files (separated by comma)',
            'css/styles, css/print',
            [$this, 'validateEmpty']
        );

        return [
            'key' => $themeKey,
            'area' => $themeArea,
            'name' => $themeName,
            'locale' => $themeLocale,
            'dsl' => $themeDsl,
            'files' => array_map('trim', explode(',', $themeFiles))
        ];
    }

    private function getInstalledThemes()
    {
        return array_map(function ($theme) {
            return preg_replace('/frontend\/|adminhtml\//', '', $theme);
        }, array_keys($this->componentRegistrar->getPaths(\Magento\Framework\Component\ComponentRegistrar::THEME)));
    }

    private function getLocales()
    {
        $locales = [];
        foreach ($this->storeManager->getStores() as $store) {
            $locales[] = $this->scopeConfig->getValue(
                \Magento\Directory\Helper\Data::XML_PATH_DEFAULT_LOCALE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store->getId()
            );
        }

        return array_unique($locales);
    }

    public function validateEmpty($answer)
    {
        if (empty($answer)) {
            throw new \RuntimeException('Please provide value.');
        }

        return $answer;
    }
}
