<?php

namespace Nitm\Content\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Spatie\Activitylog\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class LogUserActivity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $model;

    public $eventName;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($model, $eventName)
    {
        $this->model = $model;
        $this->eventName = $eventName;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->model->shouldLogEventCustom($this->eventName)) {
            return;
        }


        $description = $this->model->getDescriptionForEvent($this->eventName);

        $logName = $this->model->getLogNameToUse($this->eventName);

        if ($description == '') {
            return;
        }

        $attrs = $this->model->attributeValuesToBeLogged($this->eventName);

        if ($this->model->isLogEmpty($attrs) && !$this->model->shouldSubmitEmptyLogs()) {
            return;
        }

        $logger = app(ActivityLogger::class)
            ->useLog($logName)
            ->performedOn($this->model)
            ->withProperties($attrs);

        if (method_exists($this->model, 'tapActivity')) {
            $logger->tap([$this->model, 'tapActivity'], $this->eventName);
        }

        $logger->log($description);
    }
}