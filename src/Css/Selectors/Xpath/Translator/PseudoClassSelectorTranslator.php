<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Xpath\Translator;

use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Css\Selectors\Xpath\Exception\UnsupportedPseudoClass;
use Souplette\Css\Selectors\Xpath\Helper\NthTranslatorHelper;
use Souplette\Css\Selectors\Xpath\TranslationContext;

/**
 * @see https://html.spec.whatwg.org/multipage/semantics-other.html#pseudo-classes
 */
final class PseudoClassSelectorTranslator
{
    public function __invoke(PseudoClassSelector $selector, TranslationContext $context)
    {
        $localName = $context->expr->getLocalName();
        // TODO: scope
        $predicate = match($selector->name) {
            'root' => 'not(parent::*)',
            'empty' => 'not(*) or normalize-space(.) = " "',
            // TODO: check if star prefix is needed
            'first-child' => 'position() = 1',
            'last-child' => 'position() = last()',
            'only-child' => 'position() = 1 and position() = last()',
            'first-of-type' => match($localName) {
                '*' => throw new UnsupportedPseudoClass($selector),
                default => NthTranslatorHelper::translateNth(0, 1, $localName),
            },
            'last-of-type' => match($localName) {
                '*' => throw new UnsupportedPseudoClass($selector),
                default => NthTranslatorHelper::translateNth(0, 1, $localName, true),
            },
            'only-of-type' => match($localName) {
                '*' => throw new UnsupportedPseudoClass($selector),
                default => sprintf(
                    '(%s) and (%s)',
                    NthTranslatorHelper::translateNth(0, 1, $localName, false),
                    NthTranslatorHelper::translateNth(0, 1, $localName, true),
                ),
            },
            'link', 'any-link' => match($localName) {
                'a', 'link', 'area' => '@href',
                '*' => "@href and (name(.) = 'a' or name(.) = 'link' or name(.) = 'area')",
                default => '0',
            },
            'selected' => match($localName) {
                'option' => '@selected',
                '*' => "@selected and name(.) = 'option'",
                default => '0',
            },
            'checked' => match($localName) {
                'input' => "@checked and (@type = 'checkbox' or @type = 'radio')",
                '*' => "@checked and name(.) = 'input' and (@type = 'checkbox' or @type = 'radio')",
                default => '0',
            },
            'default' => match($localName) {
                'input' => "@checked and (@type = 'checkbox' or @type = 'radio')",
                'option' => '@selected',
                '*' => "(@checked and (@type = 'checkbox' or @type = 'radio')) or (@selected and name(.) = 'option')",
                default => '0',
            },
            'enabled' => match ($localName) {
                'a', 'link', 'area' => '@href',
                'fieldset', 'optgroup' => 'not(@disabled)',
                'option' => 'not(@disabled or ancestor::optgroup[@disabled])',
                'input' => "@type != 'hidden' and not(@disabled or ancestor::fieldset[@disabled])",
                'button', 'select', 'textarea', 'keygen' => 'not(@disabled or ancestor::fieldset[@disabled])',
                '*' => self::getDefaultEnabledPredicate(),
                default => '0',
            },
            'disabled' => match ($localName) {
                'input' => "@type != 'hidden' and (@disabled or ancestor::fieldset[@disabled])",
                'button', 'select', 'textarea' => '@disabled or ancestor::fieldset[@disabled]',
                'fieldset', 'optgroup', 'option' => '@disabled',
                '*' => self::getDefaultDisabledPredicate(),
                default => '0',
            },
            'required' => match($localName) {
                'input', 'select', 'textarea' => '@required',
                '*' => "@required and (name(.) = 'input' or name(.) = 'select' or name(.) = 'textarea')",
                default => '0',
            },
            'optional' => match($localName) {
                'input', 'select', 'textarea' => 'not(@required)',
                '*' => "not(@required) and (name(.) = 'input' or name(.) = 'select' or name(.) = 'textarea')",
                default => '0',
            },
            'read-only' => match($localName) {
                'input', 'textarea' => '@readonly',
                '*' => "((name(.) = 'input' or name(.) = 'textarea') and @readonly) or not(@contenteditable) or @contenteditable = 'false'",
                default => '1',
            },
            'read-write' => match($localName) {
                'input', 'textarea' => 'not(@readonly or @disabled)',
                '*' => "((name(.) = 'input' or name(.) = 'textarea') and not(@readonly or @disabled)) or (@contenteditable = '' or @contenteditable = 'true')",
                default => '0',
            },
            'visited', 'hover',
                'focus', 'focus-within', 'focus-visible',
                'valid', 'invalid',
                'current', 'past', 'future',
                'playing', 'paused', => '0',
            default => throw new UnsupportedPseudoClass($selector),
        };

        $context->expr->predicate($predicate);
    }

    private static ?string $enabledPredicate = null;

    private static function getDefaultEnabledPredicate(): string
    {
        if (self::$enabledPredicate) {
            return self::$enabledPredicate;
        }
        return self::$enabledPredicate = self::normalizeWhitespace(<<<'EOS'
        (
            @href and (
                name(.) = 'a'
                or name(.) = 'link'
                or name(.) = 'area'
            )
        ) or (
            (
                name(.) = 'fieldset'
                or name(.) = 'optgroup'
            )
            and not(@disabled)
        ) or  (
            (
                (name(.) = 'input' and @type != 'hidden')
                or name(.) = 'button'
                or name(.) = 'select'
                or name(.) = 'textarea'
                or name(.) = 'keygen'
            )
            and not(@disabled or ancestor::fieldset[@disabled])
        ) or (
            name(.) = 'option'
            and not(@disabled or ancestor::optgroup[@disabled])
        )
        EOS);
    }

    private static ?string $disabledPredicate = null;

    private static function getDefaultDisabledPredicate(): string
    {
        if (self::$disabledPredicate) {
            return self::$disabledPredicate;
        }
        return self::$disabledPredicate = self::normalizeWhitespace(<<<'EOS'
        (
            @disabled and (
                (name(.) = 'input' and @type != 'hidden')
                or name(.) = 'button'
                or name(.) = 'select'
                or name(.) = 'textarea'
                or name(.) = 'fieldset'
                or name(.) = 'optgroup'
                or name(.) = 'option'
            )
        ) or (
            (name(.) = 'input' and @type != 'hidden')
            or name(.) = 'button'
            or name(.) = 'select'
            or name(.) = 'textarea'
        )
        and ancestor::fieldset[@disabled]
        EOS);
    }

    private static function normalizeWhitespace(string $input): string
    {
        return preg_replace('/\s+/', ' ', $input);
    }
}
