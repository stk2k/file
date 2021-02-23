<?php
namespace Stk2k\File\Filter;

use Stk2k\File\FileFilterInterface;
use Stk2k\File\File;

class WildcardFileFilter implements FileFilterInterface
{
    private $pattern;
    private $extension;
    
    /**
     * Construct object
     *
     * @param string $pattern        regular expression pattern
     * @param string|null $extension      file extension which is ignored in pattern matching.
     */
    public function __construct(string $pattern, string $extension = NULL )
    {
        $this->pattern    = $pattern;
        $this->extension  = $extension;
    }

    /**
     * Check if the filter select the specified file.
     *
     * @param File $file         Target fileto be tested.
     *
     * @return bool
     */
    public function accept( File $file ) : bool
    {
        if ( $this->extension ){
            $ext = $file->getExtension();
            if ( $ext != $this->extension ){
                return FALSE;
            }
        }

        $suffix = $this->extension ? '.' . $this->extension : NULL;
        $name = $suffix ? $file->getName($suffix) : $file->getName();

        if ( fnmatch( $this->pattern, $name ) ){
            return TRUE;
        }
        return FALSE;
    }
}


