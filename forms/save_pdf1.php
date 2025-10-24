<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

// ----------------- PARSE INPUT -----------------
$input = json_decode(file_get_contents('php://input'), true);
if ($input === null) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

// Required fields
$required = ['formId', 'state', 'clientName', 'principalEmployer', 'locationCode', 'monthYear', 'htmlContent'];
foreach ($required as $field) {
    if (!isset($input[$field]) || $input[$field] === '') {
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

$formId = $input['formId'] ?? 'unknown_form';
$state = $input['state'] ?? '';
$clientName = $input['clientName'] ?? '';
$principalEmployer = $input['principalEmployer'] ?? '';
$locationCode = $input['locationCode'] ?? '';
$htmlContent = $input['htmlContent'] ?? '';
$monthYear = $input['monthYear'] ?? '';

if (!$htmlContent || !$state || !$clientName || !$principalEmployer || !$locationCode || !$monthYear) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit;
}

// ---- helper to count max table columns (handles colspan) ----
function get_max_table_columns(string $html): int
{
    $max = 0;
    $html = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . $html;

    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);
    foreach ($xpath->query('//table') as $table) {
        foreach ($xpath->query('.//tr', $table) as $tr) {
            $count = 0;
            foreach ($xpath->query('.//th|.//td', $tr) as $cell) {
                /** @var DOMElement $cell */
                $colspan = (int) $cell->getAttribute('colspan');
                $count += $colspan > 0 ? $colspan : 1;
            }

            if ($count > $max) $max = $count;
        }
    }
    return $max;
}

// ---- decide page format based on detected columns ----
$maxCols = get_max_table_columns($htmlContent);

// Force A3 for specific known-wide forms
$forceA3 = ['form-musterroll', 'form-attendance-register'];
if (in_array($formId, $forceA3, true)) {
    $maxCols = max($maxCols, 99);
}

$paperFormat = 'A4';
$paperOrientation = 'P';
$baseFontSize = 10;

// If too many columns, use A3 Landscape
if ($maxCols >= 10) {
    $paperFormat = 'A3';
    $paperOrientation = 'L';
    $baseFontSize = 9;
}

// Sanitize inputs
function sanitize_for_path($str) {
    $str = trim($str);
    $str = str_replace(['\\', '/', ':', '*', '?', '"', '<', '>', '|'], '', $str);
    $str = preg_replace('/\s+/', ' ', $str);
    return $str;
}

$state = sanitize_for_path($state);
$clientName = sanitize_for_path($clientName);
$principalEmployer = sanitize_for_path($principalEmployer);
$locationCode = sanitize_for_path($locationCode);

$folderMonthYear = '';
$filenameMonthYear = '';

if ($monthYear) {
    $dt = DateTime::createFromFormat('Y-m-d', $monthYear . '-01');
    if ($dt) {
        $dt->setTime(0, 0, 0);
        $folderMonthYear = $dt->format('F Y');
        $filenameMonthYear = $dt->format('F_Y');
    }
}

