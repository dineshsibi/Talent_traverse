<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo "Unauthorized: Please login first.";
    exit();
}
//database configuration
include("includes/config.php");

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);


// Include PhpSpreadsheet
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

// Get the logged-in employee's information
$uploaded_by = $_SESSION['user_name']; // Employee name
$uploaded_by_id = $_SESSION['user_id']; // Employee ID

// ------------- Helpers for normalization and existing-key snapshot -------------
function normalize_text($v)
{
    return strtolower(trim((string)$v));
}

/**
 * Normalize month into a consistent string:
 * - if numeric (or numeric-like) -> integer string (e.g. "8")
 * - if like "8-2025" or "08-2025" -> first part numeric -> "8"
 * - if textual month name -> attempt strtotime to get month number -> "8"
 * - otherwise fallback to trimmed lowercase
 */
function normalize_month($m)
{
    if ($m === null) return '';
    $m = trim((string)$m);
    if ($m === '') return '';
    if (is_numeric($m)) {
        return (string)(int)$m;
    }
    // direct matches like "8-2025" or "08-2025"
    if (preg_match('/^\s*(\d{1,2})\s*[-\/]\s*\d{2,4}\s*$/', $m, $match)) {
        return (string)(int)$match[1];
    }
    // if contains only digits and non-digit separators
    if (preg_match('/^\d{1,2}$/', $m)) return (string)(int)$m;

    // try parsing month name using strtotime (prepend day to help parsing)
    $ts = @strtotime('1 ' . $m);
    if ($ts !== false) {
        return (string) (int) date('n', $ts); // 1..12
    }

    // fallback
    return strtolower($m);
}

function normalize_year($y)
{
    if ($y === null) return '';
    $y = trim((string)$y);
    if ($y === '') return '';
    if (is_numeric($y)) {
        return (string)(int)$y;
    }
    // if like "Aug-2002" or "8-2025"
    if (preg_match('/[-\/](\d{4})$/', $y, $match)) {
        return $match[1];
    }
    $ts = @strtotime($y);
    if ($ts !== false) return (string) date('Y', $ts);
    return strtolower($y);
}

function build_key($client, $state, $location_code, $month, $year)
{
    // use normalized pieces separated by |
    $c = normalize_text($client);
    $s = normalize_text($state);
    $l = normalize_text($location_code);
    $m = normalize_month($month);
    $y = normalize_year($year);
    return $c . '|' . $s . '|' . $l . '|' . $m . '|' . $y;
}

/**
 * Get existing keys for a table BEFORE this upload.
 * Returns associative array with keys => true.
 */
function get_existing_keys_for_table($pdo, $table)
{
    $allowedTables = ['input', 'nfh', 'clra'];
    if (!in_array($table, $allowedTables)) return [];
    $sql = "SELECT client_name, state, location_code, month, year FROM $table";
    $keys = [];
    try {
        $stmt = $pdo->query($sql);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $key = build_key($row['client_name'] ?? '', $row['state'] ?? '', $row['location_code'] ?? '', $row['month'] ?? '', $row['year'] ?? '');
            if ($key !== '') $keys[$key] = true;
        }
    } catch (Exception $e) {
        // If column names differ or table empty, return empty set (safe fallback)
        return [];
    }
    return $keys;
}

