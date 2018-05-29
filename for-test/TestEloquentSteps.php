<?php

namespace App\Console\Commands;

use App\StepsItem;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\ProgressIndicator;

class TestEloquentSteps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:eloquent-steps';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Eloquent Steps test';

    public function handle()
    {
        $start = microtime(true);
        $times = [];
        $iteration = 0;
        $last = microtime(true);
        $ids = [];
        $idsCounter = 0;
        $this->createTable();
        $indicator = new ProgressIndicator($this->getOutput());
        $indicator->start('start');
        /* change to chunk() */
        StepsItem::steps(1000, function(Collection $items) use (&$last, &$times, &$iteration, $start, &$ids, &$idsCounter, $indicator) {
            foreach ($items as $item) {
                if (!isset($ids[$item->id])) {
                    $ids[$item->id] = true;
                    $idsCounter++;
                }
            }
            $times[] = microtime(true) - $last;
            $iteration++;
            $indicator->setMessage($this->createMessage($start, $times, $iteration, $idsCounter));
            $indicator->advance();
            $last = microtime(true);
            return $items->last()->id ?? false;
        });
        $indicator->finish($this->createMessage($start, $times, $iteration, $idsCounter));
    }

    protected function createMessage($start, $times, $iteration, $idsCounter) {
        $message = [
            'elapsed: ' . (microtime(true) - $start),
            'avr: ' . (array_sum($times) / $iteration),
            'iteration: ' . $iteration,
            'ids: ' . $idsCounter
        ];
        return implode(' ', $message);
    }

    protected function createTable()
    {
        $progress = new ProgressBar($this->getOutput(), 1000000);
        $progress->setFormat('debug');
        $progress->start();
        if (StepsItem::count() === 0) {
            $items = [];
            for($i = 0; $i < 1000000; $i++) {
                $item = [
                    'created_at' => new Carbon(),
                    'updated_at' => new Carbon()
                ];
                $items[] = $item;
                if (count($items) % 500 === 0) {
                    StepsItem::insert($items);
                    $items = [];
                }
                $progress->advance();
            }
            if (count($items) > 0) {
                StepsItem::insert($items);
            }
        }
        $progress->finish();
    }
}
