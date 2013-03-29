<?php
/**
 * This file is part of the Behat UI map extension.
 * (c) 2013 Vadim Kusakin <vadim.irbis@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Irs\BehatUimapExtension;

/**
 * File system operations helper
 *
 */
abstract class Fso
{
    /**
     * Copy file or directory
     *
     * @param string $from Source
     * @param string $to Target
     * @param boolean $deep Perform deep copy or symbolic link
     *                      (on OS that does not support symlinks always perform deep copy)
     */
    public static function copy($from, $to, $deep = true)
    {
        if ($deep || !self::isOsSupportsLinks()) {
                self::deepCopy($from, $to);
        } else {
            symlink($from, $to);
        }
    }

    /**
     * Performs deep recusive copy
     *
     * @param string $from Source
     * @param string $to Target
     */
    protected static function deepCopy($from, $to)
    {
        if (is_dir($from)) {
            @mkdir($to);
            $directory = dir($from);
            while (false !== ($readdirectory = $directory->read())) {
                if ($readdirectory == '.' || $readdirectory == '..') {
                    continue;
                }
                self::deepCopy($from . DIRECTORY_SEPARATOR . $readdirectory, $to . DIRECTORY_SEPARATOR . $readdirectory);
            }
            $directory->close();
        } else {
            copy($from, $to);
        }
    }

    /**
     * Moves file or directory
     *
     * @param string $from Source
     * @param string $to Target
     * @return boolean TRUE on success or FALSE on failure
     */
    public static function move($from, $to)
    {
        return @rename($from, $to);
    }

    /**
     * Recursively deletes file or directory
     *
     * @param string $filename
     * @throws \InvalidArgumentException If file does not exist
     */
    public static function delete($filename)
    {
        if (!file_exists($filename)){
            throw new \InvalidArgumentException("File '$filename' does not exist.");
        }

        if (is_file($filename)) {
            return unlink($filename);
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($filename),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
            	if ('.' != $item->getFilename() && '..' != $item->getFilename()) {
                    rmdir((string)$item);
            	}
            } else {
                unlink((string)$item);
            }
        }

        unset($iterator);
        rmdir($filename);
    }

    /**
     * Returns true if operation system supports links
     *
     * @return boolean
     */
    protected static function isOsSupportsLinks()
    {
        return (php_uname('s') == 'Windows NT')
            ? version_compare(php_uname('r'), '6.0', '>=')
            : true;
    }
}
