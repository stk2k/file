<?php
namespace Stk2k\File\Exception;

use Throwable;

use Stk2k\File\File;

class NotDirectoryException extends FileSystemException
{
    /**
     * NotDirectoryException constructor.
     *
     * @param File $file
     * @param int $code
     * @param Throwable|null $prev
     */
    public function __construct( File $file, int $code = 0, Throwable $prev = null)
    {
        $message = "Not directory: $file";
        parent::__construct($message, $code, $prev);
    }
}

