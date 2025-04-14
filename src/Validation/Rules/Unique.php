<?php

namespace LightWeight\Validation\Rules;

use LightWeight\Database\DB;
use LightWeight\Validation\Contracts\ValidationRuleContract;

class Unique implements ValidationRuleContract
{
    public function __construct(
        private string $table,
        private string $column,
        private string $exceptColumn = "",
        private string $exceptValue = ""
    ) {
        $this->table = $table;
        $this->column = $column;
        $this->exceptColumn = $exceptColumn;
        $this->exceptValue = $exceptValue;
    }
    /**
     * @inheritDoc
     */
    public function isValid(string $field, array $data): bool
    {
        if(!isset($data[$field]) && $data[$field] == "") {
            return false;
        }
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$this->column} = ?";
        $params = [$data[$field]];
        if($this->exceptColumn != "" && $this->exceptValue != "") {
            $query .= " AND {$this->exceptColumn} != ?";
            $params[] = $this->exceptValue;
        }
        $count = DB::statement($query, $params)[0]['count'] ?? 0;
        return $count == 0;
    }

    /**
     * @inheritDoc
     */
    public function message(): string
    {
        return "This field must be unique";
    }
}
