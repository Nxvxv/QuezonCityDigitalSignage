<?php
// Lightweight input validation and sanitization helpers for admin endpoints

function validate_int($value, $min = null, $max = null) {
    if (!is_numeric($value)) return null;
    $i = intval($value);
    if ($min !== null && $i < $min) return null;
    if ($max !== null && $i > $max) return null;
    return $i;
}

function validate_string($value, $maxLen = 2000) {
    if (!is_string($value)) return '';
    $s = trim($value);
    if ($s === '') return '';
    if (mb_strlen($s) > $maxLen) {
        $s = mb_substr($s, 0, $maxLen);
    }
    return $s;
}

function parse_datetime_local($value) {
    // Accept HTML datetime-local like "2025-12-03T15:30" and convert to "Y-m-d H:i:s"
    if (!$value) return null;
    $value = str_replace('T', ' ', $value);
    $dt = DateTime::createFromFormat('Y-m-d H:i', $value);
    if ($dt === false) {
        // Try with seconds
        $dt = DateTime::createFromFormat('Y-m-d H:i:s', $value);
        if ($dt === false) return null;
    }
    return $dt->format('Y-m-d H:i:s');
}

function escape_like_term($term) {
    // Escape % and _ and backslash for LIKE, caller should add surrounding %
    return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term);
}

?>