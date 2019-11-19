<?php
namespace Stk2k\File\Filter;

use Stk2k\File\FileFilterInterface;
use Stk2k\File\File;

class IsFileFileFilter implements FileFilterInterface
{
    /**
     * Check if the filter select the specified file.
     *
     * @param File $file         Target fileto be tested.
     *
     * @return bool
     */
    public function accept( File $file ) : bool
    {
        return $file->isFile();
    }
}