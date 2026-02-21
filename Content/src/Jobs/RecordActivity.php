<?php

namespace Nitm\Content\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use Nitm\Content\NitmContent;
use Illuminate\Database\Eloquent\Model;

class RecordActivity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The user who performed the action.
     *
     * @var Model
     */
    public $user;

    /**
     * The models to record for.
     *
     * @var array|Collection
     */
    public $models;

    /**
     * The properties to record.
     *
     * @var array
     */
    public $properties;

    /**
     * The name of the log.
     *
     * @var string
     */
    public $logName;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $models, $properties= [], $logName = null)
    {
        $this->models = is_array($models) || $models instanceof Collection ? $models : [$models];
        $this->user = $user;
        $this->properties = $properties;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(!function_exists('activity')) {
            return;
        }

        // Log the activity.
        $user = $this->user ?: auth()->user();
        foreach($this->models as $action=>$model) {
            $model = is_array($model) ? Arr::get($model, 'subject') : $model;
            $action = is_array($model) ? Arr::get($action, 'action') : $action;
            if($model instanceof Model) {
                activity($this->logName ?: 'default')
                ->performedOn($model)
                ->causedBy($user)
                ->withProperties($this->properties)
                ->event(is_string($action)? $action : "viewed")
                ->log(is_string($action) ? $action : "viewed");
            }
        }
    }
}