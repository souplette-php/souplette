<?php declare(strict_types=1);

namespace Souplette\Dom;

class XmlDocument extends Document
{
    public function __construct()
    {
        parent::__construct('xml');
    }
}
