<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo "Unauthorized: Please login first.";
    exit();
}

// Database configuration
include("includes/config.php");

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Include PhpSpreadsheet
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

// Get the logged-in employee's information
$uploaded_by = $_SESSION['user_name']; // Employee name
$uploaded_by_id = $_SESSION['user_id']; // Employee ID

// Function to check if data already exists
function check_duplicate_data($pdo, $client_name, $state, $location_code, $month, $year) {
    $sql = "SELECT COUNT(*) as count FROM consolidated_data 
            WHERE client_name = ? AND state = ? AND location_code = ? AND month = ? AND year = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$client_name, $state, $location_code, $month, $year]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'] > 0;
}

// Helper functions
function clean_import_value($value) {
    $value = trim((string)$value);
    
    // Handle Nil values (preserve as 'Nil')
    if (strtolower($value) === 'nil') {
        return 'Nil';
    }
    
    // Convert '-' to empty string (which will become NULL in database)
    if ($value === '-') {
        return '-';
    }
    
    // Return empty string as-is (will become NULL)
    if ($value === '') {
        return '';
    }
    
    // Return all other values as-is
    return $value;
}

function convert_excel_date($dateValue) {
    // Handle Nil values first
    if (strtolower(trim((string)$dateValue)) === 'nil') {
        return ''; // Return empty string which will become NULL in database
    }

    if (empty($dateValue)) return '';
    
    if (is_numeric($dateValue)) {
        try {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue)
                ->format('Y-m-d');
        } catch (Exception $e) {
            return '';
        }
    }
    
    // Handle string dates
    $parsedDate = date('Y-m-d', strtotime($dateValue));
    return $parsedDate ? $parsedDate : '';
}

