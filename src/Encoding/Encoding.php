<?php declare(strict_types=1);

namespace Souplette\Encoding;

use Souplette\Encoding\Exception\UnsupportedEncoding;

final class Encoding implements \Stringable
{
    public string $encoding;
    public Confidence $confidence;

    public function __construct(string $encoding, Confidence $confidence)
    {
        $encoding = strtolower($encoding);
        if (!isset(EncodingLookup::LABELS[$encoding])) {
            throw new UnsupportedEncoding($encoding);
        }
        $this->encoding = EncodingLookup::LABELS[$encoding];
        $this->confidence = $confidence;
    }

    public static function default(): self
    {
        return new self(EncodingLookup::WINDOWS_1252, Confidence::TENTATIVE);
    }

    public static function irrelevant(string $encoding): self
    {
        return new self($encoding, Confidence::IRRELEVANT);
    }

    public static function tentative(string $encoding): self
    {
        return new self($encoding, Confidence::TENTATIVE);
    }

    public static function certain(string $encoding): self
    {
        return new self($encoding, Confidence::CERTAIN);
    }

    public function getName(): string
    {
        return $this->encoding;
    }

    public function isIrrelevant(): bool
    {
        return $this->confidence === Confidence::IRRELEVANT;
    }

    public function isTentative(): bool
    {
        return $this->confidence === Confidence::TENTATIVE;
    }

    public function isCertain(): bool
    {
        return $this->confidence === Confidence::CERTAIN;
    }

    public function makeIrrelevant(): void
    {
        $this->confidence = Confidence::IRRELEVANT;
    }

    public function makeTentative(): void
    {
        $this->confidence = Confidence::TENTATIVE;
    }

    public function makeCertain(): void
    {
        $this->confidence = Confidence::CERTAIN;
    }

    public function __toString()
    {
        return $this->encoding;
    }
}
