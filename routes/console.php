<?php

use App\Models\Analytics;
use App\Models\CustomDomain;
use Illuminate\Support\Facades\Schedule;

Schedule::command('queue:prune-batches --hours=48')->daily();
Schedule::command('queue:prune-failed --hours=168')->daily();
Schedule::call(fn()=>Analytics::where('occurred_at','<',now()->subMonths(18))->delete())->dailyAt('02:20')->name('analytics:prune')->withoutOverlapping();
Schedule::call(fn()=>CustomDomain::where('ssl_expires_at','<',now()->addDays(14))->where('status','verified')->update(['ssl_status'=>'renewal_due']))->dailyAt('03:10')->name('domains:check-ssl')->withoutOverlapping();
