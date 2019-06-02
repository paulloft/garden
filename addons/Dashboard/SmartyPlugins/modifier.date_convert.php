<?php

use Garden\Helpers\Date;

function smarty_modifier_date_convert($dateString, $format = 'date_time')
{
    if (empty($dateString)) {
        return '';
    }

    switch ($format) {
        case 'sql':
            $format = Date::FORMAT_SQL;
            break;

        case 'sql_date':
            $format = Date::FORMAT_SQL_DATE;
            break;
        case 'date':
            $format = Date::FORMAT_DATE;
            break;

        case 'date_time':
            $format = Date::FORMAT_DATE_TIME;
            break;

        case 'date_time_sec':
            $format = Date::FORMAT_DATE_TIME_SEC;
            break;

        case 'time':
            $format = Date::FORMAT_TIME;
            break;

        case 'time_sec':
            $format = Date::FORMAT_TIME_SEC;
            break;
    }

    return Date::create($dateString)->format($format);
}