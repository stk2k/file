<?php
namespace Stk2k\File\Exception;

use Throwable;

use Stk2k\File\File;

class FileIsNotReadableException extends FileSystemException
{
    /**
     * FileIsNotReadableException constructor.
     *
     * @param File $file
     * @param int $code
     * @param Throwable|null $prev
     */
    public function __construct( File $file, int $code = 0, Throwable $prev = NULL )
    {
        $message = "Specified file is not readable: $file";
        parent::__construct($message, $code, $prev);
    }
}


