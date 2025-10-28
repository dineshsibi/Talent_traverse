<?php
session_start();

// Check login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo "Unauthorized: Please login first.";
    exit();
}

include("includes/config.php");
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

// ---------- HELPERS ---------- //

function normalize_text($v)
{
    return strtolower(trim((string)$v));
}

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
    $allowedTables = ['consolidated_data', 'n_f', 'clra_input'];
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

function clean_import_value($value)
{
    if ($value === null) return '';
    $value = trim((string)$value);
    if ($value === '-') return '-';
    if (strcasecmp($value, 'nil') === 0) return 'Nil';

    // Fix floating point precision
    if (is_numeric($value) && strpos($value, '.') !== false) {
        $value = number_format((float)$value, 2, '.', '');
    }

    return $value;
}

function convert_excel_date($value)
{
    if ($value === null || $value === '') return '';
    $value = trim((string)$value);
    if ($value === '-' || strcasecmp($value, 'nil') === 0) return $value;

    try {
        // If Excel date number
        if (is_numeric($value) && $value > 10000) {
            return Date::excelToDateTimeObject($value)->format('d-M-Y');
        }

        // If already string date
        $timestamp = strtotime($value);
        return ($timestamp && $timestamp > 0) ? date('d-M-Y', $timestamp) : $value;
    } catch (Exception $e) {
        return $value;
    }
}

// ---------- INSERT FUNCTIONS WITH DUPLICATE CHECK ---------- //

