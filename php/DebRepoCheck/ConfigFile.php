<?php

namespace DebRepoCheck;

use DirectoryIterator;

/**
 * Class ConfigFile
 *
 * @author Piotr Rybałtowski <piotrek@rybaltowski.pl>
 */
class ConfigFile
{
    /**
     * @var DirectoryIterator
     */
    private $file;

    /**
     * @var ConfigLine[]
     */
    private $lines = [];

    /**
     * @param DirectoryIterator $file
     */
    public function __construct(DirectoryIterator $file)
    {
        $this->file = $file;
    }

    /**
     * @return $this
     */
    public function check()
    {
        $lines = file($this->file->getPathname());
        foreach ($lines as $line) {
            $line = trim($line);
            if (!$line) {
                continue;
            }
            $configLine = new ConfigLine($line);
            $configLine->check();
            $this->lines[] = $configLine;
        }
    }

    /**
     * @return string
     */
    public function filename()
    {
        return $this->file->getFilename();
    }

    /**
     * @return \DebRepoCheck\ConfigLine[]
     */
    public function lines()
    {
        return $this->lines;
    }
}