// ------------- Insert functions (use snapshot of existing keys) -------------
function insert_input($pdo, $data, $uploaded_by)
{
    $column_mapping = [
        'A' => 'month',
        'B' => 'year',
        'C' => 'employee_code',
        'D' => 'employee_name',
        'E' => 'father_name',
        'F' => 'date_of_birth',
        'G' => 'date_of_joining',
        'H' => 'date_of_confirmation',
        'I' => 'date_of_leaving',
        'J' => 'gender',
        'K' => 'designation',
        'L' => 'department',
        'M' => 'bank_name',
        'N' => 'bank_account_no',
        'O' => 'ifsc_code',
        'P' => 'present_address',
        'Q' => 'client_name',
        'R' => 'state',
        'S' => 'city',
        'T' => 'location_name',
        'U' => 'location_code',
        'V' => 'branch_address',
        'W' => 'employer_name',
        'X' => 'employer_address',
        'Y' => 'nature_of_business',
        'Z' => 'shift_details',
        'AA' => 'paid_days',
        'AB' => 'lop',
        'AC' => 'fixed_basic',
        'AD' => 'fixed_da',
        'AE' => 'fixed_hra',
        'AF' => 'fixed_conveyance',
        'AG' => 'fixed_special_allowance',
        'AH' => 'fixed_other_allowance',
        'AI' => 'fixed_gross',
        'AJ' => 'basic',
        'AK' => 'da',
        'AL' => 'hra',
        'AM' => 'conveyance_allowance',
        'AN' => 'special_allowance',
        'AO' => 'statutory_bonus',
        'AP' => 'exgratia_bonus',
        'AQ' => 'maternity_bonus',
        'AR' => 'over_time_allowance',
        'AS' => 'medical_allowance',
        'AT' => 'attendance_bonus',
        'AU' => 'advance',
        'AV' => 'nfh_wages',
        'AW' => 'subsistence_allowance',
        'AX' => 'other_allowance',
        'AY' => 'gross_wages',
        'AZ' => 'epf',
        'BA' => 'vpf',
        'BB' => 'esi',
        'BC' => 'ptax',
        'BD' => 'lwf',
        'BE' => 'it_tds',
        'BF' => 'fines_damage_or_loss',
        'BG' => 'insurance',
        'BH' => 'advance_recovery',
        'BI' => 'other_deductions',
        'BJ' => 'total_deduction',
        'BK' => 'net_pay',
        'BL' => 'payment_date',
        'BM' => 'employer_pf',
        'BN' => 'day_1',
        'BO' => 'day_2',
        'BP' => 'day_3',
        'BQ' => 'day_4',
        'BR' => 'day_5',
        'BS' => 'day_6',
        'BT' => 'day_7',
        'BU' => 'day_8',
        'BV' => 'day_9',
        'BW' => 'day_10',
        'BX' => 'day_11',
        'BY' => 'day_12',
        'BZ' => 'day_13',
        'CA' => 'day_14',
        'CB' => 'day_15',
        'CC' => 'day_16',
        'CD' => 'day_17',
        'CE' => 'day_18',
        'CF' => 'day_19',
        'CG' => 'day_20',
        'CH' => 'day_21',
        'CI' => 'day_22',
        'CJ' => 'day_23',
        'CK' => 'day_24',
        'CL' => 'day_25',
        'CM' => 'day_26',
        'CN' => 'day_27',
        'CO' => 'day_28',
        'CP' => 'day_29',
        'CQ' => 'day_30',
        'CR' => 'day_31',
        'CS' => 'total_worked_days',
        'CT' => 'ot_hours',
        'CU' => 'extent_ot_on_which_occasion',
        'CV' => 'pl_opening',
        'CW' => 'pl_availed',
        'CX' => 'pl_credit',
        'CY' => 'pl_closing',
        'CZ' => 'cl_opening',
        'DA' => 'cl_availed',
        'DB' => 'cl_credit',
        'DC' => 'cl_closing',
        'DD' => 'sl_opening',
        'DE' => 'sl_availed',
        'DF' => 'sl_credit',
        'DG' => 'sl_closing'
    ];

    $db_columns = array_values($column_mapping);
    $db_columns[] = 'uploaded_by';
    $placeholders = implode(',', array_fill(0, count($db_columns), '?'));
    $sql = "INSERT INTO input (" . implode(',', $db_columns) . ") VALUES ($placeholders)";
    $stmt = $pdo->prepare($sql);

    // snapshot existing keys BEFORE this upload (so duplicates inside this file are NOT considered duplicates)
    $existingKeys = get_existing_keys_for_table($pdo, 'input');

    $duplicate_found = false;
    $inserted_rows = 0;
    $skipped_samples = [];

    foreach ($data as $row) {
        // build normalized key from the five columns
        $client_name = $row['Q'] ?? null;
        $state = $row['R'] ?? null;
        $location_code = $row['U'] ?? null;
        $month = $row['A'] ?? null;
        $year = $row['B'] ?? null;

        // build key only if all five present (per your rule). If any missing, treat as non-duplicate.
        $key = '';
        if ($client_name !== null && $state !== null && $location_code !== null && $month !== null && $year !== null) {
            $key = build_key($client_name, $state, $location_code, $month, $year);
        }

        // if key exists in pre-existing DB snapshot -> skip
        if ($key !== '' && isset($existingKeys[$key])) {
            $duplicate_found = true;
            // save a sample skipped row for reporting
            $skipped_samples[] = "{$client_name} / {$state} / {$location_code} / {$month} / {$year}";
            continue;
        }

        // prepare values in defined column order
        $values = [];
        foreach ($column_mapping as $excel_col => $db_col) {
            $value = $row[$excel_col] ?? null;
            // handle date conversions for date fields if Excel stored as numeric date
            $date_fields = ['date_of_birth', 'date_of_joining', 'date_of_confirmation', 'date_of_leaving', 'payment_date'];
            if (in_array($db_col, $date_fields) && is_numeric($value)) {
                try {
                    $value = Date::excelToDateTimeObject($value)->format('d-M-y');
                } catch (Exception $e) {
                    // keep original if conversion fails
                }
            }
            $values[] = $value;
        }

        // add uploaded_by
        $values[] = $uploaded_by;

        try {
            $stmt->execute($values);
            $inserted_rows++;
            // IMPORTANT: we DO NOT add $key to $existingKeys here — so duplicates inside the same upload remain insertable
        } catch (PDOException $e) {
            error_log("Error inserting INPUT record: " . $e->getMessage());
            // continue with next
            continue;
        }
    }

    return [
        'duplicate_found' => $duplicate_found,
        'inserted_rows' => $inserted_rows,
        'skipped_samples' => $skipped_samples
    ];
}

