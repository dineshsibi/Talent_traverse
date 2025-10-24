const formNames = {
    // Punjab forms
    "form-a": "Form_A",
    "form-ii-a": "Form_II_A",
    "form-xii": "Form_XII",
    "form-lwf-fine": "Form_LWF_Fine",
    "form-lwf-wage": "Form_LWF_Wage",
    "form-mb-act": "Form_MB_Act",
    "form-mw-damage": "Form_MW_Damage",
    "form-mw-fine": "Form_MW_Fine",
    "form-mw-musterroll": "Form_MW_MusterRoll",
    "form-mw-wage": "Form_MW_Wage",
    "form-ot-register": "Form_OT_Register",
    "form-se-damage": "Form_SE_Damage",
    "form-se-musterroll": "Form_SE_MusterRoll",
    "form-se-wage": "Form_SE_Wage",
    "form-various-acts": "Form_Various_Acts",

    // Gujarat forms
    'form-advance': 'Form_Advance',
    'form-child-labour': 'Form_Child_Labour',
    'form-clra': 'Form_CLRA',
    'form-lwf-b': 'Form_LWF_B',
    'form-lwf-a': 'Form_LWF_A',
    'form-maternity-a': 'Form_Maternity_A',
    'form-maternity-m': 'Form_Maternity_M',
    'form-maternity-n': 'Form_Maternity_N',
    'form-mw-damage-loss': 'Form_MW_Damage_Loss',
    'form-mw-musterroll': 'Form_MW_Musterroll',
    'form-mw-wage-slip': 'Form_MW_Wage_Slip',
    'form-mw-wage': 'Form_MW_Wage',
    'form-ot-register': 'Form_OT_Register',
    'form-se-leave-accumulation': 'Form_SE_Leave_Accumulation',
    'form-se-leave-book': 'Form_SE_Leave_Book',
    'form-se-musterroll': 'Form_SE_musterroll',
    'form-various': 'Form_Variousacts',

    //Jharkhand forms
    'form-advance': 'Form_Advance1',
    'form-child': 'Form_Child',
    'form-clra': 'Form_Clra',
    'form-mb-act': 'Form_MB_Act',
    'form-mw-damage': 'Form_MW_Damage',
    'form-mw-musterroll': 'Form_MW_Musterroll',
    'form-ot-register': 'Form_OT_Register',
    'form-se-leave-register': 'Form_SE_Leave_Register',
    'form-se-service-card': 'Form_SE_Service_Card',
    'form-se-wage-register': 'Form_SE_Wage_Register',
    'form-se-damage': 'Form_SE_Damage',
    'form-various-act': 'Form_Various_Act',

    //Assam Forms
    'form-advance': 'Form_Advance2',
    'form-child-labour': 'From_Child_Labour',
    'form-clra': 'Form_CLRA',
    'form-conf-per-workmen-register': 'Form_Conf_Per_Workmen_Register',
    'form-maternity-a': 'Form_Maternity_A',
    'form-mw-damage-loss': 'Form_MW_Damage_Loss',
    'form-mw-fine': 'Form_MW_Fine',
    'form-mw-musterroll': 'Form_MW_Musterroll',
    'form-mw-wage': 'Form_MW_Wage',
    'form-ot-register': 'Form_OT_Register',
    'form-se-leave-register': 'Form_SE_Leave_Register',
    'form-se-lime-washing': 'Form_SE_Lime_Washing',
    'form-se-musterroll': 'Form_SE_Musterroll',
    'form-various-act': 'Form_Various_Act',
    'form-workman-register': 'Form_Workman_Register',

    // Delhi forms
    'form-child-labour': 'Form_Child_Labour',
    'form-clra': 'Form_CLRA',
    'form-lwf-fine': 'Form_LWF_Fine',
    'form-lwf-wage': 'Form_LWF_Wage',
    'form-maternity-a': 'Form_Maternity_A',
    'form-mw-damage-loss': 'Form_MW_Damage_Loss',
    'form-mw-musterroll': 'Form_MW_Musterroll',
    'form-mw-wage-fine': 'Form_MW_Wage_Fine',
    'form-mw-wage': 'Form_MW_Wage',
    'form-ot-register': 'Form_OT_Register',
    'form-various': 'Form_Variousacts',

    // Haryana forms
    "form-child": "Form_Child",
    "form-advance": "Form_Advance",
    "form-clra": "Form_CLRA",
    "form-lwf-fine": "Form_LWF_Fine",
    "form-lwf-wage": "Form_LWF_Wage",
    "form-mb-act": "Form_MB_Act",
    "form-mw-damage": "Form_MW_Damage",
    "form-mw-fine": "Form_MW_Fine",
    "form-mw-musterroll": "Form_MW_MusterRoll",
    "form-mw-wage": "Form_MW_Wage",
    "form-ot-register": "Form_OT_Register",
    "form-se-damage": "Form_SE_Damage",
    "form-se-musterroll": "Form_SE_MusterRoll",
    "form-se-wage": "Form_SE_Wage",
    "form-variousacts": "Form_Various_Acts",


    //Goa forms
    'form-child-labour': 'Form_Child_Labour',
    'form-clra': 'Form_CLRA_php',
    'form-lwf-fine': 'Form_LWF Fine',
    'form-lwf-wage': 'Form_LWF_Wage',
    'form-maternity-a': 'Form_Maternity_A',
    'form-ot-register': 'Form_OT_Register',
    'form-se-advance': 'Form_SE_Advance',
    'form-se-damage-loss': 'Form_SE_Damage_Loss',
    'form-se-fine-register': 'Form_SE_Fine_Register',
    'form-se-leave-register': 'Form_SE_Leave_Register',
    'form-se-musterroll': 'Form_SE_Musterroll',
    'form-se-wage': 'Form_SE_Wage',
    'form-variousacts': 'Form_Variousacts',



    //Himachal Pradesh forms 
    'form-child-labour': 'Form_Child_Labour',
    'form-clra': 'Form_CLRA',
    'form-maternity-a': 'Form_Maternity_A',
    'form-mw-damage-loss': 'Form_MW_Damage_Loss',
    'form-mw-fine': 'Form_MW_Fine',
    'form-ot-register': 'Form_OT_Register',
    'form-se-deduction-register': 'Form_SE_Deduction_Register',
    'form-se-leave-register-8': 'Form_SE_Leave_Register_8',
    'form-se-leave-register-11': 'Form_SE_Leave_Register_11',
    'form-se-wage-register': 'Form_SE_Wage_Register',
    'form-variousacts': 'Form_Variousacts',

    //Andhra Pradesh
    'form-child-labour': 'Form_Child_Labour',
    'form-clra': 'Form_CLRA',
    'form-maternity-a': 'Form_Maternity_A',
    'form-variousacts': 'Form_Variousacts',
    'lwf-fine-regiter': 'Form_LWF_Fine_Register',
    'lwf-wage-regiter': 'Form_LWF_Wage_Register',
    'form-mw-damage': 'Form_MW_Damage',
    'form-mw-fine': 'Form_MW_Fine',
    'form-mw-musterroll': 'Form_MW_Musterroll',
    'form-ot-register': 'Form_OT_Register.php',
    'form-mw-wage-register': 'Form_MW_Wage_Register',
    'form-mw-wage-slip': 'Form_MW_Wage_Slip',
    'form-se-advance': 'Form_SE_Advance',
    'form-se-damage-loss': 'Form_SE_Damage_Loss',
    'form-se-fine-register': 'Form_SE_Fine_Register',
    'form-se-leave-register': 'Form_SE_Leave_Register',
    'form-se-musterroll': 'Form_SE_Musterroll',
    'form-se-wage-register': 'Form_SE_Wage_Register',

    //Rajastan
    'form-advance': 'Form_advance',
    'form-child': 'Form_child',
    'form-clra': 'Form_clra',
    'form-maternity-a': 'FormMaternity_A',
    'form-mw-damage': 'Form_mw_damage',
    'form-mw-fine': 'Form_mw_fine',
    'form-mw-wage': 'Form_mw_wage',
    'form-ot-register': 'Form_ot_register',
    'form-variousacts': 'Form_variousacts',
    'form-se-maternity': 'Form_SE_Maternity',
    'form-se-register-of-employement': 'Form_SE_Register Of Employement',
    'form-se-register-of-leave-with-wages': 'SE_Register Of Leave With Wages',

    //Chattisgarh forms
    'form-child-labour': 'Form_Child_Labour',
    'form-clra': 'Form_CLRA',
    'form-lwf-fine': 'Form_LWF Fine',
    'form-maternity-a': 'Form_Maternity_A',
    'form-mw-damage-loss': 'Form_MW_Damage_Loss',
    'form-se-leave-register-i': 'Form_SE_Leave_Register_Form_I',
    'form-se-leave-register-j': 'Form_SE_Leave_Register_Form_J',
    'form-se-lime-washing': 'Form_SE_Lime_Washing',
    'form-se-muster-cum-wage': 'Form_SE_Muster_Cum_Wage',
    'form-variousacts': 'Form_Variousacts',

    // Bihar forms
    'form-advance': 'Form_Advance',
    'form-child': 'Form_Child',
    'form-clra': 'Form_CLRA',
    'form-mb-act': 'Form_MB_Act',
    'form-mw-damage': 'Form_MW_Damage',
    'form-mw-musterroll': 'Form_MW_MusterRoll',
    'form-ot-register': 'Form_OT_Register',
    'form-se-leave-register': 'Form_SE_Leave_Register',
    'form-se-wage-register': 'Form_SE_Wage_Register',
    'form-se-damage': 'Form_SE_Damage',
    'form-variousacts': 'Form_Various_Acts',

    //Madhya Pradesh
    'form-child-labour': 'Form_Child Labour',
    'form-clra': ' Form_CLRA',
    "form-lwf-fine": "Form_lwf_fine",
    'form-maternity-a': 'Form_Maternity A',
    'form-mw-damage-loss': 'Form_MW Damage Loss',
    'form-se-leave-register': 'Form_SE Leave Register Form I',
    'form-se-leave-register': 'Form_SE Leave Register Form J',
    'form-se-lime-washing': 'Form_SE Lime Washing',
    'form-se-musterroll': 'Form_SE_Muster Cum Wage',
    'form-various-act': 'Form_variousacts',

    //Uttar Pradesh
    'form-advance': 'Form_Advance',
    'form-child-labour': 'Form_Child_Labour',
    'form-clra': 'Form_CLRA',
    'form-maternity-a': 'Form_Maternity_A',
    'form-mw-damage': 'Form_MW_Damage',
    'form-mw-fine': 'Form_MW_Fine',
    'form-mw-musterroll': 'Form_MW_Musterroll',
    'form-mw-ot-register': 'Form_MW_OT_Register',
    'form-mw-wage-register': 'Form_MW_Wage_Register',
    'form-mw-wage-slip': 'Form_MW_Wage_Slip',
    'form-n&f-register': 'Form_N&f_Register',
    'form-se-damage-loss-form-d': 'Form_SE_Damage_Loss_Form_D',
    'form-se-damage-loss-form-e': 'Form_SE_Damage_Loss_Form_E',
    'form-se-leave-register': 'Form_SE_Leave_Register',
    'form-se-mustercumwage': 'Form_SE_Muster_Cum_Wage',
    'form-variousacts': 'Form_Variousacts',

    //Uttarkhand
    'form-advance': 'Form_Advance',
    'form-child-labour': 'Form_Child_Labour',
    'form-clra': 'Form_CLRA',
    'form-maternity-a': 'Form_Maternity_A',
    'form-mw-damage': 'Form_MW_Damage',
    'form-mw-fine': 'Form_MW_Fine',
    'form-mw-musterroll': 'Form_MW_Musterroll',
    'form-mw-ot-register': 'Form_MW_OT_Register',
    'form-mw-wage-slip': 'Form_MW_Wage_Slip',
    'form-n&f-register': 'Form_N&f_Register',
    'form-se-damage-loss-form-d': 'Form_SE_Damage_Loss_Form_D',
    'form-se-damage-loss-form-e': 'Form_SE_Damage_Loss_Form_E',
    'form-se-leave-register': 'Form_SE_Leave_Register',
    'form-se-mustercumwage': 'Form_SE_Muster_Cum_Wage',
    'form-variousacts': 'Form_Variousacts',

    //Karnataka
    'form-child': 'Form_child',
    'form-clra': 'Form_CLRA',
    'form-combined-register': 'Form_Combined_Register',
    'form-lwf-fine-register': 'Form_LWF_Fine_Register',
    'form-lwf-wage-register': 'Form_LWF_Wage_Register',
    'form-maternity-a': 'Form_Maternity_A',
    'form-mw-wage-slips': 'Form_MW_Wage_Slips',
    'form-se-leave-register-f': 'Form_SE_Leave_Register_F',
    'form-se-leave-register-h': 'Form_SE_Leave_Register_H',
    'form-se-mustercumwage-part1': 'Form_SE_Mustercumwage_Part1',
    'form-se-mustercumwage-part2': 'Form_SE_Mustercumwage_Part2',
    'form-suspension-register': 'Form_Suspension_Register',
    'form-various-act': 'Form_Various_Act',

    //Maharashtra
    'form-child': 'Form_child',
    'form-clra': 'Form_CLRA',
    'form-hra-a': 'Form_HRA_Form-A',
    'form-hra-i': 'Form_HRA_Form_I',
    'form-lwf-wage': 'Form_LWF_Wage',
    'form-maternity-register': 'Form_Maternity_Register',
    'form-mw-wage-slip': 'Form_MW_Wage_Slip',
    'form-advance-register': 'Form_Advance_Register',
    'form-se-leave-accumulation': 'Form_SE_Leave_Accumulation',
    'form-se-leave-book': 'Form_SE_Leave_Book',
    'form-se-mustercumwage-form Q-part-i': 'Form_SE_MusterCumWage_Form_Q_Part_I',
    'form-se-mustercumwage-form Q-part-ii': 'Form_SE_MusterCumWage_Form_Q_Part_II',
    'form-se-mustercumwage-part-i': 'Form_SE_MusterCumWage_Part_I',
    'form-se-mustercumwage-part-ii': 'Form_SE_MusterCumWage_Part_II',
    'form-various-act': 'Form_Various_Act',
    'form-lwf-fine': 'LWF Fine Form C',


    //Telangana
    'form-advance-register': 'Advance Register',
    'form-child': 'Child',
    'form-clra': 'CLRA',
    'form-lwf-fine-register': 'LWF Fine Register',
    'form-lwf-wage-register': 'LWF Wage Register',
    'form-maternity-a': 'Maternity A',
    'form-mw-ot-register': 'MW Ot Register',
    'form-mw-deductions': 'MW Register of Deductions',
    'form-mw-fine': 'MW Register of Fines',
    'form-mw-wage-register': 'MW Wage Register',
    'form-mw-wage-slip': 'MW Wage Slip',
    'form-mw-musterroll': 'MW_Musterroll',
    'form-nf-register': 'N&F Register',
    'form-se-damage': 'S&E Damage or Loss',
    'form-se-fine-register': 'SE Fine Register',
    'form-se-leave-register': 'SE Leave Register',
    'form-se-musterroll': 'SE Musterroll',
    'form-se-wage-register': 'SE Wage Register',
    'form-variousacts-compliance': 'Various Act Ease Compliance',
    'form-variousacts-formii': 'Various Act Integrated_FormII',
    'form-variousacts-formiii': 'Various Act Integrated_FormIII',

    //Kerala
    'form-child-labour': 'Child Labour',
    'form-clra': 'CLRA',
    'form-maternity-a': 'Maternity A',
    'form-mw-wage-slip': 'MW Wage Slip',
    'form-mw-damage': 'MW_Damage',
    'form-mw-fine': 'MW_Fine',
    'form-mw-musterroll': 'MW_Musterroll',
    'form-mw-wage-register': 'MW_Wage Register',
    'form-nf-musterroll': 'N&F Musterroll',
    'form-ot-register': 'OT Register',
    'form-se-leave-register': 'SE Leave Register',
    'form-se-musterroll': 'SE Musterroll',
    'form-se-service-card': 'S&E Service card',
    'form-se-advance': 'SE Advance',
    'form-variousacts': 'Variousacts',

    //Tamilnadu 
    'form-child-labour': 'Child Labour',
    'form-clra': 'CLRA',
    'form-conf-per-workmen-register': 'Conf.Per.Workmen Register',
    'form-lwf-wage': 'LWF Wage',
    'form-maternity-a': 'Maternity A',
    'form-mw-damage': 'MW_Damage',
    'form-mw-fine': 'MW_Fine',
    'form-mw-musterroll': 'MW_Musterroll',
    'form-mw-ot-register': 'MW OT Register',
    'form-mw-wage-register': 'MW_Wage Register',
    'form-mw-workman': 'MW Workman',
    'form-nf-register': 'N&F Register',
    'form-se-employee-register': 'SE Employee Register',
    'form-se-leave-register': 'SE Leave Register',
    'form-se-musterroll': 'SE Musterroll',
    'form-se-notice': 'SE Notice Working Hours',
    'form-se-wage': 'SE Wage',
    'form-se-wage-slip': 'SE Wage Slip',
    'form-variousacts': 'Variousacts',

    //Odisha
    'form-child-labour': 'Form_Child',
    'form-clra': 'Form_CLRA',
    'form-lwf-fine-register-appendix': 'Form_LWF_FineRegister_Appendix',
    'form-lwf-fine-register-e': 'Form_LWF_Fine_Register_E',
    'form-lwf-wage-register': 'Form_LWF_Wage_Register',
    'form-maternity-register': 'Form_Maternity_Register',
    'form-sea-leave-register': 'Form_SEA_Leave_Register',
    'form-sea-mustercumwage': 'Form_SEA_MusterCumWage',
    'form-sea-ot-register': 'Form_SEA_OT_Register',
    'form-variousact': 'Form_Various_Act',

    //Jammu & Kashmir
    'form-child-labour': 'Form_Child',
    'form-clra': 'Form_CLRA',
    'form-maternity-register': 'Form_Maternity_Register',
    'form-mw-fine': 'Form_MW_Fine',
    'form-mw-damage': 'Form_MW_Damage',
    'form-mwa-service-card': 'Form_MWA_Service_Card',
    'form-advance-register': 'Form_Advance_Register',
    'form-sea-leave-register': 'Form_SEA_Leave_Register',
    'form-sea-leave-book': 'Form_SEA_Leave_book',
    'form-sea-lime-washing': 'Form_SEA_Lime_Washing',
    'form-sea-mustercumwage': 'Form_SEA_MusterCumWage',
    'form-variousact': 'Form_Various_Act',

    //West Bengal
    'form-child': 'Child.php',
    'form-clra': 'CLRA.php',
    'form-hra': 'HRA Form A.php',
    'form-lwf-fine': 'LWF Fine Form B.php',
    'form-lwf-wage': 'LWF Wage Form A.php',
    'form-maternity-a': 'Maternity A.php',
    'form-mw-fine': 'MW Fine Form I.php',
    'form-mw-wage': 'MW Wage Form IX.php',
    'form-mwa-damage': 'MWA Damage Form II.php',
    'form-mwa-musterroll': 'MWA Musterroll.php',
    'form-mwa-ot': 'MWA OT Register.php',
    'form-mwa-wage-slip': 'MWA Wage slip.php',
    'form-mwa-workman': 'MWA Workman Register.php',
    'form-pwa-advance': 'PWA Advance.php',
    'form-se-musterroll': 'SE Musterroll.php',
    'form-sea-leave-register': 'SEA Leave Register.php',
    'form-sea-musterroll': 'SEA Musterroll.php',
    'form-sea-ot-register': 'SEA OT Register.php',
    'form-sea-wage': 'SEA Wage.php',
    'form-sea-workman-register': 'SEA Workman Register.php',
    'form-variousacts': 'Variousacts.php',
    'form-web-workman': 'WEB Workman.php',

    //Tripura
    'form-child': 'Child.php',
    'form-clra': 'CLRA.php',
    'form-maternity-a': 'Maternity A.php',
    'form-mwa-fine': 'MWA Fine Form I.php',
    'form-mwa-damage': 'MWA Damage Form II.php',
    'form-se-musterroll': 'SE Musterroll.php',
    'form-sea-ot-register': 'SEA OT Register.php',
    'form-sea-workman-register': 'SEA Workman Register.php',
    'form-sea-leave-register': 'SEA Leave Register.php',
    'form-sea-wage': 'SEA Wage.php',
    'form-variousacts': 'Variousacts.php',

    //Manipur
    'form-child': 'Child.php',
    'form-clra': 'CLRA.php',
    'form-maternity-a': 'Maternity A.php',
    'form-mw-ot': 'MW OT Register.php',
    'form-mw-wage': 'MW Wage.php',
    'form-mwa-fine': 'MWA Fine Form I.php',
    'form-mwa-damage': 'MWA Damage Form II.php',
    'form-mwa-musterroll': 'MWA Musterroll.php',
    'form-sea-attendance-register': 'SEA Attendance Register.php',
    'form-sea-fine-register': 'SEA Fine Register.php',
    'form-sea-leave-register': 'SEA Leave Register.php',
    'form-sea-wage-register': 'SEA Wage Register.php',
    'form-variousacts': 'Variousacts.php',

    //Pondicherry
    'form-child': 'Child.php',
    'form-clra': 'CLRA.php',
    'form-maternity-a': 'Maternity A.php',
    'form-mw-ot': 'MW OT Register.php',
    'form-n&f-register': 'N&F Register.php',
    'form-se-advance': 'SE Advance Register.php',
    'form-se-musterroll': 'SE Musterroll.php',
    'form-se-wage-register': 'SE Wage Register.php',
    'form-variousacts': 'Variousacts.php',

    //Sikkim
    'form-child': 'Child.php',
    'form-clra': 'CLRA.php',
    'form-maternity-a': 'Maternity A.php',
    'form-mw-wage': 'MW Wage Form IX.php',
    'form-mwa-fine': 'MWA Fine Form I.php',
    'form-mwa-damage': 'MWA Damage Form II.php',
    'form-mwa-ot': 'MWA OT Register.php',
    'form-mwa-musterroll': 'MWA Musterroll.php',
    'form-mwa-workman': 'MWA Workman Register.php',
    'form-variousacts': 'Variousacts.php',

    //Mizoram
    'form-child': 'Form_Child',
    'form-clra': 'Form_CLRA',
    'form-h-overtime-register': 'Form_H_Overtime_Register',
    'form-mb-musterroll-form-a': 'Form_MB_Musterroll_Form A',
    'form-mw-wage-register': 'Form_MW_Wage_Register',
    'form-mwa-fine': 'Form_MWA_Fine',
    'form-mw-damage-or-loss': 'Form_MW_Damage_or_Loss',
    'form-mw-ot-register': 'Form_MW_OT_Register',
    'form-mw-musterroll': 'Form_MW_Musterroll',
    'form-se-musterroll': 'Form_SE_Musterroll',
    'form-se-lime-washing': 'Form_SE_Lime Washing',
    'form-se-workmen-register': 'Form_SE_Workmen Register',
    'form-se-leave-register': 'Form_SE_Leave Register',
    'form-variousact': 'Form_Various_act',

    //Meghalaya
    'form-child': 'Form_Child',
    'form-clra': 'Form_CLRA',
    'form-mb-musterroll-form-a': 'Form_MB_Musterroll_Form A',
    'form-mw-wage-register': 'Form_MW_Wage Register',
    'form-mwa-fine': 'Form_MWA Fine',
    'form-mw-damage-or-loss': 'Form_MW_Damage or Loss',
    'form-mw-ot-register': 'Form_MW_OT Register',
    'form-mw-musterroll': 'Form_MW_Musterroll',
    'form-se-musterroll': 'Form_SE_Musterroll',
    'form-se-ot-register': 'Form_SE_OT Register',
    'form-se-lime-washing': 'Form_SE_Lime Washing',
    'form-se--register-of-employment': 'Form_SE_Register Of Employment',
    'form-se-leave-register': 'Form_SE_Leave Register',
    'form-variousact': 'Form_Various act',

    //Nagaland
    'form-child': 'Form_Child',
    'form-clra': 'Form_CLRA',
    'form-mb-musterroll-form-a': 'Form_MB_Musterroll_Form A',
    'form-mw-wage-register': 'Form_MW_Wage_Register',
    'form-mwa-fine': 'Form_MWA_Fine',
    'form-mw-damage-or-loss': 'Form_MW_Damage_or_Loss',
    'form-mw-ot-register': 'Form_MW_OT_Register',
    'form-mw-musterroll': 'Form_MW_Musterroll',
    'form-variousact': 'Form_Various_act',

    //Arunachal Pradesh
    'form-child': 'Form_Child',
    'form-clra': 'Form_CLRA',
    'form-mb-musterroll-form-a': 'Form_MB_Musterroll_Form A',
    'form-mw-wage-register': 'Form_MW_Wage_Register',
    'form-mwa-fine': 'Form_MWA_Fine',
    'form-mw-damage-or-loss': 'Form_MW_Damage_or_Loss',
    'form-mw-ot-register': 'Form_MW_OT_Register',
    'form-mw-musterroll': 'Form_MW_Musterroll',
    'form-variousact': 'Form_Various_act',

    // Chandigarh forms
    "form-a": "Form_A",
    "form-ii-a": "Form_II_A",
    "form-xii": "Form_XII",
    "form-lwf-fine": "Form_LWF_Fine",
    "form-lwf-wage": "Form_LWF_Wage",
    "form-mb-act": "Form_MB_Act",
    "form-mw-damage": "Form_MW_Damage",
    "form-mw-fine": "Form_MW_Fine",
    "form-mw-musterroll": "Form_MW_MusterRoll",
    "form-mw-wage": "Form_MW_Wage",
    "form-ot-register": "Form_OT_Register",
    "form-se-damage": "Form_SE_Damage",
    "form-se-musterroll": "Form_SE_MusterRoll",
    "form-se-wage": "Form_SE_Wage",
    "form-various-acts": "Form_Various_Acts",


    //Lakshadweep
    'form-clra': 'Form_CLRA',
    'form-maternity-form-a': 'Form_Maternity_Form_A',
    'form-mw-wage-register': 'Form_MW_Wage_Register',
    'form-mwa-fine': 'Form_MWA_Fine',
    'form-mw-damage-or-loss': 'Form_MW_Damage_or_Loss',
    'form-mw-ot-register': 'Form_MW_OT_Register',
    'form-mw-musterroll': 'Form_MW_Musterroll',
    'form-variousact': 'Form_Various_act',

    //Andaman and Nicobar
    'form-child': 'Child.php',
    'form-clra': 'CLRA.php',
    'form-act-form-a': 'MB Act Form A.php',
    'form-mwa-fine': 'MWA Fine.php',
    'form-mwa-damage-or-loss': 'MWA Damage or Loss.php',
    'form-se-workmen-register': 'SE Workmen Register.php',
    'form-se-wage': 'SE Wage.php',
    'form-se-ot-register': 'SE OT Register.php',
    'form-sw-musterroll': 'SW Musterroll.php',
    'form-variousact': 'Various Act.php',

    //Dadra and nagra haveli
    'form-child': 'Form_Child',
    'form-clra': 'Form_CLRA',
    'form-register-of-leave': 'Form_Register_Of_Leave',
    'form-mb-musterroll-form-a': 'Form_MB_Musterroll_Form_A',
    'form-mwa-ot-register': 'Form_MWA_OT_Register',
    'form-se-fine-register': 'Form_SE_Fine_Register',
    'form-se-lime-washinng': 'Form_SE_Lime_Washing',
    'form-se-damage-or-loss': 'Form_SE_Damage_or_Loss',
    'form-advance-register': 'Form_Advance Register',
    'form-se-musterroll': 'Form_SE_Musterroll',
    'form-se-wage-register': 'Form_SE_Wage_Register',
    'form-variousact': 'Form_Various_act',

    //Ladakh
    'form-child': 'Child_Labour.php',
    'form-clra': 'CLRA.php',
    'form-mb-musterroll-form-a': 'MB_Musterroll_Form_A.php',
    'form-mw-wage-register': 'MW_Wage_Register.php',
    'form-mwa-fine': 'MWA_Fine.php',
    'form-mw-damage-or-loss': 'MW_Damage_or_Loss.php',
    'form-mw-ot-register': 'MW_OT_Register.php',
    'form-mw-musterroll': 'MW_Musterroll.php',
    'form-variousact': 'Various_Act.php',
    'form-sea-leave-register': 'SEA_Leave_Register.php',
    'form-sea-leave-book': 'SEA_Leave_book.php',
    'form-sea-lime-washing': 'SEA_Lime_Washing.php',
    'form-sea-mustercumwage': 'SEA_Muster_Cum_Wage.php',
};


