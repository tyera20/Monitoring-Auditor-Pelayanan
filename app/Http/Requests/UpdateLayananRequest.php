<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLayananRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>|string>
     */
    public function rules(): array
    {
        $layananId = $this->route('layanan')?->id;

        return [
            'nama_layanan' => ['required', 'string', 'max:255', "unique:ms_layanan,nama_layanan,{$layananId}"],
        ];
    }
}
