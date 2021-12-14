<?php declare(strict_types=1);

namespace Souplette\Dom\Traits;

use Souplette\Html\Sanitizer\Sanitizer;

trait SanitizerApiTrait
{
    /**
     * @param string $input
     * @param array $options
     * @return void
     */
    public function setHTML(string $input, array $options = []): void
    {
        $sanitizer = $options['sanitizer'] ?? null;
        if (!$sanitizer instanceof Sanitizer) {
            $sanitizer = Sanitizer::getDefault();
        }
        $sanitizer->setHTML($this, $input);
    }
}
