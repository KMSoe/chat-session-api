<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

abstract class QueryFilter
{
    protected $request;
    protected $builder;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function apply(Builder $builder)
    {
        $this->builder = $builder;
        if ($this->request->has('filters')) {
            foreach ($this->request->input('filters') as $filter => $value) {
                if (method_exists($this, $filter)) {
                    $this->$filter($value);
                }
            }
        }
        if ($this->request->has('sorting')) {
            $sortColumn = $this->request->input('sorting.column', 'id'); 
            $sortDirection = $this->request->input('sorting.direction', 'asc');
            $this->builder->orderBy($sortColumn, $sortDirection);
        }
        return $this->builder;
    }
}