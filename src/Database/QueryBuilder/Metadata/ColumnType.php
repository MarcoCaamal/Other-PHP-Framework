<?php

namespace LightWeight\Database\QueryBuilder\Metadata;

use InvalidArgumentException;

class ColumnType
{
    public string $name;
    public ?array $parameters = null;
    public ?array $enumValues = null;

    public function __construct(string $rawType)
    {
        // Match type and optional parameters
        if (preg_match('/^(\w+)(\((.+)\))?/', $rawType, $matches)) {
            $this->name = strtolower($matches[1]);

            // Parse parameters if they exist
            if (isset($matches[3])) {
                if ($this->name === 'enum' || $this->name === 'set') {
                    // Enum or set values
                    $this->enumValues = array_map(
                        fn ($v) => trim($v, " '"),
                        explode(',', $matches[3])
                    );
                } else {
                    // Numeric parameters
                    $this->parameters = array_map('trim', explode(',', $matches[3]));
                }
            }
        } else {
            throw new InvalidArgumentException("Invalid column type: $rawType");
        }
    }
}
