<?php

namespace Tests\MagicalGirl\ValueGenerator;

use MagicalGirl\ValueGenerator\RandomValueGenerator;

class RandomValueGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testString()
    {
        $length = 10;
        $letterTypes = array(
            RandomValueGenerator::LETTER_TYPE_UPPER_CASE
        );
        
        for ($i = 0; $i < 100; $i++) {
            $data = RandomValueGenerator::getRandomString($length, $letterTypes);
            $this->assertSame(gettype($data), 'string');
            $this->assertSame(strlen($data), $length);
            $this->assertTrue((bool) preg_match('/^[A-Z]+$/', $data));
        }
    }

    public function testInt()
    {
        $min = 1;
        $max = 5;

        $datas = array();
        for ($i = 0; $i < 100; $i++) {
            $data = RandomValueGenerator::getRandomInt($min, $max);
            $this->assertSame(gettype($data), 'integer');
            $this->assertGreaterThanOrEqual($min, $data);
            $this->assertLessThanOrEqual($max, $data);
            $datas[$data] = true;
        }

        $this->assertSame(count($datas), $max - $min + 1);
    }

    public function testFloat()
    {
        $min = 1;
        $max = 5;
        $precision = 10;
        
        for ($i = 0; $i < 100; $i++) {
            $data = RandomValueGenerator::getRandomFloat($min, $max, $precision);
            $this->assertGreaterThanOrEqual($min, $data);
            $this->assertLessThanOrEqual($max, $data);
            $this->assertTrue((bool) preg_match('/[0-9]+\.{0,1}[0-9]{0,' . $precision . '}/', $data));
        }
    }

    public function testBool()
    {
        $datas = array();
        for ($i = 0; $i < 100; $i++) {
            $data = RandomValueGenerator::getRandomBool();
            $this->assertSame(gettype($data), 'boolean');
            $datas[$data] = true;
        }

        $this->assertSame(count($datas), 2);
    }

    public function testDate()
    {
        $min = time();
        $max = $min + 60 * 60 * 24 * 365;

        $months = array();
        for ($i = 0; $i < 200; $i++) {
            $data = RandomValueGenerator::getRandomDate($min, $max);
            $this->assertTrue((bool) preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}/', $data));
            $timestamp = strtotime($data);
            $this->assertGreaterThanOrEqual($min, $timestamp);
            $this->assertLessThanOrEqual($max, $timestamp);
            $month = date('n', $timestamp);
            $months[$month] = true;
        }

        $this->assertSame(count($months), 12);
    }

    public function testDateTime()
    {
        $min = time();
        $max = $min + 60 * 60 * 24 * 365;

        $months = array();
        for ($i = 0; $i < 200; $i++) {
            $data = RandomValueGenerator::getRandomDateTime($min, $max);
            $this->assertTrue((bool) preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}/', $data));
            $timestamp = strtotime($data);
            $this->assertGreaterThanOrEqual($min, $timestamp);
            $this->assertLessThanOrEqual($max, $timestamp);
            $month = date('n', $timestamp);
            $months[$month] = true;
        }

        $this->assertSame(count($months), 12);
    }

    public function testMultilineText()
    {
        $lineLength = 10;
        $lineCount = 5;
        $letterTypes = array(
            RandomValueGenerator::LETTER_TYPE_UPPER_CASE
        );
        $lineSeparator = "\n";

        $this->assertMultilineText($lineLength, $lineCount, $letterTypes, $lineSeparator);
        $lineSeparator = '<br>';
        $this->assertMultilineText($lineLength, $lineCount, $letterTypes, $lineSeparator);
    }

    protected function assertMultilineText($lineLength, $lineCount, array $letterTypes, $lineSeparator)
    {
        for ($i = 0; $i < 100; $i++) {
            $data = RandomValueGenerator::getRandomMultilineText($lineLength, $lineCount, $letterTypes, $lineSeparator);
            $lines = split($lineSeparator, $data);
            foreach ($lines as $line) {
                $this->assertSame(strlen($line), $lineLength);
                $this->assertTrue((bool) preg_match('/[A-Z]{' . $lineLength . '}/', $data));
            }
            $this->assertSame(count($lines), $lineCount);
        }
    }

    public function testList()
    {
        $list = array('aaaa', 'bbbb', 'cccc', 'dddd');

        $datas = array();
        for ($i = 0; $i < 100; $i++) {
            $data = RandomValueGenerator::getRandomList($list);
            $this->assertTrue(in_array($data, $list));
            $datas[$data] = true;
        }
        $this->assertSame(count($list), count($datas));
    }

}
