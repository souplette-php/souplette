<?php declare(strict_types=1);

namespace Souplette\Tests\WebPlatformTests\Css\Selectors;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Dom\Document;
use Souplette\Dom\Element;
use Souplette\Souplette;

/**
 * Ported from web-platform-tests
 * wpt/css/selectors/has-relative-argument.html
 */
final class HasRelativeArgumentTest extends TestCase
{
    const DOCUMENT = <<<'HTML'
    <!DOCTYPE html>
    <meta charset="utf-8">
    <title>:has pseudo class behavior with various relative arguments</title>
    <link rel="author" title="Byungwoo Lee" href="mailto:blee@igalia.com">
    <link rel="help" href="https://drafts.csswg.org/selectors/#relational">

    <main id=main>
     <div id=d01>
      <div id=d02 class="x">
        <div id=d03 class="a"></div>
        <div id=d04></div>
        <div id=d05 class="b"></div>
      </div>
      <div id=d06 class="x">
        <div id=d07 class="x">
          <div id=d08 class="a"></div>
        </div>
      </div>
      <div id=d09 class="x">
        <div id=d10 class="a">
          <div id=d11 class="b"></div>
        </div>
      </div>
      <div id=d12 class="x">
        <div id=d13 class="a">
          <div id=d14>
            <div id=d15 class="b"></div>
          </div>
        </div>
        <div id=d16 class="b"></div>
      </div>
     </div>
     <div id=d17>
      <div id=d18 class="x"></div>
      <div id=d19 class="x"></div>
      <div id=d20 class="a"></div>
      <div id=d21 class="x"></div>
      <div id=d22 class="a">
       <div id=d23 class="b"></div>
      </div>
      <div id=d24 class="x"></div>
      <div id=d25 class="a">
       <div id=d26>
        <div id=d27 class="b"></div>
       </div>
      </div>
      <div id=d28 class="x"></div>
      <div id=d29 class="a"></div>
      <div id=d30 class="b">
       <div id=d31 class="c"></div>
      </div>
      <div id=d32 class="x"></div>
      <div id=d33 class="a"></div>
      <div id=d34 class="b">
       <div id=d35>
        <div id=d36 class="c"></div>
       </div>
      </div>
      <div id=d37 class="x"></div>
      <div id=d38 class="a"></div>
      <div id=d39 class="b"></div>
      <div id=d40 class="x"></div>
      <div id=d41 class="a"></div>
      <div id=d42></div>
      <div id=d43 class="b">
       <div id=d44 class="x">
        <div id=d45 class="c"></div>
       </div>
      </div>
      <div id=d46 class="x"></div>
      <div id=d47 class="a">
      </div>
     </div>
     <div>
      <div id=d48 class="x">
       <div id=d49 class="x">
        <div id=d50 class="x d">
         <div id=d51 class="x d">
          <div id=d52 class="x">
           <div id=d53 class="x e">
            <div id=d54 class="f"></div>
           </div>
          </div>
         </div>
        </div>
       </div>
      </div>
      <div id=d55 class="x"></div>
      <div id=d56 class="x d"></div>
      <div id=d57 class="x d"></div>
      <div id=d58 class="x"></div>
      <div id=d59 class="x e"></div>
      <div id=d60 class="f"></div>
     </div>
     <div>
      <div id=d61 class="x"></div>
      <div id=d62 class="x y"></div>
      <div id=d63 class="x y">
       <div id=d64 class="y g">
        <div id=d65 class="y">
         <div id=d66 class="y h">
          <div id=d67 class="i"></div>
         </div>
        </div>
       </div>
      </div>
      <div id=d68 class="x y">
       <div id=d69 class="x"></div>
       <div id=d70 class="x"></div>
       <div id=d71 class="x y">
        <div id=d72 class="y g">
         <div id=d73 class="y">
          <div id=d74 class="y h">
           <div id=d75 class="i"></div>
          </div>
         </div>
        </div>
       </div>
       <div id=d76 class="x"></div>
       <div id=d77 class="j"><div id=d78><div id=d79></div></div></div>
      </div>
      <div id=d80 class="j"></div>
     </div>
    </main>
    HTML;

    private static Document $document;

    public static function setUpBeforeClass(): void
    {
        self::$document = Souplette::parseHtml(self::DOCUMENT);
    }

