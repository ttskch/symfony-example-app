<?php

declare(strict_types=1);

namespace App\Service;

use PHPUnit\Framework\TestCase;

class NamerTest extends TestCase
{
    private $SUT;

    protected function setUp(): void
    {
        $this->SUT = new Namer();
    }

    public function testBeautify()
    {
        $this->assertNull($this->SUT->beautify(null));
        $this->assertEquals('株式会社 HOGE FUGA', $this->SUT->beautify('  株式会社　　ＨＯＧＥ  ＦＵＧＡ　　'));
    }
}
