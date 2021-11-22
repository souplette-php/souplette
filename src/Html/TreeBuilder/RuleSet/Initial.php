<?php declare(strict_types=1);

namespace Souplette\Html\TreeBuilder\RuleSet;

use Souplette\Dom\DocumentModes;
use Souplette\Html\Tokenizer\Token;
use Souplette\Html\Tokenizer\TokenType;
use Souplette\Html\TreeBuilder\InsertionLocation;
use Souplette\Html\TreeBuilder\InsertionModes;
use Souplette\Html\TreeBuilder\RuleSet;
use Souplette\Html\TreeBuilder\TreeBuilder;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#the-initial-insertion-mode
 */
final class Initial extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token::TYPE;
        if ($type === TokenType::CHARACTER && ctype_space($token->data)) {
            // Ignore the token.
            return;
        } else if ($type === TokenType::COMMENT) {
            $tree->insertComment($token, new InsertionLocation($tree->document));
        } else if ($type === TokenType::DOCTYPE) {
            // If the DOCTYPE token's name is not a case-sensitive match for the string "html",
            // or the token's public identifier is not missing,
            // or the token's system identifier is neither missing nor a case-sensitive match for the string "about:legacy-compat",
            // then there is a parse error.
            // TODO: parse error
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
                || $pub && preg_match(self::PUBLIC_ID_QUIRKS_PATTERN, $pub)
                || $sys && strcasecmp($sys, 'http://www.ibm.com/data/dtd/v11/ibmxhtml1-transitional.dtd') === 0
                || !$sys && $pub && preg_match(self::MISSING_SYSTEM_PUBLIC_PATTERN, $pub)
            ) {
                $tree->compatMode = DocumentModes::QUIRKS;
            } else if (
                ($pub && preg_match(self::PUBLIC_ID_LIMITED_QUIRKS_PATTERN, $pub))
                || ($sys && $pub && preg_match(self::MISSING_SYSTEM_PUBLIC_PATTERN, $pub))
            ) {
                // Otherwise,
                // TODO: if the document is not an iframe srcdoc document,
                // and the DOCTYPE token matches one of the conditions in the following list, then set the Document to limited-quirks mode:
                $tree->compatMode = DocumentModes::LIMITED_QUIRKS;
            }
            $tree->insertionMode = InsertionModes::BEFORE_HTML;
        } else {
            // TODO: If the document is not an iframe srcdoc document, then this is a parse error;
            // set the Document to quirks mode.
            $tree->compatMode = DocumentModes::QUIRKS;
            // In any case, switch the insertion mode to "before html", then reprocess the token.
            $tree->insertionMode = InsertionModes::BEFORE_HTML;
            $tree->processToken($token);
        }
    }

    private const PUBLIC_ID_QUIRKS_PATTERN = <<<'REGEXP'
    ~
        ^(?:
            \Q-//W3O//DTD W3 HTML Strict 3.0//EN//\E
            | \Q-/W3C/DTD HTML 4.0 Transitional/EN\E
            | HTML
        )$
        | ^(?:
            \Q+//Silmaril//dtd html Pro v0r11 19970101//\E
            | \Q-//AS//DTD HTML 3.0 asWedit + extensions//\E
            | \Q-//AdvaSoft Ltd//DTD HTML 3.0 asWedit + extensions//\E
            | \Q-//IETF//DTD HTML 2.0 Level 1//\E
            | \Q-//IETF//DTD HTML 2.0 Level 2//\E
            | \Q-//IETF//DTD HTML 2.0 Strict Level 1//\E
            | \Q-//IETF//DTD HTML 2.0 Strict Level 2//\E
            | \Q-//IETF//DTD HTML 2.0 Strict//\E
            | \Q-//IETF//DTD HTML 2.0//\E
            | \Q-//IETF//DTD HTML 2.1E//\E
            | \Q-//IETF//DTD HTML 3.0//\E
            | \Q-//IETF//DTD HTML 3.2 Final//\E
            | \Q-//IETF//DTD HTML 3.2//\E
            | \Q-//IETF//DTD HTML 3//\E
            | \Q-//IETF//DTD HTML Level 0//\E
            | \Q-//IETF//DTD HTML Level 1//\E
            | \Q-//IETF//DTD HTML Level 2//\E
            | \Q-//IETF//DTD HTML Level 3//\E
            | \Q-//IETF//DTD HTML Strict Level 0//\E
            | \Q-//IETF//DTD HTML Strict Level 1//\E
            | \Q-//IETF//DTD HTML Strict Level 2//\E
            | \Q-//IETF//DTD HTML Strict Level 3//\E
            | \Q-//IETF//DTD HTML Strict//\E
            | \Q-//IETF//DTD HTML//\E
            | \Q-//Metrius//DTD Metrius Presentational//\E
            | \Q-//Microsoft//DTD Internet Explorer 2.0 HTML Strict//\E
            | \Q-//Microsoft//DTD Internet Explorer 2.0 HTML//\E
            | \Q-//Microsoft//DTD Internet Explorer 2.0 Tables//\E
            | \Q-//Microsoft//DTD Internet Explorer 3.0 HTML Strict//\E
            | \Q-//Microsoft//DTD Internet Explorer 3.0 HTML//\E
            | \Q-//Microsoft//DTD Internet Explorer 3.0 Tables//\E
            | \Q-//Netscape Comm. Corp.//DTD HTML//\E
            | \Q-//Netscape Comm. Corp.//DTD Strict HTML//\E
            | \Q-//O'Reilly and Associates//DTD HTML 2.0//\E
            | \Q-//O'Reilly and Associates//DTD HTML Extended 1.0//\E
            | \Q-//O'Reilly and Associates//DTD HTML Extended Relaxed 1.0//\E
            | \Q-//SQ//DTD HTML 2.0 HoTMetaL + extensions//\E
            | \Q-//SoftQuad Software//DTD HoTMetaL PRO 6.0::19990601::extensions to HTML 4.0//\E
            | \Q-//SoftQuad//DTD HoTMetaL PRO 4.0::19971010::extensions to HTML 4.0//\E
            | \Q-//Spyglass//DTD HTML 2.0 Extended//\E
            | \Q-//Sun Microsystems Corp.//DTD HotJava HTML//\E
            | \Q-//Sun Microsystems Corp.//DTD HotJava Strict HTML//\E
            | \Q-//W3C//DTD HTML 3 1995-03-24//\E
            | \Q-//W3C//DTD HTML 3.2 Draft//\E
            | \Q-//W3C//DTD HTML 3.2 Final//\E
            | \Q-//W3C//DTD HTML 3.2//\E
            | \Q-//W3C//DTD HTML 3.2S Draft//\E
            | \Q-//W3C//DTD HTML 4.0 Frameset//\E
            | \Q-//W3C//DTD HTML 4.0 Transitional//\E
            | \Q-//W3C//DTD HTML Experimental 19960712//\E
            | \Q-//W3C//DTD HTML Experimental 970421//\E
            | \Q-//W3C//DTD W3 HTML//\E
            | \Q-//W3O//DTD W3 HTML 3.0//\E
            | \Q-//WebTechs//DTD Mozilla HTML 2.0//\E
            | \Q-//WebTechs//DTD Mozilla HTML//\E
        )
    ~xi
    REGEXP;

    private const MISSING_SYSTEM_PUBLIC_PATTERN = <<<'REGEXP'
    ~
        ^(?:
            \Q-//W3C//DTD HTML 4.01 Frameset//\E
            | \Q-//W3C//DTD HTML 4.01 Transitional//\E
        )
    ~xi
    REGEXP;

    private const PUBLIC_ID_LIMITED_QUIRKS_PATTERN = <<<'REGEXP'
    ~
        ^(?:
            \Q-//W3C//DTD XHTML 1.0 Frameset//\E
            |\Q -//W3C//DTD XHTML 1.0 Transitional//\E
        )
    ~xi
    REGEXP;

}
