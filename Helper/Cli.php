<?php

namespace ClawRock\SassPreprocessor\Helper;

use Symfony\Component\Console\Input\InputDefinition;

class Cli
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Symfony\Component\Console\Input\ArgvInputFactory
     */
    private $argvInputFactory;

    /**
     * @var \Symfony\Component\Console\Input\ArgvInput
     */
    private $input;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Symfony\Component\Console\Input\ArgvInputFactory $argvInputFactory
    ) {
        $this->request = $request;
        $this->argvInputFactory = $argvInputFactory;
    }

    public function getInput(InputDefinition $definition)
    {
        if ($this->input === null) {
            $this->input = $this->argvInputFactory->create();
            $this->input->bind($definition);
        }

        return $this->input;
    }

    public function isCli()
    {
        return PHP_SAPI === 'cli';
    }

    public function isCommand($name)
    {
        return in_array($name, $this->request->getServerValue('argv', []));
    }
}
