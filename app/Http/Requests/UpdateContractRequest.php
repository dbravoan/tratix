<?php

namespace App\Http\Requests;

class UpdateContractRequest extends StoreContractRequest
{
    public function authorize(): bool
    {
        $contract = $this->route('contract');

        return $contract && $this->user()->can('update', $contract);
    }
}
