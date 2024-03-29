<?php declare(strict_types=1);

namespace Souplette\HTML\TreeBuilder\RuleSet;

use Souplette\DOM\Internal\DocumentMode;
use Souplette\DOM\Namespaces;
use Souplette\HTML\Tokenizer\Token;
use Souplette\HTML\Tokenizer\TokenizerState;
use Souplette\HTML\Tokenizer\TokenKind;
use Souplette\HTML\TreeBuilder;
use Souplette\HTML\TreeBuilder\Elements;
use Souplette\HTML\TreeBuilder\InsertionModes;
use Souplette\HTML\TreeBuilder\RuleSet;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-inbody
 */
final class InBody extends RuleSet
{
    private const IN_HEAD_START_TAGS = [
        'base' => true,
        'basefont' => true,
        'bgsound' => true,
        'link' => true,
        'meta' => true,
        'noframes' => true,
        'script' => true,
        'style' => true,
        'template' => true,
        'title' => true,
    ];

    private const ADOPTION_AGENCY_END_TAG_TRIGGERS = [
        'a' => true,
        'b' => true,
        'big' => true,
        'code' => true,
        'em' => true,
        'font' => true,
        'i' => true,
        'nobr' => true,
        's' => true,
        'small' => true,
        'strike' => true,
        'strong' => true,
        'tt' => true,
        'u' => true,
    ];

    private const CLOSE_P_START_TAGS = [
        'address' => true,
        'article' => true,
        'aside' => true,
        'blockquote' => true,
        'center' => true,
        'details' => true,
        'dialog' => true,
        'dir' => true,
        'div' => true,
        'dl' => true,
        'fieldset' => true,
        'figcaption' => true,
        'figure' => true,
        'footer' => true,
        'header' => true,
        'hgroup' => true,
        'main' => true,
        'menu' => true,
        'nav' => true,
        'ol' => true,
        'p' => true,
        'search' => true,
        'section' => true,
        'summary' => true,
        'ul' => true,
    ];

