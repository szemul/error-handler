<?php
declare(strict_types=1);

namespace Szemul\ErrorHandler\Test\Helper;

use PHPUnit\Framework\TestCase;
use Szemul\ErrorHandler\Helper\ErrorIdGenerator;

class ErrorIdGeneratorTest extends TestCase
{
    private const MESSAGE_1 = 'message1';
    private const MESSAGE_2 = 'message2';
    private const FILE_1    = 'file1';
    private const FILE_2    = 'file2';
    private const LINE_1    = 5;
    private const LINE_2    = 55;

    public function testWithoutTimeLimit(): void
    {
        $sut = new ErrorIdGenerator(0);

        $m1f1l1t1 = $sut->generateErrorId(self::MESSAGE_1, self::FILE_1, self::LINE_1, 1);
        $m1f1l1   = $sut->generateErrorId(self::MESSAGE_1, self::FILE_1, self::LINE_1);
        $m2f1l1t1 = $sut->generateErrorId(self::MESSAGE_2, self::FILE_1, self::LINE_1, 1);
        $m1f2l1t1 = $sut->generateErrorId(self::MESSAGE_1, self::FILE_2, self::LINE_1, 1);
        $m1f1l2t1 = $sut->generateErrorId(self::MESSAGE_1, self::FILE_1, self::LINE_2, 1);

        $this->assertSame($m1f1l1t1, $sut->generateErrorId(self::MESSAGE_1, self::FILE_1, self::LINE_1, 1));
        $this->assertSame($m1f1l1t1, $m1f1l1);
        $this->assertNotSame($m1f1l1t1, $m2f1l1t1);
        $this->assertNotSame($m1f1l1t1, $m2f1l1t1);
        $this->assertNotSame($m1f1l1t1, $m1f2l1t1);
        $this->assertNotSame($m1f1l1t1, $m1f1l2t1);
    }

    public function testWithEachIdUnique(): void
    {
        $sut   = new ErrorIdGenerator(-1);
        $first = $sut->generateErrorId(self::MESSAGE_1, self::FILE_1, self::LINE_1, 100);

        $this->assertNotSame($first, $sut->generateErrorId(self::MESSAGE_1, self::FILE_1, self::LINE_1, 100));
    }

    public function testWithTimeLimit(): void
    {
        $sut = new ErrorIdGenerator(10);

        $first = $sut->generateErrorId(self::MESSAGE_1, self::FILE_1, self::LINE_1, 100);

        $this->assertSame($first, $sut->generateErrorId(self::MESSAGE_1, self::FILE_1, self::LINE_1, 109));
        $this->assertNotSame($first, $sut->generateErrorId(self::MESSAGE_1, self::FILE_1, self::LINE_1, 99));
        $this->assertNotSame($first, $sut->generateErrorId(self::MESSAGE_1, self::FILE_1, self::LINE_1, 110));
    }
}
