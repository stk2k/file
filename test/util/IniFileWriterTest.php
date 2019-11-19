<?php
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use Stk2k\File\Util\IniFileWriter;

class IniFileWriterTest extends TestCase
{
    const MY_ARRAY = [
        'MyFavorite' => [
            'Fruits' => 'Apple',
            'Drink' => 'Coffee',
        ],
        'MyFamilyAndAge' => [
            'MeAndWife' => [
                'Kate' => 32,
                'Roger' => 31,
            ],
            'Parents' => [
                'Donald' => 64,
                'Natalia' => 62,
            ],
            'Children' => [
                'Abraham' => 11,
                'William' => 9,
                'Alice' => 6
            ]
        ],
    ];

    public function testWrite()
    {
        vfsStream::setup();
        $path = vfsStream::url('root/sample.ini');

        try{
            IniFileWriter::write($path, self::MY_ARRAY, "\n");

        }
        catch(Exception $e){
            $this->fail($e->getMessage());
        }

        $expected = <<<EXPECTED
[MyFavorite]
Fruits = Apple
Drink = Coffee

[MyFamilyAndAge]
MeAndWife = {"Kate":32,"Roger":31}
Parents = {"Donald":64,"Natalia":62}
Children = {"Abraham":11,"William":9,"Alice":6}

EXPECTED;
        $this->assertSame($expected, file_get_contents($path));

        $data = @parse_ini_file($path, true, INI_SCANNER_RAW);

        if ($data === false){
            var_dump(error_get_last());
        }

        $this->assertInternalType("array", $data);
        $this->assertEquals([
            'MyFavorite' => [
                'Fruits' => 'Apple',
                'Drink' => 'Coffee',
            ],
            'MyFamilyAndAge' => [
                'MeAndWife' => '{"Kate":32,"Roger":31}',
                'Parents' => '{"Donald":64,"Natalia":62}',
                'Children' => '{"Abraham":11,"William":9,"Alice":6}'
            ],
        ], $data);

    }
}