<?php

namespace LightWeight\Events;

/**
 * Event fired before a view is rendered
 */
class ViewRenderingEvent extends Event
{
    /**
     * Get the name of the event
     *
     * @return string
     */
    public function getName(): string
    {
        return 'view.rendering';
    }
    
    /**
     * Get the view name
     *
     * @return string
     */
    public function getView(): string
    {
        return $this->data['view'] ?? '';
    }
    
    /**
     * Get the view parameters
     *
     * @return array
     */
    public function getParams(): array
    {
        return $this->data['params'] ?? [];
    }
    
    /**
     * Get the layout (can be string, null, or false)
     *
     * @return string|null|bool
     */
    public function getLayout()
    {
        return $this->data['layout'] ?? null;
    }
}
