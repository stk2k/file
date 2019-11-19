<?php
namespace Stk2k\File\Exception;

use Throwable;

use Stk2k\File\File;

class FileOpenException extends FileSystemException
{
    /**
     * FileOpenException constructor.
     *
     * @param File $file
     * @param int $code
     * @param Throwable|null $prev
     */
    public function __construct( File $file, int $code = 0, Throwable $prev = NULL )
    {
        $message = "File[$file] could not be opened";
        parent::__construct($message, $code, $prev);
    }
}


