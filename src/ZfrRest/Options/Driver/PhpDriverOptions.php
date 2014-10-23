<?php
/**
 * @author Antoine Hedgcock
 */

namespace ZfrRest\Options\Driver;

use Zend\Stdlib\AbstractOptions;

class PhpDriverOptions extends AbstractOptions
{
    /**
     * @var array
     */
    protected $dirs = [];

    /**
     * @return array
     */
    public function getDirs()
    {
        return $this->dirs;
    }

    /**
     * @param array $dirs
     */
    public function setDirs(array $dirs)
    {
        $this->dirs = $dirs;
    }
}