function insert_nfh($pdo, $data, $uploaded_by)
{
    $column_mapping = [
        'A' => 'client_name',
        'B' => 'state',
        'C' => 'location_code',
        'D' => 'month',
        'E' => 'year',
        'F' => 'holiday_date',
        'G' => 'description',
        'H' => 'leave_type'
    ];

    $db_columns = array_values($column_mapping);
    $db_columns[] = 'uploaded_by';
    $placeholders = implode(',', array_fill(0, count($db_columns), '?'));
    $sql = "INSERT INTO nfh (" . implode(',', $db_columns) . ") VALUES ($placeholders)";
    $stmt = $pdo->prepare($sql);

    // snapshot existing keys BEFORE this upload
    $existingKeys = get_existing_keys_for_table($pdo, 'nfh');

    $duplicate_found = false;
    $inserted_rows = 0;
    $skipped_samples = [];

    foreach ($data as $row) {
        $client_name = $row['A'] ?? null;
        $state = $row['B'] ?? null;
        $location_code = $row['C'] ?? null;
        $month = $row['D'] ?? null;
        $year = $row['E'] ?? null;

        $key = '';
        if ($client_name !== null && $state !== null && $location_code !== null && $month !== null && $year !== null) {
            $key = build_key($client_name, $state, $location_code, $month, $year);
        }

        if ($key !== '' && isset($existingKeys[$key])) {
            $duplicate_found = true;
            $skipped_samples[] = "{$client_name} / {$state} / {$location_code} / {$month} / {$year}";
            continue;
        }

        $values = [];
        foreach ($column_mapping as $excel_col => $db_col) {
            $value = $row[$excel_col] ?? null;
            if ($db_col === 'holiday_date' && is_numeric($value)) {
                try {
                    $value = Date::excelToDateTimeObject($value)->format('d-M-y');
                } catch (Exception $e) {
                }
            }
            $values[] = $value;
        }
        $values[] = $uploaded_by;

        try {
            $stmt->execute($values);
            $inserted_rows++;
        } catch (PDOException $e) {
            error_log("Error inserting NFH record: " . $e->getMessage());
            continue;
        }
    }

    return [
        'duplicate_found' => $duplicate_found,
        'inserted_rows' => $inserted_rows,
        'skipped_samples' => $skipped_samples
    ];
}

