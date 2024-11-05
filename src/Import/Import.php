<?php

namespace SMFramework\Import;

use SMFramework\Import\Adapters\Contracts\FileAdapterContract;
use SMFramework\Import\Contracts\ImporterContract;

class Import
{
    public function __construct(
        private string $fileRoot,
        private FileAdapterContract $fileAdapter,
        private ImporterContract $importer,
    ) {
        $this->fileAdapter = $fileAdapter;
        $this->importer = $importer;
    }
    /**
     * Summary of createImporter
     * @param string $fileRoot
     * @param class-string<FileAdapterContract> $fileAdapter
     * @param class-string<ImporterContract> $importer
     * @return void
     */
    public static function runImport(
        string $fileRoot,
        string $fileAdapter,
        string $importer,
    ) {
        $importerObject = new $importer();
        $fileAdapterObject = new $fileAdapter();
        $importerObject->import($fileAdapterObject->readFile($fileRoot));
    }
}
