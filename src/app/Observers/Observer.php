<?php

namespace LaravelEnso\Addresses\app\Observers;

namespace LaravelEnso\Addresses\app\Observers;

use LaravelEnso\Addresses\app\Models\Address;

class Observer
{
    public function creating(Address $address)
    {
        $address->is_default = $address
            ->addressable_type::find($address->addressable_id)
            ->addresses()
            ->default()
            ->first() === null;
    }
}