function insert_consolidated_data($pdo, $data, $uploaded_by)
{
    array_shift($data); // Skip header

    $column_mapping = [
         'A' => 'month',
        'B' => 'year',
        'C' => 'employee_code',
        'D' => 'employee_name',
        'E' => 'father_name',
        'F' => 'date_of_birth',
        'G' => 'date_of_joining',
        'H' => 'date_of_confirmation',
        'I' => 'date_of_resign',
        'J' => 'reason_for_exit',
        'K' => 'gender',
        'L' => 'designation',
        'M' => 'department',
        'N' => 'category',
        'O' => 'bank_name',
        'P' => 'bank_account_number',
        'Q' => 'ifsc_code',
        'R' => 'uan_no',
        'S' => 'pf_no',
        'T' => 'esi_no',
        'U' => 'pan_no',
        'V' => 'aadhaar',
        'W' => 'present_address',
        'X' => 'client_name',
        'Y' => 'state',
        'Z' => 'location_code',
        'AA' => 'address',
        'AB' => 'principal_employer_name',
        'AC' => 'principal_employer_address',
        'AD' => 'shift_details',
        'AE' => 'nature_of_business',
        'AF' => 'paid_days',
        'AG' => 'lop_days',
        'AH' => 'fixed_basic',
        'AI' => 'fixed_da',
        'AJ' => 'fixed_hra',
        'AK' => 'fixed_other_allowance',
        'AL' => 'rate_of_wage_fixed_gross',
        'AM' => 'basic',
        'AN' => 'da',
        'AO' => 'hra',
        'AP' => 'special_allowance',
        'AQ' => 'leave_travel_allowance',
        'AR' => 'conveyance_allowance',
        'AS' => 'over_time_allowance',
        'AT' => 'nh_fh_week_off_wages',
        'AU' => 'medical_allowance',
        'AV' => 'children_education_allowance',
        'AW' => 'incentive',
        'AX' => 'night_shift_allowance',
        'AY' => 'food_allowance',
        'AZ' => 'bonus',
        'BA' => 'mob_allowance',
        'BB' => 'vehicle_reimb',
        'BC' => 'other_allowance',
        'BD' => 'earned_gross',
        'BE' => 'pf',
        'BF' => 'vpf',
        'BG' => 'esi_deduction',
        'BH' => 'p_tax',
        'BI' => 'lwf',
        'BJ' => 'tds',
        'BK' => 'advance_loan',
        'BL' => 'deduction_for_damages_loss',
        'BM' => 'fine',
        'BN' => 'medical_insurance',
        'BO' => 'other_deductions',
        'BP' => 'total_deductions',
        'BQ' => 'net_salary',
        'BR' => 'payment_date',

        // Attendance
        'BS' => 'day_1',
        'BT' => 'day_2',
        'BU' => 'day_3',
        'BV' => 'day_4',
        'BW' => 'day_5',
        'BX' => 'day_6',
        'BY' => 'day_7',
        'BZ' => 'day_8',
        'CA' => 'day_9',
        'CB' => 'day_10',
        'CC' => 'day_11',
        'CD' => 'day_12',
        'CE' => 'day_13',
        'CF' => 'day_14',
        'CG' => 'day_15',
        'CH' => 'day_16',
        'CI' => 'day_17',
        'CJ' => 'day_18',
        'CK' => 'day_19',
        'CL' => 'day_20',
        'CM' => 'day_21',
        'CN' => 'day_22',
        'CO' => 'day_23',
        'CP' => 'day_24',
        'CQ' => 'day_25',
        'CR' => 'day_26',
        'CS' => 'day_27',
        'CT' => 'day_28',
        'CU' => 'day_29',
        'CV' => 'day_30',
        'CW' => 'day_31',
        'CX' => 'total_present_days',

        // Leave
        'CY' => 'pl_opening',
        'CZ' => 'pl_availed',
        'DA' => 'pl_credit',
        'DB' => 'pl_closing',
        'DC' => 'cl_opening',
        'DD' => 'cl_availed',
        'DE' => 'cl_credit',
        'DF' => 'cl_closing',
        'DG' => 'sl_opening',
        'DH' => 'sl_availed',
        'DI' => 'sl_credit',
        'DJ' => 'sl_closing',

        // OT
        'DK' => 'ot_hours',
        'DL' => 'fixed_ot_wages',
        'DM' => 'extent_of_overtime_on_each_occasion',
        'DN' => 'ot_day_1',
        'DO' => 'ot_day_2',
        'DP' => 'ot_day_3',
        'DQ' => 'ot_day_4',
        'DR' => 'ot_day_5',
        'DS' => 'ot_day_6',
        'DT' => 'ot_day_7',
        'DU' => 'ot_day_8',
        'DV' => 'ot_day_9',
        'DW' => 'ot_day_10',
        'DX' => 'ot_day_11',
        'DY' => 'ot_day_12',
        'DZ' => 'ot_day_13',
        'EA' => 'ot_day_14',
        'EB' => 'ot_day_15',
        'EC' => 'ot_day_16',
        'ED' => 'ot_day_17',
        'EE' => 'ot_day_18',
        'EF' => 'ot_day_19',
        'EG' => 'ot_day_20',
        'EH' => 'ot_day_21',
        'EI' => 'ot_day_22',
        'EJ' => 'ot_day_23',
        'EK' => 'ot_day_24',
        'EL' => 'ot_day_25',
        'EM' => 'ot_day_26',
        'EN' => 'ot_day_27',
        'EO' => 'ot_day_28',
        'EP' => 'ot_day_29',
        'EQ' => 'ot_day_30',
        'ER' => 'ot_day_31',

        // Advance
        'ES' => 'date_and_amount_of_advance_given',
        'ET' => 'purposes_for_which_advance_made',
        'EU' => 'no_of_installments_by_which_advance_repaid',
        'EV' => 'date_and_amount_of_each_installment_repaid',
        'EW' => 'date_on_which_last_installment_was_paid',

        // Damages
        'EX' => 'date_of_damage',
        'EY' => 'whether_worker_showed_cause_against_deduction',
        'EZ' => 'date_of_deduction_imposed',
        'FA' => 'amount_of_deduction_imposed',
        'FB' => 'no_of_installment',
        'FC' => 'last_installment_date',

        // Fine
        'FD' => 'act_or_omission_for_which_fine_was_imposed',
        'FE' => 'whether_workman_showed_cause_against_fine',
        'FF' => 'nature_and_date_of_offence_for_which_fine_imposed',
        'FG' => 'date_amount_of_fine_imposed',
        'FH' => 'date_on_which_fine_realised',

        // Maternity
        'FI' => 'date_on_which_the_woman_gives_notice_under_section_6',
        'FJ' => 'date_of_discharge_dismissal_if_any',
        'FK' => 'date_of_production_of_proof_of_pregnancy_under_section_6',
        'FL' => 'date_of_birth_of_child',
        'FM' => 'date_of_production_proof_of_delivery_miscarriage',
        'FN' => 'date_of_production_of_proof_illness_referred_in_section_10',
        'FO' => 'date_with_amount_of_maternity_paid_in_advance_of_delivery',
        'FP' => 'date_with_the_amount_of_subsequent_payment_of_maternity_benefit',
        'FQ' => 'date_with_the_amount_of_bonus_if_paid_under_section_8',
        'FR' => 'date_wages_paid_account_leave_under_section_9_15_and_section_9a',
        'FS' => 'date_amount_of_wages_paid_account_leave_under_section_10',
        'FT' => 'name_of_the_person_nominated_by_the_woman_under_section_6',
        'FU' => 'woman_dies_maternity_amount_date_of_payment',
        'FV' => 'woman_dies_child_survives_to_amount_maternity_paid',

        // Accident
        'FW' => 'date_of_notice',
        'FX' => 'time_of_notice',
        'FY' => 'number_of_days_injured_person_was_absent_from_work',
        'FZ' => 'insurance_no',
        'GA' => 'date_of_return_of_injured_person_to_work',
        'GB' => 'cause_of_injury',
        'GC' => 'nature_of_injury',
        'GD' => 'date_of_injury',
        'GE' => 'time_details_of_injury',
        'GF' => 'place_details_of_injury',
        'GG' => 'exactly_injured_person_doing_at_the_time_of_accident',
        'GH' => 'name_address_signature_thumb_impression_person_giving_notice',
        'GI' => 'name_address_and_occupation_of_two_witnesses',

        // Factory & Medical
        'GJ' => 'part_of_factory_e_g_name_of_unit_of_room',
        'GK' => 'wall_and_other_parts',
        'GL' => 'treatment_whether_lime_washed_painted_vanished_or_oiled',
        'GM' => 'date_painting_oiling_carried_out_according_english_calendar',
        'GN' => 'raw_materials_products_or_by_products_exposed_to',
        'GO' => 'dates_of_medical_examination',
        'GP' => 'medical_examination_results',
        'GQ' => 'signs_and_symptoms_observed_during_examination',
        'GR' => 'nature_of_tests_and_results_thereof',
        'GS' => 'declared_unfit_work_state_period_suspension_reasons_in_detail',
        'GT' => 'whether_certificate_of_unfitness_issued_to_the_worker',
        'GU' => 're_certified_fit_to_resume_duty_on',

        // Suspension
        'GV' => 'nature_of_offence_committed_and_date_of_offence',
        'GW' => 'date_of_suspension',
        'GX' => 'date_of_revocation_of_suspension',
        'GY' => 'rate_which_subsistence_allowance_calculated_period',
        'GZ' => 'amount_subsistence_allowance_paid_and_date_of_payment'
    ];

    $columns = array_values($column_mapping);
    $columns[] = 'uploaded_by';
    $sql = "INSERT INTO consolidated_data (" . implode(',', $columns) . ") VALUES (" . implode(',', array_fill(0, count($columns), '?')) . ")";
    $stmt = $pdo->prepare($sql);

    // snapshot existing keys BEFORE this upload
    $existingKeys = get_existing_keys_for_table($pdo, 'consolidated_data');

    $duplicate_found = false;
    $inserted_rows = 0;
    $skipped_samples = [];

    $date_fields = [
        'date_of_birth',
        'date_of_joining',
        'date_of_confirmation',
        'date_of_resign',
        'payment_date'
    ];

    foreach ($data as $row) {
        $row = array_map('clean_import_value', $row);
        if (count(array_filter($row, fn($v) => $v !== '')) === 0) continue;

        // Build key for duplicate check
        $client_name = $row['X'] ?? null;
        $state = $row['Y'] ?? null;
        $location_code = $row['Z'] ?? null;
        $month = $row['A'] ?? null;
        $year = $row['B'] ?? null;

        $key = '';
        if ($client_name !== null && $state !== null && $location_code !== null && $month !== null && $year !== null) {
            $key = build_key($client_name, $state, $location_code, $month, $year);
        }

        // Check if key exists in pre-existing DB snapshot
        if ($key !== '' && isset($existingKeys[$key])) {
            $duplicate_found = true;
            $skipped_samples[] = "{$client_name} / {$state} / {$location_code} / {$month} / {$year}";
            continue;
        }

        $values = [];
        foreach ($column_mapping as $excel_col => $db_col) {
            $val = $row[$excel_col] ?? '';

            // Convert date fields
            if (in_array($db_col, $date_fields)) {
                $val = convert_excel_date($val);
            }

            $values[] = $val;
        }

        $values[] = $uploaded_by;

        try {
            $stmt->execute($values);
            $inserted_rows++;
        } catch (Exception $e) {
            error_log("Row insert failed: " . $e->getMessage());
        }
    }

    return [
        'duplicate_found' => $duplicate_found,
        'inserted_rows' => $inserted_rows,
        'skipped_samples' => $skipped_samples
    ];
}

