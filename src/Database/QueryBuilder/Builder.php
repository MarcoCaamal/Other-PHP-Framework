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

    /**
     * The relations that should be eager loaded.
     *
     * @var array
     */
    protected array $eagerLoad = [];

    // eagerCountRelations property removed

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
        
        // Load eager relations if any
        if (!empty($this->eagerLoad) && !empty($models)) {
            $this->processEagerLoads($models);
        }
        
        // withCount functionality removed
        
        return $models;
    }
    
    // countEagerRelations method removed
    
    /**
     * Process eager loaded relationships for the models.
     *
     * @param array $models
     * @return void
     */
    protected function processEagerLoads(array $models): void
    {
        // Primero procesamos las relaciones anidadas para separarlas
        $relations = [];
        $nested = [];
        
        foreach ($this->eagerLoad as $name => $constraints) {
            // Check if the key is numeric (meaning no constraints) and get the actual relation name
            $relation = is_numeric($name) ? $constraints : $name;
            
            // Si contiene un punto, es una relación anidada
            if (is_string($relation) && strpos($relation, '.') !== false) {
                list($parent, $child) = explode('.', $relation, 2);
                if (!isset($nested[$parent])) {
                    $nested[$parent] = [];
                }
                $nested[$parent][] = $child;
                
                // Añadimos la relación padre si no existe
                if (!in_array($parent, $relations)) {
                    $relations[] = $parent;
                }
            } else {
                if (!in_array($relation, $relations)) {
                    $relations[] = $relation;
                }
            }
        }
        
        // Ahora procesamos cada relación principal
        foreach ($relations as $relation) {
            // Skip if the relation method doesn't exist
            if (empty($models) || !method_exists($this->modelClass, $relation)) {
                continue;
            }
            
            try {
                // Get a sample model to access the relation method
                $sample = reset($models);
                
                // Get the relationship instance
                $relationInstance = $sample->$relation();
                
                // Get all the keys from the models
                $keys = [];
                $localKey = $relationInstance->getLocalKey();
                
                // Collect keys from models
                foreach ($models as $model) {
                    // Accedemos directamente a la propiedad sin usar isset
                    // ya que la propiedad debería existir siempre (el ID)
                    $value = $model->$localKey;
                    if ($value !== null) {
                        $keys[] = $value;
                    }
                }
                
                // Skip if no keys found
                if (empty($keys)) {
                    continue;
                }
                
                // Creamos un nuevo Builder limpio para evitar restricciones previas
                $foreignKey = $relationInstance->getForeignKey();
                
                // Obtenemos una instancia del driver actual
                $driverClass = get_class($this->driver);
                $newDriver = new $driverClass($this->driver->getConnection());
                
                // Creamos un nuevo builder con el driver limpio
                $newBuilder = new Builder($newDriver);
                $newBuilder->setModelClass(get_class($relationInstance->getRelated()));
                $newBuilder->table($relationInstance->getTable());
                
                // Comprobar si existe un callback para aplicar restricciones adicionales
                $constraints = null;
                foreach ($this->eagerLoad as $name => $constraint) {
                    if (!is_numeric($name) && $name === $relation && is_callable($constraint)) {
                        $constraints = $constraint;
                        break;
                    }
                }
                
                // Aplicar las restricciones adicionales si existen
                if ($constraints) {
                    $constraints($newBuilder);
                }
                
                // Use whereIn to get all related models efficiently
                $relatedModels = $newBuilder->whereIn($foreignKey, array_unique($keys))->get();
                
                
                // Group related models by their foreign key
                $dictionary = [];
                foreach ($relatedModels as $relatedModel) {
                    // For BelongsTo we need special handling
                    if ($relationInstance instanceof \LightWeight\Database\ORM\Relations\BelongsTo) {
                        $keyValue = $relatedModel->{$relationInstance->getLocalKey()};
                    } else {
                        $keyValue = $relatedModel->$foreignKey;
                    }
                    
                    // Convertimos a string para asegurar que la comparación sea consistente
                    $keyValueString = (string)$keyValue;
                    
                    if (!isset($dictionary[$keyValueString])) {
                        $dictionary[$keyValueString] = [];
                    }
                    
                    $dictionary[$keyValueString][] = $relatedModel;
                }
                
                // Match and set related models to their parents
                foreach ($models as $model) {
                    // For BelongsTo we need the foreign key value from the parent
                    if ($relationInstance instanceof \LightWeight\Database\ORM\Relations\BelongsTo) {
                        $keyToMatch = $model->{$relationInstance->getForeignKey()};
                    } else {
                        $keyToMatch = $model->$localKey;
                    }
                    
                    // Convertimos a string para asegurar que la comparación sea consistente
                    // ya que las claves de un array siempre son strings
                    $keyToMatchString = (string)$keyToMatch;
                    
                    if (isset($dictionary[$keyToMatchString])) {
                        // If it's a HasOne or BelongsTo relation, take just the first item
                        if ($relationInstance instanceof \LightWeight\Database\ORM\Relations\HasOne || 
                            $relationInstance instanceof \LightWeight\Database\ORM\Relations\BelongsTo) {
                            $model->setRelation($relation, $dictionary[$keyToMatchString][0]);
                        } else {
                            $model->setRelation($relation, $dictionary[$keyToMatchString]);
                        }
                    } else {
                        // If no related models, set appropriate default value
                        if ($relationInstance instanceof \LightWeight\Database\ORM\Relations\HasOne || 
                            $relationInstance instanceof \LightWeight\Database\ORM\Relations\BelongsTo) {
                            $model->setRelation($relation, null);
                        } else {
                            $model->setRelation($relation, []);
                        }
                    }
                }
                
                // Si hay relaciones anidadas para esta relación, las procesamos
                if (isset($nested[$relation]) && !empty($nested[$relation])) {
                    // Obtenemos los modelos relacionados cargados para procesar sus relaciones anidadas
                    $nestedModels = [];
                    
                    foreach ($models as $model) {
                        if (isset($model->getRelations()[$relation])) {
                            $related = $model->getRelations()[$relation];
                            if (is_array($related)) {
                                foreach ($related as $relatedModel) {
                                    $nestedModels[] = $relatedModel;
                                }
                            } else if ($related !== null) {
                                $nestedModels[] = $related;
                            }
                        }
                    }
                    
                    // Si hay modelos anidados, cargamos sus relaciones
                    if (!empty($nestedModels)) {
                        // Creamos un nuevo Builder para la clase relacionada
                        $relatedClass = get_class(reset($nestedModels));
                        
                        // Obtenemos una instancia del driver actual
                        $driverClass = get_class($this->driver);
                        $newDriver = new $driverClass($this->driver->getConnection());
                        
                        // Creamos un nuevo builder con el driver limpio
                        $nestedBuilder = new Builder($newDriver);
                        $nestedBuilder->setModelClass($relatedClass);
                        
                        // Configuramos las relaciones a cargar en el builder anidado
                        $nestedBuilder->eagerLoadRelations($nested[$relation]);
                        
                        // Procesamos las relaciones anidadas
                        $nestedBuilder->processEagerLoads($nestedModels);
                    }
                }
            } catch (\Exception $e) {
                // Silently fail but keep processing other relations
                // We could log this error in a production environment
            }
        }
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
     * Set the relationships that should be eager loaded.
     *
     * @param array $relations
     * @return self
     */
    public function eagerLoadRelations(array $relations): self
    {
        $this->eagerLoad = $relations;
        return $this;
    }

    /**
     * Set the relationships that should be eager counted.
     *
     * @param array $relations
     * @return self
     */
    public function eagerCountRelations(array $relations): self
    {
        $this->eagerCountRelations = $relations;
        return $this;
    }
    
    // Métodos de eager loading ahora unificados en processEagerLoads
    
    /**
     * Get the model instance.
     * 
     * @return Model|null
     */
    public function getModel()
    {
        if ($this->modelClass === null) {
            return null;
        }
        
        return new $this->modelClass();
    }
    
    /**
     * Add a subselect expression to the query.
     *
     * @param string $column
     * @param \Closure $callback
     * @return $this
     */
    public function addSubSelect(string $column, \Closure $callback): self
    {
        try {
            // Create a new query builder for the subquery
            $subQuery = new self($this->driver);
            
            // Apply the callback to configure the subquery
            $callback($subQuery);
            
            // Get the SQL for the subquery if available
            if (method_exists($this->driver, 'subQuery')) {
                $this->driver->subQuery(
                    function($query) use ($subQuery, $callback) {
                        $callback($query);
                    },
                    $column
                );
            } else {
                // Fallback: Just execute the subquery and add a custom column
                // This is not ideal but allows some basic functionality
                $mainQuery = $this;
                $this->driver->whereCallback(function($query) use ($mainQuery, $column, $callback) {
                    // Try to implement a simplified version
                    $callback($mainQuery);
                });
            }
            
            return $this;
        } catch (\Exception $e) {
            // Log or handle the exception as appropriate
            throw new QueryBuilderException("Error adding subselect: " . $e->getMessage());
        }
    }
    
    /**
     * Add a raw select expression to the query.
     *
     * @param string $expression
     * @return $this
     */
    public function selectRaw(string $expression): self
    {
        // Usar el método selectRaw del driver si está disponible
        if (method_exists($this->driver, 'selectRaw')) {
            $this->driver->selectRaw($expression);
        } else {
            // Fallback: modificar la selección normal
            $this->driver->select([$expression]);
        }
        return $this;
    }
    
    /**
     * Convert the query to a SQL string.
     *
     * @return string
     */
    public function toSql(): string
    {
        return $this->driver->toSql();
    }

    /**
     * Count the number of records.
     *
     * @param string $column The column to count
     * @return int
     */
    public function count(string $column = '*'): int
    {
        return $this->driver->count($column);
    }
    
    /**
     * Add a where column constraint to the query.
     *
     * @param string $first
     * @param string $operator
     * @param string $second
     * @param string $boolean
     * @return $this
     */
    public function whereColumn(string $first, string $operator, string $second, string $boolean = 'AND'): self
    {
        if (method_exists($this->driver, 'whereRaw')) {
            $this->driver->whereRaw("$first $operator $second", [], $boolean);
        }
        return $this;
    }
    
    /**
     * Reset the query builder state.
     * Clears all conditions, joins, orders, limits, etc.
     *
     * @return static
     */
    public function reset(): static
    {
        if ($this->driver) {
            $this->driver->reset();
        }
        
        $this->eagerLoad = [];
        // eagerCountRelations property removed
        
        return $this;
    }
}
