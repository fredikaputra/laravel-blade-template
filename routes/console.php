<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('model:prune')->daily();
Schedule::command('queue:prune-failed')->daily();
Schedule::command('passport:purge')->daily();
Schedule::command('auth:clear-resets')->daily();
