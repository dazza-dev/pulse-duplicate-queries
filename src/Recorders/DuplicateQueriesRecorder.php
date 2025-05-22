<?php

namespace DazzaDev\PulseDuplicateQueries\Recorders;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Facades\Event;
use Laravel\Pulse\Facades\Pulse;
use Laravel\Pulse\Recorders\Concerns\Sampling;
use Laravel\Pulse\Recorders\Concerns\Thresholds;

class DuplicateQueriesRecorder
{
    use Sampling, Thresholds;

    /**
     * The events to listen for.
     *
     * @var list<class-string>
     */
    public array $listen = [
        RequestHandled::class,
    ];

    /**
     * Track queries for the current request
     */
    protected array $queries = [];

    /**
     * Create a new recorder instance.
     */
    public function __construct()
    {
        if ($this->shouldSample()) {
            $this->registerQueryListener();
        }
    }

    /**
     * Register the database query listener.
     */
    protected function registerQueryListener(): void
    {
        Event::listen(QueryExecuted::class, function (QueryExecuted $query) {
            $this->queries[] = [
                'sql' => $this->substituteBindingsIntoRawSql(
                    $this->normalizeSql($query->sql),
                    $query->bindings
                ),
                'time' => $query->time,
                'connection' => $query->connectionName,
            ];
        });
    }

    /**
     * Record the request.
     */
    public function record(RequestHandled $event): void
    {
        if (! $this->shouldSample()) {
            return;
        }

        $request = $event->request;

        // Ignore requests to Pulse routes
        if (str_starts_with($request->path(), 'pulse') || str_starts_with($request->path(), 'livewire')) {
            return;
        }

        // Count queries
        $queryCount = count($this->queries);
        $duplicateQueries = $this->getDuplicateQueries();

        // Record total duplicate queries for this endpoint
        if (count($duplicateQueries) > 0) {
            Pulse::set(
                type: 'duplicated_queries_by_request',
                key: $request->path(),
                value: json_encode([
                    'method' => $request->method(),
                    'duplicated' => count($duplicateQueries),
                    'total' => $queryCount,
                    'queries' => $this->queries,
                ])
            );
        }
    }

    /**
     * Get duplicate queries
     */
    public function getDuplicateQueries(): array
    {
        $uniqueQueries = [];
        $duplicateQueries = [];

        foreach ($this->queries as $query) {
            $normalizedSql = $query['sql'];
            $fingerprint = md5($normalizedSql);

            // if query is already in uniqueQueries, increment count and add time
            if (isset($uniqueQueries[$fingerprint])) {
                $uniqueQueries[$fingerprint]['count']++;
                $uniqueQueries[$fingerprint]['times'][] = $query['time'];
            } else {
                // First time we see this query
                $uniqueQueries[$fingerprint] = [
                    'sql' => $query['sql'],
                    'count' => 1,
                    'first_time' => $query['time'],
                    'times' => [$query['time']],
                    'connection' => $query['connection'],
                ];
            }
        }

        foreach ($uniqueQueries as $fingerprint => $data) {
            if ($data['count'] > 1) {
                $duplicateQueries[$fingerprint] = $data;
            }
        }

        return $duplicateQueries;
    }

    /**
     * Remove multiple spaces, new lines, and tabs
     */
    protected function normalizeSql(string $sql): string
    {
        $sql = preg_replace('/\s+/', ' ', $sql);
        $sql = trim($sql);

        return $sql;
    }

    /**
     * Substitute the given bindings into the given raw SQL query.
     */
    protected function substituteBindingsIntoRawSql(string $sql, array $bindings): string
    {
        $query = '';

        $isStringLiteral = false;

        for ($i = 0; $i < strlen($sql); $i++) {
            $char = $sql[$i];
            $nextChar = $sql[$i + 1] ?? null;

            // Single quotes can be escaped as '' according to the SQL standard while
            // MySQL uses \'. Postgres has operators like ?| that must get encoded
            // in PHP like ??|. We should skip over the escaped characters here.
            if (in_array($char.$nextChar, ["\'", "''", '??'])) {
                $query .= $char.$nextChar;
                $i += 1;
            } elseif ($char === "'") { // Starting / leaving string literal...
                $query .= $char;
                $isStringLiteral = ! $isStringLiteral;
            } elseif ($char === '?' && ! $isStringLiteral) { // Substitutable binding...
                $query .= array_shift($bindings) ?? '?';
            } else { // Normal character...
                $query .= $char;
            }
        }

        return $query;
    }
}
