<?php
namespace Stk2k\File;

use Stk2k\File\Exception\FileInputException;
use Stk2k\File\Exception\FileOutputException;
use Stk2k\File\Exception\FileRenameException;
use Stk2k\File\Exception\MakeFileException;
use Stk2k\File\Exception\MakeDirectoryException;

class File
{
    const DS = DIRECTORY_SEPARATOR;
    
    private $path;

    /**
     *    Construct object
     *
     * @param string $file_name    Name of the file or directory.
     * @param File|null $parent    Parent object
     */
    public function __construct( string $file_name, File $parent = NULL )
    {
        $parent_path = $parent instanceof File ? $parent->getPath() : $parent;
        $this->path = $parent_path ? rtrim($parent_path,self::DS) . self::DS . $file_name : $file_name;
    }
    
    /**
     * Returns file hash
     *
     * @param string $algo
     *
     * @return string
     */
    public function hash( string $algo = 'sha1' ) : string
    {
        return hash_file($algo, $this->getPath());
    }
    
    /**
     *  Returns if the file or directory can be read.
     *
     * @return bool
     */
    public function canRead() : bool
    {
        return is_readable( $this->path );
    }

    /**
     *  Returns if the file or directory can be written.
     *
     * @return bool
     */
    public function canWrite() : bool
    {
        return is_writeable( $this->path );
    }

    /**
     *  Returns file size of the file or directory in bytes.
     *
     * @return int
     */
    public function getFileSize() : int
    {
        return filesize( $this->path );
    }
    
    /**
     *  Returns file permissions
     *
     * @return int
     */
    public function getFilePerms() : int
    {
        return fileperms( $this->path );
    }
    
    /**
     *  Returns file type
     *
     * @return string
     */
    public function getFileType() : string
    {
        return filetype( $this->path );
    }

    /**
     *  Delete the file or directory
     *
     * @param bool $drilldown
     *
     * @return bool
     */
    public function delete( $drilldown = false ) : bool
    {
        if ( !file_exists($this->path) ){
            return true;
        }
        if ( is_file($this->path) ){
            return @unlink($this->path);
        }
        if ( $drilldown ){
            return self::removeDirectoryRecursive($this->path);
        }
        return @rmdir($this->path);
    }

    /**
     *  Delete the file or directory
     *
     * @param string $path
     *
     * @return bool
     */
    private static function removeDirectoryRecursive(string $path) : bool
    {
        if ( !file_exists($path) ){
            return true;
        }

        $handle = opendir("$path");
        if ( $handle === FALSE ) {
            return false;
        }
        while ( false !== ($item = readdir($handle)) ) {
            if ($item != "." && $item != "..") {
                if (is_dir("$path/$item")) {
                    $res = self::removeDirectoryRecursive( "$path/$item" );
                    if (!$res){
                        return false;
                    }
                } else {
                    $res = @unlink( "$path/$item" );
                    if (!$res){
                        return false;
                    }
                }
            }
        }
        closedir( $handle );
        return @rmdir($path);
    }

    /**
     *  Virtual path
     *
     * @return string
     */
    public function getPath() : string
    {
        return $this->path;
    }

    /**
     *  Return if the path means file.
     *
     * @return bool
     */
    public function isFile() : bool
    {
        return is_file( $this->path );
    }

    /**
     *  Return if the path means directory.
     *
     * @return bool
     */
    public function isDir() : bool
    {
        return is_dir( $this->path );
    }

    /**
     *  Return if the path means directory.
     *
     * @return bool
     */
    public function isDirectory() : bool
    {
        return is_dir( $this->path );
    }

    /**
     *  Return if the file or directory can be read.
     *
     * @return bool
     */
    public function isReadable() : bool
    {
        return is_readable( $this->path );
    }

    /**
     *  Return if the file or directory can be written.
     *
     * @return bool
     */
    public function isWriteable() : bool
    {
        return is_writable( $this->path );
    }

    /**
     *  Extension of the file
     *
     * @return string
     */
    public function getExtension() : string
    {
        return pathinfo( $this->path, PATHINFO_EXTENSION );
    }

    /**
     *  returns last modified time(UNIX time)
     *
     * @return int
     */
    public function getLastModifiedTime() : int
    {
        return filemtime( $this->path );
    }
    
    /**
     *  returns last access time(UNIX time)
     *
     * @return int
     */
    public function getLastAccessTime() : int
    {
        return fileatime( $this->path );
    }
    
    /**
     *  returns file owner
     *
     * @return int
     */
    public function getFileOwner() : int
    {
        return fileowner( $this->path );
    }

    /**
     *  Return if the file or directory exists.
     *
     * @return bool
     */
    public function exists() : bool
    {
        return file_exists( $this->path );
    }

    /**
     *  Absolute path of the file or directory
     *
     * @return string
     */
    public function getAbsolutePath() : string
    {
        return realpath( $this->path );
    }