async function downloadForm(formId, state, locationCode) {
    const element = document.getElementById(`${state.toLowerCase()}-${formId}_${locationCode}`);
    if (!element) {
        console.error("Form not found: " + formId + " for location " + locationCode);
        return { success: false, message: 'Form node not found' };
    }

    const clone = element.cloneNode(true);
    clone.style.width = '100%';
    clone.style.overflow = 'visible';

    try {
        const payload = {
            formId,
            state,
            clientName: formStateData.clientName,
            locationCode,
            monthYear: formStateData.monthYear,
            htmlContent: clone.outerHTML
        };

        console.log('Saving PDF for:', formId, state, locationCode);
        const response = await fetch('save_pdf.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const result = await response.json();
        console.log('Save PDF response:', result);

        if (!result.success) {
            console.warn("savepdf failed for", formId, result.message);
        }
        return result;
    } catch (error) {
        console.error("Error in downloadForm:", error);
        return { success: false, message: error.message || 'Fetch failed' };
    }
}

function showDownloadComplete() {
    const popup = document.createElement('div');
    popup.style.position = 'fixed';
    popup.style.top = '20px';
    popup.style.right = '20px';
    popup.style.backgroundColor = '#4CAF50';
    popup.style.color = 'white';
    popup.style.padding = '15px';
    popup.style.borderRadius = '5px';
    popup.style.boxShadow = '0 4px 8px rgba(0,0,0,0.2)';
    popup.style.zIndex = '10000';
    popup.style.transition = 'opacity 0.5s';
    popup.textContent = 'All forms processed — ZIP ready for download.';

    document.body.appendChild(popup);

    setTimeout(() => {
        popup.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(popup);
        }, 500);
    }, 3000);
}

