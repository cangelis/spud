<?php
namespace Spud;

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;

class Compiler
{

    protected $outputDir;
    protected $inputRoot;
    protected $fileSystem;

    public function __construct($outputDir, $inputRoot, Filesystem $fileSystem)
    {
        $this->outputDir = $outputDir;
        $this->inputRoot = $inputRoot;
        $this->fileSystem = $fileSystem;
    }

    public function getCompiledPath($path)
    {
        $realPath = $this->outputDir .
            preg_replace_callback(
                '/^(' . str_replace('/', '\/', $this->inputRoot) . ')(.*)?$/',
                function ($match) {
                    return str_replace('.spud', '', $match[2]);
                },
                $path
            );
        if (!is_dir(dirname($realPath))) {
            mkdir(dirname($realPath));
        }
        return $realPath;
    }

    public function compileString($content)
    {
        foreach (['Include', 'Extends'] as $expression) {
            $content = $this->{'compile' . $expression}($content);
        }
        return $content;
    }

    public function compile(SplFileInfo $file)
    {
        preg_match('#^(.*)\.spud\.html$#is', $file->getFilename(), $matches);
        // if it is a spud file then compile it
        if (count($matches) > 1) {
            $this->fileSystem->put(
                $this->getCompiledPath($file->getRealPath()),
                $this->compileString($file->getContents())
            );
            return true;
        }
        $this->fileSystem->copy($file->getRealPath(), $this->getCompiledPath($file->getRealPath()));
        return false;
    }

    protected function compileInclude($content)
    {
        return preg_replace_callback('#@include\((.+?)\)#is', function ($match) {
            return $this->compileString(
                $this->fileSystem->get(
                    $this->inputRoot . DIRECTORY_SEPARATOR . str_replace('.', '/', $match[1]) . ".spud.html"
                )
            );
        }, $content);
    }

    protected function compileExtends($content)
    {
        return preg_replace_callback('#@extends\((.+?)\)(.+?)@endextends#is', function ($match) {
            // $match[1] = file to be extended
            // $match[2] = content
            $sectionContents = $this->getSectionContents($match[2]);
            return $this->compileString($this->compileYields(
                $this->fileSystem->get(
                    $this->inputRoot . DIRECTORY_SEPARATOR . str_replace('.', '/', $match[1]) . ".spud.html"
                ),
                $sectionContents
            ));
        }, $content);
    }

    protected function compileYields($content, array $sectionContents)
    {
        return preg_replace_callback('#@yields\((.+?)\)#is', function ($match) use ($sectionContents) {
            return (isset($sectionContents[$match[1]])) ? $sectionContents[$match[1]] : "";
        }, $content);
    }

    protected function getSectionContents($content)
    {
        $sections = [];
        preg_match_all('#@section\((.+?)\)(.+?)@endsection#is', $content, $matches);
        $i = 0;
        foreach ($matches[1] as $key) {
            $sections[$key] = $matches[2][$i];
            $i++;
        }
        return $sections;
    }
}
