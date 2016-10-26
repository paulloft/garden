<?php

function date_sql($date = false)
{
    if ($date === false) $date = time();
    if (is_string($date)) {
        $date = strtotime($date);
    }

    return date("Y-m-d H:i:s", $date);
}

function date_convert($date, $format = 'indatetime')
{
    if (empty($date)) return false;

    $month = array("Января", "Февраля", "Марта", "Апреля", "Мая", "Июня", "Июля", "Августа", "Сентября", "Октября", "Ноября", "Декабря");
    $days = array("Понедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота", "Воскресенье");

    if (is_string($date)) {
        $date = strtotime($date);
    }

    switch ($format) {
        case ("fullDate")       : $date = date("j", $date)." ".$month[date("m", $date)-1]." ".date("Y", $date); break;
        case ("fullDateTime")   : $date = date("j", $date)." ".$month[date("m", $date)-1]." ".date("Y в H:i", $date); break;
        case ("shortDateTime")  : $date = date("j", $date)." ".$month[date("m", $date)-1]." ".date(" в H:i", $date); break;
        case ("date")           : $date = date("d.m.Y", $date); break;
        case ("time")           : $date = date("H:i:s", $date); break;
        case ("datetime")       : $date = date("d.m.Y H:i", $date); break;
        case ("datefulltime")   : $date = date("d.m.Y H:i:s", $date); break;

        case ("indatetime")     : $date = date("d.m.Y в H:i", $date); break;
        case ("indatefulltime") : $date = date("d.m.Y в H:i:s", $date); break;

        case ("sql")            : $date = date("Y-m-d H:i:s", $date); break;
        default                 : $date = date($format, $date); break;
    }

    return $date;
}

/**
 * Compare two dates formatted as either timestamps or strings.
 *
 * @param mixed $date1 The first date to compare expressed as an integer timestamp or a string date.
 * @param mixed $date2 The second date to compare expressed as an integer timestamp or a string date.
 * @return int Returns `1` if {@link $date1} > {@link $date2}, `-1` if {@link $date1} > {@link $date2},
 * or `0` if the two dates are equal.
 * @category Date/Time Functions
 */
function date_compare($date1, $date2 = false)
{
    if (!$date2) $date2 = time();

    if (is_string($date1)) $date1 = strtotime($date1);
    if (is_string($date2)) $date2 = strtotime($date2);

    if($date1 > $date2) {
        return 1;
    } elseif($date1 == $date2) {
        return 0;
    } elseif($date1 < $date2) {
        return -1;
    }
}

function date_get_ages($birthday, $label = true)
{
    $now = new \DateTime('now');
    $age = $now->diff(new \DateTime($birthday));
    $ages = $age->format('%y');

    return $label ? $ages.' '.format_declension($ages, ['лет', 'год', 'года']) : $ages;
}

function date_passed($date)
{
    if (is_string($date)) {
        $date = strtotime($date);
    }
    
    $diff =  time() - $date;
    switch ($diff) {
        case ($diff < 10):
            $txt = "только что";
            return $txt;
            break;
        case ($diff < 60):
            $txt = "менее минуты назад";
            return $txt;
            break;

        case ($diff < 3600):
            $tm = ceil($diff / 60);
            $forms = array("минут", "минута", "минуты");
            $txt = $tm . " " . format_declension($tm, $forms) . " назад";
            return $txt;
            break;

        case ($diff < 86400):
            $tm = ceil($diff / 3600);
            $forms = array("часов", "час", "часа");
            $txt = $tm . " " . format_declension($tm, $forms) . " назад";
            return $txt;
            break;

        case ($diff < 604800):
            $tm = round($diff / 86400);
            $forms = array("дней", "день", "дня");
            $txt = $tm . " " . format_declension($tm, $forms) . " назад";
            return $txt;
            break;

        default:
            return date_convert($date, 'indatetime');
            break;
    }
}