// ----------------- FILE / PATH GENERATION (unchanged) -----------------
$formNames = [
    'Delhi' => [
        'form-esi-form-11' => 'Form_ESI_Form_11',
        'form-xiii'        => 'Form_XIII',
        'form-xiv'         => 'Form_XIV',
        'form-xix'         => 'Form_XIX',
        'form-xv'          => 'Form_XV',
        'form-xvi'         => 'Form_XVI',
        'form-xx'          => 'Form_XX',
        'form-xxi'         => 'Form_XXI',
        'form-xxii'        => 'Form_XXII',
        'form-xxiii'       => 'Form_XXIII',
        'form-a'           => 'Form_A',
        'form-b'           => 'Form_B',
        'form-c'           => 'Form_C',
        'form-d'           => 'Form_D',
        'form-mba-form-a'  => 'Form_MBA_Form_A',
        'form-sea-form-g'  => 'Form_SEA_Form_G'
    ],
    'Haryana' => [
        'form-esi-form-11' => 'Form_ESI_Form_11',
        'form-9'           => 'Form_9',
        'form-xiii'        => 'Form_XIII_Register_of_Wages',
        'form-xiv'         => 'Form_XIV',
        'form-xix-overtime' => 'Form_XIX_Overtime',
        'form-xix'         => 'Form_XIX',
        'form-xv'          => 'Form_XV',
        'form-a'           => 'Form_A_Part_A',
        'form-b'           => 'Form_B',
        'form-c'           => 'Form_C',
        'form-d'           => 'Form_D',
        'form-mba-form-a'  => 'Form_MBA_Form_A'
    ],

    'Karnataka' => [
        'form-esi'                         => 'ESI_Form_11',
        'form-xiii'                        => 'Form XIII',
        'form-xiv'                         => 'Form XIV',
        'form-xix'                         => 'Form XIX',
        'form-xv'                          => 'Form XV',
        'form-xvi'                         => 'Form XVI',
        'form-a'                           => 'Form_A',
        'form-b'                           => 'Form_B',
        'form-c'                           => 'Form_C',
        'form-d'                           => 'Form_D',
        'form-mba-form-a'                  => 'MBA Form_A',
        'form-xiv-wage-cum-musterroll'     => 'Form XIV Wage Cum Musterroll',
        'form-xvii-wage-register'          => 'Form XVII Wage Register',
        'form-xii-register-of-contractors' => 'Form XII_Register of Contractors',
    ],

    'Rajasthan' => [
        'form-x-employment-card' => 'Form X_Employment Card',
        'form-xii-register-of-contractor' => 'Form XII_Register Of Contractors',
        'form-xiv-employement-card' => 'Form XIV_Employment Card',
        'form-xix-wage-slip' => 'Form XIX_Wage Slip',
        'form-xv-service-certificate' => 'Form XV_Service Certificate',
        'form-a-employee-register' => 'Form A_Employee Register',
        'form-b-wage-register' => 'Form B_Wage Register',
        'form-c-loan-recoveries' => 'Form C_Loan Recoveries',
        'form-d-attendance-register' => 'Form D_Attendance Register',
        'form-esi' => 'ESI Form 11_AccidentBook',
        'form-mba-maternity' => 'MBA Form A_Maternity',
    ],

    'Tamilnadu' => [
        'form-xii-register-of-contractor' => 'Form XII_Register Of Contractors',
        'form-xiv-employement-card' => 'Form XIV_Employment Card',
        'form-xix-wage-slip' => 'Form XIX_Wage Slip',
        'form-xv-service-certificate' => 'Form XV_Service Certificate',
        'form-a-employee-register' => 'Form A_Employee Register',
        'form-b-wage-register' => 'Form B_Wage Register',
        'form-c-loan-recoveries' => 'Form C_Loan Recoveries',
        'form-d-attendance-register' => 'Form D_Attendance Register',
        'form-esi' => 'ESI Form 11_AccidentBook',
        'form-mba-maternity' => 'MBA Form A_Maternity',
        'form-t-wage-slip' => 'Form T Wage Slip',
        'form-xxix-damage-loss' => 'Form XXIX Damage or Loss',
        'form-xxvi-register-of-employees' => 'Form XXVI Register of Employees',
        'form-xxviii-wage-slip' => 'Form XXVIII Wage Slip',
        'form-xxvii-wage-register' => 'Form XXVII Wage Register',
    ],

    'Gujarat' => [
        'form-xii-register-of-contractor' => 'Form XII_Register Of Contractors',
        'form-xiv-employement-card' => 'Form XIV_Employment Card',
        'form-xix-wage-slip' => 'Form XIX_Wage Slip',
        'form-xv-service-certificate' => 'Form XV_Service Certificate',
        'form-a-employee-register' => 'Form A_Employee Register',
        'form-b-wage-register' => 'Form B_Wage Register',
        'form-c-loan-recoveries' => 'Form C_Loan Recoveries',
        'form-d-attendance-register' => 'Form D_Attendance Register',
        'form-esi' => 'ESI Form 11_AccidentBook',
        'form-mba-maternity' => 'MBA Form A_Maternity',
    ],

    'Assam' => [
        'form-xii-register-of-contractor' => 'Form XII_Register Of Contractors',
        'form-xiv-employement-card' => 'Form XIV_Employment Card',
        'form-xix-wage-slip' => 'Form XIX_Wage Slip',
        'form-xv-service-certificate' => 'Form XV_Service Certificate',
        'form-a-employee-register' => 'Form A_Employee Register',
        'form-b-wage-register' => 'Form B_Wage Register',
        'form-c-loan-recoveries' => 'Form C_Loan Recoveries',
        'form-d-attendance-register' => 'Form D_Attendance Register',
        'form-esi' => 'ESI Form 11_AccidentBook',
        'form-mba-maternity' => 'MBA Form A_Maternity',
        'form-xxvii-wage-register' => 'Form XXVII Wage Register',
        'form-xiii' => 'Form XIII',
        'form-xvi' => 'Form XVI',
        'form-xx' => 'Form XX',
        'form-xxi' => 'Form XXI',
        'form-xxii' => 'Form XXII',
        'form-xxiii' => 'Form XXIII',
    ],

    'Chattisgarh' => [
        'form-xii-register-of-contractor' => 'Form XII_Register Of Contractors',
        'form-xiv-employement-card' => 'Form XIV_Employment Card',
        'form-xix-wage-slip' => 'Form XIX_Wage Slip',
        'form-xv-service-certificate' => 'Form XV_Service Certificate',
        'form-a-employee-register' => 'Form A_Employee Register',
        'form-b-wage-register' => 'Form B_Wage Register',
        'form-c-loan-recoveries' => 'Form C_Loan Recoveries',
        'form-d-attendance-register' => 'Form D_Attendance Register',
        'form-esi' => 'ESI Form 11_AccidentBook',
        'form-mba-maternity' => 'MBA Form A_Maternity',
        'form-xxvii-wage-register' => 'Form XXVII Wage Register',
        'form-xiii' => 'Form XIII',
        'form-xvi' => 'Form XVI',
        'form-xx' => 'Form XX',
        'form-xxi' => 'Form XXI',
        'form-xxii' => 'Form XXII',
        'form-xxiii' => 'Form XXIII',
    ],

    'Goa' => [
        'form-ix-register-of-workman' => 'Form IX Register of Workman',
        'form-x-employee-card' => 'Form X Employee Card',
        'form-xii-musterroll' => 'Form XII Musterroll',
        'form-xiii-register-of-wages' => 'Form XIII Register of Wages',
        'form-xiv-employement-card' => 'Form XIV_Employment Card',
        'form-xiv-wages-cum-musterroll' => 'Form XIV Wages Cum Musterroll',
        'form-xv-service-certificate' => 'Form XV_Service Certificate',
        'form-xix-wage-slip' => 'Form XIX_Wage Slip',
        'form-xvii-register-of-fines' => 'Form XVII Register of Fines',
        'form-x-damages' => 'Form X Damages',
        'form-xviii-register-of-advance' => 'Form XVIII Register of Advance',
        'form-xvii-wage-register' => 'Form XVII Wage Register',
        'form-a-employee-register' => 'Form A_Employee Register',
        'form-b-wage-register' => 'Form B_Wage Register',
        'form-c-loan-recoveries' => 'Form C_Loan Recoveries',
        'form-d-attendance-register' => 'Form D_Attendance Register',
        'form-esi' => 'ESI Form 11_AccidentBook',
        'form-mba-maternity' => 'MBA Form A_Maternity',
    ],

    'Himachal Pradesh' => [
        'form-xiv-employement-card' => 'Form XIV_Employment Card',
        'form-xix-wage-slip' => 'Form XIX_Wage Slip',
        'form-xv-service-certificate' => 'Form XV_Service Certificate',
        'form-a-employee-register' => 'Form A_Employee Register',
        'form-b-wage-register' => 'Form B_Wage Register',
        'form-c-loan-recoveries' => 'Form C_Loan Recoveries',
        'form-d-attendance-register' => 'Form D_Attendance Register',
        'form-esi' => 'ESI Form 11_AccidentBook',
        'form-mba-maternity' => 'MBA Form A_Maternity',
        'form-xxvii-wage-register' => 'Form XVII Wage Register',
        'form-xiii' => 'Form XIII',
        'form-xvi' => 'Form XVI',
        'form-xx' => 'Form XX',
        'form-xxi' => 'Form XXI',
        'form-xxii' => 'Form XXII',
    ],

    'Kerala' => [
        'form-xii-register-of-contractor' => 'Form XII_Register Of Contractors',
        'form-xiv-employement-card' => 'Form XIV_Employment Card',
        'form-xix-wage-slip' => 'Form XIX_Wage Slip',
        'form-xv-service-certificate' => 'Form XV_Service Certificate',
        'form-a-employee-register' => 'Form A_Employee Register',
        'form-b-wage-register' => 'Form B_Wage Register',
        'form-c-loan-recoveries' => 'Form C_Loan Recoveries',
        'form-d-attendance-register' => 'Form D_Attendance Register',
        'form-esi' => 'ESI Form 11_AccidentBook',
        'form-mba-maternity' => 'MBA Form A_Maternity',
        'form-xxvii-wage-register' => 'Form XXVII Wage Register',
        'form-xiii' => 'Form XIII',
        'form-xvi' => 'Form XVI',
        'form-xx' => 'Form XX',
        'form-xxi' => 'Form XXI',
        'form-xxii' => 'Form XXII',
        'form-xxiii' => 'Form XXIII',
    ],

    'Madhya Pradesh' => [
        'form-xii-register-of-contractor' => 'Form XII_Register Of Contractors',
        'form-xiv-employement-card' => 'Form XIV_Employment Card',
        'form-xix-wage-slip' => 'Form XIX_Wage Slip',
        'form-xv-service-certificate' => 'Form XV_Service Certificate',
        'form-a-employee-register' => 'Form A_Employee Register',
        'form-b-wage-register' => 'Form B_Wage Register',
        'form-c-loan-recoveries' => 'Form C_Loan Recoveries',
        'form-d-attendance-register' => 'Form D_Attendance Register',
        'form-esi' => 'ESI Form 11_AccidentBook',
        'form-mba-maternity' => 'MBA Form A_Maternity',
        'form-xxvii-wage-register' => 'Form XXVII Wage Register',
        'form-xiii' => 'Form XIII',
        'form-xvi' => 'Form XVI',
        'form-xx' => 'Form XX',
        'form-xxi' => 'Form XXI',
        'form-xxii' => 'Form XXII',
        'form-xxiii' => 'Form XXIII',
    ],

    'Punjab' => [
        'form-xii-register-of-contractor' => 'Form XII_Register Of Contractors',
        'form-xiv-employement-card' => 'Form XIV_Employment Card',
        'form-xix-wage-slip' => 'Form XIX_Wage Slip',
        'form-xv-service-certificate' => 'Form XV_Service Certificate',
        'form-a-employee-register' => 'Form A_Employee Register',
        'form-b-wage-register' => 'Form B_Wage Register',
        'form-c-loan-recoveries' => 'Form C_Loan Recoveries',
        'form-d-attendance-register' => 'Form D_Attendance Register',
        'form-esi' => 'ESI Form 11_AccidentBook',
        'form-mba-maternity' => 'MBA Form A_Maternity',
        'form-xxvii-wage-register' => 'Form XXVII Wage Register',
        'form-xiii' => 'Form XIII',
        'form-xvi' => 'Form XVI',
        'form-xx' => 'Form XX',
        'form-xxi' => 'Form XXI',
        'form-xxii' => 'Form XXII',
        'form-xxiii' => 'Form XXIII',
    ],

    'Sikkim' => [
        'form-xii-register-of-contractor' => 'Form XII_Register Of Contractors',
        'form-xiv-employement-card' => 'Form XIV_Employment Card',
        'form-xix-wage-slip' => 'Form XIX_Wage Slip',
        'form-xv-service-certificate' => 'Form XV_Service Certificate',
        'form-a-employee-register' => 'Form A_Employee Register',
        'form-b-wage-register' => 'Form B_Wage Register',
        'form-c-loan-recoveries' => 'Form C_Loan Recoveries',
        'form-d-attendance-register' => 'Form D_Attendance Register',
        'form-esi' => 'ESI Form 11_AccidentBook',
        'form-xvii-wage-register' => 'Form XVII Wage Register',
        'form-xiii' => 'Form XIII',
        'form-xvi' => 'Form XVI',
        'form-xxii' => 'Form XXII',
        'form-xxiii' => 'Form XXIII',
        'form-xiv-wage-cum-musterroll' => 'Form XIV Wage Cum Musterroll',
        'form-xiii-wage-register' => 'Form XIII Wage Register',
    ],

    'Uttar Pradesh' => [
        'form-xii-register-of-contractor' => 'Form XII_Register Of Contractors',
        'form-xiv-employement-card' => 'Form XIV_Employment Card',
        'form-xix-wage-slip' => 'Form XIX_Wage Slip',
        'form-xv-service-certificate' => 'Form XV_Service Certificate',
        'form-a-employee-register' => 'Form A_Employee Register',
        'form-b-wage-register' => 'Form B_Wage Register',
        'form-c-loan-recoveries' => 'Form C_Loan Recoveries',
        'form-d-attendance-register' => 'Form D_Attendance Register',
        'form-esi' => 'ESI Form 11_AccidentBook',
        'form-mba-maternity' => 'MBA Form A_Maternity',
        'form-xxvii-wage-register' => 'Form XXVII Wage Register',
        'form-xiii' => 'Form XIII',
        'form-xvi' => 'Form XVI',
        'form-xx' => 'Form XX',
        'form-xxi' => 'Form XXI',
        'form-xxii' => 'Form XXII',
        'form-xxiii' => 'Form XXIII',
    ],

    'West Bengal' => [
        'form-xii-register-of-contractor' => 'Form XII_Register Of Contractors',
        'form-xiv-employement-card' => 'Form XIV_Employment Card',
        'form-xix-wage-slip' => 'Form XIX_Wage Slip',
        'form-xv-service-certificate' => 'Form XV_Service Certificate',
        'form-a-employee-register' => 'Form A_Employee Register',
        'form-b-wage-register' => 'Form B_Wage Register',
        'form-c-loan-recoveries' => 'Form C_Loan Recoveries',
        'form-d-attendance-register' => 'Form D_Attendance Register',
        'form-esi' => 'ESI Form 11_AccidentBook',
        'form-mba-maternity' => 'MBA Form A_Maternity',
        'form-xxvii-wage-register' => 'Form XXVII Wage Register',
        'form-xiii' => 'Form XIII',
        'form-xvi' => 'Form XVI',
        'form-xx' => 'Form XX',
        'form-xxi' => 'Form XXI',
        'form-xxii' => 'Form XXII',
        'form-xxiii' => 'Form XXIII',
    ],

    'Telangana' => [
        'form-xii-register-of-contractor' => 'Form XII_Register Of Contractors',
        'form-xiii' => 'Form XIII',
        'form-xiv-employement-card' => 'Form XIV_Employment Card',
        'form-xv-service-certificate' => 'Form XV_Service Certificate',
        'form-xix-wage-slip' => 'Form XIX_Wage Slip',
        'form-xvi' => 'Form XVI',
        'form-xxii' => 'Form XXII',
        'form-xxiii' => 'Form XXIII',
        'form-a-employee-register' => 'Form A_Employee Register',
        'form-b-wage-register' => 'Form B_Wage Register',
        'form-c-loan-recoveries' => 'Form C_Loan Recoveries',
        'form-d-attendance-register' => 'Form D_Attendance Register',
        'form-esi' => 'ESI Form 11_AccidentBook',
        'form-xx' => 'Form XX',
        'form-xxi' => 'Form XXI',
        'form-xvii-register-of-fines' => 'Form XVII Register of Fine',
        'form-xii-integrated-register' => 'Form II Integrated Register',
        'form-xiii-integrated-register' => 'Form III Integrated Register',
    ],

    'Maharashtra' => [
        'form-xiv-employement-card' => 'Form XIV_Employment Card',
        'form-xix-wage-slip' => 'Form XIX_Wage Slip',
        'form-xv-service-certificate' => 'Form XV_Service Certificate',
        'form-a-employee-register' => 'Form A_Employee Register',
        'form-b-wage-register' => 'Form B_Wage Register',
        'form-c-loan-recoveries' => 'Form C_Loan Recoveries',
        'form-d-attendance-register' => 'Form D_Attendance Register',
        'form-esi' => 'ESI Form 11_AccidentBook',
        'form-mba-maternity' => 'MBA Form A_Maternity',
        'form-xiii' => 'Form XIII',
        'form-ii' => 'Form II_Musterroll Cum Wages',
        'form-xii-register-of-contractor' => 'Form XII_Register Of Contractors',
        'form-ix' => 'Form IX_Register Of Workmen',
        'form-10-overtime' => 'Form 10_Overtime Register',
        'form-attendance-card' => 'Form MWA_Attendance Card',
    ],

    'Odisha' => [
        'form-xvi' => 'Form XVI',
        'form-xxiii' => 'Form XXIII',
        'form-x-employment-card' => 'Form X_Employment Card',
        'form-xiv-employement-card' => 'Form XIV_Employment Card',
        'form-xix-wage-slip' => 'Form XIX_Wage Slip',
        'form-xv-service-certificate' => 'Form XV_Service Certificate',
        'form-a-employee-register' => 'Form A_Employee Register',
        'form-b-wage-register' => 'Form B_Wage Register',
        'form-c-loan-recoveries' => 'Form C_Loan Recoveries',
        'form-d-attendance-register' => 'Form D_Attendance Register',
        'form-esi' => 'ESI Form 11_AccidentBook',
        'form-xii-register-of-contractor' => 'Form XII_Register Of Contractors',
        'form-ix' => 'Form IX_Register Of Workmen',
        'form-8-service' => 'Form 8_Service And Leave Account',
        'form-12-register' => 'Form 12_Register Of Workers',
        'form-10-combined-musterroll' => 'Form 10_Combined Musterroll',
        'form-xiii-register-of-wage' => 'Form XIII_Register Of Wages',
    ],

    'Andaman and Nicobar' => [
        'form-a-employee-register' => 'Form A_Employee Register',
        'form-b-wage-register' => 'Form B_Wage Register',
        'form-c-loan-recoveries' => 'Form C_Loan Recoveries',
        'form-d-attendance-register' => 'Form D_Attendance Register',
        'form-esi' => 'ESI Form 11_AccidentBook',
        'form-mba-maternity' => 'MBA Form A_Maternity',
    ],

    'Andhra Pradesh' => [
        'form-a-employee-register' => 'Form A_Employee Register',
        'form-b-wage-register' => 'Form B_Wage Register',
        'form-c-loan-recoveries' => 'Form C_Loan Recoveries',
        'form-d-attendance-register' => 'Form D_Attendance Register',
        'form-esi' => 'ESI Form 11_AccidentBook',
        'form-mba-maternity' => 'MBA Form A_Maternity',
    ],

    'Arunachal Pradesh' => [
        'form-a-employee-register' => 'Form A_Employee Register',
        'form-b-wage-register' => 'Form B_Wage Register',
        'form-c-loan-recoveries' => 'Form C_Loan Recoveries',
        'form-d-attendance-register' => 'Form D_Attendance Register',
        'form-esi' => 'ESI Form 11_AccidentBook',
        'form-mba-maternity' => 'MBA Form A_Maternity',
    ],

    'Bihar' => [
        'form-a-employee-register' => 'Form A_Employee Register',
        'form-b-wage-register' => 'Form B_Wage Register',
        'form-c-loan-recoveries' => 'Form C_Loan Recoveries',
        'form-d-attendance-register' => 'Form D_Attendance Register',
        'form-esi' => 'ESI Form 11_AccidentBook',
        'form-mba-maternity' => 'MBA Form A_Maternity',
    ],

    'Chandigarh' => [
        'form-a-employee-register' => 'Form A_Employee Register',
        'form-b-wage-register' => 'Form B_Wage Register',
        'form-c-loan-recoveries' => 'Form C_Loan Recoveries',
        'form-d-attendance-register' => 'Form D_Attendance Register',
        'form-esi' => 'ESI Form 11_AccidentBook',
        'form-mba-maternity' => 'MBA Form A_Maternity',
    ],
];

