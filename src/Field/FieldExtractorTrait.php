<?php

declare(strict_types=1);

namespace Andi\GraphQL\Field;

use GraphQL\Type\Definition as Webonyx;

trait FieldExtractorTrait
{
    /**
     * @param array{
     *     name: string,
     *     type: Webonyx\Type|string,
     *     mode: int,
     *     description: string,
     *     deprecationReason: string,
     *     defaultValue: mixed
     * } $config
     * @param class-string $class
     *
     * @return mixed
     */
    private function extract(array $config, string $class): mixed
    {
        if (! isset($config['name'], $config['type'])) {
            return $config;
        }

        if ($config['type'] instanceof Webonyx\Type) {
            return $config;
        }

        $parameters = [
            'name' => $config['name'],
            'type' => $config['type'],
            'mode' => $config['mode'] ?? 0,
            'description' => $config['description'] ?? null,
            'deprecationReason'=> $config['deprecationReason'] ?? null,
        ];

        if (isset($config['defaultValue']) || array_key_exists('defaultValue', $config)) {
            $parameters['defaultValue'] = $config['defaultValue'];
        }

        return new $class(...$parameters);
    }
}
