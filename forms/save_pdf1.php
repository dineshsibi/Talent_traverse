<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json');
error_reporting(E_ALL);

// ----------------- PARSE INPUT -----------------
$input = json_decode(file_get_contents('php://input'), true);
if ($input === null) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

// Required fields
$required = ['formId', 'state', 'clientName', 'principalEmployer', 'locationCode', 'monthYear'];
foreach ($required as $field) {
    if (!isset($input[$field]) || $input[$field] === '') {
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

// Optional: htmlContent (pre-rendered HTML from the page)
$postedHtml = isset($input['htmlContent']) && is_string($input['htmlContent'])
    ? trim($input['htmlContent'])
    : '';

// ----------------- HELPERS -----------------
function sanitize_for_path($str)
{
    $str = trim($str);
    $str = str_replace(['\\', '/', ':', '*', '?', '"', '<', '>', '|'], '', $str);
    $str = preg_replace('/\s+/', ' ', $str);
    return $str;
}
function sanitize_for_filename($str)
{
    return str_replace(' ', '_', sanitize_for_path($str));
}

// ----------------- VARIABLES -----------------
$clientName        = sanitize_for_path($input['clientName']);
$principalEmployer = sanitize_for_path($input['principalEmployer']);
$state             = ucfirst(strtolower(sanitize_for_path($input['state'])));
$locationCode      = sanitize_for_path($input['locationCode']);
$formId            = $input['formId'];
$monthYear         = $input['monthYear'];

// ----------------- FORM TEMPLATE MAP -----------------
$formMappingFile = __DIR__ . '/config/form_mapping1.php';
if (!file_exists($formMappingFile)) {
    echo json_encode(['success' => false, 'message' => 'Mapping file not found']);
    exit;
}
$stateFormMapping = include($formMappingFile);

// ----------------- BUILD HTML -----------------
$globalCSS = "
    <style>
        * { font-family: 'Times New Roman', Times, serif !important; }
        body { font-size: 12px; margin: 15px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 15px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; vertical-align: top; }
        th { background-color: #f5f5f5; }
        h1, h2, h3, h4, h5 { font-family: 'Times New Roman', Times, serif !important; margin: 6px 0; }
        .form-container { width: 100%; }
    </style>
";

// Prefer the exact HTML sent by JS (already includes its own <html> + <body> + styles)
if ($postedHtml !== '') {
    // If user-sent HTML is just inner content (no <html>), wrap it
    if (stripos($postedHtml, '<html') === false) {
        $postedHtml = "<!doctype html><html><head><meta charset='UTF-8'>{$globalCSS}</head><body>{$postedHtml}</body></html>";
    }
    $html = $postedHtml;
} else {
    // Fallback: render from template (single form), ensuring variables match what templates expect
    if (!isset($stateFormMapping[$state][$formId])) {
        echo json_encode(['success' => false, 'message' => "Form template not found for $state / $formId"]);
        exit;
    }

    $formData = [
        'clientName'        => $clientName,
        'principalEmployer' => $principalEmployer,
        'state'             => $state,
        'locationCode'      => $locationCode,
        'monthYear'         => $monthYear
    ];

    // Make data available as local variables: $clientName, $principalEmployer, $state, $locationCode, $monthYear
    extract($formData, EXTR_OVERWRITE);

    ob_start();
    include $stateFormMapping[$state][$formId];
    $templateOut = ob_get_clean();

    if (trim($templateOut) === '') {
        // very last resort: simple table
        $templateOut = "<h2>Form Data</h2><table>";
        foreach ($formData as $k => $v) {
            $templateOut .= "<tr><th>" . htmlspecialchars($k) . "</th><td>" . htmlspecialchars($v) . "</td></tr>";
        }
        $templateOut .= "</table>";
    }

    // Wrap with our CSS
    $html = "<!doctype html><html><head><meta charset='UTF-8'>{$globalCSS}</head><body>{$templateOut}</body></html>";
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

// Parse month/year for folder/file
try {
    $dt = new DateTime($monthYear);
    $folderMonthYear = $dt->format('F Y');
    $fileYearMonth   = $dt->format('F_Y');
} catch (Exception $e) {
    $folderMonthYear = sanitize_for_path($monthYear);
    $fileYearMonth   = preg_replace('/\s+/', '_', $folderMonthYear);
}

// Determine system downloads folder
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $baseDir = getenv('USERPROFILE') ?: (getenv('HOMEDRIVE') . getenv('HOMEPATH'));
} else {
    $baseDir = getenv('HOME') ?: __DIR__;
}
$baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR);

// Build target directory (unchanged)
$pathParts = [
    $baseDir,
    'Downloads',
    $clientName,
    $principalEmployer,
    $state,
    $locationCode,
    $folderMonthYear
];
$targetDir = implode(DIRECTORY_SEPARATOR, $pathParts);

if (!is_dir($targetDir)) {
    if (!mkdir($targetDir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => "Failed to create directory: $targetDir"]);
        exit;
    }
}

// Filename (unchanged)
$filename = sprintf(
    '%s_%s_%s.pdf',
    sanitize_for_filename($state),
    sanitize_for_filename($readableFormName),
    $fileYearMonth
);
$fullPath = $targetDir . DIRECTORY_SEPARATOR . $filename;

// ----------------- GENERATE PDF -----------------
try {
    $mpdf = new \Mpdf\Mpdf([
        'mode'          => 'utf-8',
        'format'        => 'A4',
        'margin_left'   => 10,
        'margin_right'  => 10,
        'margin_top'    => 10,
        'margin_bottom' => 10,
        'default_font'  => 'times',
        'tempDir'       => sys_get_temp_dir()
    ]);

    // Helpful when debugging images/HTML
    // $mpdf->showImageErrors = true;

    $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::DEFAULT_MODE);
    $mpdf->Output($fullPath, \Mpdf\Output\Destination::FILE);

    echo json_encode([
        'success'         => true,
        'message'         => 'PDF generated successfully',
        'path'            => $fullPath,
        'folderStructure' => str_replace($baseDir . DIRECTORY_SEPARATOR, '', $targetDir),
        'filename'        => basename($fullPath)
    ]);
} catch (\Mpdf\MpdfException $e) {
    echo json_encode(['success' => false, 'message' => 'PDF generation failed: ' . $e->getMessage()]);
}
