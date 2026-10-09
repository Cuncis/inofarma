<?php

namespace App\Support\CsvTemplates;

use RuntimeException;

/**
 * Reads an uploaded CSV into header-keyed rows.
 *
 * Excel in an Indonesian locale saves CSV with `;` instead of `,` and may add a
 * UTF-8 byte-order mark, so both delimiters are accepted (guessed from the
 * header line) and the mark is dropped. Header names are matched
 * case-insensitively. Fully blank lines are skipped but still counted, so the
 * line numbers reported back to the user match what Excel shows.
 */
class CsvTable
{
    /**
     * @param  array<string, string>  $aliases  header name in the file => name the importer expects
     * @return array{header: list<string>, rows: list<array{line: int, cells: array<string, string>}>}
     */
    public static function read(string $path, array $aliases = []): array
    {
        $content = file_get_contents($path);

        if ($content === false || trim($content) === '') {
            throw new RuntimeException('File CSV kosong.');
        }

        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content) ?? $content;
        $firstLine = strtok($content, "\n") ?: '';
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';

        $stream = fopen('php://temp', 'w+');
        fwrite($stream, $content);
        rewind($stream);

        $header = array_map(
            fn (?string $name) => $aliases[strtolower(trim((string) $name))] ?? strtolower(trim((string) $name)),
            fgetcsv($stream, separator: $delimiter, escape: '\\') ?: [],
        );

        $rows = [];
        $line = 1;

        while (($record = fgetcsv($stream, separator: $delimiter, escape: '\\')) !== false) {
            $line++;

            if ($record === [null]) {
                continue;
            }

            $cells = [];

            foreach ($header as $position => $name) {
                $cells[$name] = trim((string) ($record[$position] ?? ''));
            }

            $rows[] = ['line' => $line, 'cells' => $cells];
        }

        fclose($stream);

        return ['header' => $header, 'rows' => $rows];
    }

    /**
     * @param  list<string>  $header
     * @param  list<string>  $required
     */
    public static function assertColumns(array $header, array $required): void
    {
        $missing = array_values(array_diff($required, $header));

        if ($missing !== []) {
            throw new RuntimeException('Kolom wajib tidak ada di file: '.implode(', ', $missing).'.');
        }
    }
}
