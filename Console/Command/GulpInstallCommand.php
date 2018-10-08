<?php

namespace ClawRock\SassPreprocessor\Console\Command;

use Magento\Framework\Component\ComponentRegistrar;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GulpInstallCommand extends Command
{
    const ROOT_FILES = ['.babelrc', 'gulpfile.babel.js', 'package.json'];

    const GULP_PATH = 'dev/tools/gulp';

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
     * @var string
     */
    private $modulePath;

    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyleFactory
     */
    private $styleFactory;

    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $io;

    /**
     * @var array
     */
    private $copiedFiles = [];

    public function __construct(
        \Magento\Framework\Filesystem\Driver\File $filesystem,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar,
        \Symfony\Component\Console\Style\SymfonyStyleFactory $styleFactory
    ) {
        parent::__construct('dev:gulp:install');

        $this->filesystem = $filesystem;
        $this->directoryList = $directoryList;
        $this->componentRegistrar = $componentRegistrar;
        $this->styleFactory = $styleFactory;
    }

    protected function configure()
    {
        parent::configure();

        $this->setDescription('Install Gulp');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|null|void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = $this->styleFactory->create(['input' => $input, 'output' => $output]);

        $this->io->title('Gulp install');
        if (!$this->io->confirm('It may overwrite your files, are you sure?', true)) {
            return;
        }

        $this->modulePath = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, 'ClawRock_SassPreprocessor');
        $this->filesystem->createDirectory($this->directoryList->getRoot() . '/' . self::GULP_PATH);
        $this->recursiveCopyToRoot($this->modulePath . '/' . self::GULP_PATH);

        foreach (self::ROOT_FILES as $file) {
            $this->copy($this->modulePath . '/dev/tools/' . $file, $this->directoryList->getRoot() . '/' . $file);
        }

        $this->io->success(array_map(function ($file) {
            return $file . ' created!';
        }, $this->copiedFiles));
    }

    /**
     * @param string $source
     * @throws \Magento\Framework\Exception\FileSystemException
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    private function recursiveCopyToRoot($source)
    {
        foreach ($this->filesystem->readDirectory($source) as $path) {
            $destination = $this->directoryList->getRoot() . str_replace($this->modulePath, '', $path);
            if ($this->filesystem->isDirectory($path)) {
                $this->filesystem->createDirectory($destination);
                $this->recursiveCopyToRoot($path);
            } else {
                $this->copy($path, $destination);
            }
        }
    }

    /**
     * @param string $source
     * @param string $destination
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function copy($source, $destination)
    {
        $this->filesystem->copy($source, $destination);
        $this->copiedFiles[] = $destination;
    }
}
