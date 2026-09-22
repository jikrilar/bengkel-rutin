<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** @var array<string, string> */
    private array $constraints = [
        'users.chk_users_role' => "role IN ('customer', 'admin')",
        'service_profiles.chk_service_profiles_intervals' => 'interval_km > 0 AND interval_days > 0',
        'vehicles.chk_vehicles_year' => 'year BETWEEN 1886 AND 2155',
        'vehicles.chk_vehicles_baseline' => "baseline_source IS NULL OR baseline_source IN ('customer_input', 'service_record', 'admin_correction')",
        'fuzzy_configs.chk_fuzzy_configs_progress_order' => 'progress_safe_end < progress_approaching_peak AND progress_approaching_peak < progress_critical_full',
        'fuzzy_configs.chk_fuzzy_configs_usage_order' => 'usage_normal_full_until < usage_intensive_full_from',
        'fuzzy_rules.chk_fuzzy_rules_km_state' => "km_state IN ('safe', 'approaching', 'critical')",
        'fuzzy_rules.chk_fuzzy_rules_time_state' => "time_state IN ('safe', 'approaching', 'critical')",
        'fuzzy_rules.chk_fuzzy_rules_usage_state' => "usage_state IN ('normal', 'intensive')",
        'fuzzy_rules.chk_fuzzy_rules_consequent' => "consequent IN ('not_urgent', 'urgent')",
        'odometer_logs.chk_odometer_logs_source' => "source IN ('initial', 'customer_update', 'service', 'admin_correction')",
        'fuzzy_calculations.chk_fuzzy_calculations_trigger' => "trigger_type IN ('vehicle_created', 'odometer_updated', 'daily_scheduler', 'service_completed', 'service_profile_changed', 'fuzzy_config_changed', 'manual_recalculate')",
        'fuzzy_calculations.chk_fuzzy_calculations_status' => "fuzzy_status IN ('not_needed', 'approaching', 'urgent', 'unavailable') AND final_status IN ('not_needed', 'approaching', 'urgent', 'unavailable')",
        'fuzzy_calculations.chk_fuzzy_calculations_score' => 'score BETWEEN 0 AND 100',
        'fuzzy_calculations.chk_fuzzy_calculations_membership' => 'km_safe_mu BETWEEN 0 AND 1 AND km_approaching_mu BETWEEN 0 AND 1 AND km_critical_mu BETWEEN 0 AND 1 AND time_safe_mu BETWEEN 0 AND 1 AND time_approaching_mu BETWEEN 0 AND 1 AND time_critical_mu BETWEEN 0 AND 1 AND (usage_normal_mu IS NULL OR usage_normal_mu BETWEEN 0 AND 1) AND (usage_intensive_mu IS NULL OR usage_intensive_mu BETWEEN 0 AND 1)',
        'fuzzy_rule_results.chk_fuzzy_rule_results_alpha' => 'alpha BETWEEN 0 AND 1',
        'bookings.chk_bookings_status' => "status IN ('pending', 'confirmed', 'in_service', 'completed', 'cancelled')",
        'bookings.chk_bookings_duration' => 'duration_minutes > 0',
        'booking_events.chk_booking_events_type' => "event_type IN ('created', 'confirmed', 'rescheduled', 'started', 'completed', 'cancelled')",
        'booking_events.chk_booking_events_statuses' => "(old_status IS NULL OR old_status IN ('pending', 'confirmed', 'in_service', 'completed', 'cancelled')) AND (new_status IS NULL OR new_status IN ('pending', 'confirmed', 'in_service', 'completed', 'cancelled'))",
        'service_records.chk_service_records_total_cost' => 'total_cost >= 0',
        'workshop_settings.chk_workshop_settings_slots' => 'slot_duration_minutes > 0 AND slot_capacity > 0',
        'operating_hours.chk_operating_hours_day' => 'day_of_week BETWEEN 1 AND 7',
        'operating_hours.chk_operating_hours_times' => '(is_open = 0 AND open_time IS NULL AND close_time IS NULL) OR (is_open = 1 AND open_time IS NOT NULL AND close_time IS NOT NULL AND open_time < close_time)',
        'schedule_exceptions.chk_schedule_exceptions_times' => '(is_closed = 1 AND open_time IS NULL AND close_time IS NULL) OR (is_closed = 0 AND open_time IS NOT NULL AND close_time IS NOT NULL AND open_time < close_time)',
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach ($this->constraints as $identifier => $expression) {
            [$table, $name] = explode('.', $identifier, 2);
            DB::statement("ALTER TABLE `{$table}` ADD CONSTRAINT `{$name}` CHECK ({$expression})");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach (array_reverse(array_keys($this->constraints)) as $identifier) {
            [$table, $name] = explode('.', $identifier, 2);
            DB::statement("ALTER TABLE `{$table}` DROP CHECK `{$name}`");
        }
    }
};
