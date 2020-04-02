<?php declare(strict_types=1);

namespace JoliPotage\Html\Dom\Api;

/**
 * @property string $data
 * @property-read int $length
 */
interface DomCharacterDataInterface extends DomNodeInterface
{
    public function substringData($offset, $count);
    public function appendData($data);
    public function insertData($offset, $data);
    public function deleteData($offset, $count);
    public function replaceData($offset, $count, $data);
}
