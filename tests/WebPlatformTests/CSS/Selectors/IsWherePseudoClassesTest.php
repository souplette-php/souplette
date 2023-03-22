<?php declare(strict_types=1);

namespace Souplette\Tests\WebPlatformTests\CSS\Selectors;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Souplette\DOM\Document;
use Souplette\DOM\Element;
use Souplette\Souplette;

/**
 * Ported from web-platform-tests
 * wpt/css/selectors/is-where-pseudo-classes.html
 */
final class IsWherePseudoClassesTest extends TestCase
{
    const DOCUMENT = <<<'HTML'
    <!DOCTYPE html>
    <title>:is() combined with pseudo-classes</title>
    <link rel="help" href="https://drafts.csswg.org/selectors-4/#matches">
    <link rel="help" href="https://drafts.csswg.org/selectors/#useraction-pseudos">
    <main>
      <button id=a>A</button>
      <button id=b>B</button>
      <button id=c>C</button>
      <button id=d disabled>D</button>
      <button id=e disabled>E</button>
      <button id=f disabled>F</button>
    </main>
    HTML;

    private static Document $document;

    public static function setUpBeforeClass(): void
    {
        self::$document = Souplette::parseHTML(self::DOCUMENT);
    }

    /**
     * @param Element[] $elements
     * @return string
     */
    private function formatElements(array $elements): string
    {
        return implode(',', array_map(fn($el) => $el->id, $elements));
    }

    #[DataProvider('querySelectorAllProvider')]
    public function testQuerySelectorAll(string $selector, string $expected)
    {
        /** @var Element $main */
        $main = self::$document->getElementsByTagName('main')[0];
        $actual = $main->querySelectorAll($selector);
        Assert::assertEquals($expected, $this->formatElements($actual));
    }

    public static function querySelectorAllProvider(): iterable
    {
        yield [
            ':is(main :where(main #a), #c:nth-child(odd), #d):is(:enabled)',
            'a,c'
        ];
        yield [
            'button:is(:nth-child(even), span #e):is(:enabled, :where(:disabled))',
            'b,d,f'
        ];
    }
}