    /**
     * @param Element[] $elements
     * @return string[]
     */
    private function formatElements(array $elements): array
    {
        return array_map(fn($el) => $el->id, $elements);
    }

    /**
     * @dataProvider querySelectorAllProvider
     */
    public function testQuerySelectorAll(string $selector, array $expected)
    {
        $main = self::$document->getElementById('main');
        $actual = $main->querySelectorAll($selector);
        Assert::assertEquals($expected, $this->formatElements($actual));
    }

    public function querySelectorAllProvider(): iterable
    {
        yield ['.x:has(.a)', ['d02', 'd06', 'd07', 'd09', 'd12']];
        yield ['.x:has(.a > .b)', ['d09']];
        yield ['.x:has(.a .b)', ['d09', 'd12']];
        yield ['.x:has(.a + .b)', ['d12']];
        yield ['.x:has(.a ~ .b)', ['d02', 'd12']];

        yield ['.x:has(> .a)', ['d02', 'd07', 'd09', 'd12']];
        yield ['.x:has(> .a > .b)', ['d09']];
        yield ['.x:has(> .a .b)', ['d09', 'd12']];
        yield ['.x:has(> .a + .b)', ['d12']];
        yield ['.x:has(> .a ~ .b)', ['d02', 'd12']];

        yield ['.x:has(+ .a)', ['d19', 'd21', 'd24', 'd28', 'd32', 'd37', 'd40', 'd46']];
        yield ['.x:has(+ .a > .b)', ['d21']];
        yield ['.x:has(+ .a .b)', ['d21', 'd24']];
        yield ['.x:has(+ .a + .b)', ['d28', 'd32', 'd37']];
        yield ['.x:has(+ .a ~ .b)', ['d19', 'd21', 'd24', 'd28', 'd32', 'd37', 'd40']];

        yield ['.x:has(~ .a)', ['d18', 'd19', 'd21', 'd24', 'd28', 'd32', 'd37', 'd40', 'd46']];
        yield ['.x:has(~ .a > .b)', ['d18', 'd19', 'd21']];
        yield ['.x:has(~ .a .b)', ['d18', 'd19', 'd21', 'd24']];
        yield ['.x:has(~ .a + .b)', ['d18', 'd19', 'd21', 'd24', 'd28', 'd32', 'd37']];
        yield ['.x:has(~ .a + .b > .c)', ['d18', 'd19', 'd21', 'd24', 'd28']];
        yield ['.x:has(~ .a + .b .c)', ['d18', 'd19', 'd21', 'd24', 'd28', 'd32']];

        yield ['.x:has(.d .e)', ['d48', 'd49', 'd50']];
        yield ['.x:has(.d .e) .f', ['d54']];
        yield ['.x:has(> .d)', ['d49', 'd50']];
        yield ['.x:has(> .d) .f', ['d54']];
        yield ['.x:has(~ .d ~ .e)', ['d48', 'd55', 'd56']];
        yield ['.x:has(~ .d ~ .e) ~ .f', ['d60']];
        yield ['.x:has(+ .d ~ .e)', ['d55', 'd56']];
        yield ['.x:has(+ .d ~ .e) ~ .f', ['d60']];

        yield ['.y:has(> .g .h)', ['d63', 'd71']];
        yield ['.y:has(.g .h)', ['d63', 'd68', 'd71']];
        yield ['.y:has(> .g .h) .i', ['d67', 'd75']];
        yield ['.y:has(.g .h) .i', ['d67', 'd75']];
        // FIXME: the following fails
        yield ['.x:has(+ .y:has(> .g .h) .i)', ['d62', 'd70']];
        yield ['.x:has(+ .y:has(.g .h) .i)', ['d62', 'd63', 'd70']];
        yield ['.x:has(+ .y:has(> .g .h) .i) ~ .j', ['d77', 'd80']];
        yield ['.x:has(+ .y:has(.g .h) .i) ~ .j', ['d77', 'd80']];
        yield ['.x:has(~ .y:has(> .g .h) .i)', ['d61', 'd62', 'd69', 'd70']];
        yield ['.x:has(~ .y:has(.g .h) .i)', ['d61', 'd62', 'd63', 'd69', 'd70']];
        
        yield ['.d .x:has(.e)', ['d51', 'd52']];
        
        yield ['.d ~ .x:has(~ .e)', ['d57', 'd58']];

    }
}
