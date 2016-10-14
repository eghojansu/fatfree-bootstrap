<?php

namespace app\command;

use Nutrition\Helper;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use ZipArchive;
use app\command\AbstractCommand;

class BuildDistCommand extends AbstractCommand
{
    const ZIPNAME = 'fa';

    protected $distDir;
    protected $tempDir;
    protected $baseDir;

    public function configure()
    {
        $this->baseDir = strtr(dirname(dirname(__DIR__)), '\\', '/').'/';
        $this->distDir = $this->baseDir.'dist/';
        $this->tempDir = $this->baseDir.'dist/tmp/';

        $this
            ->setName('dist:build')
            ->setDescription('Build distributable')
            ->addArgument('vtag', InputArgument::OPTIONAL, 'Version tag', 'unstable')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this
            ->configureIO($input, $output)
            ->deployInitialize()
            ->deployCopyScript()
            ->deployInstallVendor()
            ->deployPrepareCompress()
            ->deployCompress()
            ->reallyDone('Building dist package complete')
        ;
    }

    protected function deployCompress()
    {
        $this->info('compressing script');

        $saveAs = self::ZIPNAME.'-'.$this->input->getArgument('vtag').'.zip';
        $path = $this->distDir.$saveAs;
        if (file_exists($path)) {
            unlink($path);
        }

        $zip = new ZipArchive();
        $ret = $zip->open($path, ZipArchive::CREATE);
        if ($ret === ZipArchive::ER_EXISTS) {
            $error = "File already exists.";
        }
        elseif ($ret === ZipArchive::ER_INCONS) {
            $error = "Zip archive inconsistent.";
        }
        elseif ($ret === ZipArchive::ER_INVAL) {
            $error = "Invalid argument.";
        }
        elseif ($ret === ZipArchive::ER_MEMORY) {
            $error = "Malloc failure.";
        }
        elseif ($ret === ZipArchive::ER_NOENT) {
            $error = "No such file.";
        }
        elseif ($ret === ZipArchive::ER_NOZIP) {
            $error = "Not a zip archive.";
        }
        elseif ($ret === ZipArchive::ER_OPEN) {
            $error = "Can't open file.";
        }
        elseif ($ret === ZipArchive::ER_READ) {
            $error = "Read error.";
        }
        elseif ($ret === ZipArchive::ER_SEEK) {
            $error = "Seek error.";
        }
        else {
            $error = false;
        }

        if ($error) {
            $error .= ' ('.$path.')';
            $this->error($error);

            throw new RuntimeException($error);
        }
        else {
            $it = new RecursiveDirectoryIterator($this->tempDir, RecursiveDirectoryIterator::SKIP_DOTS);
            $entries = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);

            $zip->addEmptyDir(self::ZIPNAME);
            foreach($entries as $entry) {
                $full = strtr($entry->getRealPath(), '\\', '/');
                $rel = self::ZIPNAME.str_replace($this->tempDir, '/', $full);
                if ($entry->isDir()){
                    $zip->addEmptyDir($rel);
                } else {
                    $zip->addFile($full, $rel);
                }
            }

            $zip->setArchiveComment("$saveAs by Eko Kurniawan <ekokurniawanbs@gmail.com>");
            $zip->close();

            $this->done();
        }

        return $this;
    }

    protected function deployPrepareCompress()
    {
        $this->info('preparing compression');

        $removes = [
            $this->tempDir.'composer.json',
            $this->tempDir.'composer.lock',
            $this->tempDir.'app/console',
            $this->tempDir.'app/command',
        ];
        foreach ($removes as $path) {
            if (is_dir($path)) {
                Helper::removeDir2($path, true);
            }
            else {
                unlink($path);
            }
        }

        $this->done();

        return $this;
    }

    protected function deployInstallVendor()
    {
        $this->info('installing vendor');

        $command = [
            'composer',
            'install',
            '--no-dev',
            '--quiet ',
            '--optimize-autoloader',
        ];
        $process = new Process(implode(' ', $command), $this->tempDir);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $this->done();

        return $this;
    }

    protected function deployCopyScript()
    {
        $this->info('copying script');

        $sources = [
            $this->baseDir.'app',
            $this->baseDir.'asset',
            $this->baseDir.'.htaccess',
            $this->baseDir.'app.php',
            $this->baseDir.'composer.json',
            $this->baseDir.'favicon.ico',
            $this->baseDir.'LICENSE',
            $this->baseDir.'README.md',
        ];

        foreach ($sources as $source) {
            Helper::copyDir($source, $this->tempDir.basename($source));
        }

        $this->done();

        return $this;
    }

    protected function deployInitialize()
    {
        $this->output->writeln("<fg=yellow>building dist package...</> <fg=cyan>(please wait until process complete)</>\n");

        $this->info('initializing');

        if (false === file_exists($this->distDir)) {
            mkdir($this->distDir, 0777, true);
        }
        if (file_exists($this->tempDir)) {
            Helper::removeDir2($this->tempDir, false);
        } else {
            mkdir($this->tempDir, 0777, true);
        }

        $this->done();

        return $this;
    }
}
