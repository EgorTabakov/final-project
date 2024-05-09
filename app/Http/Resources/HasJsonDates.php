<?php

namespace App\Http\Resources;

trait HasJsonDates
{
    protected static ?string $debugDateFormat = null;  // 'Y-m-d h:i:s'

    protected function appDateFormat(): string
    {
        return static::$debugDateFormat ?? config('app.date_format');
    }
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->appDateFormat());
    }


}