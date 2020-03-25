<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\TreeBuilder\RuleSet;

use ju1ius\HtmlParser\Namespaces;
use ju1ius\HtmlParser\Tokenizer\Token;
use ju1ius\HtmlParser\Tokenizer\TokenTypes;
use ju1ius\HtmlParser\TreeBuilder\Elements;
use ju1ius\HtmlParser\TreeBuilder\RuleSet;
use ju1ius\HtmlParser\TreeBuilder\TreeBuilder;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-inforeign
 */
final class InForeignContent extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token->type;
        $currentNode = $tree->openElements->top();
        if ($type === TokenTypes::CHARACTER && $token->data === "\0") {
            // TODO: Parse error.
            // Insert a U+FFFD REPLACEMENT CHARACTER character.
            $tree->insertCharacter(new Token\Character("\u{FFFD}"));
        } elseif ($type === TokenTypes::CHARACTER && ctype_space($token->data)) {
            // Insert the token's character.
            $tree->insertCharacter($token);
        } elseif ($type === TokenTypes::CHARACTER) {
            // Insert the token's character.
            $tree->insertCharacter($token);
            // Set the frameset-ok flag to "not ok".
            $tree->framesetOK = false;
        } elseif ($type === TokenTypes::COMMENT) {
            // Insert a comment.
            $tree->insertComment($token);
        } elseif ($type === TokenTypes::DOCTYPE) {
            // TODO: Parse error.
            // Ignore the token.
            return;
        } elseif ($type === TokenTypes::START_TAG && (
            isset(self::BREAKOUT_TAGS[$token->name])
            || ($token->name === 'font' && (
                isset($token->attributes['color'])
                || isset($token->attributes['face'])
                || isset($token->attributes['size'])
            ))
        )) {
            // TODO: Parse error.
            // If the parser was created as part of the HTML fragment parsing algorithm,
            // then act as described in the "any other start tag" entry below. (fragment case)
            if ($tree->isBuildingFragment) {
                goto ANY_OTHER_START_TAG;
            }
            // Otherwise:
            // Pop an element from the stack of open elements, and then keep popping more elements from the stack of open elements
            // until the current node is a MathML text integration point, an HTML integration point, or an element in the HTML namespace.
            $tree->openElements->popUntilForeignContentScopeMarker();
            // Then, reprocess the token.
            $tree->processToken($token);
        } elseif ($type === TokenTypes::START_TAG) {
            ANY_OTHER_START_TAG:
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
        } elseif ($type === TokenTypes::END_TAG && $token->name === 'script' && $currentNode->namespaceURI === Namespaces::SVG) {
            // Pop the current node off the stack of open elements.
            $tree->openElements->pop();
            // NOTE: The rest of the spec is skipped since we don't execute scripts
        } elseif ($type === TokenTypes::END_TAG) {
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

    private const BREAKOUT_TAGS = [
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
}
