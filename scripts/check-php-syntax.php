<?php

declare(strict_types=1);

$roots = ["app", "bootstrap", "config", "database", "resources", "routes", "scripts", "tests"];
$errors = [];
$checked = 0;

foreach($roots as $root) {

    $absoluteRoot = dirname(__DIR__).DIRECTORY_SEPARATOR.$root;

    if(!is_dir($absoluteRoot)) {

        continue;

    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($absoluteRoot, FilesystemIterator::SKIP_DOTS)
    );

    foreach($iterator as $file) {

        if(!$file->isFile() || strtolower($file->getExtension()) !== "php") {

            continue;

        }

        $checked++;

        $source = file_get_contents($file->getPathname());

        if($source === false) {

            $errors[] = "No se pudo leer {$file->getPathname()}.";

            continue;

        }

        try {

            token_get_all($source, TOKEN_PARSE);

        }catch(ParseError $exception) {

            $errors[] = "{$file->getPathname()}: {$exception->getMessage()}";

        }

    }

}

if($errors !== []) {

    fwrite(STDERR, implode(PHP_EOL, $errors).PHP_EOL);
    exit(1);

}

fwrite(STDOUT, "Sintaxis PHP válida en {$checked} archivos.".PHP_EOL);
