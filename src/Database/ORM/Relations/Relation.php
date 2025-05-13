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
    
    /**
     * Execute the query as a "select" statement.
     *
     * @return array<int, TRelatedModel>
     */
    public function get()
    {
        return $this->query->get();
    }

    /**
     * Find a model by its primary key.
     *
     * @param mixed $id
     * @return TRelatedModel|null
     */
    public function find($id)
    {
        return $this->query
            ->where($this->getQuery()->getModel()->getPrimaryKeyName(), '=', $id)
            ->first();
    }

    /**
     * Get the first related model record matching the attributes.
     *
     * @return TRelatedModel|null
     */
    public function first()
    {
        return $this->query->first();
    }

    /**
     * Add a basic where clause to the query.
     *
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @param string $boolean
     * @return $this
     */
    public function where($column, $operator, $value, $boolean = 'AND')
    {
        $this->query->where($column, $operator, $value, $boolean);
        
        return $this;
    }

    /**
     * Add a "or where" clause to the query.
     *
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return $this
     */
    public function orWhere($column, $operator, $value)
    {
        $this->query->orWhere($column, $operator, $value);
        
        return $this;
    }

    /**
     * Apply the callback's query changes if the given "value" is true.
     *
     * @param mixed $value
     * @param callable $callback
     * @param callable|null $default
     * @return $this
     */
    public function when($value, callable $callback, ?callable $default = null)
    {
        if ($value) {
            return $callback($this, $value) ?: $this;
        } elseif ($default) {
            return $default($this, $value) ?: $this;
        }
        
        return $this;
    }

    /**
     * Add an "order by" clause to the query.
     *
     * @param string $column
     * @param string $direction
     * @return $this
     */
    public function orderBy($column, $direction = 'asc')
    {
        $this->query->orderBy($column, $direction);
        
        return $this;
    }

    /**
     * Set the "limit" value of the query.
     *
     * @param int $value
     * @return $this
     */
    public function limit($value)
    {
        $this->query->limit($value);
        
        return $this;
    }

    /**
     * Get the related model instance.
     *
     * @return TRelatedModel
     */
    public function getRelated(): Model
    {
        return $this->query->getModel();
    }
    
    /**
     * Add a where in condition to the query.
     *
     * @param string $column
     * @param array $values
     * @param string $boolean
     * @return self
     */
    public function whereIn(string $column, array $values, string $boolean = 'AND'): self
    {
        $this->query->whereIn($column, $values, $boolean);
        return $this;
    }

    /**
     * Count related records.
     *
     * @return int
     */
    public function count(): int
    {
        return $this->query->count();
    }
}
