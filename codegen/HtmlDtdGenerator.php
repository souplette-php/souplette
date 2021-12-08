<?php declare(strict_types=1);

namespace Souplette\Codegen;

final class HtmlDtdGenerator extends EntityLookupGenerator
{
    private const PREDEFINED_ENTITIES = [
        'amp' => true,
        'gt' => true,
        'lt' => true,
    ];

    protected function getTemplateFile(): string
    {
        return 'html.dtd.twig';
    }

    protected function getOutputFile(): string
    {
        return __DIR__ . '/../src/Xml/Parser/html.dtd';
    }

    protected function generateLookupTable(): array
    {
        $spec = $this->fetchSpecData();
        $lookup = [];
        foreach ($spec as $entity => $value) {
            if (!str_ends_with($entity, ';')) {
                continue;
            }
            $key = trim($entity, '&;');
            if (self::PREDEFINED_ENTITIES[$key] ?? false) {
                continue;
            }
            $lookup[$key] = self::reprEntityValue($value->codepoints);
        }

        return $lookup;
    }

    private static function reprEntityValue(array $codepoints): string
    {
        return implode(
            '',
            array_map(fn(int $cp) => sprintf('&#x%02X;', $cp), $codepoints),
        );
    }
}
