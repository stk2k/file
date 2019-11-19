<?php
use PHPUnit\Framework\TestCase;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

use Stk2k\File\File;
use Stk2k\File\Filter\IsDirectoryFileFilter;
use Stk2k\File\Filter\IsFileFileFilter;
use Stk2k\File\Filter\ExtensionFileFilter;
use Stk2k\File\Filter\ImageFileFilter;
use Stk2k\File\Exception\FileRenameException;

class FileTest extends TestCase
{
    /** @var  vfsStreamDirectory */
    private $root;
    
    /** @var  string */
    private $rootPath;
    
    /** @var  vfsStreamDirectory */
    private $filesRoot;
    
    protected function setUp()
    {
        $files_dir = new File(__DIR__ . '/files');
        
        $this->root = vfsStream::setup("myrootdir");
        $this->rootPath = vfsStream::url($this->root->getName().DIRECTORY_SEPARATOR);
        
        $this->filesRoot = vfsStream::copyFromFileSystem($files_dir);
    }
    private static function getFileList(array $files)
    {
        $ret = [];
        foreach($files as $file){
            /** @var File $file */
            $ret[] = $file->getName();
        }
        sort($ret);
        return $ret;
    }
    public function testListFiles()
    {
        // filterless
        $files_dir = new File(__DIR__ . '/files');
    
        $files = $files_dir->listFiles();
        $this->assertEquals([
            'a.txt',
            'b.txt',
            'c.sql',
            'dangohiyoko.png',
            'neko.jpg',
            'piyopiyo.gif',
            'x',
            'y',
            'z',
        ], self::getFileList($files));
        
        // directory filter
        $files = $files_dir->listFiles(new IsDirectoryFileFilter());
        $this->assertEquals([
            'x',
            'y',
            'z',
        ], self::getFileList($files));
    
        // file filter
        $files = $files_dir->listFiles(new IsFileFileFilter());
        $this->assertEquals([
            'a.txt',
            'b.txt',
            'c.sql',
            'dangohiyoko.png',
            'neko.jpg',
            'piyopiyo.gif',
        ], self::getFileList($files));
        
        // extension file filter
        $files = $files_dir->listFiles(new ExtensionFileFilter('txt'));
        $this->assertEquals([
            'a.txt',
            'b.txt',
        ], self::getFileList($files));
        
        $files = $files_dir->listFiles(new ExtensionFileFilter('sql'));
        $this->assertEquals([
            'c.sql',
        ], self::getFileList($files));
        
        // image file filter
        $files = $files_dir->listFiles(new ImageFileFilter([IMAGETYPE_GIF]));
        $this->assertEquals([
            'piyopiyo.gif',
        ], self::getFileList($files));
    
        $files = $files_dir->listFiles(new ImageFileFilter([IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG]));
        $this->assertEquals([
            'dangohiyoko.png',
            'neko.jpg',
            'piyopiyo.gif',
        ], self::getFileList($files));
    }

    /**
     * @throws
     */
    public function testHash()
    {
        $files_dir = new File(__DIR__ . '/files');
        
        $target_file = new File('a.txt', $files_dir);
        
        $this->assertEquals(true, $target_file->exists());
        $this->assertEquals('ae1ee9b3697975a181ac41fb3d2b1703e33df179', $target_file->hash());
        $this->assertEquals('12d29e0edc6c68f84667e1bb51bd8225', $target_file->hash('md5'));
        $this->assertEquals(sprintf("%x",crc32($target_file->getContents())), $target_file->hash('crc32b'));
    
        $target_file = new File('neko.jpg', $files_dir);
    
        $this->assertEquals(true, $target_file->exists());
        $this->assertEquals('49c3979c90053537c461df7d1b69b4c5c2419942', $target_file->hash());
        $this->assertEquals('81e748c64578317d0b80dcc69d8f2f1a', $target_file->hash('md5'));
        $this->assertEquals(sprintf("%x",crc32($target_file->getContents())), $target_file->hash('crc32b'));
    }
    
    public function testCanRead()
    {
        $files_dir = new File(__DIR__ . '/files');
    
        $target_file = new File('a.txt', $files_dir);
    
        $this->assertEquals(true, $target_file->canRead());
    }
    
    public function testCanWrite()
    {
        $files_dir = new File(__DIR__ . '/files');
        
        $target_file = new File('a.txt', $files_dir);
        
        $this->assertEquals(true, $target_file->canWrite());
    }
    
