<?php
namespace Stk2k\File\Exception;

use Throwable;

use Stk2k\File\File;

class MakeDirectoryException extends FileSystemException
{
    /**
     * MakeDirectoryException constructor.
     *
     * @param File $file
     * @param int $code
     * @param Throwable|NULL $prev
     */
    public function __construct( File $file, int $code = 0, Throwable $prev = NULL )
    {
        $message = "Making directory failed: $file";
        parent::__construct($message, $code, $prev);
    }
}


