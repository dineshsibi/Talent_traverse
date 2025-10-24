<?php
// functions.php
if (!function_exists('safe')) {
    function safe($value, $default = '') {
        if (is_array($value)) {
            return implode(', ', array_map(function($item) {
                return htmlspecialchars($item ?? '');
            }, $value));
        }
        return htmlspecialchars($value ?? $default);
    }

}

// Function to calculate tenure
function calculateTenure($joiningDate, $reportMonth, $reportYear)
{
    if (empty($joiningDate) || empty($reportMonth) || empty($reportYear)) {
        return '';
    }

    try {
        $joinDate = new DateTime($joiningDate);
        $endDate = DateTime::createFromFormat('Y-m-d', "$reportYear-$reportMonth-01");
        if (!$endDate) {
            return '';
        }

        $interval = $endDate->diff($joinDate);
        $years = $interval->y;
        $months = $interval->m;

        $tenure = '';
        if ($years > 0) {
            $tenure .= $years . ' year' . ($years > 1 ? 's' : '');
        }
        if ($months > 0) {
            if (!empty($tenure)) {
                $tenure .= ' ';
            }
            $tenure .= $months . ' month' . ($months > 1 ? 's' : '');
        }

        return empty($tenure) ? 'Less than 1 month' : $tenure;
    } catch (Exception $e) {
        return '';
    }
}