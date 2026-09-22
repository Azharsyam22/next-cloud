<?php

namespace App\Http\Requests\File;

use Illuminate\Foundation\Http\FormRequest;

class UploadFileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('files.upload');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $maxKb = (int) config('cloudcampus.max_upload_size_kb', 102400);

        return [
            'file' => ['required', 'file', "max:{$maxKb}"],
            'folder_id' => ['nullable', 'integer', 'exists:folders,id'],
        ];
    }

    /**
     * Pesan kustom untuk validasi.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Berkas yang akan diunggah wajib disertakan.',
            'file.file' => 'Input yang dikirimkan harus berupa berkas valid.',
            'file.max' => 'Ukuran berkas melebihi batas maksimal yang diizinkan sistem.',
            'folder_id.exists' => 'Folder tujuan yang dipilih tidak ditemukan.',
        ];
    }
}