    /**
     *  Name of the file or directory
     *
     * @param string|NULL $suffix       file suffix which is ignored.
     *
     * @return string
     */
    public function getName( $suffix = NULL ) : string
    {
        return $suffix ? basename( $this->path, $suffix ) : basename( $this->path );
    }

    /**
     *  Name of parent directory
     *
     * @return string
     */
    public function getDirName() : string
    {
        return dirname( $this->path );
    }

    /**
     *  Child of the file or directory
     *
     * @param string $file_or_dir_name
     *
     * @return File
     */
    public function getChild( string $file_or_dir_name ) : File
    {
        return new File( $this->path . self::DS . $file_or_dir_name );
    }

    /**
     *  Parent of the file or directory
     *
     * @return File
     */
    public function getParent() : File
    {
        return new File( dirname($this->path) );
    }

    /**
     *  Contents of the file or directory
     *
     * @return string
     *
     * @throws FileInputException
     */
    public function getContents() : string
    {
        $ret = file_get_contents( $this->path );
        if ($ret === false){
            throw new FileInputException($this, 'file_get_contents failed');
        }
        return $ret;
    }

    /**
     *  get contents of the file as array
     *
     * @param int $flags
     *
     * @return array|false
     */
    public function getContentsAsArray($flags = FILE_IGNORE_NEW_LINES) : array
    {
        return @file( $this->path, $flags );
    }

    /**
     *  Save string data as a file
     *
     * @param string $contents
     * @param bool $ex_lock
     *
     * @return int
     *
     * @throws FileOutputException
     */
    public function putContents( string $contents, bool $ex_lock = false ) : int
    {
        $flags = $ex_lock ? LOCK_EX : 0;
        $res = file_put_contents( $this->path, $contents, $flags );
        if ($res === FALSE){
            throw new FileOutputException($this);
        }
        return $res;
    }

    /**
     *  Rename the file or directory
     *
     * @param File $new_file
     *
     * @throws FileRenameException
     */
    public function rename( File $new_file )
    {
        $res = @rename( $this->path, $new_file->getPath() );
        if ( $res === FALSE ){
            throw new FileRenameException($this, $new_file);
        }
    }

    /**
     *  Create file
     *
     * @param string $mode File mode
     * @param string $contents File contents
     *
     * @return void
     *
     * @throws MakeFileException|MakeDirectoryException
     */
    public function makeFile( string $mode, string $contents )
    {
        $parent_dir = $this->getParent();

        $parent_dir->makeDirectory( $mode );

        $ret = @file_put_contents( $this->path, $contents );
        if ( $ret === FALSE ){
            throw new MakeFileException($this);
        }
    }

    /**
     *  Create empty directory
     *
     * @param string $mode                  File mode.If this parameter is set NULL, 0777 will be applied.
     *
     * @return void
     *
     * @throws MakeDirectoryException
     */
    public function makeDirectory( $mode = NULL )
    {
        $mode = $mode ? $mode : 0777;

        if ( file_exists($this->path) ){
            if ( is_file($this->path) ){
                throw new MakeDirectoryException($this);
            }
            return;
        }

        $parent_dir = $this->getParent();

        if ( !$parent_dir->exists() ){
            $parent_dir->makeDirectory( $mode );
        }

        $res = @mkdir( $this->path, $mode );
        if ( $res === FALSE ){
            throw new MakeDirectoryException($this);
        }
    }

    /**
     *  Listing up files in directory which this object means
     *
     * @param FileFilterInterface|callable $filter       Fileter object which implements selection logic. If this parameter is omitted, all files will be selected.
     *
     * @return File[]
     */
    public function listFiles( $filter = NULL ) : array
    {
        $path = $this->path;

        if ( !file_exists($path) )    return [];

        if ( !is_readable($path) )    return [];

        if ( is_file($path) )    return [];

        $files = array();

        $dh = opendir($path);
        while( ($file_name = readdir($dh)) !== FALSE ){
            if ( $file_name === '.' || $file_name === '..' ){
                continue;
            }
            $file = new File( $file_name, $this );
            if ( $filter ){
                if ( $filter instanceof FileFilterInterface ){
                    if ( $filter->accept($file) ){
                        $files[] = $file;
                    }
                }
                else if ( is_callable($filter) ){
                    if ( $filter($file) ){
                        $files[] = $file;
                    }
                }
            }
            else{
                $files[] = $file;
            }
        }
        return $files;
    }


    /**
     *  Update last modified date of the file
     *
     * @param int|null $time      time value to set
     *
     * @return bool
     */
    public function touch( int $time = NULL ) : bool
    {
        if ( $time === NULL ){
            return touch( $this->path );
        }
        return touch( $this->path, $time );
    }

    /**
     *  String expression of this object
     *
     * @return string
     */
    public function __toString() : string
    {
        return $this->path;
    }
}

