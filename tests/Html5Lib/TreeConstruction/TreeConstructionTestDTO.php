<?php declare(strict_types=1);

namespace JoliPotage\Tests\Html5Lib\TreeConstruction;

use JoliPotage\Html\Namespaces;

final class TreeConstructionTestDTO
{
    public $id;
    public $shouldFail = false;
    public $scriptingEnabled = false;
    public $contextElement;
    public $input = '';
    public $output = '';
    public $errors = [];

    public static function fromArray(array $data): self
    {
        $test = new self();
        $test->id = $data['id'];
        $test->shouldFail = isset($data['xfail']);
        $test->input = $data['data'];
        $test->output = $data['document'];
        $test->errors = $data['errors'];
        $test->scriptingEnabled = isset($data['script-on']);
        if (isset($data['document-fragment'])) {
            $context = explode(' ', trim($data['document-fragment']));
            if (count($context) === 2) {
                [$prefix, $localName] = $context;
                $test->contextElement = [Namespaces::NAMESPACES[$prefix], $localName];
            } else {
                $test->contextElement = [Namespaces::HTML, $context[0]];
            }
            $test->output = sprintf("#document-fragment\n%s", $test->output);
        } else {
            $test->output = sprintf("#document\n%s", $test->output);
        }

        $test->output = self::normalizeTreeDump($test->output);

        return $test;
    }

    private static function normalizeTreeDump(string $treeDump): string
    {
        return preg_replace('/^\| /m', '', $treeDump);
    }
}