    public static function process(Token $token, TreeBuilder $tree): void
    {
        $type = $token::KIND;
        if ($type === TokenKind::EOF) {
            // If the stack of template insertion modes is not empty,
            // then process the token using the rules for the "in template" insertion mode.
            if (!$tree->templateInsertionModes->isEmpty()) {
                InTemplate::process($token, $tree);
                return;
            }
            // Otherwise, follow these steps:
            // 1. If there is a node in the stack of open elements that is not either
            // a dd, dt, li, optgroup, option, p, rb, rp, rt, rtc, tbody, td, tfoot, th, thead, tr, body or html element,
            // then this is a parse error.
            // 2. TODO: Stop parsing
            return;
        } else if ($type === TokenKind::Characters) {
            $data = $token->data;
            if ($tree->shouldSkipNextNewLine && $data[0] === "\n") {
                if (\strlen($data) === 1) {
                    return;
                }
                $data = $token->data = substr($data, 1);
            }
            if ($data === "\0") {
                // TODO: Parse error.
                // Ignore the token.
            } else if (ctype_space($data)) {
                // Reconstruct the active formatting elements, if any.
                $tree->reconstructTheListOfActiveElements();
                // Insert the token's character.
                $tree->insertCharacter($token);
            } else {
                // Reconstruct the active formatting elements, if any.
                $tree->reconstructTheListOfActiveElements();
                // Insert the token's character.
                $tree->insertCharacter($token);
                // Set the frameset-ok flag to "not ok".
                $tree->framesetOK = false;
            }
            return;
        } else if ($type === TokenKind::Comment) {
            $tree->insertComment($token);
            return;
        } else if ($type === TokenKind::Doctype) {
            // TODO: Parse error. Ignore the token.
            return;
        } else if ($type === TokenKind::StartTag) {
            $tagName = $token->name;
            if ($tagName === 'html') {
                // TODO: Parse error.
                if ($tree->openElements->containsTag('template')) {
                    // If there is a template element on the stack of open elements, then ignore the token.
                    return;
                }
                // Otherwise, for each attribute on the token,
                // check to see if the attribute is already present on the top element of the stack of open elements.
                // If it is not, add the attribute and its corresponding value to that element.
                if ($token->attributes) {
                    $tree->mergeAttributes($token, $tree->openElements->bottom());
                }
                return;
            } else if (isset(self::IN_HEAD_START_TAGS[$tagName])) {
                // Process the token using the rules for the "in head" insertion mode.
                InHead::process($token, $tree);
                return;
            } else if ($tagName === 'body') {
                // TODO: Parse error.
                $count = $tree->openElements->count();
                if (
                    // if the stack of open elements has only one node on it,
                    // FIXME: is this safe ?
                    $count < 2
                    // if the second element on the stack of open elements is not a body element,
                    || $tree->openElements[$count - 2]->localName !== 'body'
                    // or if there is a template element on the stack of open elements,
                    || $tree->openElements->containsTag('template')
                ) {
                    // then ignore the token. (fragment case)
                    return;
                }
                // Otherwise, set the frameset-ok flag to "not ok";
                $tree->framesetOK = false;
                // then, for each attribute on the token,
                // check to see if the attribute is already present on the body element (the second element) on the stack of open elements,
                // and if it is not, add the attribute and its corresponding value to that element.
                if ($token->attributes) {
                    $body = $tree->openElements[$count - 2];
                    $tree->mergeAttributes($token, $body);
                }
                return;
            } else if ($tagName === 'frameset') {
                // TODO: Parse error.
                $count = $tree->openElements->count();
                if (
                    // if the stack of open elements has only one node on it,
                    // FIXME: is this safe ?
                    $count < 2
                    // if the second element on the stack of open elements is not a body element,
                    || $tree->openElements[$count - 2]->localName !== 'body'
                ) {
                    // then ignore the token. (fragment case)
                    return;
                }
                // If the frameset-ok flag is set to "not ok", ignore the token.
                if (!$tree->framesetOK) {
                    return;
                }
                // Otherwise, run the following steps:
                // Remove the second element on the stack of open elements from its parent node, if it has one.
                $body = $tree->openElements[$count - 2];
                if ($body->parentNode) {
                    $body->parentNode->removeChild($body);
                }
                // Pop all the nodes from the bottom of the stack of open elements,
                // from the current node up to, but not including, the root html element.
                while ($tree->openElements->count() > 1) {
                    $tree->openElements->pop();
                }
                // Insert an HTML element for the token.
                $tree->insertElement($token);
                // Switch the insertion mode to "in frameset".
                $tree->insertionMode = InsertionModes::IN_FRAMESET;
                return;
            } else if (isset(self::CLOSE_P_START_TAGS[$tagName])) {
                // If the stack of open elements has a p element in button scope, then close a p element.
                if ($tree->openElements->hasParagraphInButtonScope()) {
                    self::closeParagraph($tree);
                }
                // Insert an HTML element for the token.
                $tree->insertElement($token);
                return;
            } else if (isset(Elements::HEADING_ELEMENTS[$tagName])) {
                // If the stack of open elements has a p element in button scope, then close a p element.
                if ($tree->openElements->hasParagraphInButtonScope()) {
                    self::closeParagraph($tree);
                }
                // If the current node is an HTML element whose tag name is one of "h1", "h2", "h3", "h4", "h5", or "h6",
                $currentNode = $tree->openElements->top();
                if (isset(Elements::HEADING_ELEMENTS[$currentNode->localName])) {
                    // TODO: then this is a parse error;
                    // pop the current node off the stack of open elements.
                    $tree->openElements->pop();
                }
                // Insert an HTML element for the token.
                $tree->insertElement($token);
                return;
            } else if ($tagName === 'pre' || $tagName === 'listing') {
                // If the stack of open elements has a p element in button scope, then close a p element.
                if ($tree->openElements->hasParagraphInButtonScope()) {
                    self::closeParagraph($tree);
                }
                // Insert an HTML element for the token.
                $tree->insertElement($token);

                // TODO: If the next token is a U+000A LINE FEED (LF) character token,
                // then ignore that token and move on to the next one.
                // (Newlines at the start of pre blocks are ignored as an authoring convenience.)

                // Set the frameset-ok flag to "not ok".
                $tree->framesetOK = false;
                return;
            } else if ($tagName === 'form') {
                // If the form element pointer is not null, and there is no template element on the stack of open elements,
                $hasTemplateOnStack = $tree->openElements->containsTag('template');
                if ($tree->formElement && !$hasTemplateOnStack) {
                    // TODO: then this is a parse error;
                    // ignore the token.
                    return;
                }
                // Otherwise:
                // If the stack of open elements has a p element in button scope, then close a p element.
                if ($tree->openElements->hasParagraphInButtonScope()) {
                    self::closeParagraph($tree);
                }
                // Insert an HTML element for the token,
                $form = $tree->insertElement($token);
                // and, if there is no template element on the stack of open elements,
                if (!$hasTemplateOnStack) {
                    // set the form element pointer to point to the element created.
                    $tree->formElement = $form;
                }
                return;
            } else if ($tagName === 'li') {
                // Set the frameset-ok flag to "not ok".
                $tree->framesetOK = false;
                // Initialize node to be the current node (the bottommost node of the stack).
                foreach ($tree->openElements as $node) {
                    if ($node->localName === 'li') {
                        // Generate implied end tags, except for li elements.
                        $tree->generateImpliedEndTags('li');
                        // If the current node is not an li element, then this is a parse error.
                        if (!$tree->openElements->currentNodeHasType('li')) {
                            // TODO: Parse error.
                        }
                        // Pop elements from the stack of open elements until an li element has been popped from the stack.
                        $tree->openElements->popUntilTag('li');
                        break;
                    }
                    if (
                        isset(Elements::SPECIAL[$node->namespaceURI][$node->localName])
                        && $node->localName !== 'address'
                        && $node->localName !== 'div'
                        && $node->localName !== 'p'
                    ) {
                        break;
                    }
                }
                // If the stack of open elements has a p element in button scope, then close a p element.
                if ($tree->openElements->hasParagraphInButtonScope()) {
                    self::closeParagraph($tree);
                }
                // Finally, insert an HTML element for the token.
                $tree->insertElement($token);
                return;
            } else if ($tagName === 'dd' || $tagName === 'dt') {
                // Set the frameset-ok flag to "not ok".
                $tree->framesetOK = false;
                foreach ($tree->openElements as $node) {
                    if ($node->localName === 'dd') {
                        $tree->generateImpliedEndTags('dd');
                        if (!$tree->openElements->currentNodeHasType('dd')) {
                            // TODO: Parse error.
                        }
                        $tree->openElements->popUntilTag('dd');
                        break;
                    }
                    if ($node->localName === 'dt') {
                        $tree->generateImpliedEndTags('dt');
                        if (!$tree->openElements->currentNodeHasType('dt')) {
                            // TODO: Parse error.
                        }
                        $tree->openElements->popUntilTag('dt');
                        break;
                    }
                    if (
                        isset(Elements::SPECIAL[$node->namespaceURI][$node->localName])
                        && $node->localName !== 'address'
                        && $node->localName !== 'div'
                        && $node->localName !== 'p'
                    ) {
                        break;
                    }
                }
                if ($tree->openElements->hasParagraphInButtonScope()) {
                    self::closeParagraph($tree);
                }
                $tree->insertElement($token);
                return;
            } else if ($tagName === 'plaintext') {
                // If the stack of open elements has a p element in button scope, then close a p element.
                if ($tree->openElements->hasParagraphInButtonScope()) {
                    self::closeParagraph($tree);
                }
                // Insert an HTML element for the token.
                $tree->insertElement($token);
                // Switch the tokenizer to the PLAINTEXT state.
                $tree->tokenizer->state = TokenizerState::PLAINTEXT;
                // NOTE: Once a start tag with the tag name "plaintext" has been seen,
                // that will be the last token ever seen other than character tokens (and the end-of-file token),
                // because there is no way to switch out of the PLAINTEXT state.
            } else if ($tagName === 'button') {
                // If the stack of open elements has a button element in scope, then run these substeps:
                if ($tree->openElements->hasTagInScope('button')) {
                    // TODO: Parse error.
                    // Generate implied end tags.
                    $tree->generateImpliedEndTags();
                    // Pop elements from the stack of open elements until a button element has been popped from the stack.
                    $tree->openElements->popUntilTag('button');
                }
                // Reconstruct the active formatting elements, if any.
                $tree->reconstructTheListOfActiveElements();
                // Insert an HTML element for the token.
                $tree->insertElement($token);
                // Set the frameset-ok flag to "not ok".
                $tree->framesetOK = false;
            } else if ($tagName === 'a') {
                // If the list of active formatting elements contains an a element between the end of the list and the last marker on the list
                // (or the start of the list if there is no marker on the list)
                if ($a = $tree->activeFormattingElements->containsTag('a')) {
                    // TODO: Parse error.
                    // run the adoption agency algorithm for the token
                    self::runTheAdoptionAgencyAlgorithm($tree, $token);
                    // then remove that element from the list of active formatting elements and the stack of open elements
                    // if the adoption agency algorithm didn't already remove it (it might not have if the element is not in table scope).
                    $tree->activeFormattingElements->remove($a);
                    $tree->openElements->remove($a);
                }
                // Reconstruct the active formatting elements, if any.
                $tree->reconstructTheListOfActiveElements();
                // Insert an HTML element for the token.
                $element = $tree->insertElement($token);
                // Push onto the list of active formatting elements that element.
                $tree->activeFormattingElements->push($element);
                return;
            } else if (
                $tagName === 'b'
                || $tagName === 'big'
                || $tagName === 'code'
                || $tagName === 'em'
                || $tagName === 'font'
                || $tagName === 'i'
                || $tagName === 's'
                || $tagName === 'small'
                || $tagName === 'strike'
                || $tagName === 'strong'
                || $tagName === 'tt'
                || $tagName === 'u'
            ) {
                // Reconstruct the active formatting elements, if any.
                $tree->reconstructTheListOfActiveElements();
                // Insert an HTML element for the token.
                $element = $tree->insertElement($token);
                // Push onto the list of active formatting elements that element.
                $tree->activeFormattingElements->push($element);
                return;
            } else if ($tagName === 'nobr') {
                // Reconstruct the active formatting elements, if any.
                $tree->reconstructTheListOfActiveElements();
                // If the stack of open elements has a nobr element in scope, then this is a parse error;
                if ($tree->openElements->hasTagInScope('nobr')) {
                    // TODO: Parse error.
                    // run the adoption agency algorithm for the token,
                    self::runTheAdoptionAgencyAlgorithm($tree, $token);
                    // then once again reconstruct the active formatting elements, if any.
                    $tree->reconstructTheListOfActiveElements();
                }
                // Insert an HTML element for the token.
                $element = $tree->insertElement($token);
                // Push onto the list of active formatting elements that element.
                $tree->activeFormattingElements->push($element);
                return;
            } else if (
                $tagName === 'applet'
                || $tagName === 'marquee'
                || $tagName === 'object'
            ) {
                // Reconstruct the active formatting elements, if any.
                $tree->reconstructTheListOfActiveElements();
                // Insert an HTML element for the token.
                $tree->insertElement($token);
                // Insert a marker at the end of the list of active formatting elements.
                $tree->activeFormattingElements->push(null);
                // Set the frameset-ok flag to "not ok".
                $tree->framesetOK = false;
                return;
            } else if ($tagName === 'table') {
                // If the Document is not set to quirks mode, and the stack of open elements has a p element in button scope,
                if (
                    $tree->document->_mode !== DocumentMode::QUIRKS
                    && $tree->openElements->hasParagraphInButtonScope()
                ) {
                    // then close a p element.
                    self::closeParagraph($tree);
                }
                // Insert an HTML element for the token.
                $tree->insertElement($token);
                // Set the frameset-ok flag to "not ok".
                $tree->framesetOK = false;
                // Switch the insertion mode to "in table".
                $tree->insertionMode = InsertionModes::IN_TABLE;
                return;
            } else if (
                $tagName === 'area'
                || $tagName === 'br'
                || $tagName === 'embed'
                || $tagName === 'img'
                || $tagName === 'keygen'
                || $tagName === 'wbr'
            ) {
                // Reconstruct the active formatting elements, if any.
                $tree->reconstructTheListOfActiveElements();
                // Insert an HTML element for the token.
                $tree->insertElement($token);
                // Immediately pop the current node off the stack of open elements.
                $tree->openElements->pop();
                // Acknowledge the token's self-closing flag, if it is set.
                $tree->acknowledgeSelfClosingFlag($token);
                // Set the frameset-ok flag to "not ok".
                $tree->framesetOK = false;
                return;
            } else if ($tagName === 'input') {
                // Reconstruct the active formatting elements, if any.
                $tree->reconstructTheListOfActiveElements();
                // Insert an HTML element for the token.
                $element = $tree->insertElement($token);
                // Immediately pop the current node off the stack of open elements.
                $tree->openElements->pop();
                // Acknowledge the token's self-closing flag, if it is set.
                $tree->acknowledgeSelfClosingFlag($token);
                if (
                    // If the token does not have an attribute with the name "type",
                    !$element->hasAttribute('type')
                    // or if it does, but that attribute's value is not an ASCII case-insensitive match for the string "hidden",
                    || strcasecmp('hidden', $element->getAttribute('type')) !== 0
                ) {
                    // then: set the frameset-ok flag to "not ok".
                    $tree->framesetOK = false;
                }
                return;
            } else if (
                $tagName === 'param'
                || $tagName === 'source'
                || $tagName === 'track'
            ) {
                // Insert an HTML element for the token.
                $tree->insertElement($token);
                // Immediately pop the current node off the stack of open elements.
                $tree->openElements->pop();
                // Acknowledge the token's self-closing flag, if it is set.
                $tree->acknowledgeSelfClosingFlag($token);
                return;
            } else if ($tagName === 'hr') {
                // If the stack of open elements has a p element in button scope, then close a p element.
                if ($tree->openElements->hasParagraphInButtonScope()) {
                    self::closeParagraph($tree);
                }
                // Insert an HTML element for the token.
                $tree->insertElement($token);
                // Immediately pop the current node off the stack of open elements.
                $tree->openElements->pop();
                // Acknowledge the token's self-closing flag, if it is set.
                $tree->acknowledgeSelfClosingFlag($token);
                // Set the frameset-ok flag to "not ok".
                $tree->framesetOK = false;
                return;
            } else if ($tagName === 'image') {
                // TODO: Parse error.
                // Change the token's tag name to "img" and reprocess it. (Don't ask.)
                $token->name = 'img';
                $tree->processToken($token);
                return;
            } else if ($tagName === 'textarea') {
                // 1. Insert an HTML element for the token.
                $tree->insertElement($token);
                // TODO: 2. If the next token is a U+000A LINE FEED (LF) character token,
                // then ignore that token and move on to the next one.
                // (Newlines at the start of textarea elements are ignored as an authoring convenience.)

                // 3. Switch the tokenizer to the RCDATA state.
                $tree->tokenizer->state = TokenizerState::RCDATA;
                // 4. Let the original insertion mode be the current insertion mode.
                $tree->originalInsertionMode = $tree->insertionMode;
                // 5. Set the frameset-ok flag to "not ok".
                $tree->framesetOK = false;
                // 6. Switch the insertion mode to "text".
                $tree->insertionMode = InsertionModes::TEXT;
                return;
            } else if ($tagName === 'xmp') {
                // If the stack of open elements has a p element in button scope, then close a p element.
                if ($tree->openElements->hasParagraphInButtonScope()) {
                    self::closeParagraph($tree);
                }
                // Reconstruct the active formatting elements, if any.
                $tree->reconstructTheListOfActiveElements();
                // Set the frameset-ok flag to "not ok".
                $tree->framesetOK = false;
                // Follow the generic raw text element parsing algorithm.
                $tree->followTheGenericTextElementParsingAlgorithm($token, true);
                return;
            } else if ($tagName === 'iframe') {
                // Set the frameset-ok flag to "not ok".
                $tree->framesetOK = false;
                // Follow the generic raw text element parsing algorithm.
                $tree->followTheGenericTextElementParsingAlgorithm($token, true);
                return;
            } else if (
                $tagName === 'noembed'
                || ($tree->scriptingEnabled && $tagName === 'noscript')
            ) {
                // Follow the generic raw text element parsing algorithm.
                $tree->followTheGenericTextElementParsingAlgorithm($token, true);
                return;
            } else if ($tagName === 'select') {
                // Reconstruct the active formatting elements, if any.
                $tree->reconstructTheListOfActiveElements();
                // Insert an HTML element for the token.
                $tree->insertElement($token);
                // Set the frameset-ok flag to "not ok".
                $tree->framesetOK = false;
                // If the insertion mode is one of "in table", "in caption", "in table body", "in row", or "in cell",
                $tree->insertionMode = match ($tree->insertionMode) {
                    // then switch the insertion mode to "in select in table".
                    InsertionModes::IN_TABLE, InsertionModes::IN_CAPTION, InsertionModes::IN_TABLE_BODY,
                    InsertionModes::IN_ROW, InsertionModes::IN_CELL => InsertionModes::IN_SELECT_IN_TABLE,
                    // Otherwise, switch the insertion mode to "in select".
                    default => InsertionModes::IN_SELECT
                };
                return;
            } else if (
                $tagName === 'optgroup'
                || $tagName === 'option'
            ) {
                // If the current node is an option element,
                if ($tree->openElements->currentNodeHasType('option')) {
                    // then pop the current node off the stack of open elements.
                    $tree->openElements->pop();
                }
                // Reconstruct the active formatting elements, if any.
                $tree->reconstructTheListOfActiveElements();
                // Insert an HTML element for the token.
                $tree->insertElement($token);
                return;
            } else if (
                $tagName === 'rb'
                || $tagName === 'rtc'
            ) {
                // If the stack of open elements has a ruby element in scope, then generate implied end tags.
                if ($tree->openElements->hasTagInScope('ruby')) {
                    $tree->generateImpliedEndTags();
                    // If the current node is not now a ruby element, this is a parse error.
                    if (!$tree->openElements->currentNodeHasType('ruby')) {
                        // TODO: Parse error.
                    }
                }
                // Insert an HTML element for the token.
                $tree->insertElement($token);
                return;
            } else if (
                $tagName === 'rp'
                || $tagName === 'rt'
            ) {
                // If the stack of open elements has a ruby element in scope,
                if ($tree->openElements->hasTagInScope('ruby')) {
                    // then generate implied end tags, except for rtc elements.
                    $tree->generateImpliedEndTags('rtc');
                    //  If the current node is not now a rtc element or a ruby element, this is a parse error.
                    $currentNode = $tree->openElements->top();
                    if ($currentNode->localName !== 'rtc' || $currentNode->localName !== 'ruby') {
                        // TODO: Parse error.
                    }
                }
                // Insert an HTML element for the token.
                $tree->insertElement($token);
                return;
            } else if ($tagName === 'math') {
                // Reconstruct the active formatting elements, if any.
                $tree->reconstructTheListOfActiveElements();
                // Adjust MathML attributes for the token. (This fixes the case of MathML attributes that are not all lowercase.)
                $tree->adjustMathMlAttributes($token);
                // Adjust foreign attributes for the token. (This fixes the use of namespaced attributes, in particular XLink.)
                $tree->adjustForeignAttributes($token);
                // Insert a foreign element for the token, in the MathML namespace.
                $tree->insertElement($token, Namespaces::MATHML);
                // If the token has its self-closing flag set,
                if ($token->selfClosing) {
                    // pop the current node off the stack of open elements
                    $tree->openElements->pop();
                    // and acknowledge the token's self-closing flag.
                    $tree->acknowledgeSelfClosingFlag($token);
                }
                return;
            } else if ($tagName === 'svg') {
                // Reconstruct the active formatting elements, if any.
                $tree->reconstructTheListOfActiveElements();
                // Adjust SVG attributes for the token. (This fixes the case of SVG attributes that are not all lowercase.)
                $tree->adjustSvgAttributes($token);
                // Adjust foreign attributes for the token. (This fixes the use of namespaced attributes, in particular XLink.)
                $tree->adjustForeignAttributes($token);
                // Insert a foreign element for the token, in the SVG namespace.
                $tree->insertElement($token, Namespaces::SVG);
                // If the token has its self-closing flag set,
                if ($token->selfClosing) {
                    // pop the current node off the stack of open elements
                    $tree->openElements->pop();
                    // and acknowledge the token's self-closing flag.
                    $tree->acknowledgeSelfClosingFlag($token);
                }
                return;
            } else if (
                $tagName === 'caption'
                || $tagName === 'col'
                || $tagName === 'colgroup'
                || $tagName === 'frame'
                || $tagName === 'head'
                || $tagName === 'tbody'
                || $tagName === 'td'
                || $tagName === 'tfoot'
                || $tagName === 'th'
                || $tagName === 'thead'
                || $tagName === 'tr'
            ) {
                // TODO: Parse error.
                //  Ignore the token.
                return;
            } else {
                // Reconstruct the active formatting elements, if any.
                $tree->reconstructTheListOfActiveElements();
                // Insert an HTML element for the token.
                $tree->insertElement($token);
            }
            return;
        // endif StartTag
        } else if ($type === TokenKind::EndTag) {
            $tagName = $token->name;
            if ($tagName === 'template') {
                // Process the token using the rules for the "in head" insertion mode.
                InHead::process($token, $tree);
                return;
            } else if ($tagName === 'body') {
                // If the stack of open elements does not have a body element in scope,
                if (!$tree->openElements->hasTagInScope('body')) {
                    // TODO: Parse error.
                    // Ignore the token
                    return;
                }
                // TODO: Otherwise, if there is a node in the stack of open elements that is not either
                // a dd, dt, li, optgroup, option, p, rb, rp, rt, rtc, tbody, td, tfoot, th, thead, tr, body or html element,
                // then this is a parse error.

                // Switch the insertion mode to "after body".
                $tree->insertionMode = InsertionModes::AFTER_BODY;
                return;
            } else if ($tagName === 'html') {
                // If the stack of open elements does not have a body element in scope,
                if (!$tree->openElements->hasTagInScope('body')) {
                    // TODO: Parse error.
                    // Ignore the token
                    return;
                }
                // TODO: Otherwise, if there is a node in the stack of open elements that is not either
                // a dd, dt, li, optgroup, option, p, rb, rp, rt, rtc, tbody, td, tfoot, th, thead, tr, body or html element,
                // then this is a parse error.

                // Switch the insertion mode to "after body".
                $tree->insertionMode = InsertionModes::AFTER_BODY;
                // Reprocess the token.
                $tree->processToken($token);
                return;
            } else if (
                $tagName === 'address'
                || $tagName === 'article'
                || $tagName === 'aside'
                || $tagName === 'blockquote'
                || $tagName === 'button'
                || $tagName === 'center'
                || $tagName === 'details'
                || $tagName === 'dialog'
                || $tagName === 'dir'
                || $tagName === 'div'
                || $tagName === 'dl'
                || $tagName === 'fieldset'
                || $tagName === 'figcaption'
                || $tagName === 'figure'
                || $tagName === 'footer'
                || $tagName === 'header'
                || $tagName === 'hgroup'
                || $tagName === 'listing'
                || $tagName === 'main'
                || $tagName === 'menu'
                || $tagName === 'nav'
                || $tagName === 'ol'
                || $tagName === 'pre'
                || $tagName === 'search'
                || $tagName === 'section'
                || $tagName === 'summary'
                || $tagName === 'ul'
            ) {
                // If the stack of open elements does not have an element in scope
                // that is an HTML element with the same tag name as that of the token,
                if (!$tree->openElements->hasTagInScope($tagName)) {
                    // TODO: then this is a parse error;
                    // ignore the token.
                    return;
                }
                // Generate implied end tags.
                $tree->generateImpliedEndTags();
                // If the current node is not an HTML element with the same tag name as that of the token,
                if (!$tree->openElements->currentNodeHasType($tagName)) {
                    // TODO: then this is a parse error.
                }
                // Pop elements from the stack of open elements
                // until an HTML element with the same tag name as the token has been popped from the stack.
                $tree->openElements->popUntilTag($tagName);
                return;
            } else if ($tagName === 'form') {
                // If there is no template element on the stack of open elements, then run these substeps:
                if (!$tree->openElements->containsTag('template')) {
                    // 1. Let node be the element that the form element pointer is set to, or null if it is not set to an element.
                    $node = $tree->formElement ?? null;
                    // 2. Set the form element pointer to null.
                    $tree->formElement = null;
                    // 3. If node is null or if the stack of open elements does not have node in scope,
                    if ($node === null || !$tree->openElements->hasElementInScope($node)) {
                        // TODO: then this is a parse error;
                        // return and ignore the token.
                        return;
                    }
                    // 4. Generate implied end tags.
                    $tree->generateImpliedEndTags();
                    // 5. If the current node is not node, then this is a parse error.
                    if ($tree->openElements->top() !== $node) {
                        // TODO: Parse error.
                    }
                    // 6. Remove node from the stack of open elements.
                    $tree->openElements->remove($node);
                } else {
                    // 1. If the stack of open elements does not have a form element in scope,
                    if (!$tree->openElements->hasTagInScope('form')) {
                        // TODO: then this is a parse error;
                        // return and ignore the token.
                        return;
                    }
                    // 2. Generate implied end tags.
                    $tree->generateImpliedEndTags();
                    // 3. If the current node is not a form element, then this is a parse error.
                    if (!$tree->openElements->currentNodeHasType('form')) {
                        // TODO: Parse error.
                    }
                    // 4. Pop elements from the stack of open elements until a form element has been popped from the stack.
                    $tree->openElements->popUntilTag('form');
                }
                return;
            } else if ($tagName === 'p') {
                // If the stack of open elements does not have a p element in button scope,
                if (!$tree->openElements->hasParagraphInButtonScope()) {
                    // TODO: then this is a parse error;
                    // insert an HTML element for a "p" start tag token with no attributes.
                    $tree->insertElement(new Token\StartTag('p'));
                }
                self::closeParagraph($tree);
                return;
            } else if ($tagName === 'li') {
                // If the stack of open elements does not have an li element in list item scope,
                if (!$tree->openElements->hasTagInListItemScope('li')) {
                    // TODO:  then this is a parse error;
                    // ignore the token.
                    return;
                }
                // Otherwise, run these steps:
                // 1. Generate implied end tags, except for li elements.
                $tree->generateImpliedEndTags('li');
                // 2. If the current node is not an li element, then this is a parse error.
                if (!$tree->openElements->currentNodeHasType('li')) {
                    // TODO: Parse error.
                }
                // 3. Pop elements from the stack of open elements until an li element has been popped from the stack.
                $tree->openElements->popUntilTag('li');
            } else if (
                $tagName === 'dd'
                || $tagName === 'dt'
            ) {
                // If the stack of open elements does not have an element in scope that is an HTML element
                // with the same tag name as that of the token,
                if (!$tree->openElements->hasTagInScope($tagName)) {
                    // TODO:  then this is a parse error;
                    // ignore the token.
                    return;
                }
                // Otherwise, run these steps:
                // 1. Generate implied end tags, except for HTML elements with the same tag name as the token.
                $tree->generateImpliedEndTags($tagName);
                // 2. If the current node is not an HTML element with the same tag name as that of the token, then this is a parse error.
                if (!$tree->openElements->currentNodeHasType($tagName)) {
                    // TODO: Parse error.
                }
                // 3. Pop elements from the stack of open elements until an HTML element with the same tag name as the token has been popped from the stack.
                $tree->openElements->popUntilTag($tagName);
            } else if (isset(Elements::HEADING_ELEMENTS[$tagName])) {
                // If the stack of open elements does not have an element in scope that is an HTML element
                // and whose tag name is one of "h1", "h2", "h3", "h4", "h5", or "h6",
                if (!$tree->openElements->hasHeadingElementInScope()) {
                    // TODO:  then this is a parse error;
                    // ignore the token.
                    return;
                }
                // Otherwise, run these steps:
                // 1. Generate implied end tags
                $tree->generateImpliedEndTags();
                // 2. If the current node is not an HTML element with the same tag name as that of the token, then this is a parse error.
                if (!$tree->openElements->currentNodeHasType($tagName)) {
                    // TODO: Parse error.
                }
                // 3. Pop elements from the stack of open elements until an HTML element
                // whose tag name is one of "h1", "h2", "h3", "h4", "h5", or "h6" has been popped from the stack.
                $tree->openElements->popUntilOneOf(['h1', 'h2', 'h3', 'h4', 'h5', 'h6']);
            } else if ($tagName === 'sarcasm') {
                // 😬 Take a deep breath, then act as described in the "any other end tag" entry below.
                self::anyOtherEndTag($tree, $token);
                return;
            } else if (isset(self::ADOPTION_AGENCY_END_TAG_TRIGGERS[$tagName])) {
                // Run the adoption agency algorithm for the token.
                self::runTheAdoptionAgencyAlgorithm($tree, $token);
                return;
            } else if (
                $tagName === 'applet'
                || $tagName === 'marquee'
                || $tagName === 'object'
            ) {
                // If the stack of open elements does not have an element in scope that is an HTML element
                // with the same tag name as that of the token,
                if (!$tree->openElements->hasTagInScope($tagName)) {
                    // TODO:  then this is a parse error;
                    // ignore the token.
                    return;
                }
                // Otherwise, run these steps:
                // 1. Generate implied end tags.
                $tree->generateImpliedEndTags();
                // 2. If the current node is not an HTML element with the same tag name as that of the token, then this is a parse error.
                if (!$tree->openElements->currentNodeHasType($tagName)) {
                    // TODO: Parse error.
                }
                // 3. Pop elements from the stack of open elements until an HTML element with the same tag name as the token has been popped from the stack.
                $tree->openElements->popUntilTag($tagName);
                // 4. Clear the list of active formatting elements up to the last marker.
                $tree->activeFormattingElements->clearUpToLastMarker();
                return;
            } else if ($tagName === 'br') {
                // TODO: Parse error.
                // Drop the attributes from the token, and act as described in the next entry;
                // i.e. act as if this was a "br" start tag token with no attributes, rather than the end tag token that it actually is.
                $tree->processToken(new Token\StartTag('br'));
                return;
            } else {
                self::anyOtherEndTag($tree, $token);
            }
        }
    }

