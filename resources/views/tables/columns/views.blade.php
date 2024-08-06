<div>
    {{ collect($getState())->map(function ($state) {
        return str()->headline($state['observations']['view']);
    })->implode(', ') }}
</div>
