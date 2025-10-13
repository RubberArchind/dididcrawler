<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;

class Tz
{
    /**
     * Format a datetime in a given timezone.
     *
     * @param mixed $datetime Carbon instance, DateTime, string, or null
     * @param string $format Output format (default d/m/Y H:i:s)
     * @param string $timezone Target timezone (default Asia/Jakarta)
     */
    public static function format($datetime, string $format = 'd/m/Y H:i:s', string $timezone = 'Asia/Jakarta'): string
    {
        if (empty($datetime)) {
            return '';
        }

        try {
            if ($datetime instanceof CarbonInterface) {
                return $datetime->copy()->setTimezone($timezone)->format($format);
            }

            // Accept DateTime or string
            $c = $datetime instanceof \DateTimeInterface
                ? Carbon::instance($datetime)
                : Carbon::parse($datetime);

            return $c->setTimezone($timezone)->format($format);
        } catch (\Throwable $e) {
            return '';
        }
    }
}
