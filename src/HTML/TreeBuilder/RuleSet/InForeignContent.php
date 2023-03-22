<?php declare(strict_types=1);

namespace Souplette\HTML\TreeBuilder\RuleSet;

use Souplette\DOM\Namespaces;
use Souplette\HTML\Tokenizer\Token;
use Souplette\HTML\Tokenizer\TokenKind;
use Souplette\HTML\TreeBuilder;
use Souplette\HTML\TreeBuilder\RuleSet;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-inforeign
 */
final class InForeignContent extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree): void
    {
        $type = $token::KIND;
        $currentNode = $tree->openElements->top();
        if ($type === TokenKind::Characters && $token->data === "\0") {
            // TODO: Parse error. unexpected-null-character
            // Insert a U+FFFD REPLACEMENT CHARACTER character.
            $tree->insertCharacter(new Token\Character("\u{FFFD}"));
        } else if ($type === TokenKind::Characters && ctype_space($token->data)) {
            // Insert the token's character.
            $tree->insertCharacter($token);
        } else if ($type === TokenKind::Characters) {
            // Insert the token's character.
            $tree->insertCharacter($token);
            // Set the frameset-ok flag to "not ok".
            $tree->framesetOK = false;
        } else if ($type === TokenKind::Comment) {
            // Insert a comment.
            $tree->insertComment($token);
        } else if ($type === TokenKind::Doctype) {
            // TODO: Parse error.
            // Ignore the token.
            return;
        } else if (match($type) {
            TokenKind::StartTag => (
                isset(self::BREAKOUT_START_TAGS[$token->name])
                || ($token->name === 'font' && (
                    isset($token->attributes['color'])
                    || isset($token->attributes['face'])
                    || isset($token->attributes['size'])
                ))
            ),
            TokenKind::EndTag => isset(self::BREAKOUT_END_TAGS[$token->name]),
            default => false,
        }) {
            // TODO: Parse error.
            // While the current node is not a MathML text integration point, an HTML integration point,
            // or an element in the HTML namespace, pop elements from the stack of open elements.
            $tree->openElements->popUntilForeignContentScopeMarker();
            // Reprocess the token according to the rules given in the section
            // corresponding to the current insertion mode in HTML content.
            $tree->processToken($token);
        } else if ($type === TokenKind::StartTag) {
            $adjustedCurrentNode = $tree->getAdjustedCurrentNode();
            // If the adjusted current node is an element in the MathML namespace, adjust MathML attributes for the token.
            // (This fixes the case of MathML attributes that are not all lowercase.)
            if ($adjustedCurrentNode->namespaceURI === Namespaces::MATHML) {
                $tree->adjustMathMlAttributes($token);
            }
            // If the adjusted current node is an element in the SVG namespace,
            // and the token's tag name is one of the ones in the first column of the following table,
            // change the tag name to the name given in the corresponding cell in the second column.
            // (This fixes the case of SVG elements that are not all lowercase.)
            if ($adjustedCurrentNode->namespaceURI === Namespaces::SVG) {
                $tree->adjustSvgTagName($token);
            }
            // If the adjusted current node is an element in the SVG namespace,
            // adjust SVG attributes for the token. (This fixes the case of SVG attributes that are not all lowercase.)
            if ($adjustedCurrentNode->namespaceURI === Namespaces::SVG) {
                $tree->adjustSvgAttributes($token);
            }
            // Adjust foreign attributes for the token.
            // (This fixes the use of namespaced attributes, in particular XLink in SVG.)
            $tree->adjustForeignAttributes($token);
            // Insert a foreign element for the token, in the same namespace as the adjusted current node.
            $tree->insertElement($token, $adjustedCurrentNode->namespaceURI);
            // If the token has its self-closing flag set, then run the appropriate steps from the following list:
            if ($token->selfClosing) {
                // -> If the token's tag name is "script", and the new current node is in the SVG namespace
                //    Acknowledge the token's self-closing flag, and then act as described in the steps for a "script" end tag below.
                if ($token->name === 'script' && $tree->openElements->top()->namespaceURI === Namespaces::SVG) {
                    $tree->acknowledgeSelfClosingFlag($token);
                    $tree->openElements->pop();
                } else {
                    // -> Otherwise
                    //    Pop the current node off the stack of open elements and acknowledge the token's self-closing flag.
                    $tree->openElements->pop();
                    $tree->acknowledgeSelfClosingFlag($token);
                }
            }
        } else if ($type === TokenKind::EndTag && $token->name === 'script' && $currentNode->namespaceURI === Namespaces::SVG) {
            // Pop the current node off the stack of open elements.
            $tree->openElements->pop();
            // NOTE: The rest of the spec is skipped since we don't execute scripts
        } else if ($type === TokenKind::EndTag) {
            // Initialize node to be the current node (the bottommost node of the stack).
            $tree->openElements->rewind();
            $node = $tree->openElements->current();
            // If node's tag name, converted to ASCII lowercase, is not the same as the tag name of the token, then this is a parse error.
            if (strcasecmp($node->localName, $token->name) !== 0) {
                // TODO: Parse error.
            }
            // TODO: both Blink & html5lib do other work in here. Investigate this.
            while ($node && $node->namespaceURI !== Namespaces::HTML) {
                // Loop: If node is the topmost element in the stack of open elements, then return. (fragment case)
                if ($node === $tree->openElements->bottom()) {
                    return;
                }
                // If node's tag name, converted to ASCII lowercase, is the same as the tag name of the token,
                // pop elements from the stack of open elements until node has been popped from the stack, and then return.
                if (strcasecmp($node->localName, $token->name) === 0) {
                    $tree->openElements->popUntil($node);
                    return;
                }
                // Set node to the previous entry in the stack of open elements.
                $tree->openElements->next();
                $node = $tree->openElements->current();
                // If node is not an element in the HTML namespace, return to the step labeled loop.
            }
            // Otherwise, process the token according to the rules given in the section corresponding to the current insertion mode in HTML content.
            $tree->processToken($token);
        }
    }

    private const BREAKOUT_START_TAGS = [
        'b' => true,
        'big' => true,
        'blockquote' => true,
        'body' => true,
        'br' => true,
        'center' => true,
        'code' => true,
        'dd' => true,
        'div' => true,
        'dl' => true,
        'dt' => true,
        'em' => true,
        'embed' => true,
        'h1' => true,
        'h2' => true,
        'h3' => true,
        'h4' => true,
        'h5' => true,
        'h6' => true,
        'head' => true,
        'hr' => true,
        'i' => true,
        'img' => true,
        'li' => true,
        'listing' => true,
        'menu' => true,
        'meta' => true,
        'nobr' => true,
        'ol' => true,
        'p' => true,
        'pre' => true,
        'ruby' => true,
        's' => true,
        'small' => true,
        'span' => true,
        'strong' => true,
        'strike' => true,
        'sub' => true,
        'sup' => true,
        'table' => true,
        'tt' => true,
        'u' => true,
        'ul' => true,
        'var' => true,
    ];

    private const BREAKOUT_END_TAGS = [
        'br' => true,
        'p' => true,
    ];
}
