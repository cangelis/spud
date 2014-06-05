<?php
namespace Spud;

use Spud\Compiler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class BuildCommand extends Command
{
    protected $outputFolder;

    protected $inputFolder;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $outStream;

    /**
     * @var \Spud\Compiler
     */
    protected $compiler;

    protected function configure()
    {
        $this
            ->setName('build')
            ->setDescription('Starts the build')
            ->addOption(
                'input',
                null,
                InputOption::VALUE_OPTIONAL,
                'Input folder that will be compiled',
                '.'
            )
            ->addOption(
                'output',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output folder in which compiled files will be put in',
                './compiled'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputFolder = $input->getOption('output');
        $this->inputFolder = realpath($input->getOption('input'));
        $this->outStream = $output;
        $this->prepareFolders();
        $this->registerCompiler();
        $this->compileFiles();
    }

    protected function registerCompiler()
    {
        $this->compiler = new Compiler(
            $this->outputFolder,
            $this->inputFolder,
            new \Illuminate\Filesystem\Filesystem()
        );
    }

    protected function compileFiles()
    {
        $finder = new Finder();
        $this->log("Compiling the files...");
        foreach ($finder->files()->in($this->inputFolder) as $file) {
            if ($this->compiler->compile($file)) {
                $this->log("Compiled: " . $file->getRealPath());
            } else {
                $this->log("Copy: " . $file->getRealPath() . " (not a spud file)");
            }
        }
    }

    protected function prepareFolders()
    {
        $this->log('Preparing the output folder...');
        if (!is_dir($this->outputFolder)) {
            mkdir($this->outputFolder);
        }
        // clean output directory
        with(new Filesystem())->remove(
            new \RecursiveDirectoryIterator($this->outputFolder, \FilesystemIterator::SKIP_DOTS)
        );
    }

    protected function log($message)
    {
        if (OutputInterface::VERBOSITY_QUIET < $this->outStream->getVerbosity()) {
            $this->outStream->writeln($message);
        }
    }
}
