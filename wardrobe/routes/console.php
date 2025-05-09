<?php

use Illuminate\Support\Facades\Schedule;
 
// For Testing
Schedule::call(function () {
    // \App\Schedule\AuditSchedule::audit_error();
    // \App\Schedule\AuditSchedule::audit_apps();
    // \App\Schedule\CleanSchedule::clean_history();
    // \App\Schedule\CleanSchedule::clean_deleted_clothes();
    // \App\Schedule\ReminderSchedule::remind_predeleted_clothes();
    // \App\Schedule\ReminderSchedule::remind_unwashed_clothes();
    // \App\Schedule\ReminderSchedule::remind_unironed_clothes();
    // \App\Schedule\ReminderSchedule::remind_unused_clothes();
    // \App\Schedule\ReminderSchedule::remind_weekly_schedule();
    // \App\Schedule\ReminderSchedule::remind_unanswered_question();
    // \App\Schedule\ReminderSchedule::remind_old_last_track();
    // \App\Schedule\ReminderSchedule::remind_wash_used_clothes();
    // \App\Schedule\GeneratorSchedule::generate_outfit();
    // \App\Schedule\WeatherSchedule::weather_routine_fetch();
})->everyMinute();

// For Production
/*
Schedule::call(function () {
    \App\Schedule\WeatherSchedule::weather_routine_fetch();
})->dailyAt('0:10');

Schedule::call(function () {
    \App\Schedule\AuditSchedule::audit_error();
    \App\Schedule\AuditSchedule::audit_apps();
})->weeklyOn(1, '1:00');

Schedule::call(function () {
    \App\Schedule\CleanSchedule::clean_history();
    \App\Schedule\CleanSchedule::clean_deleted_clothes();
})->dailyAt('2:00');

Schedule::call(function () {
    \App\Schedule\ReminderSchedule::remind_unused_clothes();
    \App\Schedule\ReminderSchedule::remind_old_last_track();
})->weeklyOn(0, '2:20')->weeklyOn(2, '2:20')->weeklyOn(5, '2:20');

Schedule::call(function () {
    \App\Schedule\ReminderSchedule::remind_unanswered_question();
})->weeklyOn(1, '2:20')->weeklyOn(3, '2:20')->weeklyOn(6, '2:20');

Schedule::call(function () {
    \App\Schedule\ReminderSchedule::remind_predeleted_clothes();
    \App\Schedule\ReminderSchedule::remind_unwashed_clothes();
    \App\Schedule\ReminderSchedule::remind_wash_used_clothes();
})->dailyAt('3:00');

Schedule::call(function () {
    \App\Schedule\ReminderSchedule::remind_unironed_clothes();
})->dailyAt('3:20');

Schedule::call(function () {
    \App\Schedule\ReminderSchedule::remind_weekly_schedule();
    \App\Schedule\GeneratorSchedule::generate_outfit();
})->dailyAt('18:00');
*/
