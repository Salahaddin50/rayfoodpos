<?php

namespace App\Models\Scopes;

use App\Traits\DefaultAccessModelTrait;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class BranchScope implements Scope
{
    use DefaultAccessModelTrait;

    public function apply(Builder $builder, Model $model)
    {
        if (!App::runningInConsole() && Auth::check()) {
            $tableName = $model->getTable();
            $field = sprintf('%s.%s', $tableName, 'branch_id');
            $branchId = $this->branch();
            if ($branchId !== null) {
                $builder->where(function($query) use ($field, $branchId) {
                    $query->where($field, '=', $branchId)->orWhere($field, '=', 0);
                });
            } else {
                $builder->where($field, '=', 0);
            }
        }
    }
    
    /**
     * Extend the query builder with custom macros for strict branch filtering
     */
    public function extend(Builder $builder)
    {
        $builder->macro('strictBranch', function (Builder $builder) {
            if (!App::runningInConsole() && Auth::check()) {
                $tableName = $builder->getModel()->getTable();
                $field = sprintf('%s.%s', $tableName, 'branch_id');
                $branchId = (new static)->branch();
                
                if ($branchId !== null) {
                    $builder->where($field, '=', $branchId);
                }
            }
            
            return $builder;
        });
    }
}
