<?php
include("../includes/config.php");
session_start();

require_once __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Get JSON POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$formId       = $data['formId'] ?? 'unknown_form';
$state        = $data['state'] ?? '';
$clientName   = $data['clientName'] ?? '';
$locationCode = $data['locationCode'] ?? '';
$htmlContent  = $data['htmlContent'] ?? '';
$monthYear    = $data['monthYear'] ?? '';

if (!$htmlContent || !$state || !$clientName || !$locationCode || !$monthYear) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit;
}

// ---- helper to count max table columns (handles colspan) ----
function get_max_table_columns(string $html): int
{
    $max = 0;
    // Ensure proper encoding header for DOMDocument
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
$forceA3 = ['form-mw-musterroll'];
if (in_array($formId, $forceA3, true)) {
    $maxCols = max($maxCols, 99);
}

$paperFormat      = 'A4';
$paperOrientation = 'P';
$baseFontSize     = 10;

// If too many columns, use A3 Landscape
if ($maxCols >= 10) {
    $paperFormat      = 'A3';
    $paperOrientation = 'L';
    $baseFontSize     = 9;
}

// Sanitize inputs
$state       = preg_replace('/[^a-zA-Z0-9,\s-]/', '', $state);
$clientName  = preg_replace('/[^a-zA-Z0-9,\s-]/', '', $clientName);
$locationCode = preg_replace('/[^a-zA-Z0-9,\s-]/', '', $locationCode);

$state       = preg_replace('/[\s,]+/', ' ', trim($state));
$clientName  = preg_replace('/[\s,]+/', ' ', trim($clientName));
$locationCode = preg_replace('/[\s,]+/', ' ', trim($locationCode));

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

// Define form names for all states
$formNames = [
    'Punjab' => [
        'form-ii-a' => 'Form_II_A',
        'form-a' => 'Form_A',
        'form-xii' => 'Form_XII',
        'form-lwf-fine' => 'Form_LWF_Fine',
        'form-lwf-wage' => 'Form_LWF_Wage',
        'form-mb-act' => 'Form_MB_Act',
        'form-mw-damage' => 'Form_MW_Damage',
        'form-mw-fine' => 'Form_MW_Fine',
        'form-mw-musterroll' => 'Form_MW_MusterRoll',
        'form-mw-wage' => 'Form_MW_Wage',
        'form-ot-register' => 'Form_OT_Register',
        'form-se-damage' => 'Form_SE_Damage',
        'form-se-musterroll' => 'Form_SE_MusterRoll',
        'form-se-wage' => 'Form_SE_Wage',
        'form-various-acts' => 'Form_Various_Acts'
    ],

    'gujarat' => [
        'form-advance' => 'Form_Advance',
        'form-child-labour' => 'Form_Child_Labour',
        'form-clra' => 'Form_CLRA',
        'form-lwf-b' => 'Form_LWF_B',
        'form-lwf-a' => 'Form_LWF_A',
        'form-maternity-a' => 'Form_Maternity_A',
        'form-maternity-m' => 'Form_Maternity_M',
        'form-maternity-n' => 'Form_Maternity_N',
        'form-mw-damage-loss' => 'Form_MW Damage_Loss',
        'form-mw-musterroll' => 'Form_MW_Musterroll',
        'form-mw-wage-slip' => 'Form_MW _Wage Slip',
        'form-mw-wage' => 'Form_MW_Wage',
        'form-ot-register' => 'Form_OT_Register',
        'form-se-leave-accumulation' => 'Form_SE_Leave_Accumulation',
        'form-se-leave-book' => 'Form_SE_Leave_Book',
        'form-se-musterroll' => 'Form_SE_musterroll',
        'form-various' => 'Form_Variousacts',
    ],

    'Jharkhand' => [
        'form-advance' => 'Form_Advance',
        'form-child' => 'Form_Child',
        'form-clra' => 'Form_Clra',
        'form-mb-act' => 'Form_MB_Act',
        'form-mw-damage' => 'Form_MW_Damage',
        'form-mw-musterroll' => 'Form_MW_Musterroll',
        'form-ot-register' => 'Form_OT_Register',
        'form-se-leave-register' => 'Form_SE_Leave_Register',
        'form-se-service-card' => 'Form_SE_Service_Card',
        'form-se-wage-register' => 'Form_SE_Wage_Register',
        'form-se-damage' => 'Form_SE_Damage',
        'form-various-act' => 'Form_Various_Act',
    ],

    'Assam' => [
        'form-advance' => 'Form_Advance2',
        'form-child-labour' => 'From_Child_Labour',
        'form-clra' => 'Form_CLRA',
        'form-conf-per-workmen-register' => 'Form_Conf_Per_Workmen_Register',
        'form-maternity-a' => 'Form_Maternity_A',
        'form-mw-damage-loss' => 'Form_MW_Damage_Loss',
        'form-mw-fine' => 'Form_MW_Fine',
        'form-mw-musterroll' => 'Form_MW_Musterroll',
        'form-mw-wage' => 'Form_MW_Wage',
        'form-ot-register' => 'Form_OT_Register',
        'form-se-leave-register' => 'Form_SE_Leave_Register',
        'form-se-lime-washing' => 'Form_SE_Lime_Washing',
        'form-se-musterroll' => 'Form_SE_Musterroll',
        'form-various-act' => 'Form_Various_Act',
        'form-workman-register' => 'Form_Workman_Register',
    ],

    'Delhi' => [
        'form-child-labour' => 'Form_Child_Labour',
        'form-clra' => 'Form_CLRA',
        'form-lwf-fine' => 'Form_LWF_Fine',
        'form-lwf-wage' => 'Form_LWF_Wage',
        'form-maternity-a' => 'Form_Maternity_A',
        'form-mw-damage-loss' => 'Form_MW_Damage_Loss',
        'form-mw-musterroll' => 'Form_MW_Musterroll',
        'form-mw-wage-fine' => 'Form_MW_Wage_Fine',
        'form-mw-wage' => 'Form_MW_Wage',
        'form-ot-register' => 'Form_OT_Register',
        'form-various' => 'Form_Variousacts',
    ],

    'Haryana' => [
        "form-a" => "Form_A",
        "form-advance" => "Form_Advance",
        "form-xii" => "Form_XII",
        "form-lwf-fine" => "Form_LWF_Fine",
        "form-lwf-wage" => "Form_LWF_Wage",
        "form-mb-act" => "Form_MB_Act",
        "form-mw-damage" => "Form_MW_Damage",
        "form-mw-fine" => "Form_MW_Fine",
        "form-mw-musterroll" => "Form_MW_MusterRoll",
        "form-mw-wage" => "Form_MW_Wage",
        "form-ot-register" => "Form_OT_Register",
        "form-se-damage" => "Form_SE_Damage",
        "form-se-musterroll" => "Form_SE_MusterRoll",
        "form-se-wage" => "Form_SE_Wage",
        "form-various-acts" => "Form_Various_Acts",
    ],

    'Bihar' => [
        'form-advance' => 'Form_Advance',
        'form-child' => 'Form_Child',
        'form-clra' => 'Form_CLRA',
        'form-mb-act' => 'Form_MB_Act',
        'form-mw-damage' => 'Form_MW_Damage',
        'form-mw-musterroll' => 'Form_MW_MusterRoll',
        'form-ot-register' => 'Form_OT_Register',
        'form-se-leave-register' => 'Form_SE_Leave_Register',
        'form-se-wage-register' => 'Form_SE_Wage_Register',
        'form-se-damage' => 'Form_SE_Damage',
        'form-variousacts' => 'Form_Various_Acts',
    ],

    'Goa' => [
        'form-child-labour' => 'Form_Child_Labour',
        'form-clra' => 'Form_CLRA_php',
        'form-lwf-fine' => 'Form_LWF Fine',
        'form-lwf-wage' => 'Form_LWF_Wage',
        'form-maternity-a' => 'Form_Maternity_A',
        'form-ot-register' => 'Form_OT_Register',
        'form-se-advance' => 'Form_SE_Advance',
        'form-se-damage-loss' => 'Form_SE_Damage_Loss',
        'form-se-fine-register' => 'Form_SE_Fine_Register',
        'form-se-leave-register' => 'Form_SE_Leave_Register',
        'form-se-musterroll' => 'Form_SE_Musterroll',
        'form-se-wage' => 'Form_SE_Wage',
        'form-variousacts' => 'Form_Variousacts',
    ],

    'Himachal Pradesh' => [
        'form-child-labour' => 'Form_Child_Labour',
        'form-clra' => 'Form_CLRA',
        'form-maternity-a' => 'Form_Maternity_A',
        'form-mw-damage-loss' => 'Form_MW_Damage_Loss',
        'form-mw-fine' => 'Form_MW_Fine',
        'form-ot-register' => 'Form_OT_Register',
        'form-se-deduction-register' => 'Form_SE_Deduction_Register',
        'form-se-leave-register-8' => 'Form_SE_Leave_Register_8',
        'form-se-leave-register-11' => 'Form_SE_Leave_Register_11',
        'form-se-wage-register' => 'Form_SE_Wage_Register',
        'form-variousacts' => 'Form_Variousacts',
    ],

    'Andhra Pradesh' => [
        'form-child-labour' => 'Form_Child_Labour',
        'form-clra' => 'Form_CLRA',
        'form-maternity-a' => 'Form_Maternity_A',
        'form-variousacts' => 'Form_Variousacts',
        'lwf-fine-regiter' => 'Form_LWF_Fine_Register',
        'lwf-wage-regiter' => 'Form_LWF_Wage_Register',
        'form-mw-damage' => 'Form_MW_Damage',
        'form-mw-fine' => 'Form_MW_Fine',
        'form-mw-musterroll' => 'Form_MW_Musterroll',
        'form-ot-register' => 'Form_OT_Register.php',
        'form-mw-wage-register' => 'Form_MW_Wage_Register',
        'form-mw-wage-slip' => 'Form_MW_Wage_Slip',
        'form-se-advance' => 'Form_SE_Advance',
        'form-se-damage-loss' => 'Form_SE_Damage_Loss',
        'form-se-fine-register' => 'Form_SE_Fine_Register',
        'form-se-leave-register' => 'Form_SE_Leave_Register',
        'form-se-musterroll' => 'Form_SE_Musterroll',
        'form-se-wage-register' =>   'Form_SE_Wage_Register'
    ],


    'Chattisgarh' => [
        'form-child-labour' => 'Child Labour.php',
        'form-clra' => 'CLRA.php',
        'form-lwf-fine' => 'LWF Fine.php',
        'form-maternity-a' => 'Maternity A.php',
        'form-mw-damage-or-loss' => 'MW Damage Loss.php',
        'form-se-leave-register-i' => 'SE Leave Register Form I.php',
        'form-se-leave-register-j' => 'SE Leave Register Form J.php',
        'form-se-lime-washing' => 'SE Lime Washing.php',
        'form-se-muster-cum-wage' => 'SE Muster Cum Wage.php',
        'form-variousacts' => 'Variousacts.php',
    ],

    'Madhya Pradesh' => [
        'form-child-labour' => 'Form_Child Labour',
        'form-clra' => ' Form_CLRA',
        "form-lwf-fine" => "Form_lwf_fine",
        'form-maternity-a' => 'Form_Maternity A',
        'form-mw-damage-loss' => 'Form_MW Damage Loss',
        'form-se-leave-register' => 'Form_SE Leave Register Form I',
        'form-se-leave-register' => 'Form_SE Leave Register Form J',
        'form-se-lime-washing' => 'Form_SE Lime Washing',
        'form-se-musterroll' => 'Form_SE_Muster Cum Wage',
        'form-various-act' => 'Form_variousacts',
    ],

    'Uttar Pradesh' => [
        'form-advance' => 'Form_Advance',
        'form-child-labour' => 'Form_Child_Labour',
        'form-clra' => 'Form_CLRA',
        'form-maternity-a' => 'Form_Maternity_A',
        'form-mw-damage' => 'Form_MW_Damage',
        'form-mw-fine' => 'Form_MW_Fine',
        'form-mw-musterroll' => 'Form_MW_Musterroll',
        'form-mw-ot-register' => 'Form_MW_OT_Register',
        'form-mw-wage-register' => 'Form_MW_Wage_Register',
        'form-mw-wage-slip' => 'Form_MW_Wage_Slip',
        'form-n&f-register' => 'Form_N&f_Register',
        'form-se-damage-loss-form-d' => 'Form_SE_Damage_Loss_Form_D',
        'form-se-damage-loss-form-e' => 'Form_SE_Damage_Loss_Form_E',
        'form-se-leave-register' => 'Form_SE_Leave_Register',
        'form-se-mustercumwage' => 'Form_SE_Muster_Cum_Wage',
        'form-variousacts' => 'Form_Variousacts',
    ],

    'Uttarkhand' => [
        'form-advance' => 'Form_Advance',
        'form-child-labour' => 'Form_Child_Labour',
        'form-clra' => 'Form_CLRA',
        'form-maternity-a' => 'Form_Maternity_A',
        'form-mw-damage' => 'Form_MW_Damage',
        'form-mw-fine' => 'Form_MW_Fine',
        'form-mw-musterroll' => 'Form_MW_Musterroll',
        'form-mw-ot-register' => 'Form_MW_OT_Register',
        'form-mw-wage-slip' => 'Form_MW_Wage_Slip',
        'form-n&f-register' => 'Form_N&f_Register',
        'form-se-damage-loss-form-d' => 'Form_SE_Damage_Loss_Form_D',
        'form-se-damage-loss-form-e' => 'Form_SE_Damage_Loss_Form_E',
        'form-se-leave-register' => 'Form_SE_Leave_Register',
        'form-se-mustercumwage' => 'Form_SE_Muster_Cum_Wage',
        'form-variousacts' => 'Form_Variousacts',
    ],

    'Karnataka' => [
        'form-child' => 'Form_child',
        'form-clra' => 'Form_CLRA',
        'form-combined-register' => 'Form_Combined_Register',
        'form-lwf-fine-register' => 'Form_LWF_Fine_Register',
        'form-lwf-wage-register' => 'Form_LWF_Wage_Register',
        'form-maternity-a' => 'Form_Maternity_A',
        'form-mw-wage-slips' => 'Form_MW_Wage_Slips',
        'form-se-leave-register-f' => 'Form_SE_Leave_Register_F',
        'form-se-leave-register-h' => 'Form_SE_Leave_Register_H',
        'form-se-mustercumwage-part1' => 'Form_SE_Mustercumwage_Part1',
        'form-se-mustercumwage-part2' => 'Form_SE_Mustercumwage_Part2',
        'form-suspension-register' => 'Form_Suspension_Register',
        'form-various-act' => 'Form_Various_Act',
    ],

    'Maharashtra' => [
        'form-child' => 'Form_child',
        'form-clra' => 'Form_CLRA',
        'form-hra-a' => 'Form_HRA_Form-A',
        'form-hra-i' => 'Form_HRA_Form_I',
        'form-lwf-wage' => 'Form_LWF_Wage',
        'form-maternity-register' => 'Form_Maternity_Register',
        'form-mw-wage-slip' => 'Form_MW_Wage_Slip',
        'form-advance-register' => 'Form_Advance_Register',
        'form-se-leave-accumulation' => 'Form_SE_Leave_Accumulation',
        'form-se-leave-book' => 'Form_SE_Leave_Book',
        'form-se-mustercumwage-form Q-part-i' => 'Form_SE_MusterCumWage_Form_Q_Part_I',
        'form-se-mustercumwage-form Q-part-ii' => 'Form_SE_MusterCumWage_Form_Q_Part_II',
        'form-se-mustercumwage-part-i' => 'Form_SE_MusterCumWage_Part_I',
        'form-se-mustercumwage-part-ii' => 'Form_SE_MusterCumWage_Part_II',
        'form-various-act' => 'Form_Various_Act',
        'form-lwf-fine' => 'LWF Fine Form C',
    ],

    'Telangana' => [
        'form-advance-register' => 'Advance Register',
        'form-child' => 'Child',
        'form-clra' => 'CLRA',
        'form-lwf-fine-register' => 'LWF Fine Register',
        'form-lwf-wage-register' => 'LWF Wage Register',
        'form-maternity-a' => 'Maternity A',
        'form-mw-ot-register' => 'MW Ot Register',
        'form-mw-deductions' => 'MW Register of Deductions',
        'form-mw-fine' => 'MW Register of Fines',
        'form-mw-wage-register' => 'MW Wage Register',
        'form-mw-wage-slip' => 'MW Wage Slip',
        'form-mw-musterroll' => 'MW_Musterroll',
        'form-nf-register' => 'N&F Register',
        'form-se-damage' => 'S&E Damage or Loss',
        'form-se-fine-register' => 'SE Fine Register',
        'form-se-leave-register' => 'SE Leave Register',
        'form-se-musterroll' => 'SE Musterroll',
        'form-se-wage-register' => 'SE Wage Register',
        'form-variousacts-compliance' => 'Various Act Ease Compliance',
        'form-variousacts-formii' => 'Various Act Integrated_FormII',
        'form-variousacts-formiii' => 'Various Act Integrated_FormIII',
    ],

    'Kerala' => [
        'form-child-labour' => 'Child Labour',
        'form-clra' => 'CLRA',
        'form-maternity-a' => 'Maternity A',
        'form-mw-wage-slip' => 'MW Wage Slip',
        'form-mw-damage' => 'MW_Damage',
        'form-mw-fine' => 'MW_Fine',
        'form-mw-musterroll' => 'MW_Musterroll',
        'form-mw-wage-register' => 'MW_Wage Register',
        'form-nf-musterroll' => 'N&F Musterroll',
        'form-ot-register' => 'OT Register',
        'form-se-leave-register' => 'SE Leave Register',
        'form-se-musterroll' => 'SE Musterroll',
        'form-se-service-card' => 'S&E Service card',
        'form-se-advance' => 'SE Advance',
        'form-variousacts' => 'Variousacts',
    ],

    'Tamilnadu' => [
        'form-child-labour' => 'Child Labour',
        'form-clra' => 'CLRA',
        'form-conf-per-workmen-register' => 'Conf.Per.Workmen Register',
        'form-lwf-wage' => 'LWF Wage',
        'form-maternity-a' => 'Maternity A',
        'form-mw-damage' => 'MW_Damage',
        'form-mw-fine' => 'MW_Fine',
        'form-mw-musterroll' => 'MW_Musterroll',
        'form-mw-ot-register' => 'MW OT Register',
        'form-mw-wage-register' => 'MW_Wage Register',
        'form-mw-workman' => 'MW Workman',
        'form-nf-register' => 'N&F Register',
        'form-se-employee-register' => 'SE Employee Register',
        'form-se-leave-register' => 'SE Leave Register',
        'form-se-musterroll' => 'SE Musterroll',
        'form-se-notice' => 'SE Notice Working Hours',
        'form-se-wage' => 'SE Wage',
        'form-se-wage-slip' => 'SE Wage Slip',
        'form-variousacts' => 'Variousacts',
    ],

    'Odisha' => [
        'form-child-labour' => 'Form_Child',
        'form-clra' => 'Form_CLRA',
        'form-lwf-fine-register-appendix' => 'Form_LWF_FineRegister_Appendix',
        'form-lwf-fine-register-e' => 'Form_LWF_Fine_Register_E',
        'form-lwf-wage-register' => 'Form_LWF_Wage_Register',
        'form-maternity-register' => 'Form_Maternity_Register',
        'form-sea-leave-register' => 'Form_SEA_Leave_Register',
        'form-sea-mustercumwage' => 'Form_SEA_MusterCumWage',
        'form-sea-ot-register' => 'Form_SEA_OT_Register',
        'form-variousact' => 'Form_Various_Act',
    ],

    'Jammu and Kashmir' => [
        'form-child-labour' => 'Form_Child',
        'form-clra' => 'Form_CLRA',
        'form-maternity-register' => 'Form_Maternity_Register',
        'form-mw-fine' => 'Form_MW_Fine',
        'form-mw-damage' => 'Form_MW_Damage',
        'form-mwa-service-card' => 'Form_MWA_Service_Card',
        'form-advance-register' => 'Form_Advance_Register',
        'form-sea-leave-register' => 'Form_SEA_Leave_Register',
        'form-sea-leave-book' => 'Form_SEA_Leave_book',
        'form-sea-lime-washing' => 'Form_SEA_Lime_Washing',
        'form-sea-mustercumwage' => 'Form_SEA_MusterCumWage',
        'form-variousact' => 'Form_Various_Act',
    ],

    'West Bengal' => [
        'form-child' => 'Child.php',
        'form-clra' => 'CLRA.php',
        'form-hra' => 'HRA Form A.php',
        'form-lwf-fine' => 'LWF Fine Form B.php',
        'form-lwf-wage' => 'LWF Wage Form A.php',
        'form-maternity-a' => 'Maternity A.php',
        'form-mw-fine' => 'MW Fine Form I.php',
        'form-mw-wage' => 'MW Wage Form IX.php',
        'form-mwa-damage' => 'MWA Damage Form II.php',
        'form-mwa-musterroll' => 'MWA Musterroll.php',
        'form-mwa-ot' => 'MWA OT Register.php',
        'form-mwa-wage-slip' => 'MWA Wage slip.php',
        'form-mwa-workman' => 'MWA Workman Register.php',
        'form-pwa-advance' => 'PWA Advance.php',
        'form-se-musterroll' => 'SE Musterroll.php',
        'form-sea-leave-register' => 'SEA Leave Register.php',
        'form-sea-musterroll' => 'SEA Musterroll.php',
        'form-sea-ot-register' => 'SEA OT Register.php',
        'form-sea-wage' => 'SEA Wage.php',
        'form-sea-workman-register' => 'SEA Workman Register.php',
        'form-variousacts' => 'Variousacts.php',
        'form-web-workman' => 'WEB Workman.php',

    ],

    'Tripura' => [
        'form-child' => 'Child.php',
        'form-clra' => 'CLRA.php',
        'form-maternity-a' => 'Maternity A.php',
        'form-mwa-fine' => 'MWA Fine Form I.php',
        'form-mwa-damage' => 'MWA Damage Form II.php',
        'form-se-musterroll' => 'SE Musterroll.php',
        'form-sea-ot-register' => 'SEA OT Register.php',
        'form-sea-workman-register' => 'SEA Workman Register.php',
        'form-sea-leave-register' => 'SEA Leave Register.php',
        'form-sea-wage' => 'SEA Wage.php',
        'form-sea-musterroll' => 'SEA Musterroll.php',
        'form-variousacts' => 'Variousacts.php',

    ],

    'Manipur' => [
        'form-child' => 'Child.php',
        'form-clra' => 'CLRA.php',
        'form-maternity-a' => 'Maternity A.php',
        'form-mw-ot' => 'MW OT Register.php',
        'form-mw-wage' => 'MW Wage.php',
        'form-mwa-fine' => 'MWA Fine Form I.php',
        'form-mwa-damage' => 'MWA Damage Form II.php',
        'form-mwa-musterroll' => 'MWA Musterroll.php',
        'form-sea-attendance-register' => 'SEA Attendance Register.php',
        'form-sea-fine-register' => 'SEA Fine Register.php',
        'form-sea-leave-register' => 'SEA Leave Register.php',
        'form-sea-wage-register' => 'SEA Wage Register.php',
        'form-variousacts' => 'Variousacts.php',
    ],

    'Pondicherry' => [
        'form-child' => 'Child.php',
        'form-clra' => 'CLRA.php',
        'form-maternity-a' => 'Maternity A.php',
        'form-mw-ot' => 'MW OT Register.php',
        'form-n&f-register' => 'N&F Register.php',
        'form-se-advance' => 'SE Advance Register.php',
        'form-se-musterroll' => 'SE Musterroll.php',
        'form-se-wage-register' => 'SE Wage Register.php',
        'form-variousacts' => 'Variousacts.php',

    ],

    'Sikkim' => [
        'form-child' => 'Child.php',
        'form-clra' => 'CLRA.php',
        'form-maternity-a' => 'Maternity A.php',
        'form-mw-wage' => 'MW Wage Form IX.php',
        'form-mwa-fine' => 'MWA Fine Form I.php',
        'form-mwa-damage' => 'MWA Damage Form II.php',
        'form-mwa-ot' => 'MWA OT Register.php',
        'form-mwa-musterroll' => 'MWA Musterroll.php',
        'form-mwa-workman' => 'MWA Workman Register.php',
        'form-variousacts' => 'Variousacts.php',

    ],

    'Mizoram' => [
        'form-child' => 'Form_Child',
        'form-clra' => 'Form_CLRA',
        'form-h-overtime-register' => 'Form_H_Overtime_Register',
        'form-mb-musterroll-form-a' => 'Form_MB_Musterroll_Form A',
        'form-mw-wage-register' => 'Form_MW_Wage_Register',
        'form-mwa-fine' => 'Form_MWA_Fine',
        'form-mw-damage-or-loss' => 'Form_MW_Damage_or_Loss',
        'form-mw-ot-register' => 'Form_MW_OT_Register',
        'form-mw-musterroll' => 'Form_MW_Musterroll',
        'form-se-musterroll' => 'Form_SE_Musterroll',
        'form-se-lime-washing' => 'Form_SE_Lime Washing',
        'form-se-workmen-register' => 'Form_SE_Workmen Register',
        'form-se-leave-register' => 'Form_SE_Leave Register',
        'form-variousact' => 'Form_Various_act',
    ],

    'Meghalaya' => [
        'form-child' => 'Form_Child',
        'form-clra' => 'Form_CLRA',
        'form-mb-musterroll-form-a' => 'Form_MB_Musterroll_Form A',
        'form-mw-wage-register' => 'Form_MW_Wage Register',
        'form-mwa-fine' => 'Form_MWA Fine',
        'form-mw-damage-or-loss' => 'Form_MW_Damage or Loss',
        'form-mw-ot-register' => 'Form_MW_OT Register',
        'form-mw-musterroll' => 'Form_MW_Musterroll',
        'form-se-musterroll' => 'Form_SE_Musterroll',
        'form-se-ot-register' => 'Form_SE_OT Register',
        'form-se-lime-washing' => 'Form_SE_Lime Washing',
        'form-se--register-of-employment' => 'Form_SE_Register Of Employment',
        'form-se-leave-register' => 'Form_SE_Leave Register',
        'form-variousact' => 'Form_Various act',
    ],

    'Nagaland' => [
        'form-child' => 'Form_Child',
        'form-clra' => 'Form_CLRA',
        'form-mb-musterroll-form-a' => 'Form_MB_Musterroll_Form A',
        'form-mw-wage-register' => 'Form_MW_Wage_Register',
        'form-mwa-fine' => 'Form_MWA_Fine',
        'form-mw-damage-or-loss' => 'Form_MW_Damage_or_Loss',
        'form-mw-ot-register' => 'Form_MW_OT_Register',
        'form-mw-musterroll' => 'Form_MW_Musterroll',
        'form-variousact' => 'Form_Various_act',
    ],

    'Arunachal Pradesh' => [
        'form-child' => 'Form_Child',
        'form-clra' => 'Form_CLRA',
        'form-mb-musterroll-form-a' => 'Form_MB_Musterroll_Form A',
        'form-mw-wage-register' => 'Form_MW_Wage_Register',
        'form-mwa-fine' => 'Form_MWA_Fine',
        'form-mw-damage-or-loss' => 'Form_MW_Damage_or_Loss',
        'form-mw-ot-register' => 'Form_MW_OT_Register',
        'form-mw-musterroll' => 'Form_MW_Musterroll',
        'form-variousact' => 'Form_Various_act',
    ],

    'Chandigarh' => [
        'form-ii-a' => 'Form_II_A',
        'form-a' => 'Form_A',
        'form-xii' => 'Form_XII',
        'form-lwf-fine' => 'Form_LWF_Fine',
        'form-lwf-wage' => 'Form_LWF_Wage',
        'form-mb-act' => 'Form_MB_Act',
        'form-mw-damage' => 'Form_MW_Damage',
        'form-mw-fine' => 'Form_MW_Fine',
        'form-mw-musterroll' => 'Form_MW_MusterRoll',
        'form-mw-wage' => 'Form_MW_Wage',
        'form-ot-register' => 'Form_OT_Register',
        'form-se-damage' => 'Form_SE_Damage',
        'form-se-musterroll' => 'Form_SE_MusterRoll',
        'form-se-wage' => 'Form_SE_Wage',
        'form-various-acts' => 'Form_Various_Acts'
    ],

    'Lakshadweep' => [
        'form-clra' => 'Form_CLRA',
        'form-maternity-form-a' => 'Form_Maternity_Form_A',
        'form-mw-wage-register' => 'Form_MW_Wage_Register',
        'form-mwa-fine' => 'Form_MWA_Fine',
        'form-mw-damage-or-loss' => 'Form_MW_Damage_or_Loss',
        'form-mw-ot-register' => 'Form_MW_OT_Register',
        'form-mw-musterroll' => 'Form_MW_Musterroll',
        'form-variousact' => 'Form_Various_act',
    ],

    'Andaman and Nicobar' => [
        'form-child' => 'Child.php',
        'form-clra' => 'CLRA.php',
        'form-act-form-a' => 'MB Act Form A.php',
        'form-mwa-fine' => 'MWA Fine.php',
        'form-mwa-damage-or-loss' => 'MWA Damage or Loss.php',
        'form-se-workmen-register' => 'SE Workmen Register.php',
        'form-se-wage' => 'SE Wage.php',
        'form-se-ot-register' => 'SE OT Register.php',
        'form-sw-musterroll' => 'SW Musterroll.php',
        'form-variousact' => 'Various Act.php',
    ],

    'Dadra and nagra haveli' => [
        'form-child' => 'Form_Child',
        'form-clra' => 'Form_CLRA',
        'form-register-of-leave' => 'Form_Register_Of_Leave',
        'form-mb-musterroll-form-a' => 'Form_MB_Musterroll_Form_A',
        'form-mwa-ot-register' => 'Form_MWA_OT_Register',
        'form-se-fine-register' => 'Form_SE_Fine_Register',
        'form-se-lime-washinng' => 'Form_SE_Lime_Washing',
        'form-se-damage-or-loss' => 'Form_SE_Damage_or_Loss',
        'form-advance-register' => 'Form_Advance Register',
        'form-se-musterroll' => 'Form_SE_Musterroll',
        'form-se-wage-register' => 'Form_SE_Wage_Register',
        'form-variousact' => 'Form_Various_act',
    ],

    'Ladakh' => [
        'form-child' => 'Child_Labour.php',
        'form-clra' => 'CLRA.php',
        'form-mb-musterroll-form-a' => 'MB_Musterroll_Form_A.php',
        'form-mw-wage-register' => 'MW_Wage_Register.php',
        'form-mwa-fine' => 'MWA_Fine.php',
        'form-mw-damage-or-loss' => 'MW_Damage_or_Loss.php',
        'form-mw-ot-register' => 'MW_OT_Register.php',
        'form-mw-musterroll' => 'MW_Musterroll.php',
        'form-variousact' => 'Various_Act.php',
        'form-sea-leave-register' => 'SEA_Leave_Register.php',
        'form-sea-leave-book' => 'SEA_Leave_book.php',
        'form-sea-lime-washing' => 'SEA_Lime_Washing.php',
        'form-sea-mustercumwage' => 'SEA_Muster_Cum_Wage.php',
    ],

    'Rajasthan' => [
        'form-advance' => 'Form_advance',
        'form-child' => 'Form_child',
        'form-clra' => 'Form_clra',
        'form-maternity-a' => 'FormMaternity_A',
        'form-mw-damage' => 'Form_mw_damage',
        'form-mw-fine' => 'Form_mw_fine',
        'form-mw-wage' => 'Form_mw_wage',
        'form-ot-register' => 'Form_ot_register',
        'form-variousacts' => 'Form_variousacts',
        'form-se-maternity' => 'Form_SE_Maternity',
        'form-se-register-of-employement' => 'Form_SE_Register Of Employement',
        'form-se-register-of-leave-with-wages' => 'SE_Register Of Leave With Wages',
    ],
];

// Get the base form ID (remove state prefix if present)
$baseFormId = $formId;
if (strpos($formId, '-') !== false) {
    $parts = explode('-', $formId);
    $baseFormId = end($parts);
}

$stateKey = strtolower($state);
$readableFormName = $formNames[$stateKey][$formId]
    ?? $formNames[$stateKey][$baseFormId]
    ?? $formId;

// Define paths
$basePath = __DIR__ . '/../downloads/';
$targetPath = $basePath . "{$clientName}/{$state}/{$locationCode}/{$folderMonthYear}/";

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
$server_relative_path = "{$clientName}/{$state}/{$locationCode}/{$folderMonthYear}/{$filename}";

try {
    $mpdf = new \Mpdf\Mpdf([
        'mode'               => 'utf-8',
        'format'             => $paperFormat,
        'orientation'        => $paperOrientation,
        'margin_left'        => 10,
        'margin_right'       => 10,
        'margin_top'         => 10,
        'margin_bottom'      => 10,
        'default_font_size'  => $baseFontSize,
        'default_font'       => 'timesnewroman',
        'autoScriptToLang'   => true,
        'autoLangToFont'     => true,
        'tempDir'            => __DIR__ . '/tmp',
        'useSubstitutions'   => false,
        'simpleTables'       => false,
        'packTableData'      => true,
        'ignore_table_percents' => false,
        'ignore_table_widths'   => false,
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

    // In save_pdf.php, after saving the file, add this:
    if (file_exists($filepath)) {
        error_log("✓ File successfully saved at: " . $filepath);
        error_log("✓ File size: " . filesize($filepath) . " bytes");
    } else {
        error_log("✗ File NOT saved at: " . $filepath);
    }

    echo json_encode([
        'success'  => true,
        'path'     => $filepath,
        'server_relative_path' => $server_relative_path,
        'filename' => $filename,
        'detected_columns' => $maxCols,
        'paper'    => $paperFormat . '-' . $paperOrientation,
        'folder_month' => $folderMonthYear,
        'file_exists' => file_exists($filepath), // Add this for debugging
        'actual_path' => $filepath // Add this
    ]);
    exit;
} catch (\Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'PDF generation failed: ' . $e->getMessage()
    ]);
    exit;
}
