<?php

use Illuminate\Support\Facades\Schema;

it('creates every locked domain and required technical table', function () {
    $tables = [
        'users',
        'service_profiles',
        'vehicles',
        'odometer_logs',
        'fuzzy_configs',
        'fuzzy_rules',
        'fuzzy_calculations',
        'fuzzy_rule_results',
        'bookings',
        'booking_events',
        'service_records',
        'workshop_settings',
        'operating_hours',
        'schedule_exceptions',
        'notifications',
        'jobs',
        'job_batches',
        'failed_jobs',
        'sessions',
        'password_reset_tokens',
    ];

    foreach ($tables as $table) {
        expect(Schema::hasTable($table))->toBeTrue("Table {$table} is missing.");
    }
});

it('keeps odometer and recommendation data normalized', function () {
    expect(Schema::hasColumn('vehicles', 'current_odometer'))->toBeFalse()
        ->and(Schema::hasTable('recommendations'))->toBeFalse()
        ->and(Schema::hasColumns('vehicles', [
            'plate_number',
            'plate_number_normalized',
            'baseline_service_record_id',
        ]))->toBeTrue();
});

it('defines the critical unique indexes and circular foreign key', function () {
    $vehicleIndexes = collect(Schema::getIndexes('vehicles'));
    $serviceRecordIndexes = collect(Schema::getIndexes('service_records'));
    $ruleResultIndexes = collect(Schema::getIndexes('fuzzy_rule_results'));
    $vehicleForeignKeys = collect(Schema::getForeignKeys('vehicles'));

    expect($vehicleIndexes->contains(
        fn (array $index): bool => $index['unique']
            && $index['columns'] === ['plate_number_normalized'],
    ))->toBeTrue()
        ->and($serviceRecordIndexes->contains(
            fn (array $index): bool => $index['unique']
                && $index['columns'] === ['booking_id'],
        ))->toBeTrue()
        ->and($ruleResultIndexes->contains(
            fn (array $index): bool => $index['unique']
                && $index['columns'] === ['fuzzy_calculation_id', 'fuzzy_rule_id'],
        ))->toBeTrue()
        ->and($vehicleForeignKeys->contains(
            fn (array $foreignKey): bool => $foreignKey['columns'] === ['baseline_service_record_id']
                && $foreignKey['foreign_table'] === 'service_records',
        ))->toBeTrue();
});
