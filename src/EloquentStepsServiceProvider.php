<?php namespace Romach3\EloquentSteps;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\ServiceProvider;

class EloquentStepsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Builder::macro('steps', (new StepsMacro())->getStepsMacro());
    }
}
