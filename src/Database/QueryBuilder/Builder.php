<?php

namespace LightWeight\Database\QueryBuilder;

use LightWeight\Database\ORM\Model;
use LightWeight\Database\QueryBuilder\Contracts\QueryBuilderContract;
use LightWeight\Database\QueryBuilder\Exceptions\QueryBuilderException;

/**
 * Builder Class
 * @template TModel of Model
 *
 * @method static Builder<TModel> select(array $columns = ['*'])
 * @method static Builder<TModel> where(string $column, string $operator, mixed $value, string $boolean = 'AND')
 * @method static Builder<TModel> orWhere(string $column, string $operator, mixed $value)
 * @method static Builder<TModel> whereIn(string $column, array $values, string $boolean = 'AND')
 * @method static Builder<TModel> whereNotIn(string $column, array $values, string $boolean = 'AND')
 * @method static Builder<TModel> whereNull(string $column, string $boolean = 'AND')
 * @method static Builder<TModel> whereNotNull(string $column, string $boolean = 'AND')
 *
 * @method static Builder<TModel> orderBy(string $column, string $direction = 'asc')
 * @method static Builder<TModel> limit(int $limit)
 * @method static Builder<TModel> offset(int $offset)
 *
 * @method static Builder<TModel> join(string $table, string $first, string $operator, string $second, string $type = 'inner')
 * @method static Builder<TModel> leftJoin(string $table, string $first, string $operator, string $second)
 * @method static Builder<TModel> rightJoin(string $table, string $first, string $operator, string $second)
 *
 * @method static bool insert(array $data)
 * @method static string|int lastInsertId()
 *
 * @method bool update(array $data)
 * @method bool delete()
 */
class Builder
{

    public function __construct(
        private QueryBuilderContract $driver,
        private ?string $modelClass = null
    ) {
    }
    public function setDriver(QueryBuilderContract $driver)
    {
        $this->driver = $driver;
    }
    public function setModelClass(string $modelClass): static
    {
        $this->modelClass = $modelClass;
        return $this;
    }
    public function __call(string $method, array $arguments): mixed
    {
        if (!method_exists($this->driver, $method)) {
            throw new QueryBuilderException("Method $method is not defined.");
        }

        $result = $this->driver->{$method}(...$arguments);

        return $result instanceof QueryBuilderContract ? $this : $result;
    }
    public function table(string $table): static
    {
        $this->driver->table($table);
        return $this;
    }
    /**
     *
     * @return Metadata\Column[]
     */
    public function getMetadataOfTableColumns(): array
    {
        return $this->driver->getMetadataOfTableColumns();
    }
    /**
     * Get all entities with where's filters
     *
     * @return TModel[]|array
     */
    public function get(): array
    {
        $entities = $this->driver->get();
        if($this->modelClass === null) {
            return $entities;
        }
        $models = [];

        foreach($entities as $entity) {
            // Create a new instance of the model
            $model = new $this->modelClass();
            
            // Set raw attributes directly, bypassing any fillable check
            $model->setRawAttributes($entity);
            
            $models[] = $model;
        }
        
        return $models;
    }
    
    /**
     * Get first entity
     * @return ?TModel|array
     */
    public function first(): array|Model|null
    {
        $entity = $this->driver->first();
        if($this->modelClass === null) {
            return $entity;
        }
        if($entity === null) {
            return $entity;
        }
        
        $model = new $this->modelClass();
        $model->setRawAttributes($entity);
        
        return $model;
    }
    



    
    /**
     * Get the query builder driver.
     *
     * @return \LightWeight\Database\QueryBuilder\Contracts\QueryBuilderContract
     */
    public function getDriver()
    {
        return $this->driver;
    }


}
