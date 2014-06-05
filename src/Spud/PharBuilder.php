<?php
namespace Spud;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class PharBuilder
{
    /**
     * @var \Symfony\Component\Finder\Finder
     */
    protected $finder;

    public function __construct(Finder $finder)
    {
        $this->finder = $finder;
    }

    public function build()
    {
        $phar = new \Phar(__DIR__ .  '/../../build/spud.phar', 0, "spud.phar");
        $phar->startBuffering();

        $this->finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->name('spud')
            ->notName('PharBuilder.php')
            ->ignoreDotFiles(true)
            ->in(__DIR__ . '/../..');

        foreach ($this->finder as $file) {
            $this->addFile($file, $phar);
        }

        $phar->setStub($this->getStub());

        $phar->stopBuffering();
    }

    protected function addFile(SplFileInfo $file, \Phar &$phar)
    {
        $path =
            strtr(
                str_replace(
                    dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR,
                    '',
                    $file->getRealPath()
                ),
                '\\',
                '/'
            );
        echo "Added: " . $path . "\n";
        $phar->addFromString($path, $file->getContents());
    }

    private function getStub()
    {
        return <<<'EOF'
#!/usr/bin/env php
<?php
Phar::mapPhar('spud.phar');
require 'phar://spud.phar/bin/spud';
__HALT_COMPILER();
EOF;
    }

}
