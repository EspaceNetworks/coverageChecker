<?php
namespace exussum12\CoverageChecker\FileMatchers;

use exussum12\CoverageChecker\FileMatcher;
use exussum12\CoverageChecker\Exceptions\FileNotFound;

/**
 * Class EndsWith
 * @package exussum12\CoverageChecker\FileMatchers
 */
class EndsWith implements FileMatcher
{

    /**
     * {@inheritdoc}
     */
    public function match($needle, array $haystack)
    {
        foreach ($haystack as $file) {
            if ($this->fileEndsWith($file, $needle)) {
                return $file;
            }
        }

        throw new FileNotFound();
    }

    /**
     * Find if two strings end in the same way
     * @param $haystack
     * @param $needle
     * @return bool
     */
    protected function fileEndsWith($haystack, $needle)
    {
        $length = strlen($needle);

        return (substr($haystack, -$length) === $needle);
    }
}
