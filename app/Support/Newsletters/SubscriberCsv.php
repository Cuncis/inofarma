<?php

namespace App\Support\Newsletters;

use App\Models\Subscriber;
use Illuminate\Database\Eloquent\Builder;

/**
 * Reads subscribers in from a CSV and writes them out to one.
 *
 * Import rules that matter for consent: an address that already exists is
 * left exactly as it is. In particular, importing a list never turns an
 * unsubscribed person back into a subscriber.
 */
class SubscriberCsv
{
    /**
     * @return array{imported: int, duplicates: int, invalid: int}
     */
    public static function import(string $path): array
    {
        $result = ['imported' => 0, 'duplicates' => 0, 'invalid' => 0];
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return $result;
        }

        $emailColumn = 0;
        $statusColumn = null;
        $first = true;

        while (($row = fgetcsv($handle, 0, self::delimiter($path))) !== false) {
            if ($row === [null] || $row === []) {
                continue;
            }

            if ($first) {
                $first = false;
                $header = array_map(fn ($cell) => strtolower(trim(self::stripBom((string) $cell))), $row);

                if (in_array('email', $header, true)) {
                    $emailColumn = array_search('email', $header, true);
                    $statusIndex = array_search('status', $header, true);
                    $statusColumn = $statusIndex === false ? null : $statusIndex;

                    continue;
                }
            }

            $email = strtolower(trim(self::stripBom((string) ($row[$emailColumn] ?? ''))));

            if (! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
                $result['invalid']++;

                continue;
            }

            if (Subscriber::where('email', $email)->exists()) {
                $result['duplicates']++;

                continue;
            }

            $optedOut = $statusColumn !== null
                && in_array(strtolower(trim((string) ($row[$statusColumn] ?? ''))), ['berhenti', 'unsubscribed'], true);

            Subscriber::create($optedOut
                ? ['email' => $email, 'status' => Subscriber::UNSUBSCRIBED, 'unsubscribed_at' => now()]
                : ['email' => $email, 'status' => Subscriber::SUBSCRIBED]);

            $result['imported']++;
        }

        fclose($handle);

        return $result;
    }

    /**
     * Streams the given subscribers as CSV: email, status, dates.
     *
     * @param  Builder<Subscriber>  $query
     */
    public static function export(Builder $query): \Closure
    {
        return function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['email', 'status', 'subscribed_at', 'unsubscribed_at']);

            $query->clone()->orderBy('id')->each(function (Subscriber $subscriber) use ($out) {
                fputcsv($out, [
                    $subscriber->email,
                    $subscriber->status,
                    $subscriber->subscribed_at?->toDateTimeString(),
                    $subscriber->unsubscribed_at?->toDateTimeString(),
                ]);
            });

            fclose($out);
        };
    }

    private static function stripBom(string $value): string
    {
        return preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;
    }

    private static function delimiter(string $path): string
    {
        $handle = fopen($path, 'r');
        $line = (string) fgets($handle);
        fclose($handle);

        return substr_count($line, ';') > substr_count($line, ',') ? ';' : ',';
    }
}
