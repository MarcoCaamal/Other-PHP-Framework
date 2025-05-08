<?php

namespace LightWeight\Database\ORM\Relations;

use LightWeight\Database\ORM\Model;

/**
 * HasMany relation
 * 
 * @template TRelatedModel of Model
 * @extends Relation<TRelatedModel>
 */
class HasMany extends Relation
{
    /**
     * Create a new has many relationship instance.
     *
     * @param \LightWeight\Database\QueryBuilder\Builder $query
     * @param Model $parent
     * @param string $foreignKey
     * @param string $localKey
     */
    public function __construct(
        $query,
        Model $parent,
        protected string $foreignKey,
        protected string $localKey
    ) {
        parent::__construct($query, $parent);
        
        $this->query->where($this->foreignKey, '=', $this->parent->{$this->localKey});
    }

    /**
     * Get the results of the relationship.
     *
     * @return array<int, TRelatedModel>
     */
    public function getResults()
    {
        return $this->query->get();
    }
    
    /**
     * Get the foreign key of the relationship.
     * 
     * @return string
     */
    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }
    
    /**
     * Get the local key of the relationship.
     * 
     * @return string
     */
    public function getLocalKey(): string
    {
        return $this->localKey;
    }
}
