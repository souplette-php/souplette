<?php declare(strict_types=1);

namespace Souplette\Html\Parser\TreeBuilder\RuleSet;

use Souplette\Encoding\EncodingLookup;
use Souplette\Html\Namespaces;
use Souplette\Html\Parser\EncodingSniffer;
use Souplette\Html\Parser\Tokenizer\Token;
use Souplette\Html\Parser\Tokenizer\TokenizerStates;
use Souplette\Html\Parser\Tokenizer\TokenTypes;
use Souplette\Html\Parser\TreeBuilder\InsertionModes;
use Souplette\Html\Parser\TreeBuilder\RuleSet;
use Souplette\Html\Parser\TreeBuilder\TreeBuilder;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-inhead
 */
final class InHead extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token::TYPE;
        if ($type === TokenTypes::CHARACTER) {
            if (ctype_space($token->data)) {
                $tree->insertCharacter($token);
                return;
            }
            if ($l = strspn($token->data, " \n\t\f")) {
                $tree->insertCharacter($token, substr($token->data, 0, $l));
                $token->data = substr($token->data, $l);
            }
        } elseif ($type === TokenTypes::COMMENT) {
            $tree->insertComment($token);
            return;
        } elseif ($type === TokenTypes::DOCTYPE) {
            // TODO: Parse error. Ignore the token.
            return;
        } elseif ($type === TokenTypes::START_TAG) {
            $name = $token->name;
            if ($name === 'html') {
                InBody::process($token, $tree);
                return;
            } elseif (
                $name === 'base'
                || $name === 'basefont'
                || $name === 'bgsound'
                || $name === 'link'
            ) {
                $tree->insertElement($token);
                $tree->openElements->pop();
                $tree->acknowledgeSelfClosingFlag($token);
                return;
            } elseif ($name === 'meta') {
                $tree->insertElement($token);
                $tree->openElements->pop();
                $tree->acknowledgeSelfClosingFlag($token);
                if ($tree->encoding->isTentative()) {
                    if (isset($token->attributes['charset'])) {
                        // If the element has a charset attribute,
                        // and getting an encoding from its value results in an encoding,
                        // and the confidence is currently tentative,
                        // then change the encoding to the resulting encoding.
                        $label = strtolower(trim($token->attributes['charset']));
                        if (isset(EncodingLookup::LABELS[$label])) {
                            $tree->changeTheEncoding(EncodingLookup::LABELS[$label]);
                        }
                    } elseif (
                        isset($token->attributes['http-equiv'], $token->attributes['content'])
                        && strcasecmp($token->attributes['http-equiv'], 'content-type') === 0
                    ) {
                        // Otherwise, if the element has an http-equiv attribute
                        // whose value is an ASCII case-insensitive match for the string "Content-Type",
                        // and the element has a content attribute,
                        // and applying the algorithm for extracting a character encoding from a meta element
                        // to that attribute's value returns an encoding,
                        // and the confidence is currently tentative,
                        // then change the encoding to the extracted encoding.
                        $label = EncodingSniffer::extractFromMetaContentAttribute($token->attributes['content']);
                        if (isset(EncodingLookup::LABELS[$label])) {
                            $tree->changeTheEncoding(EncodingLookup::LABELS[$label]);
                        }
                    }
                }
                return;
            } elseif ($name === 'title') {
                // Follow the generic RCDATA element parsing algorithm.
                $tree->followTheGenericTextElementParsingAlgorithm($token);
                return;
            } elseif (
                ($tree->scriptingEnabled && $name === 'noscript')
                || $name === 'noframes'
                || $name === 'style'
            ) {
                // Follow the generic RAWTEXT element parsing algorithm.
                $tree->followTheGenericTextElementParsingAlgorithm($token, true);
                return;
            } elseif (!$tree->scriptingEnabled && $name === 'noscript') {
                $tree->insertElement($token);
                $tree->insertionMode = InsertionModes::IN_HEAD_NOSCRIPT;
                return;
            } elseif ($name === 'script') {
                $location = $tree->appropriatePlaceForInsertingANode();
                $node = $tree->createElement($token, Namespaces::HTML, $location->parent);
                $location->insert($node);
                $tree->openElements->push($node);
                $tree->tokenizer->state = TokenizerStates::SCRIPT_DATA;
                $tree->originalInsertionMode = $tree->insertionMode;
                $tree->insertionMode = InsertionModes::TEXT;
                return;
            } elseif ($name === 'template') {
                // Insert an HTML element for the token.
                $tree->insertElement($token);
                // Insert a marker at the end of the list of active formatting elements.
                $tree->activeFormattingElements->push(null);
                // Set the frameset-ok flag to "not ok".
                $tree->framesetOK = false;
                // Switch the insertion mode to "in template".
                $tree->insertionMode = InsertionModes::IN_TEMPLATE;
                // Push "in template" onto the stack of template insertion modes so that it is the new current template insertion mode.
                $tree->templateInsertionModes->push(InsertionModes::IN_TEMPLATE);
                return;
            } elseif ($name === 'head') {
                // TODO: Parse error. Ignore the token.
                return;
            }
        } elseif ($type === TokenTypes::END_TAG) {
            $name = $token->name;
            if ($name === 'head') {
                $tree->openElements->pop();
                $tree->insertionMode = InsertionModes::AFTER_HEAD;
                return;
            } elseif (
                $name === 'body'
                || $name === 'html'
                || $name === 'br'
            ) {
                // Act as described in the "anything else" entry below.
            } elseif ($name === 'template') {
                // If there is no template element on the stack of open elements, then this is a parse error; ignore the token.
                if (!$tree->openElements->containsTag('template')) {
                    // TODO: Parse error
                    return;
                }
                // Otherwise, run these steps:
                // Generate all implied end tags thoroughly.
                $tree->generateImpliedEndTags(null, true);
                // If the current node is not a template element, then this is a parse error.
                if ($tree->openElements->top()->localName !== 'template') {
                    // TODO: Parse error.
                }
                // Pop elements from the stack of open elements until a template element has been popped from the stack.
                $tree->openElements->popUntilTag('template');
                // Clear the list of active formatting elements up to the last marker.
                $tree->activeFormattingElements->clearUpToLastMarker();
                // Pop the current template insertion mode off the stack of template insertion modes.
                $tree->templateInsertionModes->pop();
                // Reset the insertion mode appropriately.
                $tree->resetInsertionModeAppropriately();
                return;
            } else {
                // TODO: Parse error. Ignore the token.
                return;
            }
        }

        $tree->openElements->pop();
        $tree->insertionMode = InsertionModes::AFTER_HEAD;
        $tree->processToken($token);
    }
}
