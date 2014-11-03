<?php

namespace DebRepoCheck;

/**
 * Class ConfigLine
 * 
 * @author Piotr Rybałtowski <piotrek@rybaltowski.pl>
 */
class ConfigLine
{
    /**
     * @var string
     */
    private $line;

    /**
     * @var bool
     */
    private $isParsed = false;

    /**
     * @var bool
     */
    private $isRem = false;

    /**
     * @var bool
     */
    private $isFault = false;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var string
     */
    private $release;

    /**
     * @var string
     */
    private $section;

    /**
     * @var string
     */
    private $comment;

    /**
     * @param string $line
     */
    public function __construct($line)
    {
        $this->line = $line;
    }

    public function check()
    {
        if (!$this->isParsed) {
            $this->parse();
        }
        if (!$this->isRem && !$this->isFault) {
            $this->load();
        }
    }

    /**
     * @return $this
     */
    private function parse()
    {
        if ('#' == substr($this->line, 0, 1)) {
            $this->isRem = true;
        } else {
            $parts = preg_split('/\s+/', $this->line);
            $debFound = false;
            $useComment = false;
            while (!$this->isFault && count($parts)) {
                $part = array_shift($parts);
                if (!$debFound) {
                    if ('deb' == $part) {
                        $debFound = true;
                    } else {
                        $this->isFault = 'Not a "deb" line';
                    }
                    continue;
                }
                if ('#' == substr($part, 0, 1)) {
                    $useComment = [];
                }
                if (is_array($useComment)) {
                    $useComment[] = $part;
                    continue;
                } else {
                    if (!$this->uri) {
                        $this->uri = $part;
                        continue;
                    }
                    if (!$this->release) {
                        $this->release = $part;
                        continue;
                    }
                    if (!$this->section) {
                        $this->section = $part;
                        continue;
                    }
                }
            }
            if (is_array($useComment)) {
                $this->comment = implode(' ', $useComment);
            }
            if (!$this->uri || !$this->release) {
                $this->isFault = 'No URI or release name found';
            }
        }
        return $this;
    }


    private function load()
    {
        $distsUri = $this->uri();
        if ($this->release() && '/' != $this->release()) {
            $distsUri .= '/dists';
        }
        set_error_handler(function() {});
        $distsOutput = file_get_contents($distsUri);
        restore_error_handler();
        if ($distsOutput) {
            
        } else {
            $this->isFault = 'Could not load URI ' . $distsUri;
        }
    }

    /**
     * @return bool
     */
    public function isRem()
    {
        return $this->isRem;
    }

    /**
     * @return bool
     */
    public function isFault()
    {
        return $this->isFault;
    }

    /**
     * @return string
     */
    public function line()
    {
        return $this->line;
    }

    /**
     * @return string
     */
    public function uri()
    {
        return $this->uri;
    }

    /**
     * @return string
     */
    public function release()
    {
        return $this->release;
    }

    /**
     * @return string
     */
    public function section()
    {
        return $this->section;
    }

    /**
     * @return string
     */
    public function comment()
    {
        return $this->comment;
    }


}