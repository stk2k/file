<?php
namespace Stk2k\File\Util;

use Stk2k\File\Exception\FileOpenException;
use Stk2k\File\Exception\FileOutputException;
use Stk2k\File\File;

class IniFileWriter
{
    /**
     * write ini file
     *
     * The argument 2 array($data) should be like this:
     *
     * $data = [
     *      'Section1' = [
     *          'Key1' => Value1
     *          'Key2' => Value2
     *      ],
     *      'Section2' = [
     *          'Key3' => Value3
     *          'Key4' => Value4
     *      ],
     * ];
     *
     * Then, the output file will be like this:
     *
     * =========================================================================
     * [Section1]
     * Key1 = Value1
     * Key2 = Value2
     *
     * [Section2]
     * Key3 = Value3
     * Key4 = Value4
     * =========================================================================
     *
     * @param string $filename
     * @param array $data
     * @param string $line_end
     *
     * @throws
     */
    public static function write(string $filename, array $data, string $line_end = "\r\n")
    {
        $fp = fopen($filename, 'w');
        if (!$fp){
            throw new FileOpenException(new File($filename));
        }

        $first_section = true;
        foreach($data as $k => $v){
            if (!$first_section){
                // write empty line
                if (!fwrite($fp, "{$line_end}")){
                    throw new FileOutputException(new File($filename));
                }
            }
            // write section name
            if (!fwrite($fp, "[$k]{$line_end}")){
                throw new FileOutputException(new File($filename));
            }
            $first_section = false;
            if (is_array($v)){
                foreach($v as $_k => $_v){
                    // covert value to string
                    $str_value = null;
                    if (is_scalar($_v)){
                        // scalar value will be output directly
                        $str_value = $_v;
                    }
                    else if (is_array($_v)||is_object($_v)){
                        // array/object value will be converted to json
                        $str_value = json_encode($_v);
                    }
                    // write key and it's value
                    if (!fwrite($fp, "$_k = $str_value{$line_end}")){
                        throw new FileOutputException(new File($filename));
                    }
                }
            }
        }

        fclose($fp);
    }
}