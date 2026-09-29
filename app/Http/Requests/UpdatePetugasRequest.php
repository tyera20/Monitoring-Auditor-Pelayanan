<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePetugasRequest extends FormRequest
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
        $petugasId = $this->route('petuga')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'nip' => ['required', 'string', 'max:32', "unique:ms_petugas,nip,{$petugasId}"],
            'position' => ['required', 'string', 'max:255'],
        ];
    }
}
