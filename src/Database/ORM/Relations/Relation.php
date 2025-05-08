<?php

namespace LightWeight\Database\ORM\Relations;

use LightWeight\Database\ORM\Model;
use LightWeight\Database\QueryBuilder\Builder;

/**
 * Base class for all relation types
 * 
 * @template TRelatedModel of Model
 */
abstract class Relation
{
    /**
     * Create a new relation instance.
     *
     * @param Builder<TRelatedModel> $query
     * @param Model $parent
     */
    public function __construct(
        protected Builder $query,
        protected Model $parent
    ) {}

    /**
     * Get the results of the relationship.
     *
     * @return array<int, TRelatedModel>|TRelatedModel|null
     */
    abstract public function getResults();

    /**
     * Get the relationship query.
     *
     * @return Builder<TRelatedModel>
     */
    public function getQuery(): Builder
    {
        return $this->query;
    }

    /**
     * Get the parent model of the relationship.
     *
     * @return Model
     */
    public function getParent(): Model
    {
        return $this->parent;
    }
    
    /**
     * Get the foreign key of the relationship.
     * 
     * @return string
     */
    abstract public function getForeignKey(): string;
    
    /**
     * Get the local key of the relationship.
     * 
     * @return string
     */
    abstract public function getLocalKey(): string;
    
    /**
     * Forward calls to the query builder.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        // Forward the call to the query builder
        $result = $this->query->$method(...$parameters);
        
        // If the result is a query builder, return $this for chaining
        if ($result instanceof Builder) {
            return $this;
        }
        
        // Otherwise return the result
        return $result;
    }
}
