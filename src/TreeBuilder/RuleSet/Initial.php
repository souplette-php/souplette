<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\TreeBuilder\RuleSet;

use ju1ius\HtmlParser\Tokenizer\Token;
use ju1ius\HtmlParser\Tokenizer\TokenTypes;
use ju1ius\HtmlParser\TreeBuilder\CompatModes;
use ju1ius\HtmlParser\TreeBuilder\InsertionLocation;
use ju1ius\HtmlParser\TreeBuilder\InsertionModes;
use ju1ius\HtmlParser\TreeBuilder\RuleSet;
use ju1ius\HtmlParser\TreeBuilder\TreeBuilder;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#the-initial-insertion-mode
 */
final class Initial extends RuleSet
{
    private static $PUBLIC_ID_QUIRKS_PATTERN;

    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token->type;
        if ($type === TokenTypes::CHARACTER && ctype_space($token->data)) {
            // Ignore the token.
            return;
        } elseif ($type === TokenTypes::COMMENT) {
            $tree->insertComment($token, new InsertionLocation($tree->document));
        } elseif ($type === TokenTypes::DOCTYPE) {
            if (self::$PUBLIC_ID_QUIRKS_PATTERN === null) {
                self::buildDoctypeQuirksPattern();
            }
            // TODO: If the DOCTYPE token's name is not a case-sensitive match for the string "html",
            // or the token's public identifier is not missing,
            // or the token's system identifier is neither missing nor a case-sensitive match for the string "about:legacy-compat",
            // then there is a parse error.

            // Append a DocumentType node to the Document node,
            // with the name attribute set to the name given in the DOCTYPE token,
            // or the empty string if the name was missing;
            $doctype = $tree->createDoctype($token);
            $tree->document->appendChild($doctype);
            $pub = $token->publicIdentifier;
            $sys = $token->systemIdentifier;
            if (
                $token->forceQuirks
                || $token->name !== 'html'
                || $pub && preg_match(self::$PUBLIC_ID_QUIRKS_PATTERN, $pub)
                || $sys && strcasecmp($sys, 'http://www.ibm.com/data/dtd/v11/ibmxhtml1-transitional.dtd')
                || !$sys && $pub && stripos($pub, '-//W3C//DTD HTML 4.01 Frameset//') === 0
                || !$sys && $pub && stripos($pub, '-//W3C//DTD HTML 4.01 Transitional//') === 0
            ) {
                $tree->compatMode = CompatModes::QUIRKS;
            } elseif (
                $pub && stripos($pub, '-//W3C//DTD XHTML 1.0 Frameset//') === 0
                || $pub && stripos($pub, '-//W3C//DTD XHTML 1.0 Transitional//') === 0
                || $sys && $pub && stripos($pub, '-//W3C//DTD HTML 4.01 Frameset//') === 0
                || $sys && $pub && stripos($pub, '-//W3C//DTD HTML 4.01 Transitional//') === 0
            ) {
                // Otherwise, if the document is not an iframe srcdoc document,
                // and the DOCTYPE token matches one of the conditions in the following list, then set the Document to limited-quirks mode:
                $tree->compatMode = CompatModes::LIMITED_QUIRKS;
            }
            $tree->insertionMode = InsertionModes::BEFORE_HTML;
        } else {
            // TODO: If the document is not an iframe srcdoc document, then this is a parse error;
            // set the Document to quirks mode.
            $tree->compatMode = CompatModes::QUIRKS;
            // In any case, switch the insertion mode to "before html", then reprocess the token.
            $tree->insertionMode = InsertionModes::BEFORE_HTML;
            $tree->processToken($token);
        }
    }

    private static function buildDoctypeQuirksPattern()
    {
        $patterns = array_map(function($p) {
            return preg_quote($p, '#') . '$';
        }, self::PUBLIC_ID_QUIRKS_PATTERNS);
        $patterns += array_map(function($p) {
            return preg_quote($p, '#');
        }, self::PUBLIC_ID_QUIRKS_START_PATTERNS);

        self::$PUBLIC_ID_QUIRKS_PATTERN = sprintf('#^(?:%s)#i', implode('|', $patterns));
    }

    private const PUBLIC_ID_QUIRKS_PATTERNS = [
        '-//W3O//DTD W3 HTML Strict 3.0//EN//',
        '-/W3C/DTD HTML 4.0 Transitional/EN',
        'HTML',
    ];

    private const PUBLIC_ID_QUIRKS_START_PATTERNS = [
        '+//Silmaril//dtd html Pro v0r11 19970101//',
        '-//AS//DTD HTML 3.0 asWedit + extensions//',
        '-//AdvaSoft Ltd//DTD HTML 3.0 asWedit + extensions//',
        '-//IETF//DTD HTML 2.0 Level 1//',
        '-//IETF//DTD HTML 2.0 Level 2//',
        '-//IETF//DTD HTML 2.0 Strict Level 1//',
        '-//IETF//DTD HTML 2.0 Strict Level 2//',
        '-//IETF//DTD HTML 2.0 Strict//',
        '-//IETF//DTD HTML 2.0//',
        '-//IETF//DTD HTML 2.1E//',
        '-//IETF//DTD HTML 3.0//',
        '-//IETF//DTD HTML 3.2 Final//',
        '-//IETF//DTD HTML 3.2//',
        '-//IETF//DTD HTML 3//',
        '-//IETF//DTD HTML Level 0//',
        '-//IETF//DTD HTML Level 1//',
        '-//IETF//DTD HTML Level 2//',
        '-//IETF//DTD HTML Level 3//',
        '-//IETF//DTD HTML Strict Level 0//',
        '-//IETF//DTD HTML Strict Level 1//',
        '-//IETF//DTD HTML Strict Level 2//',
        '-//IETF//DTD HTML Strict Level 3//',
        '-//IETF//DTD HTML Strict//',
        '-//IETF//DTD HTML//',
        '-//Metrius//DTD Metrius Presentational//',
        '-//Microsoft//DTD Internet Explorer 2.0 HTML Strict//',
        '-//Microsoft//DTD Internet Explorer 2.0 HTML//',
        '-//Microsoft//DTD Internet Explorer 2.0 Tables//',
        '-//Microsoft//DTD Internet Explorer 3.0 HTML Strict//',
        '-//Microsoft//DTD Internet Explorer 3.0 HTML//',
        '-//Microsoft//DTD Internet Explorer 3.0 Tables//',
        '-//Netscape Comm. Corp.//DTD HTML//',
        '-//Netscape Comm. Corp.//DTD Strict HTML//',
        "-//O'Reilly and Associates//DTD HTML 2.0//",
        "-//O'Reilly and Associates//DTD HTML Extended 1.0//",
        "-//O'Reilly and Associates//DTD HTML Extended Relaxed 1.0//",
        '-//SQ//DTD HTML 2.0 HoTMetaL + extensions//',
        '-//SoftQuad Software//DTD HoTMetaL PRO 6.0::19990601::extensions to HTML 4.0//',
        '-//SoftQuad//DTD HoTMetaL PRO 4.0::19971010::extensions to HTML 4.0//',
        '-//Spyglass//DTD HTML 2.0 Extended//',
        '-//Sun Microsystems Corp.//DTD HotJava HTML//',
        '-//Sun Microsystems Corp.//DTD HotJava Strict HTML//',
        '-//W3C//DTD HTML 3 1995-03-24//',
        '-//W3C//DTD HTML 3.2 Draft//',
        '-//W3C//DTD HTML 3.2 Final//',
        '-//W3C//DTD HTML 3.2//',
        '-//W3C//DTD HTML 3.2S Draft//',
        '-//W3C//DTD HTML 4.0 Frameset//',
        '-//W3C//DTD HTML 4.0 Transitional//',
        '-//W3C//DTD HTML Experimental 19960712//',
        '-//W3C//DTD HTML Experimental 970421//',
        '-//W3C//DTD W3 HTML//',
        '-//W3O//DTD W3 HTML 3.0//',
        '-//WebTechs//DTD Mozilla HTML 2.0//',
        '-//WebTechs//DTD Mozilla HTML//',
    ];
}
