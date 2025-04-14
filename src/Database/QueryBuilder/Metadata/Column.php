<?php

namespace LightWeight\Database\QueryBuilder\Metadata;

class Column
{
    public string $name;
    public ColumnType $type;
    public ?string $collation;
    public bool $nullable;
    public ?string $key;
    public mixed $default;
    public ?string $extra;
    public ?string $privileges;
    public ?string $comment;

    // Puedes agregar un constructor si querés también
    public function __construct(array $data)
    {
        $this->name = $data['Field'];
        $this->type = new ColumnType($data['Type']);
        $this->collation = $data['Collation'];
        $this->nullable = $data['Null'] === 'YES';
        $this->key = $data['Key'];
        $this->default = $data['Default'];
        $this->extra = $data['Extra'];
        $this->privileges = $data['Privileges'];
        $this->comment = $data['Comment'];
    }
}
