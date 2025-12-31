<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RedeemRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'code' => ['required', 'string'],
            'user.email' => ['required', 'email'],
        ];
    }
}
