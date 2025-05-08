<?php

namespace LightWeight\Database\ORM\Relations;

use LightWeight\Database\ORM\Model;

/**
 * BelongsTo relation
 * 
 * @template TRelatedModel of Model
 * @extends Relation<TRelatedModel>
 */
class BelongsTo extends Relation
{
    /**
     * Create a new belongs to relationship instance.
     *
     * @param \LightWeight\Database\QueryBuilder\Builder $query
     * @param Model $parent
     * @param string $foreignKey
     * @param string $ownerKey
     */
    public function __construct(
        $query,
        Model $parent,
        protected string $foreignKey,
        protected string $ownerKey
    ) {
        parent::__construct($query, $parent);
        
        $this->query->where($this->ownerKey, '=', $this->parent->{$this->foreignKey});
    }

    /**
     * Get the results of the relationship.
     *
     * @return TRelatedModel|null
     */
    public function getResults()
    {
        return $this->query->first();
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
        return $this->ownerKey;
    }
}