function insert_clra($pdo, $data, $uploaded_by)
{
    $column_mapping = [
        'A' => 'client_name',
        'B' => 'state',
        'C' => 'city',
        'D' => 'location_name',
        'E' => 'location_code',
        'F' => 'branch_address',
        'G' => 'employer_name',
        'H' => 'employer_address',
        'I' => 'month',
        'J' => 'year',
        'K' => 'name_of_contractor',
        'L' => 'address_of_contractor',
        'M' => 'nature_of_work_on_contract',
        'N' => 'from_date',
        'O' => 'to_date',
        'P' => 'maximum_number_of_workmen_employed_by_contractor'
    ];

    $db_columns = array_values($column_mapping);
    $db_columns[] = 'uploaded_by';
    $placeholders = implode(',', array_fill(0, count($db_columns), '?'));
    $sql = "INSERT INTO clra (" . implode(',', $db_columns) . ") VALUES ($placeholders)";
    $stmt = $pdo->prepare($sql);

    // snapshot existing keys BEFORE this upload
    $existingKeys = get_existing_keys_for_table($pdo, 'clra');

    $duplicate_found = false;
    $inserted_rows = 0;
    $skipped_samples = [];

    foreach ($data as $row) {
        $client_name = $row['A'] ?? null;
        $state = $row['B'] ?? null;
        $location_code = $row['E'] ?? null;
        $month = $row['I'] ?? null;
        $year = $row['J'] ?? null;

        $key = '';
        if ($client_name !== null && $state !== null && $location_code !== null && $month !== null && $year !== null) {
            $key = build_key($client_name, $state, $location_code, $month, $year);
        }

        if ($key !== '' && isset($existingKeys[$key])) {
            $duplicate_found = true;
            $skipped_samples[] = "{$client_name} / {$state} / {$location_code} / {$month} / {$year}";
            continue;
        }

        $values = [];
        foreach ($column_mapping as $excel_col => $db_col) {
            $values[] = $row[$excel_col] ?? null;
        }
        $values[] = $uploaded_by;

        try {
            $stmt->execute($values);
            $inserted_rows++;
        } catch (PDOException $e) {
            error_log("Error inserting CLRA record: " . $e->getMessage());
            continue;
        }
    }

    return [
        'duplicate_found' => $duplicate_found,
        'inserted_rows' => $inserted_rows,
        'skipped_samples' => $skipped_samples
    ];
}

// ------------- MAIN UPLOAD HANDLING -------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excel_file']['tmp_name'])) {
    try {
        $filePath = $_FILES['excel_file']['tmp_name'];
        $spreadsheet = IOFactory::load($filePath);

        $duplicate_messages = [];
        $inserted_total = 0;

        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            if (!$sheet) continue;

            $data = $sheet->toArray(null, true, true, true);
            array_shift($data); // Remove header row

            // Filter out empty rows
            $data = array_filter($data, function ($row) {
                return !empty(array_filter($row, function ($value) {
                    return $value !== null && $value !== '';
                }));
            });

            if (empty($data)) continue;

            switch (strtolower(trim($sheetName))) {
                case 'input':
                    $res = insert_input($pdo, $data, $uploaded_by);
                    if ($res['duplicate_found']) {
                        $duplicate_messages[] = "INPUT: some rows already existed before this upload and were skipped. Example(s): " . implode(' ; ', array_slice($res['skipped_samples'], 0, 5));
                    }
                    $inserted_total += $res['inserted_rows'];
                    break;
                case 'nfh':
                    $res = insert_nfh($pdo, $data, $uploaded_by);
                    if ($res['duplicate_found']) {
                        $duplicate_messages[] = "NFH: some rows already existed before this upload and were skipped. Example(s): " . implode(' ; ', array_slice($res['skipped_samples'], 0, 5));
                    }
                    $inserted_total += $res['inserted_rows'];
                    break;
                case 'clra':
                    $res = insert_clra($pdo, $data, $uploaded_by);
                    if ($res['duplicate_found']) {
                        $duplicate_messages[] = "CLRA: some rows already existed before this upload and were skipped. Example(s): " . implode(' ; ', array_slice($res['skipped_samples'], 0, 5));
                    }
                    $inserted_total += $res['inserted_rows'];
                    break;
                default:
                    // unknown sheet -> skip
                    break;
            }
        }
        // Set session messages and redirect appropriately
        if ($inserted_total > 0) {
            // If ANY new records were inserted, redirect to temp page
            if (!empty($duplicate_messages)) {
                $_SESSION['warning'] = $inserted_total . " new records inserted. Some data was skipped as it already exists.";
            } else {
                $_SESSION['success'] = "File uploaded successfully by " . $uploaded_by . ". " . $inserted_total . " records inserted.";
            }
            header("Location: temp.php?category=establishment"); // Go to temp page
        } else {
            // If NO new records inserted (only duplicates), stay on upload page
            $_SESSION['warning'] = "Data already exists. No new records inserted.";
            $referer = $_SERVER['HTTP_REFERER'] ?? 'temp.php';
            header("Location: $referer");
        }
        exit();
    } catch (Exception $e) {
        // For errors, show on upload page
        $_SESSION['error'] = "Error processing file: " . $e->getMessage();
        header("Location: temp.php?category=establishment");
        exit();
    }
} else {
    $_SESSION['error'] = "File not uploaded properly.";
    header("Location: temp.php?category=establishment");
    exit();
}
