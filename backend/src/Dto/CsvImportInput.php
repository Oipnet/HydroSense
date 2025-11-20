<?php

namespace App\Dto;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * DTO for CSV import input.
 * 
 * This class is used to receive the file upload in the API Platform operation.
 */
class CsvImportInput
{
    public ?UploadedFile $file = null;
}