    public static function anyOtherEndTag(TreeBuilder $tree, Token\EndTag $token)
    {
        foreach ($tree->openElements as $node) {
            if ($node->localName === $token->name && $node->isHTML) {
                // Generate implied end tags, except for HTML elements with the same tag name as the token.
                $tree->generateImpliedEndTags($node->localName);
                // If node is not the current node, then this is a parse error.
                if ($node !== $tree->openElements->top()) {
                    // TODO: Parse error.
                }
                // Pop all the nodes from the current node up to node, including node, then stop these steps.
                $tree->openElements->popUntil($node);
                return;
            }
            // Otherwise, if node is in the special category, then this is a parse error; ignore the token, and return.
            if (isset(Elements::SPECIAL[$node->namespaceURI][$node->localName])) {
                // TODO: Parse error.
                return;
            }
        }
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#close-a-p-element
     * @param TreeBuilder $tree
     */
    private static function closeParagraph(TreeBuilder $tree): void
    {
        // Generate implied end tags, except for p elements.
        $tree->generateImpliedEndTags('p');
        // If the current node is not a p element, then this is a parse error.
        if (!$tree->openElements->currentNodeHasType('p')) {
            // TODO: Parse error.
        }
        // Pop elements from the stack of open elements until a p element has been popped from the stack.
        $tree->openElements->popUntilTag('p');
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#adoption-agency-algorithm
     */
    private static function runTheAdoptionAgencyAlgorithm(TreeBuilder $tree, Token $token): void
    {
        // 1. Let subject be token's tag name.
        $subject = $token->name;
        // 2. If the current node is an HTML element whose tag name is subject,
        // and the current node is not in the list of active formatting elements,
        $currentNode = $tree->openElements->top();
        if (
            $currentNode->localName === $subject
            && $currentNode->isHTML
            && !$tree->activeFormattingElements->contains($currentNode)
        ) {
            // then pop the current node off the stack of open elements, and return.
            $tree->openElements->pop();
            return;
        }
        // 3. Let outer loop counter be 0.
        $outerLoopCounter = 0;
        // 4. While true
        while (true) {
            // 4.1 If outer loop counter is greater than or equal to 8, then return.
            if ($outerLoopCounter >= 8) return;
            // 4.2 Increment outer loop counter by 1.
            $outerLoopCounter++;
            // 4.3 Let formatting element be the last element in the list of active formatting elements that:
            //   - is between the end of the list and the last marker in the list, if any, or the start of the list otherwise, and
            //   - has the tag name subject.
            // If there is no such element, then return and instead act as described in the "any other end tag" entry above.
            $formattingElement = $tree->activeFormattingElements->containsTag($subject);
            if (!$formattingElement) {
                InBody::anyOtherEndTag($tree, $token);
                return;
            }
            // 4.4 If formatting element is not in the stack of open elements, then this is a parse error;
            //     remove the element from the list, and return.
            if (!$tree->openElements->contains($formattingElement)) {
                // TODO: parse error;
                $tree->activeFormattingElements->remove($formattingElement);
                return;
            }
            // 4.5 If formatting element is in the stack of open elements, but the element is not in scope,
            //     then this is a parse error; return.
            if (!$tree->openElements->hasElementInScope($formattingElement)) {
                // TODO: parse error;
                return;
            }
            // 4.6 If formatting element is not the current node, this is a parse error. (But do not return.)
            if ($formattingElement !== $tree->openElements->top()) {
                // TODO: Parse error.
            }
            // 4.7 Let furthest block be the topmost node in the stack of open elements
            //     that is lower in the stack than formatting element, and is an element in the special category.
            //     There might not be one.
            [$furthestBlock, $formattingElementIndex] = $tree->openElements->furthestBlockForFormattingElement($formattingElement);
            // 4.8 If there is no furthest block, then the UA must first
            if (!$furthestBlock) {
                // pop all the nodes from the bottom of the stack of open elements,
                // from the current node up to and including formatting element,
                $tree->openElements->popUntil($formattingElement);
                // then remove formatting element from the list of active formatting elements,
                $tree->activeFormattingElements->remove($formattingElement);
                // and finally return.
                return;
            }
            // 4.9 Let common ancestor be the element immediately above formatting element in the stack of open elements.
            $commonAncestor = $tree->openElements[$formattingElementIndex + 1];
            // 4.10 Let a bookmark note the position of formatting element in the list of active formatting elements
            //     relative to the elements on either side of it in the list.
            $bookmark = $tree->activeFormattingElements->indexOf($formattingElement);
            // 4.11 Let node and last node be furthest block.
            $node = $lastNode = $furthestBlock;
            // 4.12 Let inner loop counter be 0.
            $innerLoopCounter = 0;
            //
            $initialIndex = $tree->openElements->indexOf($node);
            // 4.13 While true
            while (true) {
                // 4.13.1 Increment inner loop counter by 1.
                $innerLoopCounter++;
                // 4.13.2 Let node be the element immediately above node in the stack of open elements,
                //        or if node is no longer in the stack of open elements (e.g. because it got removed by this algorithm),
                //        the element that was immediately above node in the stack of open elements before node was removed.
                $index = $tree->openElements->indexOf($node);
                if ($index === null) {
                    $index = $initialIndex;
                }
                $node = $tree->openElements[$index + 1];
                //$index = $tree->openElements->indexOf($node);
                //$node = $tree->openElements[$index];
                // 4.13.3 If node is formatting element, then break.
                if ($node === $formattingElement) break;
                // 4.13.4 If inner loop counter is greater than 3 and node is in the list of active formatting elements,
                //        then remove node from the list of active formatting elements.
                $isInActiveElements = $tree->activeFormattingElements->contains($node);
                if ($innerLoopCounter > 3 && $isInActiveElements) {
                    $tree->activeFormattingElements->remove($node);
                    $isInActiveElements = false;
                }
                // 4.13.5 If node is not in the list of active formatting elements,
                //        then remove node from the stack of open elements and continue.
                if (!$isInActiveElements) {
                    $tree->openElements->remove($node);
                    $initialIndex = $index;
                    continue;
                }
                // 4.13.6 Create an element for the token for which the element node was created, in the HTML namespace,
                //        with common ancestor as the intended parent;
                // TODO: check if we need to build a new element from scratch because of namespace
                $element = $node->cloneNode();
                //        replace the entry for node in the list of active formatting elements with an entry for the new element,
                $tree->activeFormattingElements->replace($node, $element);
                //        replace the entry for node in the stack of open elements with an entry for the new element,
                $tree->openElements->replace($node, $element);
                //        and let node be the new element.
                $node = $element;
                // 4.13.7 If last node is furthest block,
                //        then move the aforementioned bookmark to be immediately
                //        after the new node in the list of active formatting elements.
                if ($lastNode === $furthestBlock) {
                    $bookmark = $tree->activeFormattingElements->indexOf($node);
                }
                // 4.13.8 Append last node to node.
                $node->parserInsertBefore($lastNode, null);
                // 4.13.9 Set last node to node.
                $lastNode = $node;
            }
            // 4.14 Insert whatever last node ended up being in the previous step
            //      at the appropriate place for inserting a node,
            //      but using common ancestor as the override target.
            $pos = $tree->appropriatePlaceForInsertingANode($commonAncestor);
            $pos->insert($lastNode);
            // 4.15 Create an element for the token for which formatting element was created, in the HTML namespace,
            //      with furthest block as the intended parent.
            // TODO: check if we need to build a new element from scratch because of namespace
            $element = $formattingElement->cloneNode();
            // 4.16 Take all of the child nodes of furthest block and append them to the element created in the last step.
            //$childNodes = $furthestBlock->childNodes;
            //for ($i = \count($childNodes) - 1; $i >= 0; $i--) {
            //    $childNode = $childNodes[$i];
            //    $element->insertBefore($childNode, $element->lastChild);
            //}
            foreach ($furthestBlock->getChildNodes() as $childNode) {
                $element->parserInsertBefore($childNode, null);
            }
            // 4.17 Append that new element to furthest block.
            $furthestBlock->parserInsertBefore($element, null);
            // 4.18 Remove formatting element from the list of active formatting elements,
            //      and insert the new element into the list of active formatting elements
            //      at the position of the aforementioned bookmark.
            $tree->activeFormattingElements->remove($formattingElement);
            $tree->activeFormattingElements->insert($bookmark, $element);
            // 4.19 Remove formatting element from the stack of open elements,
            //      and insert the new element into the stack of open elements
            //      immediately below the position of furthest block in that stack.
            $tree->openElements->remove($formattingElement);
            $tree->openElements->insert($tree->openElements->indexOf($furthestBlock), $element);
        }
    }
}
