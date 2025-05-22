<?php

namespace DazzaDev\PulseDuplicateQueries\Livewire;

use Laravel\Pulse\Livewire\Card;

class DuplicateQueries extends Card
{
    public function render()
    {
        $data = $this->values('duplicated_queries_by_request')
            ->map(function ($item) {
                $value = json_decode($item->value, true);

                return (object) [
                    'key' => $item->key,
                    'total' => $value['total'],
                    'duplicated' => $value['duplicated'],
                    'method' => $value['method'],
                    'queries' => $value['queries'] ?? [],
                ];
            })
            ->values()
            ->sortByDesc('duplicated');

        return view('duplicate-queries::livewire.duplicate-queries', [
            'data' => $data,
        ]);
    }
}
