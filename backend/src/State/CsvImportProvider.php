<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\CsvImportInput;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provider for CSV import that extracts the uploaded file from the request.
 * 
 * Since we're using multipart/form-data, we need to manually extract
 * the file from the request instead of using the default deserialization.
 */
class CsvImportProvider implements ProviderInterface
{
    public function __construct(
        private readonly RequestStack $requestStack
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $request = $this->requestStack->getCurrentRequest();
        
        if (!$request) {
            return new CsvImportInput();
        }

        $input = new CsvImportInput();
        $input->file = $request->files->get('file');

        return $input;
    }
}
