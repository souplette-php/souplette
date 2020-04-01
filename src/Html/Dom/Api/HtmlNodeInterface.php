<?php declare(strict_types=1);

namespace JoliPotage\Html\Dom\Api;

use DOMNode;

interface HtmlNodeInterface
{
    public function hasAttributes();
    public function insertBefore(DOMNode $newNode, DOMNode $refNode = null);
    public function replaceChild(DOMNode $newNode, DOMNode $oldNode);
    public function removeChild(DOMNode $oldNode);
    public function appendChild(DOMNode $newNode);
    public function hasChildNodes();
    public function cloneNode($deep = null);
    public function normalize();
    public function compareDocumentPosition(DOMNode $other);
    public function isSameNode(DOMNode $node);
    public function isDefaultNamespace($namespaceURI);
    public function isEqualNode(DOMNode $node);
    public function setUserData($key, $data, $handler);
    public function getUserData($key);
    public function getNodePath();
}
