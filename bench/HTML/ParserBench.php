<?php declare(strict_types=1);

namespace Souplette\Benchmarks\HTML;

use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\OutputMode;
use PhpBench\Attributes\OutputTimeUnit;
use PhpBench\Attributes\ParamProviders;
use PhpBench\Attributes\RetryThreshold;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Subject;
use Souplette\Benchmarks\ResourceHelper;
use Souplette\HTML\HTMLParser;

#[RetryThreshold(2.0)]
#[OutputMode('throughput')]
#[OutputTimeUnit('seconds')]
final class ParserBench
{
    #[Subject]
    #[ParamProviders(['fileProvider'])]
    #[Iterations(10)]
    #[Revs(20)]
    public function parse(array $args): void
    {
        $parser = new HTMLParser();
        $parser->parse($args[0]);
    }

    public static function fileProvider(): iterable
    {
        yield 'nyt.html' => [ResourceHelper::readFile('nyt.html')];
    }
}
