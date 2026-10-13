<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

final class ImportTransactionsRequest extends FormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'extensions:csv', 'max:10240'],
        ];
    }

    public function csv(): UploadedFile
    {
        /** @var UploadedFile */
        return $this->file('file');
    }
}
