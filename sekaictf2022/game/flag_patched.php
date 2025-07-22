<?php
$flag_path = '/flag';

if (file_exists($flag_path) && is_readable($flag_path)) {
    $flag_contents = file_get_contents($flag_path);
    if ($flag_contents !== false) {
        $flag = trim($flag_contents);
        putenv('FLAG=' . $flag);
    } else {
        error_log("Failed to read flag file at {$flag_path}");
    }
} else {
    error_log("Flag file not found or not readable at {$flag_path}");
}
?>

