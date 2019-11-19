<?php
namespace Stk2k\File\Exception;

use Throwable;
use Exception;

class FileSystemException extends Exception implements FileSystemExceptionInterface
{
    /**
     * FileRenameException constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $prev
     */
    public function __construct(string $message, int $code = 0, Throwable $prev = NULL )
    {
        parent::__construct($message, $code, $prev);
    }
}


