<?php

namespace Stk2k\File\Util;

use Stk2k\File\File;
use Stk2k\File\Exception\NotFileException;
use Stk2k\File\Exception\NotDirectoryException;
use Stk2k\File\Exception\FileIsNotReadableException;
use Stk2k\File\Exception\FileOpenException;
use Stk2k\File\Exception\FileOutputException;
use Stk2k\File\Exception\FileCopyException;

class FileSystemUtil
{
    /**
     *  Copy file
     *
     * @param File $src
     * @param File $dest
     *
     * @throws NotFileException|FileIsNotReadableException|NotDirectoryException|FileCopyException
     */
    public static function copyFile(File $src, File $dest)
    {
        // check source path
        if (!$src->isFile()) {
            throw new NotFileException($src);
        }
        if (!$src->isReadable()) {
            throw new FileIsNotReadableException($src);
        }

        // check destination path
        $dir = $dest->getParent();
        if (!$dir->isDir()) {
            throw new NotDirectoryException($dir);
        }

        // do copy
        $result = @copy($src->getPath(), $dest->getPath());

        if (false === $result) {
            throw new FileCopyException($src, $dest);
        }
    }

    /**
     *  Move file
     *
     * @param File $src
     * @param File $dest
     *
     * @throws NotFileException|FileIsNotReadableException|NotDirectoryException|FileCopyException
     */
    public static function moveFile(File $src, File $dest)
    {
        // check source path
        if (!$src->isFile()) {
            throw new NotFileException($src);
        }
        if (!$src->isReadable()) {
            throw new FileIsNotReadableException($src);
        }

        // check destination path
        $dir = $dest->getParent();
        if (!$dir->isDir()) {
            throw new NotDirectoryException($dir);
        }

        // do move
        $result = @rename($src->getPath(), $dest->getPath());

        if (false === $result) {
            throw new FileCopyException($src, $dest);
        }
    }

    /**
     *  Get extension
     *
     * @param string $path
     *
     * @return string
     */
    public static function getExtension($path)
    {
        $info = pathinfo($path);
        return isset($info['extension']) ? $info['extension'] : '';
    }

    /**
     * Output file
     *
     * @param File $file path to output
     * @param array $lines each line of the file to output
     *
     * @throws FileOpenException|FileOutputException
     */
    public static function outputFile(File $file, array $lines)
    {
        $file_name = $file->getPath();
        $fp = fopen($file_name, "w");
        if ($fp === FALSE) {
            throw new FileOpenException($file);
        }
        foreach($lines as $line) {
            $res = fwrite($fp, $line . PHP_EOL);
            if ($res === FALSE) {
                throw new FileOutputException($file);
            }
        }
        fclose($fp);
    }
}


