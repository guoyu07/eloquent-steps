<?php namespace Romach3\EloquentSteps;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Builder
 * @method enforceOrderBy()
 * @property Model $model
 */
class StepsMacro
{
    public function getStepsMacro()
    {
        return function (int $count, callable $callback) {
            $this->enforceOrderBy();
            $lastId = 0;
            do {
                $results = $this->where($this->model->getQualifiedKeyName(), '>', $lastId)->limit($count)->get();
                $countResults = $results->count();
                if ($countResults == 0) {
                    break;
                }
                $lastId = $callback($results, $lastId);
                if ($lastId === false) {

                    return false;
                }
                unset($results);
            } while ($countResults == $count);

            return true;
        };
    }

}
