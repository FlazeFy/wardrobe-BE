<?php

use Illuminate\Support\Facades\Schedule;
 
// For Testing
Schedule::call(function () {
    // \App\Schedule\AuditSchedule::audit_error();
    // \App\Schedule\AuditSchedule::audit_apps();
})->everyMinute();

// For Production
/*
Schedule::call(function () {
    \App\Schedule\AuditSchedule::audit_error();
    \App\Schedule\AuditSchedule::audit_apps();
})->weeklyOn(1, '1:00');
*/