function insert_n_f($pdo, $data, $uploaded_by)
{
    array_shift($data); // Skip header

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

    $columns = array_values($column_mapping);
    $columns[] = 'uploaded_by';
    $stmt = $pdo->prepare("INSERT INTO n_f (" . implode(',', $columns) . ") VALUES (" . implode(',', array_fill(0, count($columns), '?')) . ")");
    
    // snapshot existing keys BEFORE this upload
    $existingKeys = get_existing_keys_for_table($pdo, 'n_f');

    $duplicate_found = false;
    $inserted_rows = 0;
    $skipped_samples = [];

    foreach ($data as $row) {
        $row = array_map('clean_import_value', $row);
        if (count(array_filter($row, fn($v) => $v !== '')) === 0) continue;

        // Build key for duplicate check
        $client_name = $row['A'] ?? null;
        $state = $row['B'] ?? null;
        $location_code = $row['C'] ?? null;
        $month = $row['D'] ?? null;
        $year = $row['E'] ?? null;

        $key = '';
        if ($client_name !== null && $state !== null && $location_code !== null && $month !== null && $year !== null) {
            $key = build_key($client_name, $state, $location_code, $month, $year);
        }

        // Check if key exists in pre-existing DB snapshot
        if ($key !== '' && isset($existingKeys[$key])) {
            $duplicate_found = true;
            $skipped_samples[] = "{$client_name} / {$state} / {$location_code} / {$month} / {$year}";
            continue;
        }

        $values = [];
        foreach ($column_mapping as $excel_col => $db_col) {
            $val = $row[$excel_col] ?? '';
            if ($db_col === 'holiday_date') $val = convert_excel_date($val);
            $values[] = $val;
        }

        $values[] = $uploaded_by;

        try {
            $stmt->execute($values);
            $inserted_rows++;
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }

    return [
        'duplicate_found' => $duplicate_found,
        'inserted_rows' => $inserted_rows,
        'skipped_samples' => $skipped_samples
    ];
}

function insert_clra_input($pdo, $data, $uploaded_by)
{
    array_shift($data);

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

    $columns = array_values($column_mapping);
    $columns[] = 'uploaded_by';
    $stmt = $pdo->prepare("
        INSERT INTO clra_input (" . implode(',', $columns) . ")
        VALUES (" . implode(',', array_fill(0, count($columns), '?')) . ")
    ");
    
    // snapshot existing keys BEFORE this upload
    $existingKeys = get_existing_keys_for_table($pdo, 'clra_input');

    $duplicate_found = false;
    $inserted_rows = 0;
    $skipped_samples = [];

    foreach ($data as $row) {
        $row = array_map('clean_import_value', $row);
        if (count(array_filter($row, fn($v) => $v !== '')) === 0) continue;

        // Build key for duplicate check
        $client_name = $row['A'] ?? null;
        $state = $row['B'] ?? null;
        $location_code = $row['E'] ?? null;
        $month = $row['I'] ?? null;
        $year = $row['J'] ?? null;

        $key = '';
        if ($client_name !== null && $state !== null && $location_code !== null && $month !== null && $year !== null) {
            $key = build_key($client_name, $state, $location_code, $month, $year);
        }

        // Check if key exists in pre-existing DB snapshot
        if ($key !== '' && isset($existingKeys[$key])) {
            $duplicate_found = true;
            $skipped_samples[] = "{$client_name} / {$state} / {$location_code} / {$month} / {$year}";
            continue;
        }

        $values = [];
        foreach ($column_mapping as $excel_col => $db_col) {
            $val = $row[$excel_col] ?? '';

            // Only format date columns
            if (in_array($db_col, ['from_date', 'to_date'])) {
                $converted = convert_excel_date($val);
                if (!empty($converted)) {
                    $timestamp = strtotime($converted);
                    $val = $timestamp ? date('d-M-Y', $timestamp) : $converted;
                }
            }

            $values[] = $val;
        }

        $values[] = $uploaded_by;

        try {
            $stmt->execute($values);
            $inserted_rows++;
        } catch (Exception $e) {
            error_log("Insert failed: " . $e->getMessage());
        }
    }

    return [
        'duplicate_found' => $duplicate_found,
        'inserted_rows' => $inserted_rows,
        'skipped_samples' => $skipped_samples
    ];
}

// ---------- MAIN ---------- //

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file']['tmp_name'])) {
    try {
        $filePath = $_FILES['excel_file']['tmp_name'];
        $reader = IOFactory::createReaderForFile($filePath);
        $spreadsheet = $reader->load($filePath);
        $uploaded_by = $_SESSION['user_name'];
        
        $duplicate_messages = [];
        $inserted_total = 0;

        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            if (!$sheet) continue;

            $data = [];
            foreach ($sheet->getRowIterator() as $row) {
                $rowData = [];
                foreach ($row->getCellIterator() as $cell) {
                    $rowData[$cell->getColumn()] = $cell->getFormattedValue();
                }
                $data[] = $rowData;
            }

            $data = array_filter($data, fn($r) => !empty(array_filter($r, fn($v) => $v !== null && $v !== '')));
            if (empty($data)) continue;

            $normalized = strtolower(trim($sheetName));
            if ($normalized === 'consolidated data') {
                $res = insert_consolidated_data($pdo, $data, $uploaded_by);
                if ($res['duplicate_found']) {
                    $duplicate_messages[] = "Consolidated Data: some rows already existed before this upload and were skipped. Example(s): " . implode(' ; ', array_slice($res['skipped_samples'], 0, 5));
                }
                $inserted_total += $res['inserted_rows'];
            } elseif ($normalized === 'n&f') {
                $res = insert_n_f($pdo, $data, $uploaded_by);
                if ($res['duplicate_found']) {
                    $duplicate_messages[] = "N&F: some rows already existed before this upload and were skipped. Example(s): " . implode(' ; ', array_slice($res['skipped_samples'], 0, 5));
                }
                $inserted_total += $res['inserted_rows'];
            } elseif ($normalized === 'clra input') {
                $res = insert_clra_input($pdo, $data, $uploaded_by);
                if ($res['duplicate_found']) {
                    $duplicate_messages[] = "CLRA Input: some rows already existed before this upload and were skipped. Example(s): " . implode(' ; ', array_slice($res['skipped_samples'], 0, 5));
                }
                $inserted_total += $res['inserted_rows'];
            } else {
                error_log("Skipped unknown sheet: $sheetName");
                continue;
            }
        }

        // Log upload and set appropriate messages
        if ($inserted_total > 0) {
            $pdo->prepare("INSERT INTO upload_logs (file_name, uploaded_by, uploaded_at) VALUES (?, ?, NOW())")
                ->execute([$_FILES['excel_file']['name'], $uploaded_by]);

            if (!empty($duplicate_messages)) {
                $_SESSION['warning'] = $inserted_total . " new records inserted. Some data was skipped as it already exists.";
            } else {
                $_SESSION['success'] = "Upload complete. Total rows inserted: $inserted_total";
            }
            header("Location: temp2.php");
        } else {
            // If NO new records inserted (only duplicates), stay on upload page
            $_SESSION['warning'] = "Data already exists. No new records inserted.";
            $referer = $_SERVER['HTTP_REFERER'] ?? 'temp2.php';
            header("Location: $referer");
        }
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = "Upload failed: " . $e->getMessage();
        header("Location: temp2.php");
        exit();
    }
} else {
    $_SESSION['error'] = "No file uploaded.";
    header("Location: temp2.php");
    exit();
}