<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class SalonScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  TModel  $model
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::check()) {
            $user = Auth::user();

            // التحقق من وجود المعرف لتجنب المشاكل
            if ($user->salon_id) {
                // استخدام qualifyColumn يزيل الالتباس ويستخدم المتغير $model
                $builder->where($model->qualifyColumn('salon_id'), $user->salon_id);
            }
        }
    }
}
