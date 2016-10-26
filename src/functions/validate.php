<?php
function validate_email($value, $params)
{
    if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
        return false;
    }
    return true;
}

function validate_ip($value, $params)
{
    if (filter_var($value, FILTER_VALIDATE_IP) === false) {
        return false;
    }
    return true;
}

function validate_url($value, $params)
{
    if (filter_var($value, FILTER_VALIDATE_URL) === false) {
        return false;
    }
    return true;
}

function validate_mac($value, $params)
{
    if (filter_var($value, FILTER_VALIDATE_MAC) === false) {
        return false;
    }
    return true;
}

function validate_not_empty($value, $params)
{
    if (in_array($value, array(NULL, FALSE, '', array()), TRUE)) {
        return false;
    }
    return true;
}

function validate_not_value($value, $params)
{
    if (!is_array($params)) {
        return false;
    }

    if (in_array($value, $params)) {
        return false;
    }
    return true;
}

function validate_min_length($value, $params)
{
    if (mb_strlen($value) < $params) {
        return false;
    }
    return true;
}

function validate_max_length($value, $params)
{
    if (mb_strlen($value) > $params) {
        return false;
    }
    return true;
}

function validate_length($value, $params)
{
    if (mb_strlen($value) <> $params) {
        return false;
    }
    return true;
}

function validate_int($value, $params)
{
    if (!is_int($value)) {
        return false;
    }
    return true;
}

function validate_numeric($value, $params)
{
    if (!is_numeric($value)) {
        return false;
    }
    return true;
}

function validate_regexp($value, $params)
{
    if (!preg_match($params, (string)$value)) {
        return false;
    }
    return true;
}

function validate_sql_date($value)
{
    if (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $value, $matches)) {
        if (checkdate($matches[2], $matches[3], $matches[1])) {
            return true;
        }
    }

    if (preg_match("/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $value, $matches)) {
        if (checkdate($matches[2], $matches[3], $matches[1])) {
            return true;
        }
    }

    return false;
}