// Import functions for all tables
function insert_consolidated_data($pdo, $data, $uploaded_by) {
    // Remove header row if exists
    if (isset($data[1]['A']) && $data[1]['A'] === 'Month') {
        array_shift($data);
    }
    
    // Define column mapping from Excel columns (A, B, C, etc.) to database columns
    $column_mapping = [
        'A' => 'month',
        'B' => 'year', 
        'C' => 'employee_code',
        'D' => 'employee_name',
        'E' => 'father_name',
        'F' => 'date_of_birth',
        'G' => 'date_of_joining',
        'H' => 'date_of_resign',
        'I' => 'reason_for_exit',
        'J' => 'gender',
        'K' => 'designation',
        'L' => 'category',
        'M' => 'bank_name',
        'N' => 'bank_account_number',
        'O' => 'ifsc_code',
        'P' => 'uan_no',
        'Q' => 'pan_no',
        'R' => 'ip',
        'S' => 'aadhaar',
        'T' => 'identification_marks',
        'U' => 'nationality',
        'V' => 'education_level',
        'W' => 'client_name',
        'X' => 'state',
        'Y' => 'location_code',
        'Z' => 'address',
        'AA' => 'principal_employer_name',
        'AB' => 'principal_employer_address',
        'AC' => 'shift_details',
        'AD' => 'nature_of_business',
        'AE' => 'paid_days',
        'AF' => 'rate_of_wage',
        'AG' => 'basic',
        'AH' => 'da',
        'AI' => 'hra',
        'AJ' => 'special_allowance',
        'AK' => 'leave_travel_allowance',
        'AL' => 'conveyance_allowance',
        'AM' => 'over_time_wages',
        'AN' => 'nh_fh_week_off_wages',
        'AO' => 'medical_allowance',
        'AP' => 'children_education_allowance',
        'AQ' => 'incentive',
        'AR' => 'other_allowance',
        'AS' => 'night_shift_allowance',
        'AT' => 'food_allowance',
        'AU' => 'bonus',
        'AV' => 'mob_allowance',
        'AW' => 'vehicle_reimb',
        'AX' => 'earned_gross',
        'AY' => 'pf',
        'AZ' => 'vpf',
        'BA' => 'esi',
        'BB' => 'p_tax',
        'BC' => 'lwf',
        'BD' => 'tds',
        'BE' => 'advance_loan',
        'BF' => 'deduction_for_damages_loss',
        'BG' => 'fine',
        'BH' => 'medical_insurance',
        'BI' => 'other_deductions',
        'BJ' => 'total_deductions',
        'BK' => 'net_salary',
        'BL' => 'payment_date',
        'BM' => 'day_1',
        'BN' => 'day_2',
        'BO' => 'day_3',
        'BP' => 'day_4',
        'BQ' => 'day_5',
        'BR' => 'day_6',
        'BS' => 'day_7',
        'BT' => 'day_8',
        'BU' => 'day_9',
        'BV' => 'day_10',
        'BW' => 'day_11',
        'BX' => 'day_12',
        'BY' => 'day_13',
        'BZ' => 'day_14',
        'CA' => 'day_15',
        'CB' => 'day_16',
        'CC' => 'day_17',
        'CD' => 'day_18',
        'CE' => 'day_19',
        'CF' => 'day_20',
        'CG' => 'day_21',
        'CH' => 'day_22',
        'CI' => 'day_23',
        'CJ' => 'day_24',
        'CK' => 'day_25',
        'CL' => 'day_26',
        'CM' => 'day_27',
        'CN' => 'day_28',
        'CO' => 'day_29',
        'CP' => 'day_30',
        'CQ' => 'day_31',
        'CR' => 'total_present_days',
        'CS' => 'pl_opening',
        'CT' => 'pl_availed',
        'CU' => 'pl_credit',
        'CV' => 'pl_closing',
        'CW' => 'cl_opening',
        'CX' => 'cl_availed',
        'CY' => 'cl_credit',
        'CZ' => 'cl_closing',
        'DA' => 'sl_opening',
        'DB' => 'sl_availed',
        'DC' => 'sl_credit',
        'DD' => 'sl_closing',
        // OT columns
        'DE' => 'ot_hours',
        'DF' => 'fixed_ot_wages',
        'DG' => 'normal_rate_of_wages',
        'DH' => 'overtime_rate_of_wages',
        'DI' => 'overtime_earnings',
        'DJ' => 'date_on_ot_payment',
        'DK' => 'ot_day_1', 'DL' => 'ot_day_2', 'DM' => 'ot_day_3', 'DN' => 'ot_day_4', 'DO' => 'ot_day_5',
        'DP' => 'ot_day_6', 'DQ' => 'ot_day_7', 'DR' => 'ot_day_8', 'DS' => 'ot_day_9', 'DT' => 'ot_day_10',
        'DU' => 'ot_day_11', 'DV' => 'ot_day_12', 'DW' => 'ot_day_13', 'DX' => 'ot_day_14', 'DY' => 'ot_day_15',
        'DZ' => 'ot_day_16', 'EA' => 'ot_day_17', 'EB' => 'ot_day_18', 'EC' => 'ot_day_19', 'ED' => 'ot_day_20',
        'EE' => 'ot_day_21', 'EF' => 'ot_day_22', 'EG' => 'ot_day_23', 'EH' => 'ot_day_24', 'EI' => 'ot_day_25',
        'EJ' => 'ot_day_26', 'EK' => 'ot_day_27', 'EL' => 'ot_day_28', 'EM' => 'ot_day_29', 'EN' => 'ot_day_30',
        'EO' => 'ot_day_31',
        // Advance columns
        'EP' => 'advance_wage_period_and_wage_payable',
        'EQ' => 'date_and_amount_of_advance_given',
        'ER' => 'purposes_for_which_advance_made',
        'ES' => 'no_of_installments_by_which_advance_to_be_repaid',
        'ET' => 'date_and_amount_of_each_installment_repaid',
        'EU' => 'date_on_which_last_installment_was_paid',
        // Damages columns
        'EV' => 'particulars_of_damage_loss',
        'EW' => 'date_of_damage',
        'EX' => 'whether_worker_showed_cause_against_deduction',
        'EY' => 'name_of_person_in_whose_presence_employees_explanation_was_heard',
        'EZ' => 'amount_of_deduction_imposed',
        'FA' => 'no_of_installment',
        'FB' => 'first_installment_date',
        'FC' => 'last_installment_date',
        // Fines columns
        'FD' => 'act_omission_for_which_fine_is_imposed',
        'FE' => 'date_of_offences',
        'FF' => 'whether_workman_showed_cause_against_fine',
        'FG' => 'name_of_person_in_whose_presence_employees_explanation_for_fine',
        'FH' => 'wage_period_and_wages_payable',
        'FI' => 'amount_of_fine_imposed',
        'FJ' => 'date_on_which_fine_realised',
        // Maternity columns
        'FK' => 'notice_date_section6',
        'FL' => 'discharge_dismissal_date',
        'FM' => 'pregnancy_proof_date_section6',
        'FN' => 'child_birth_date',
        'FO' => 'delivery_proof_details',
        'FP' => 'illness_proof_date_section10',
        'FQ' => 'advance_maternity_benefit_details',
        'FR' => 'subsequent_maternity_benefit_details',
        'FS' => 'bonus_payment_details_section8',
        'FT' => 'leave_wages_details_section9',
        'FU' => 'leave_wages_details_section10',
        'FV' => 'nominated_person_name',
        'FW' => 'death_details',
        'FX' => 'child_survival_details',
        // Accident columns
        'FY' => 'date_of_notice',
        'FZ' => 'time_of_notice',
        'GA' => 'name_and_address_of_injured_person',
        'GB' => 'insurance_no',
        'GC' => 'shift_department_and_occupation_of_the_employee',
        'GD' => 'injury_date',
        'GE' => 'injury_time',
        'GF' => 'injury_place',
        'GG' => 'cause_of_injury',
        'GH' => 'nature_of_injury',
        'GI' => 'what_exactly_was_the_injured_person_doing',
        'GJ' => 'name_occupation_address_and_signature_of_notice_givers',
        'GK' => 'signature_and_designation_of_the_person_who_makes_the_entry',
        'GL' => 'name_address_and_occupation_of_two_witnesses'
    ];
    
    // Get all database column names in order
    $db_columns = array_values($column_mapping);
    $db_columns[] = 'uploaded_by'; // Add uploaded_by at the end
    
    // Create placeholders for SQL
    $placeholders = implode(',', array_fill(0, count($db_columns), '?'));
    
    // Prepare SQL statement
    $sql = "INSERT INTO consolidated_data (" . implode(',', $db_columns) . ") VALUES ($placeholders)";
    $stmt = $pdo->prepare($sql);
    
    $successCount = 0;
    $errorCount = 0;
    $duplicateCount = 0;
    
    foreach ($data as $rowIndex => $row) {
        $row = array_map('clean_import_value', $row);
        
        // Skip empty rows
        if (count(array_filter($row, function($v) { return $v !== ''; })) === 0) {
            continue;
        }

        // Check for duplicate data before insertion
        $client_name = $row['W'] ?? null;
        $state = $row['X'] ?? null;
        $location_code = $row['Y'] ?? null;
        $month = $row['A'] ?? null;
        $year = $row['B'] ?? null;
        
        if ($client_name && $state && $location_code && $month && $year) {
            if (check_duplicate_data($pdo, $client_name, $state, $location_code, $month, $year)) {
                $duplicateCount++;
                continue; // Skip this row
            }
        }

        $values = [];
        
        // Process each column according to mapping
        foreach ($column_mapping as $excel_col => $db_col) {
            $value = $row[$excel_col] ?? null;
            
            // Handle date fields
            $date_fields = ['date_of_birth', 'date_of_joining', 'date_of_resign', 'payment_date', 
                           'date_on_ot_payment', 'date_of_damage', 'first_installment_date', 
                           'last_installment_date', 'date_of_offences', 'date_on_which_fine_realised',
                           'notice_date_section6', 'discharge_dismissal_date', 'pregnancy_proof_date_section6',
                           'child_birth_date', 'illness_proof_date_section10', 'date_of_notice', 'injury_date'];
            
            if (in_array($db_col, $date_fields) && is_numeric($value)) {
                $value = Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            
            $values[] = $value;
        }
        
        // Add uploaded_by as the last value
        $values[] = $uploaded_by;
        
        try {
            $stmt->execute($values);
            $successCount++;
        } catch (PDOException $e) {
            $errorCount++;
            error_log("Error inserting CONSOLIDATED_DATA record: " . $e->getMessage());
            // Continue with next record even if one fails
            continue;
        }
    }
    
    error_log("Inserted $successCount records into consolidated_data with $errorCount errors and $duplicateCount duplicates skipped");
    return ['duplicate_found' => ($duplicateCount > 0), 'inserted_rows' => $successCount, 'duplicate_count' => $duplicateCount];
}

function insert_n_f($pdo, $data, $uploaded_by) {
    // Define column mapping for NFH
    $column_mapping = [
        'A' => 'client_name',
        'B' => 'state', 
        'C' => 'location_code',
        'D' => 'address',
        'E' => 'principal_employer_name',
        'F' => 'principal_employer_address',
        'G' => 'month',
        'H' => 'year',
        'I' => 'holiday_date',
        'J' => 'description',
        'K' => 'leave_type',
        'L' => 'days'
    ];
    
    // Get all database column names in order
    $db_columns = array_values($column_mapping);
    $db_columns[] = 'uploaded_by';
    
    // Create placeholders for SQL
    $placeholders = implode(',', array_fill(0, count($db_columns), '?'));
    
    // Prepare SQL statement
    $sql = "INSERT INTO n_f (" . implode(',', $db_columns) . ") VALUES ($placeholders)";
    $stmt = $pdo->prepare($sql);

    foreach ($data as $row) {
        $row = array_map('clean_import_value', $row);
        
        $values = [];
        
        // Process each column according to mapping
        foreach ($column_mapping as $excel_col => $db_col) {
            $value = $row[$excel_col] ?? null;
            
            // Handle date field
            if ($db_col === 'holiday_date' && is_numeric($value)) {
                $value = Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            
            $values[] = $value;
        }
        
        // Add uploaded_by as the last value
        $values[] = $uploaded_by;
        
        try {
            $stmt->execute($values);
        } catch (PDOException $e) {
            error_log("Error inserting N_F record: " . $e->getMessage());
            continue;
        }
    }
}

function insert_clra_input($pdo, $data, $uploaded_by) {
    // Define column mapping for NFH
    $column_mapping = [
        'A' => 'client_name',
        'B' => 'state', 
        'C' => 'location_code',
        'D' => 'address',
        'E' => 'principal_employer_name',
        'F' => 'principal_employer_address',
        'G' => 'month',
        'H' => 'year',
        'I' => 'holiday_date',
        'J' => 'description',
        'K' => 'leave_type',
        'L' => 'days'
    ];
    
    // Get all database column names in order
    $db_columns = array_values($column_mapping);
    $db_columns[] = 'uploaded_by';
    
    // Create placeholders for SQL
    $placeholders = implode(',', array_fill(0, count($db_columns), '?'));
    
    // Prepare SQL statement
    $sql = "INSERT INTO clra_input (" . implode(',', $db_columns) . ") VALUES ($placeholders)";
    $stmt = $pdo->prepare($sql);

    foreach ($data as $row) {
        $row = array_map('clean_import_value', $row);
        
        $values = [];
        
        // Process each column according to mapping
        foreach ($column_mapping as $excel_col => $db_col) {
            $value = $row[$excel_col] ?? null;
            
            // Handle date field
            if ($db_col === 'holiday_date' && is_numeric($value)) {
                $value = Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            
            $values[] = $value;
        }
        
        // Add uploaded_by as the last value
        $values[] = $uploaded_by;
        
        try {
            $stmt->execute($values);
        } catch (PDOException $e) {
            error_log("Error inserting clra_input record: " . $e->getMessage());
            continue;
        }
    }
}

// Main Upload Handling
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excel_file']['tmp_name'])) {
    try {
        $filePath = $_FILES['excel_file']['tmp_name'];
        $spreadsheet = IOFactory::load($filePath);

        $duplicate_message = '';
        $inserted_total = 0;
        $duplicate_total = 0;

        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            if (!$sheet) continue;

            $data = $sheet->toArray(null, true, true, true);
            
            // Remove header row if detected
            $firstCell = $data[1]['A'] ?? '';
            if (is_string($firstCell) && preg_match('/month|client name|s_no/i', $firstCell)) {
                array_shift($data);
            }

            // Filter out empty rows
            $data = array_filter($data, function($row) {
                return !empty(array_filter($row, function($value) {
                    return $value !== null && $value !== '';
                }));
            });

            if (empty($data)) {
                continue; // Skip empty sheets
            }

            $normalizedSheetName = strtolower(str_replace([' ', '&'], ['_', '_'], trim($sheetName)));

            switch ($normalizedSheetName) {
                case 'consolidated_data':
                    $result = insert_consolidated_data($pdo, $data, $uploaded_by);
                    if ($result['duplicate_found']) {
                        $duplicate_message = "Some data already exists and was skipped.";
                        $duplicate_total += $result['duplicate_count'];
                    }
                    $inserted_total += $result['inserted_rows'];
                    break;
                case 'n_f':
                    insert_n_f($pdo, $data, $uploaded_by);
                    break;
                case 'clra_input':
                    insert_n_f($pdo, $data, $uploaded_by);
                    break;
                default:
                    // Skip unknown sheets
                    error_log("Unknown sheet name: $sheetName");
                    break;
            }
        }

        // Log the file upload activity
        $log_sql = "INSERT INTO upload_logs (file_name, uploaded_by, uploaded_at) VALUES (?, ?, NOW())";
        $log_stmt = $pdo->prepare($log_sql);
        $log_stmt->execute([$_FILES['excel_file']['name'], $uploaded_by]);

        // Set appropriate session message
        if (!empty($duplicate_message)) {
            $_SESSION['warning'] = $duplicate_message . " " . $inserted_total . " new records inserted successfully. " . $duplicate_total . " duplicate records skipped.";
        } else {
            $_SESSION['success'] = "File uploaded successfully by " . $uploaded_by . ". " . $inserted_total . " records inserted.";
        }
        
        header("Location: temp2.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = "Error processing file: " . $e->getMessage();
        header("Location: temp2.php");
        exit();
    }
} else {
    $_SESSION['error'] = "File not uploaded properly.";
    header("Location: temp2.php");
    exit();
}
?>