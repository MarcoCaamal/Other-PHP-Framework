<?php

namespace LightWeight\Events;

/**
 * Event fired after a view has been rendered
 */
class ViewRenderedEvent extends Event
{
    /**
     * Get the name of the event
     *
     * @return string
     */
    public function getName(): string
    {
        return 'view.rendered';
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
     * Get the layout used (can be string, null, or false)
     *
     * @return string|null|bool
     */
    public function getLayout()
    {
        return $this->data['layout'] ?? null;
    }

    /**
     * Get the rendered content
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->data['content'] ?? '';
    }
}
