<?php

namespace App\Support\CsvTemplates;

/**
 * Folds an importer's failed rows by message, so one cause that hits hundreds
 * of rows (an unknown branch, say) is reported once with its row numbers.
 */
class ImportFailures
{
    private const SHOWN_ROWS = 10;

    /**
     * @param  list<array{row: int, message: string}>  $failed
     * @return list<array{message: string, count: int, rows: list<int>, more: int}>
     */
    public static function group(array $failed): array
    {
        $groups = [];

        foreach ($failed as $failure) {
            $groups[$failure['message']][] = $failure['row'];
        }

        return array_values(array_map(fn (string $message) => [
            'message' => $message,
            'count' => count($groups[$message]),
            'rows' => array_slice($groups[$message], 0, self::SHOWN_ROWS),
            'more' => max(0, count($groups[$message]) - self::SHOWN_ROWS),
        ], array_keys($groups)));
    }
}
