<?php declare(strict_types=1);

namespace Souplette\Tests\Encoding;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Encoding\Confidence;
use Souplette\Encoding\Encoding;
use Souplette\Encoding\EncodingLookup;
use Souplette\Encoding\Exception\UnsupportedEncoding;

final class EncodingTest extends TestCase
{
    public function testItThrowsIfEncodingIsNotSupported()
    {
        $this->expectException(UnsupportedEncoding::class);
        $encoding = Encoding::irrelevant('nothing');
    }

    public function testItNormalizesLabels()
    {
        $encoding = Encoding::irrelevant('utf8');
        Assert::assertSame(EncodingLookup::UTF_8, (string)$encoding);
    }

    public function testTheDefaultEncoding()
    {
        $encoding = Encoding::default();
        Assert::assertSame('windows-1252', $encoding->name);
        Assert::assertSame(Confidence::TENTATIVE, $encoding->confidence);
    }

    public function testConstructors()
    {
        Assert::assertTrue(Encoding::irrelevant('utf-8')->isIrrelevant());
        Assert::assertTrue(Encoding::tentative('utf-8')->isTentative());
        Assert::assertTrue(Encoding::certain('utf-8')->isCertain());
    }

    public function testMutators()
    {
        Assert::assertTrue(Encoding::irrelevant('utf-8')->makeCertain()->isCertain());
        Assert::assertTrue(Encoding::irrelevant('utf-8')->makeTentative()->isTentative());
        Assert::assertTrue(Encoding::tentative('utf-8')->makeIrrelevant()->isIrrelevant());
    }
}
