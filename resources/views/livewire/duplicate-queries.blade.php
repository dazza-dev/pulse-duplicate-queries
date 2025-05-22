<x-pulse::card :cols="$cols" :rows="$rows" :class="$class" wire:poll.5s="">
    <x-pulse::card-header name="Duplicated Queries By Request" details="queries duplicated">
        <x-slot:icon>
            <x-pulse::icons.circle-stack />
        </x-slot:icon>
    </x-pulse::card-header>

    <x-pulse::scroll :expand="$expand">
        @if ($data->isEmpty())
            <x-pulse::no-results />
        @else
            <x-pulse::table>
                <colgroup>
                    <col width="10%" />
                    <col width="50%" />
                    <col width="15%" />
                    <col width="15%" />
                    <col width="10%" />
                </colgroup>
                <x-pulse::thead>
                    <tr>
                        <x-pulse::th>Method</x-pulse::th>
                        <x-pulse::th>Route</x-pulse::th>
                        <x-pulse::th class="text-center">Total</x-pulse::th>
                        <x-pulse::th class="text-center">Duplicates</x-pulse::th>
                        <x-pulse::th class="text-center">Queries</x-pulse::th>
                    </tr>
                </x-pulse::thead>
                <tbody>
                    @foreach ($data as $item)
                        <tr wire:key="{{ $item->key }}-spacer" class="h-2 first:h-0"></tr>
                        <tr wire:key="{{ $item->key }}-row">
                            <x-pulse::td>
                                <x-pulse::http-method-badge :method="$item->method" />
                            </x-pulse::td>
                            <x-pulse::td class="font-mono text-xs">
                                {{ $item->key }}
                            </x-pulse::td>
                            <x-pulse::td class="text-center font-mono text-sm">
                                {{ $item->total }}
                            </x-pulse::td>
                            <x-pulse::td class="text-center font-mono text-sm">
                                {{ $item->duplicated }}
                            </x-pulse::td>
                            <x-pulse::td class="text-center">
                                <button title="Show queries"
                                    @click="alert(
                                        JSON.stringify(
                                            @js($item->queries).map(q => ({
                                                sql: q.sql,
                                                time: q.time,
                                                connection: q.connection,
                                            })), 
                                            null, 
                                            2
                                        )
                                    )"
                                    class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300">
                                    <x-pulse::icons.circle-stack class="w-5 h-5 stroke-current" />
                                </button>
                            </x-pulse::td>
                        </tr>
                    @endforeach
                </tbody>
            </x-pulse::table>
        @endif
    </x-pulse::scroll>
</x-pulse::card>