    public function testGetFileSize()
    {
        $files_dir = new File(__DIR__ . '/files');
        
        $target_file = new File('a.txt', $files_dir);
        
        $this->assertEquals(208, $target_file->getFileSize());
    
        $target_file = new File('piyopiyo.gif', $files_dir);
    
        $this->assertEquals(31568, $target_file->getFileSize());
    }
    
    public function testGetFilePerms()
    {
        $target_file = new File(vfsStream::url('myrootdir/a.txt'));
    
        chmod($target_file, 0666);
        
        $perms = sprintf("%o",$target_file->getFilePerms());
        
        $this->assertEquals(0666, octdec(substr($perms, -3)));
    
        chmod($target_file, 0644);
    
        $perms = sprintf("%o",$target_file->getFilePerms());
    
        $this->assertEquals(0644, octdec(substr($perms, -3)));
    }
    
    public function testGetFileType()
    {
        $files_dir = new File(__DIR__ . '/files');
        
        $target_file = new File('a.txt', $files_dir);
        
        $this->assertEquals('file', $target_file->getFileType());
    
        $target_file = new File('x', $files_dir);
    
        $this->assertEquals('dir', $target_file->getFileType());
    }
    
    public function testDelete()
    {
        // remove single file
        $target_file = new File(vfsStream::url('myrootdir/a.txt'));
    
        $this->assertEquals(true, $target_file->exists());
        $this->assertEquals(true, $target_file->delete());
        $this->assertEquals(false, $target_file->exists());
    }
    
    public function testDeleteRecursive()
    {
        // remove directoy which include directory/files
        $target_dir = new File(vfsStream::url('myrootdir/x'));
        $parent_dir = new File(vfsStream::url('myrootdir'));
        $text_file = new File(vfsStream::url('myrootdir/a.txt'));
        
        $this->assertEquals(true, $target_dir->exists());
        $this->assertEquals(true, $parent_dir->exists());
        $this->assertEquals(true, $text_file->exists());
        $this->assertEquals(true, $target_dir->delete(true));
        $this->assertEquals(false, $target_dir->exists());
        $this->assertEquals(true, $parent_dir->exists());
        $this->assertEquals(true, $text_file->exists());
    
        // remove directoy which include directory/files
        $target_dir = new File(vfsStream::url('myrootdir/y/q'));
        $parent_dir = new File(vfsStream::url('myrootdir/y'));
    
        $this->assertEquals(true, $target_dir->exists());
        $this->assertEquals(true, $parent_dir->exists());
        $this->assertEquals(true, $text_file->exists());
        $this->assertEquals(true, $target_dir->delete(true));
        $this->assertEquals(false, $target_dir->exists());
        $this->assertEquals(true, $parent_dir->exists());
        $this->assertEquals(true, $text_file->exists());
    }

    /**
     * @throws
     */
    public function testRenameFile()
    {
        // rename file with over writing
        $target_file = new File(vfsStream::url('myrootdir/a.txt'));
    
        $this->assertEquals(true, $target_file->exists());
    
        $contents = $target_file->getContents();
    
        try{
            $rename = new File(vfsStream::url('myrootdir/new.txt'));
            $target_file->rename($rename);
            $this->assertEquals($contents, $rename->getContents());
            $this->assertEquals(false, $target_file->exists());
        }
        catch(FileRenameException $e){
            $this->fail();
        }
        
        // rename file with over writing
        $target_file = new File(vfsStream::url('myrootdir/b.txt'));
    
        $this->assertEquals(true, $target_file->exists());
        
        $contents = $target_file->getContents();
    
        try{
            $rename = new File(vfsStream::url('myrootdir/c.txt'));
            $target_file->rename($rename);
            $this->assertEquals($contents, $rename->getContents());
            $this->assertEquals(false, $target_file->exists());
        }
        catch(FileRenameException $e){
            $this->fail();
        }
    }
    
    public function testRenameDir()
    {
        // rename directory with over writing
        $target_dir = new File(vfsStream::url('myrootdir/x'));
        
        $this->assertEquals(true, $target_dir->exists());
        
        try{
            $rename = new File(vfsStream::url('myrootdir/w'));
            $target_dir->rename($rename);
            $this->assertEquals([
                'p',
                'x-1.txt',
            ], self::getFileList($rename->listFiles()));
        }
        catch(FileRenameException $e){
            $this->assertTrue(true);
        }
    
        $files_dir = new File(__DIR__ . '/files');
    
        $target_dir = new File('x', $files_dir);
    
        $this->assertEquals(true, $target_dir->exists());
    
        try{
            $rename = new File('y', $files_dir);
            $target_dir->rename($rename);
            $this->fail();
        }
        catch(FileRenameException $e){
            $this->assertTrue(true);
        }
    }
}