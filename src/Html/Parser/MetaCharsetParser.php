<?php declare(strict_types=1);

namespace Souplette\Html\Parser;

use Souplette\Encoding\EncodingLookup;
use Souplette\Html\Tokenizer\Token\StartTag;
use Souplette\Html\Tokenizer\Tokenizer;
use Souplette\Html\Tokenizer\TokenizerState;
use Souplette\Html\Tokenizer\TokenType;

final class MetaCharsetParser
{
    private const MIN_BYTES_CHECKED = 1024;

    private const ALLOWED_END_TAGS = [
        'script' => true,
        'noscript' => true,
        'style' => true,
        'link' => true,
        'meta' => true,
        'object' => true,
        'title' => true,
        'base' => true,
    ];

    private const ALLOWED_START_TAGS = self::ALLOWED_END_TAGS + [
        'html' => true,
        'head' => true,
    ];

    private const META_CHARSET_PATTERN = <<<'REGEXP'
    @
       charset \s* = \s*
       (?>
            " (?<value> [^"]+ ) "
            | ' (?<value> [^']+ ) '
            | (?<value> [^\t\n\f\r ;]+ )
       )
    @Jix
    REGEXP;

    public function __construct(private Tokenizer $tokenizer)
    {
    }

    public function parse(): ?string
    {
        // We still don't have an encoding, and are in the head. The following tags
        // are allowed in <head>: SCRIPT|STYLE|META|LINK|OBJECT|TITLE|BASE

        // We stop scanning when a tag that is not permitted in <head> is seen, rather
        // when </head> is seen, because that more closely matches behavior in other
        // browsers; more details in <http://bugs.webkit.org/show_bug.cgi?id=3590>.

        // Additionally, we ignore things that looks like tags in <title>, <script>
        // and <noscript>; see:
        // <http://bugs.webkit.org/show_bug.cgi?id=4560>
        // <http://bugs.webkit.org/show_bug.cgi?id=12165>
        // <http://bugs.webkit.org/show_bug.cgi?id=12389>

        // Since many sites have charset declarations after <body> or other tags that
        // are disallowed in <head>, we don't bail out until we've checked at least
        // MIN_BYTES_CHECKED bytes of input.

        $inHead = true;
        $charset = null;
        foreach ($this->tokenizer->tokenize() as $token) {
            $tt = $token::TYPE;
            if ($tt === TokenType::START_TAG && $token->name === 'meta') {
                if ($charset = self::encodingFromMetaAttributes($token->attributes ?? [])) {
                    return $charset;
                }
                $this->updateTokenizerState($token);
            } else if ($tt === TokenType::START_TAG) {
                if (!isset(self::ALLOWED_START_TAGS[$token->name])) {
                    $inHead = false;
                }
            } else if ($tt === TokenType::END_TAG) {
                if (!isset(self::ALLOWED_END_TAGS[$token->name])) {
                    $inHead = false;
                }
            }
            if (!$inHead && $this->tokenizer->getPosition() >= self::MIN_BYTES_CHECKED) {
                return $charset;
            }
        }

        return $charset;
    }

    public static function encodingFromMetaAttributes(array $attributes): ?string
    {
        $attributeList = [];
        $gotPragma = false;
        $needPragma = null;
        $charset = null;
        foreach ($attributes as $name => $value) {
            if (isset($attributeList[$name])) {
                continue;
            } else {
                $attributeList[$name] = true;
            }
            if ($name === 'http-equiv' && strcasecmp($value, 'content-type') === 0) {
                // If the attribute's value is "content-type", then set got pragma to true.
                $gotPragma = true;
            } else if ($charset === null && $name === 'content') {
                // Apply the algorithm for extracting a character encoding from a meta element,
                // giving the attribute's value as the string to parse.
                $charset = self::extractFromMetaContentAttribute($value);
                // If a character encoding is returned, and if charset is still set to null,
                // let charset be the encoding returned, and set need pragma to true.
                if ($charset) {
                    $needPragma = true;
                }
            } else if ($name === 'charset') {
                // Let charset be the result of getting an encoding from the attribute's value,
                $label = strtolower(trim($value));
                $charset = EncodingLookup::LABELS[$label] ?? null;
                // and set need pragma to false.
                $needPragma = false;
            }
        }
        // 11. Processing: If need pragma is null, then jump to the step below labeled next byte.
        if ($needPragma === null) {
            return null;
        }
        // 12. If need pragma is true but got pragma is false, then jump to the step below labeled next byte.
        if ($needPragma && !$gotPragma) {
            return null;
        }
        // 13. If charset is failure, then jump to the step below labeled next byte.
        if (!$charset) {
            return null;
        }
        // 14. If charset is a UTF-16 encoding, then set charset to UTF-8.
        if ($charset === EncodingLookup::UTF_16BE || $charset === EncodingLookup::UTF_16LE) {
            $charset = EncodingLookup::UTF_8;
        }
        // 15. If charset is x-user-defined, then set charset to windows-1252.
        if ($charset === EncodingLookup::X_USER_DEFINED) {
            $charset = EncodingLookup::WINDOWS_1252;
        }
        // 16. Abort the prescan a byte stream to determine its encoding algorithm,
        // returning the encoding given by charset.
        return $charset;
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/urls-and-fetching.html#algorithm-for-extracting-a-character-encoding-from-a-meta-element
     */
    public static function extractFromMetaContentAttribute(string $input): ?string
    {
        // NOTE: This method has been inlined in self::sniff()
        // Please keep the code in sync if you change the algorithm.
        if (preg_match(self::META_CHARSET_PATTERN, $input, $matches)) {
            $label = strtolower(trim($matches['value']));
            return EncodingLookup::LABELS[$label] ?? null;
        }

        return null;
    }

    private function updateTokenizerState(StartTag $token): void
    {
        switch ($token->name) {
            case 'textarea':
            case 'title':
                $this->tokenizer->state = TokenizerState::RCDATA;
                break;
            case 'plaintext':
                $this->tokenizer->state = TokenizerState::PLAINTEXT;
                break;
            case 'script':
                $this->tokenizer->state = TokenizerState::SCRIPT_DATA;
                break;
            case 'style':
            case 'iframe':
            case 'xmp':
            case 'noembed':
            case 'noframes':
                $this->tokenizer->state = TokenizerState::RAWTEXT;
                break;
            case 'noscript':
                if (true /* scripting enabled */) {
                    $this->tokenizer->state = TokenizerState::RAWTEXT;
                }
                break;
            default:
                break;
        }
    }
}
