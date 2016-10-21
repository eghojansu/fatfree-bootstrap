<?php

namespace app\command\dist;

use Nutrition\Helper;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use ZipArchive;
use app\command\AbstractCommand;

class PatchCommand extends AbstractCommand
{
    protected $zipname;
    protected $distDir;
    protected $tempDir;
    protected $baseDir;

    public function configure()
    {
        $this->baseDir = $this->base()->get('ROOTDIR');
        $this->distDir = $this->baseDir.'dist/';
        $this->tempDir = $this->baseDir.'dist/patch/';
        $this->zipname = basename($this->baseDir);

        $this
            ->setName('dist:patch')
            ->setDescription('Build distributable patch')
            ->addArgument('stag', InputArgument::REQUIRED, 'Start version tag')
            ->addArgument('etag', InputArgument::REQUIRED, 'End version tag')
            ->addOption('no-vendor', null, InputOption::VALUE_NONE, 'Do not install vendor')
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
            ->reallyDone('Building dist patch package complete')
        ;
    }

    protected function deployCompress()
    {
        $this->info('compressing script');

        $tagToTag = $this->input->getArgument('stag').'-to-'.$this->input->getArgument('etag');
        $saveAs = $this->zipname.'-patch-'.$tagToTag.'.zip';
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

            $zip->addEmptyDir($this->zipname);
            foreach($entries as $entry) {
                $full = strtr($entry->getRealPath(), '\\', '/');
                $rel = $this->zipname.str_replace($this->tempDir, '/', $full);
                if ($entry->isDir()){
                    $zip->addEmptyDir($rel);
                } else {
                    $zip->addFile($full, $rel);
                }
            }

            $zip->setArchiveComment("$saveAs\nby Eko Kurniawan <ekokurniawanbs@gmail.com>");
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
            $this->tempDir.'dev',
        ];
        foreach ($removes as $path) {
            if (!file_exists($path)) {
                continue;
            }
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

        if (file_exists($this->tempDir.'composer.json') && !$this->input->getOption('no-vendor')) {
            $command = [
                'composer',
                'install',
                '--no-dev',
                '--quiet ',
                '--optimize-autoloader',
                '--no-suggest',
            ];
            $this->process(implode(' ', $command), $this->tempDir);

            $this->done();
        } else {
            $this->error('skipped');
        }

        return $this;
    }

    protected function deployCopyScript()
    {
        $this->info('copying script');

        $tag = $this->input->getArgument('stag').'..'.$this->input->getArgument('etag');
        $command = [
            'git',
            'diff',
            '--name-only',
            '--diff-filter=duxb',
            $tag,
        ];

        $process = $this->process(implode(' ', $command), $this->tempDir);
        $sources = array_filter(explode("\n", str_replace(["\n","\r"], "\n", $process->getOutput())));

        foreach ($sources as $source) {
            Helper::copyDir($this->baseDir.$source, $this->tempDir.$source);
        }

        $this->done();

        return $this;
    }

    protected function deployInitialize()
    {
        $this->output->writeln("<fg=yellow>building dist patch package...</> <fg=cyan>(please wait until process complete)</>\n");

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
