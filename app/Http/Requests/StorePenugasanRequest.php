<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePenugasanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nomor_surat_tugas' => [
                'nullable',
                'string',
                'max:150',
            ],

            'petugas_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'petugas_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:ms_petugas,id',
            ],

            'layanan_id' => [
                'required',
                'integer',
                'exists:ms_layanan,id',
            ],

            'tempat' => [
                'required',
                'string',
                'max:255',
            ],

            'komoditi' => [
                'required',
                'string',
                'max:255',
            ],

            'task_detail' => [
                'required',
                'string',
            ],

            'tanggal_mulai' => [
                'required',
                'date',
            ],

            'tanggal_selesai' => [
                'required',
                'date',
                'after_or_equal:tanggal_mulai',
            ],

            'force_conflict' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'petugas_ids.required' =>
                'Minimal satu petugas harus dipilih.',

            'petugas_ids.min' =>
                'Minimal satu petugas harus dipilih.',

            'layanan_id.required' =>
                'Layanan wajib dipilih.',

            'tempat.required' =>
                'Tempat wajib diisi.',

            'komoditi.required' =>
                'Komoditi wajib diisi.',

            'task_detail.required' =>
                'Detail tugas wajib diisi.',

            'tanggal_mulai.required' =>
                'Tanggal mulai wajib diisi.',

            'tanggal_selesai.required' =>
                'Tanggal selesai wajib diisi.',

            'tanggal_selesai.after_or_equal' =>
                'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.',
        ];
    }
}