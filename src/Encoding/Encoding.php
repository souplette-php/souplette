<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Encoding;

use ju1ius\HtmlParser\Encoding\Exception\UnsupportedEncoding;

final class Encoding
{
    const CONFIDENCE_IRRELEVANT = 0;
    const CONFIDENCE_TENTATIVE = 1;
    const CONFIDENCE_CERTAIN = 2;

    /**
     * @var string
     */
    public $encoding;
    /**
     * @var int
     */
    public $confidence;

    public function __construct(string $encoding, int $confidence)
    {
        $encoding = strtolower($encoding);
        if (!isset(EncodingLookup::LABELS[$encoding])) {
            throw new UnsupportedEncoding($encoding);
        }
        $this->encoding = EncodingLookup::LABELS[$encoding];
        $this->confidence = $confidence;
    }

    public static function unknown(): self
    {
        return new self(EncodingLookup::UTF_8, self::CONFIDENCE_TENTATIVE);
    }

    public static function irrelevant(string $encoding): self
    {
        return new self($encoding, self::CONFIDENCE_IRRELEVANT);
    }

    public static function tentative(string $encoding): self
    {
        return new self($encoding, self::CONFIDENCE_TENTATIVE);
    }

    public static function certain(string $encoding): self
    {
        return new self($encoding, self::CONFIDENCE_CERTAIN);
    }

    public function getName(): string
    {
        return $this->encoding;
    }

    public function isIrrelevant(): bool
    {
        return $this->confidence === self::CONFIDENCE_IRRELEVANT;
    }

    public function isTentative(): bool
    {
        return $this->confidence === self::CONFIDENCE_TENTATIVE;
    }

    public function isCertain(): bool
    {
        return $this->confidence === self::CONFIDENCE_CERTAIN;
    }

    public function makeIrrelevant(): void
    {
        $this->confidence = self::CONFIDENCE_IRRELEVANT;
    }

    public function makeTentative(): void
    {
        $this->confidence = self::CONFIDENCE_TENTATIVE;
    }

    public function makeCertain(): void
    {
        $this->confidence = self::CONFIDENCE_CERTAIN;
    }

    public function __toString()
    {
        return $this->encoding;
    }
}
