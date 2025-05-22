# Duplicate queries card for Laravel Pulse

![PulseDuplicateQueriesPreview](https://github.com/user-attachments/assets/18877ff2-c0cf-40af-bc43-d8c4671d523c)

A Laravel Pulse recorder and card package designed to monitor and visualize database query duplication per HTTP request.

## Installation

Require the package with Composer:

```shell
composer require dazza-dev/pulse-duplicate-queries
```

## Register the recorder

Right now, in each request, the system will check how many queries are being executed and how many of them are duplicated. To run the checks you must add the `DuplicatedQueriesRecorder` to the `config/pulse.php` file.

```php
    'recorders' => [
        \DazzaDev\PulseDuplicateQueries\Recorders\DuplicateQueriesRecorder::class => [
            'enabled' => env('PULSE_DUPLICATED_QUERIES_BY_REQUEST_ENABLED', true),
            'sample_rate' => env('PULSE_DUPLICATED_QUERIES_BY_REQUEST_SAMPLE_RATE', 1)
        ],
    ]
```

You also need to be running [the `pulse:check` command](https://laravel.com/docs/10.x/pulse#dashboard-cards).

## Add to your dashboard

To add the card to the Pulse dashboard, you must first [publish the vendor view](https://laravel.com/docs/10.x/pulse#dashboard-customization).

Then, you can modify the `resources/views/vendor/pulse/dashboard.blade.php` file:

```php
<livewire:duplicate-queries cols='4' rows='2' />
```

## Contributions

Contributions are welcome. If you find any bugs or have ideas for improvements, please open an issue or send a pull request. Make sure to follow the contribution guidelines.

## Author

Laravel Batch Validation was created by [DAZZA](https://github.com/dazza-dev).

## License

This project is licensed under the [MIT License](https://opensource.org/licenses/MIT).