$readableFormName = $formNames[$state][$formId] ?? $formId;

// Define paths - Save to project downloads folder
$basePath = __DIR__ . '/../downloads/';
$targetPath = $basePath . "{$clientName}/{$principalEmployer}/{$state}/{$locationCode}/{$folderMonthYear}/";

// Ensure the directory exists
if (!is_dir($targetPath)) {
    if (!mkdir($targetPath, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Failed to create target directory: ' . $targetPath]);
        exit;
    }
}

$filename = "{$state}_{$readableFormName}_{$filenameMonthYear}.pdf";
$filepath = $targetPath . $filename;

// Server relative path for ZIP creation (relative to downloads folder)
$server_relative_path = "{$clientName}/{$principalEmployer}/{$state}/{$locationCode}/{$folderMonthYear}/{$filename}";

try {
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => $paperFormat,
        'orientation' => $paperOrientation,
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 10,
        'margin_bottom' => 10,
        'default_font_size' => $baseFontSize,
        'default_font' => 'timesnewroman',
        'autoScriptToLang' => true,
        'autoLangToFont' => true,
        'tempDir' => __DIR__ . '/tmp',
        'useSubstitutions' => false,
        'simpleTables' => false,
        'packTableData' => true,
        'ignore_table_percents' => false,
        'ignore_table_widths' => false,
    ]);

    // metadata
    $mpdf->SetTitle("{$state} - {$readableFormName}");
    $mpdf->SetAuthor($clientName);
    $mpdf->SetCreator('Compliance Forms Generator');

    // base styles
    $baseCss = '
        .left-align { text-align: left !important; }
        .info-cell { text-align: left !important; padding-left: 5px !important; }
        th, td { text-align: left !important; }
        table { border-collapse: collapse; }
    ';
    $mpdf->WriteHTML($baseCss, \Mpdf\HTMLParserMode::HEADER_CSS);

    // tighter spacing when A3-L to fit more columns nicely
    if ($paperFormat === 'A3' && $paperOrientation === 'L') {
        $a3Css = '
            th, td { padding: 3px 4px !important; }
            body { font-size: 9px !important; }
        ';
        $mpdf->WriteHTML($a3Css, \Mpdf\HTMLParserMode::HEADER_CSS);
    }

    // content
    $mpdf->WriteHTML($htmlContent);

    // save
    $mpdf->Output($filepath, \Mpdf\Output\Destination::FILE);

    // Debug logging
    if (file_exists($filepath)) {
        error_log("✓ CLRA File successfully saved at: " . $filepath);
        error_log("✓ CLRA File size: " . filesize($filepath) . " bytes");
    } else {
        error_log("✗ CLRA File NOT saved at: " . $filepath);
    }

    echo json_encode([
        'success' => true,
        'path' => $filepath,
        'server_relative_path' => $server_relative_path,
        'filename' => $filename,
        'detected_columns' => $maxCols,
        'paper' => $paperFormat . '-' . $paperOrientation,
        'folder_month' => $folderMonthYear,
        'file_exists' => file_exists($filepath),
        'actual_path' => $filepath
    ]);
    exit;
} catch (\Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'PDF generation failed: ' . $e->getMessage()
    ]);
    exit;
}
?>
