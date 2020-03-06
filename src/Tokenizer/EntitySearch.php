<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tokenizer;

use ju1ius\HtmlParser\Tokenizer\EntitySearchNode;
use ju1ius\HtmlParser\Tokenizer\EntityLookup;

final class EntitySearch extends EntitySearchNode
{
    public static function create()
    {
        $root = new self();
        foreach (EntityLookup::NAMED_ENTITIES as $entity => $unicodeValue) {
            $root->add($entity, $unicodeValue);
        }

        return $root;
    }

    public function add(string $key, string $value): void
    {
        $node = $this;
        for ($i = 0; $i < strlen($key); $i++) {
            $char = $key[$i];
            if (!isset($node->children[$char])) {
                $node->children[$char] = new EntitySearchNode();
            }
            $node = $node->children[$char];
        }
        $node->value = $value;
    }
}
