<?php

namespace LightWeight\Events\Model;

use LightWeight\Database\ORM\Model;
use LightWeight\Events\Event;

/**
 * Event fired after a model is deleted
 */
class ModelDeletedEvent extends Event
{
    /**
     * ModelDeletedEvent constructor.
     *
     * @param array $data Event data containing the model
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * Get the name of the event
     *
     * @return string
     */
    public function getName(): string
    {
        return 'model.deleted';
    }

    /**
     * Get the model that was deleted
     *
     * @return Model|null
     */
    public function getModel(): ?Model
    {
        return $this->data['model'] ?? null;
    }
}
