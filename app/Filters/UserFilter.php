<?php

namespace App\Filters;

use Illuminate\Http\Request;

class UserFilter extends QueryFilter
{
    protected $request;
    
    public function __construct(Request $request)
    {
        $this->request = $request;
        parent::__construct($request);
    }

    public function keyword($value)
    {
        return $this->builder->where(function ($query) use ($value) {
            $query->where('name', 'like', '%' . $value . '%')->orWhere('email', 'like', '%' . $value . '%');
        });
    }

    public function role_id($value)
    {
        return $this->builder->whereHas('roles', function ($query) use ($value) {
            $query->where('id', $value);
        });
    }
}