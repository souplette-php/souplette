<?php declare(strict_types=1);

$loadJsonFile = static function(string $path) {
    $payload = file_get_contents($path);
    $data = json_decode($payload, false, 512, JSON_THROW_ON_ERROR);
    $xfails = [];
    foreach ($data as $xfail) {
        $id = sprintf('%s::%s', $xfail->file, $xfail->id);
        $message = sprintf('Test fails in browser: "%s"', $xfail->browser);
        $xfails[$id] = $message;
    }
    return $xfails;
};


/**
 * An array of html5lib tests that are expected to fail,
 * either because the library's implementation goes against the spec,
 * or because of unavoidable discrepancies between the underlying DOM implementations.
 */
return [
    'tree-construction' => [
        //...$loadJsonFile(__DIR__ . '/xfails/wpt-tree-construction.json'),
    ],
];