function showDownloadProgress(current, total) {
    let progress = document.getElementById('download-progress-popup');

    if (!progress) {
        progress = document.createElement('div');
        progress.id = 'download-progress-popup';
        progress.style.position = 'fixed';
        progress.style.top = '20px';
        progress.style.right = '20px';
        progress.style.backgroundColor = '#2196F3';
        progress.style.color = 'white';
        progress.style.padding = '15px';
        progress.style.borderRadius = '5px';
        progress.style.boxShadow = '0 4px 8px rgba(0,0,0,0.2)';
        progress.style.zIndex = '10000';
        document.body.appendChild(progress);
    }

    progress.textContent = `Generating PDFs... ${current} of ${total}`;
}

async function createZipOnServer(filePaths, clientName, folderMonthYear) {
    try {
        console.log('Creating ZIP with files:', filePaths);

        const response = await fetch('create_zip.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                files: filePaths,
                clientName,
                folderMonthYear
            })
        });

        if (!response || !response.ok) {
            throw new Error('Server response error');
        }

        const result = await response.json();
        console.log('ZIP creation result:', result);
        return result;
    } catch (err) {
        console.error('createZipOnServer error:', err);
        return { success: false, message: err.message || 'Zip request failed' };
    }
}

async function downloadAllForms() {
    const downloadBtn = document.getElementById('downloadAllBtn');
    const originalText = downloadBtn.textContent;
    downloadBtn.disabled = true;
    downloadBtn.textContent = 'Processing...';

    const formsToDownload = [];

    for (const state of formStateData.selectedStates) {
        const statePrefix = state.toLowerCase() + '-';

        for (const locationCode of formStateData.locationCodes) {
            const locationSuffix = '_' + locationCode;
            const stateForms = Array.from(document.querySelectorAll(
                `[id^="${statePrefix}"][id$="${locationSuffix}"]`
            ));

            if (stateForms.length > 0) {
                const formIds = [...new Set(stateForms.map(el => {
                    const fullId = el.id;
                    return fullId.replace(statePrefix, '').replace(locationSuffix, '');
                }))];

                formIds.forEach(formId => {
                    formsToDownload.push({ state, locationCode, formId });
                });
            }
        }
    }

    const totalForms = formsToDownload.length;
    console.log('Forms to download:', formsToDownload);

    if (totalForms === 0) {
        downloadBtn.disabled = false;
        downloadBtn.textContent = originalText;
        alert('No forms found to download');
        return;
    }

    showDownloadProgress(0, totalForms);

    // Process forms sequentially
    const serverPaths = [];

    for (let i = 0; i < formsToDownload.length; i++) {
        const item = formsToDownload[i];
        try {
            console.log(`Processing form ${i + 1}/${totalForms}:`, item);
            const result = await downloadForm(item.formId, item.state, item.locationCode);
            showDownloadProgress(i + 1, totalForms);

            if (result && result.success && result.server_relative_path) {
                serverPaths.push(result.server_relative_path);
                console.log(`✓ PDF generated: ${result.server_relative_path}`);
            } else {
                console.warn('✗ Failed to generate PDF for', item, result?.message);
            }
        } catch (error) {
            console.error('Error processing form', item, error);
        }
    }

    console.log('All PDF processing completed. Files:', serverPaths);

    if (serverPaths.length === 0) {
        alert('Failed to generate any PDFs.');
        downloadBtn.disabled = false;
        downloadBtn.textContent = originalText;
        return;
    }

    // Now create ZIP
    // Now create ZIP and download it
    console.log('Calling create_zip.php with', serverPaths.length, 'files');

    const zipRes = await createZipOnServer(
        serverPaths,
        formStateData.clientName,
        formStateData.monthYear || ''
    );

    console.log('ZIP creation response:', zipRes);

    if (zipRes && zipRes.success && zipRes.zip_url) {
        // Create temporary download link for ZIP file
        const a = document.createElement('a');
        a.href = zipRes.zip_url;
        a.download = zipRes.zip_filename || 'forms_bundle.zip';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        showDownloadComplete();

        // Optional: Clean up the server ZIP file after download
        setTimeout(() => {
            cleanupServerZip(zipRes.zip_path);
        }, 5000);
    } else {
        const errorMsg = zipRes?.message || 'Unknown error during ZIP creation';
        alert('Failed to create ZIP: ' + errorMsg);
        console.error('Zip creation failed:', zipRes);
    }

    downloadBtn.disabled = false;
    downloadBtn.textContent = originalText;

    // Clean up progress popup
    setTimeout(() => {
        const progress = document.getElementById('download-progress-popup');
        if (progress) document.body.removeChild(progress);
    }, 1500);
}

document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('downloadAllBtn');
    if (btn) btn.addEventListener('click', downloadAllForms);
});

// Optional: Clean up server ZIP files after download
async function cleanupServerZip(zipPath) {
    try {
        const response = await fetch('cleanup_zip.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ zip_path: zipPath })
        });
        const result = await response.json();
        if (result.success) {
            console.log('Server ZIP file cleaned up');
        }
    } catch (error) {
        console.log('Cleanup not essential, continuing...');
    }
}

