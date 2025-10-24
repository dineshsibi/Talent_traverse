// js/download_handler1.js  — FIXED VERSION

const formStateData = {
    clientName: document.querySelector('.client-header h1')?.textContent.trim() || '',
    monthYear: document.querySelector('.client-header h3')?.textContent.replace('Compliance Forms for ', '').trim() || ''
};

const formNames = {
    // Delhi Forms   
    'form-esi-form-11': 'Form_ESI_Form_11',
    'form-xiii': 'Form_XIII',
    'form-xiv': 'Form_XIV',
    'form-xix': 'Form_XIX',
    'form-xv': 'Form_XV',
    'form-xvi': 'Form_XVI',
    'form-xx': 'Form_XX',
    'form-xxi': 'Form_XXI',
    'form-xxii': 'Form_XXII',
    'form-xxiii': 'Form_XXIII',
    'form-a': 'Form_A',
    'form-b': 'Form_B',
    'form-c': 'Form_C',
    'form-d': 'Form_D',
    'form-mba-form-a': 'Form_MBA_Form_A',
    'form-sea-form-g': 'Form_SEA_Form_G',

    // Haryana Forms
    'form-esi-form-11': 'Form_ESI_Form_11',
    'form-9': 'Form_9',
    'form-xiii': 'Form_XIII_Register_of_Wages',
    'form-xiv': 'Form_XIV',
    'form-xix-overtime': 'Form_XIX_Overtime',
    'form-xix': 'Form_XIX',
    'form-xv': 'Form_XV',
    'form-a': 'Form_A_Part_A',
    'form-b': 'Form_B',
    'form-c': 'Form_C',
    'form-d': 'Form_D',
    'form-mba-form-a': 'Form_MBA_Form_A',

    //Karnataka Forms
    'form-esi': 'ESI_Form_11',
    'form-xiii': 'Form XIII',
    'form-xiv': 'Form XIV',
    'form-xix': 'Form XIX',
    'form-xv': 'Form XV',
    'form-xvi': 'Form XVI',
    'form-a': 'Form_A',
    'form-b': 'Form_B',
    'form-c': 'Form_C',
    'form-d': 'Form_D',
    'form-mba-form-a': 'MBA Form_A',
    'form-xiv-wage-cum-musterroll': 'Form XIV Wage Cum Musterroll',
    'form-xvii-wage-register': 'Form XVII Wage Register',
    'form-xii-register-of-contractors': 'Form XII_Register of Contractors',

    //Rajasthan
    'form-x-employment-card': 'Form X_Employment Card',
    'form-xii-register-of-contractor': 'Form XII_Register Of Contractors',
    'form-xiv-employement-card': 'Form XIV_Employment Card',
    'form-xix-wage-slip': 'Form XIX_Wage Slip',
    'form-xv-service-certificate': 'Form XV_Service Certificate',
    'form-a-employee-register': 'Form A_Employee Register',
    'form-b-wage-register': 'Form B_Wage Register',
    'form-c-loan-recoveries': 'Form C_Loan Recoveries',
    'form-d-attendance-register': 'Form D_Attendance Register',
    'form-esi': 'ESI Form 11_AccidentBook',
    'form-mba-maternity': 'MBA Form A_Maternity',

    //Tamilnadu
    'form-xii-register-of-contractor': 'Form XII_Register Of Contractors',
    'form-xiv-employement-card': 'Form XIV_Employment Card',
    'form-xix-wage-slip': 'Form XIX_Wage Slip',
    'form-xv-service-certificate': 'Form XV_Service Certificate',
    'form-a-employee-register': 'Form A_Employee Register',
    'form-b-wage-register': 'Form B_Wage Register',
    'form-c-loan-recoveries': 'Form C_Loan Recoveries',
    'form-d-attendance-register': 'Form D_Attendance Register',
    'form-esi': 'ESI Form 11_AccidentBook',
    'form-mba-maternity': 'MBA Form A_Maternity',
    'form-t-wage-slip': 'Form T Wage Slip',
    'form-xxix-damage-loss': 'Form XXIX Damage or Loss',
    'form-xxvi-register-of-employees': 'Form XXVI Register of Employees',
    'form-xxviii-wage-slip': 'Form XXVIII Wage Slip',
    'form-xxvii-wage-register': 'Form XXVII Wage Register',

    //Gujarat
    'form-xii-register-of-contractor': 'Form XII_Register Of Contractors',
    'form-xiv-employement-card': 'Form XIV_Employment Card',
    'form-xix-wage-slip': 'Form XIX_Wage Slip',
    'form-xv-service-certificate': 'Form XV_Service Certificate',
    'form-a-employee-register': 'Form A_Employee Register',
    'form-b-wage-register': 'Form B_Wage Register',
    'form-c-loan-recoveries': 'Form C_Loan Recoveries',
    'form-d-attendance-register': 'Form D_Attendance Register',
    'form-esi': 'ESI Form 11_AccidentBook',
    'form-mba-maternity': 'MBA Form A_Maternity',

    //Assam
    'form-xii-register-of-contractor': 'Form XII_Register Of Contractors',
    'form-xiv-employement-card': 'Form XIV_Employment Card',
    'form-xix-wage-slip': 'Form XIX_Wage Slip',
    'form-xv-service-certificate': 'Form XV_Service Certificate',
    'form-a-employee-register': 'Form A_Employee Register',
    'form-b-wage-register': 'Form B_Wage Register',
    'form-c-loan-recoveries': 'Form C_Loan Recoveries',
    'form-d-attendance-register': 'Form D_Attendance Register',
    'form-esi': 'ESI Form 11_AccidentBook',
    'form-mba-maternity': 'MBA Form A_Maternity',
    'form-xxvii-wage-register': 'Form XXVII Wage Register',
    'form-xiii': 'Form XIII',
    'form-xvi': 'Form XVI',
    'form-xx': 'Form XX',
    'form-xxi': 'Form XXI',
    'form-xxii': 'Form XXII',
    'form-xxiii': 'Form XXIII',

    //Chattisgarh
    'form-xii-register-of-contractor': 'Form XII_Register Of Contractors',
    'form-xiv-employement-card': 'Form XIV_Employment Card',
    'form-xix-wage-slip': 'Form XIX_Wage Slip',
    'form-xv-service-certificate': 'Form XV_Service Certificate',
    'form-a-employee-register': 'Form A_Employee Register',
    'form-b-wage-register': 'Form B_Wage Register',
    'form-c-loan-recoveries': 'Form C_Loan Recoveries',
    'form-d-attendance-register': 'Form D_Attendance Register',
    'form-esi': 'ESI Form 11_AccidentBook',
    'form-mba-maternity': 'MBA Form A_Maternity',
    'form-xxvii-wage-register': 'Form XXVII Wage Register',
    'form-xiii': 'Form XIII',
    'form-xvi': 'Form XVI',
    'form-xx': 'Form XX',
    'form-xxi': 'Form XXI',
    'form-xxii': 'Form XXII',
    'form-xxiii': 'Form XXIII',

    //Goa
    'form-ix-register-of-workman': 'Form IX Register of Workman',
    'form-x-employee-card': 'Form X Employee Card',
    'form-xii-musterroll': 'Form XII Musterroll',
    'form-xiii-register-of-wages': 'Form XIII Register of Wages',
    'form-xiv-employement-card': 'Form XIV_Employment Card',
    'form-xiv-wages-cum-musterroll': 'Form XIV Wages Cum Musterroll',
    'form-xv-service-certificate': 'Form XV_Service Certificate',
    'form-xix-wage-slip': 'Form XIX_Wage Slip',
    'form-xvii-register-of-fines': 'Form XVII Register of Fines',
    'form-x-damages': 'Form X Damages',
    'form-xviii-register-of-advance': 'Form XVIII Register of Advance',
    'form-xvii-wage-register': 'Form XVII Wage Register',
    'form-a-employee-register': 'Form A_Employee Register',
    'form-b-wage-register': 'Form B_Wage Register',
    'form-c-loan-recoveries': 'Form C_Loan Recoveries',
    'form-d-attendance-register': 'Form D_Attendance Register',
    'form-esi': 'ESI Form 11_AccidentBook',
    'form-mba-maternity': 'MBA Form A_Maternity',

    //Himachal Pradesh
    'form-xiv-employement-card': 'Form XIV_Employment Card',
    'form-xix-wage-slip': 'Form XIX_Wage Slip',
    'form-xv-service-certificate': 'Form XV_Service Certificate',
    'form-a-employee-register': 'Form A_Employee Register',
    'form-b-wage-register': 'Form B_Wage Register',
    'form-c-loan-recoveries': 'Form C_Loan Recoveries',
    'form-d-attendance-register': 'Form D_Attendance Register',
    'form-esi': 'ESI Form 11_AccidentBook',
    'form-mba-maternity': 'MBA Form A_Maternity',
    'form-xxvii-wage-register': 'Form XVII Wage Register',
    'form-xiii': 'Form XIII',
    'form-xvi': 'Form XVI',
    'form-xx': 'Form XX',
    'form-xxi': 'Form XXI',
    'form-xxii': 'Form XXII',

    //Kerala
    'form-xii-register-of-contractor': 'Form XII_Register Of Contractors',
    'form-xiv-employement-card': 'Form XIV_Employment Card',
    'form-xix-wage-slip': 'Form XIX_Wage Slip',
    'form-xv-service-certificate': 'Form XV_Service Certificate',
    'form-a-employee-register': 'Form A_Employee Register',
    'form-b-wage-register': 'Form B_Wage Register',
    'form-c-loan-recoveries': 'Form C_Loan Recoveries',
    'form-d-attendance-register': 'Form D_Attendance Register',
    'form-esi': 'ESI Form 11_AccidentBook',
    'form-mba-maternity': 'MBA Form A_Maternity',
    'form-xxvii-wage-register': 'Form XXVII Wage Register',
    'form-xiii': 'Form XIII',
    'form-xvi': 'Form XVI',
    'form-xx': 'Form XX',
    'form-xxi': 'Form XXI',
    'form-xxii': 'Form XXII',
    'form-xxiii': 'Form XXIII',

    //Madhya Pradesh
    'form-xii-register-of-contractor': 'Form XII_Register Of Contractors',
    'form-xiv-employement-card': 'Form XIV_Employment Card',
    'form-xix-wage-slip': 'Form XIX_Wage Slip',
    'form-xv-service-certificate': 'Form XV_Service Certificate',
    'form-a-employee-register': 'Form A_Employee Register',
    'form-b-wage-register': 'Form B_Wage Register',
    'form-c-loan-recoveries': 'Form C_Loan Recoveries',
    'form-d-attendance-register': 'Form D_Attendance Register',
    'form-esi': 'ESI Form 11_AccidentBook',
    'form-mba-maternity': 'MBA Form A_Maternity',
    'form-xxvii-wage-register': 'Form XXVII Wage Register',
    'form-xiii': 'Form XIII',
    'form-xvi': 'Form XVI',
    'form-xx': 'Form XX',
    'form-xxi': 'Form XXI',
    'form-xxii': 'Form XXII',
    'form-xxiii': 'Form XXIII',

    //Punjab
    'form-xii-register-of-contractor': 'Form XII_Register Of Contractors',
    'form-xiv-employement-card': 'Form XIV_Employment Card',
    'form-xix-wage-slip': 'Form XIX_Wage Slip',
    'form-xv-service-certificate': 'Form XV_Service Certificate',
    'form-a-employee-register': 'Form A_Employee Register',
    'form-b-wage-register': 'Form B_Wage Register',
    'form-c-loan-recoveries': 'Form C_Loan Recoveries',
    'form-d-attendance-register': 'Form D_Attendance Register',
    'form-esi': 'ESI Form 11_AccidentBook',
    'form-mba-maternity': 'MBA Form A_Maternity',
    'form-xxvii-wage-register': 'Form XXVII Wage Register',
    'form-xiii': 'Form XIII',
    'form-xvi': 'Form XVI',
    'form-xx': 'Form XX',
    'form-xxi': 'Form XXI',
    'form-xxii': 'Form XXII',
    'form-xxiii': 'Form XXIII',

    //Sikkim
    'form-xii-register-of-contractor': 'Form XII_Register Of Contractors',
    'form-xiv-employement-card': 'Form XIV_Employment Card',
    'form-xix-wage-slip': 'Form XIX_Wage Slip',
    'form-xv-service-certificate': 'Form XV_Service Certificate',
    'form-a-employee-register': 'Form A_Employee Register',
    'form-b-wage-register': 'Form B_Wage Register',
    'form-c-loan-recoveries': 'Form C_Loan Recoveries',
    'form-d-attendance-register': 'Form D_Attendance Register',
    'form-esi': 'ESI Form 11_AccidentBook',
    'form-xvii-wage-register': 'Form XVII Wage Register',
    'form-xiii': 'Form XIII',
    'form-xvi': 'Form XVI',
    'form-xxii': 'Form XXII',
    'form-xxiii': 'Form XXIII',
    'form-xiv-wage-cum-musterroll': 'Form XIV Wage Cum Musterroll',
    'form-xiii-wage-register': 'Form XIII Wage Register',

    //Uttar Pradesh
    'form-xii-register-of-contractor': 'Form XII_Register Of Contractors',
    'form-xiv-employement-card': 'Form XIV_Employment Card',
    'form-xix-wage-slip': 'Form XIX_Wage Slip',
    'form-xv-service-certificate': 'Form XV_Service Certificate',
    'form-a-employee-register': 'Form A_Employee Register',
    'form-b-wage-register': 'Form B_Wage Register',
    'form-c-loan-recoveries': 'Form C_Loan Recoveries',
    'form-d-attendance-register': 'Form D_Attendance Register',
    'form-esi': 'ESI Form 11_AccidentBook',
    'form-mba-maternity': 'MBA Form A_Maternity',
    'form-xxvii-wage-register': 'Form XXVII Wage Register',
    'form-xiii': 'Form XIII',
    'form-xvi': 'Form XVI',
    'form-xx': 'Form XX',
    'form-xxi': 'Form XXI',
    'form-xxii': 'Form XXII',
    'form-xxiii': 'Form XXIII',

    //West Bengal
    'form-xii-register-of-contractor': 'Form XII_Register Of Contractors',
    'form-xiv-employement-card': 'Form XIV_Employment Card',
    'form-xix-wage-slip': 'Form XIX_Wage Slip',
    'form-xv-service-certificate': 'Form XV_Service Certificate',
    'form-a-employee-register': 'Form A_Employee Register',
    'form-b-wage-register': 'Form B_Wage Register',
    'form-c-loan-recoveries': 'Form C_Loan Recoveries',
    'form-d-attendance-register': 'Form D_Attendance Register',
    'form-esi': 'ESI Form 11_AccidentBook',
    'form-mba-maternity': 'MBA Form A_Maternity',
    'form-xxvii-wage-register': 'Form XXVII Wage Register',
    'form-xiii': 'Form XIII',
    'form-xvi': 'Form XVI',
    'form-xx': 'Form XX',
    'form-xxi': 'Form XXI',
    'form-xxii': 'Form XXII',
    'form-xxiii': 'Form XXIII',

    //Telangana
    'form-xii-register-of-contractor': 'Form XII_Register Of Contractors',
    'form-xiii': 'Form XIII',
    'form-xiv-employement-card': 'Form XIV_Employment Card',
    'form-xv-service-certificate': 'Form XV_Service Certificate',
    'form-xix-wage-slip': 'Form XIX_Wage Slip',
    'form-xvi': 'Form XVI',
    'form-xxii': 'Form XXII',
    'form-xxiii': 'Form XXIII',
    'form-a-employee-register': 'Form A_Employee Register',
    'form-b-wage-register': 'Form B_Wage Register',
    'form-c-loan-recoveries': 'Form C_Loan Recoveries',
    'form-d-attendance-register': 'Form D_Attendance Register',
    'form-esi': 'ESI Form 11_AccidentBook',
    'form-xx': 'Form XX',
    'form-xxi': 'Form XXI',
    'form-xvii-register-of-fines': 'Form XVII Register of Fine',
    'form-xii-integrated-register': 'Form II Integrated Register',
    'form-xiii-integrated-register': 'Form III Integrated Register',

    //Maharashtra
    'form-xiv-employement-card': 'Form XIV_Employment Card',
    'form-xix-wage-slip': 'Form XIX_Wage Slip',
    'form-xv-service-certificate': 'Form XV_Service Certificate',
    'form-a-employee-register': 'Form A_Employee Register',
    'form-b-wage-register': 'Form B_Wage Register',
    'form-c-loan-recoveries': 'Form C_Loan Recoveries',
    'form-d-attendance-register': 'Form D_Attendance Register',
    'form-esi': 'ESI Form 11_AccidentBook',
    'form-mba-maternity': 'MBA Form A_Maternity',
    'form-xiii': 'Form XIII',
    'form-ii': 'Form II_Musterroll Cum Wages',
    'form-xii-register-of-contractor': 'Form XII_Register Of Contractors',
    'form-ix': 'Form IX_Register Of Workmen',
    'form-10-overtime': 'Form 10_Overtime Register',
    'form-attendance-card': 'Form MWA_Attendance Card',

    //Odisha
    'form-xvi': 'Form XVI',
    'form-xxiii': 'Form XXIII',
    'form-x-employment-card': 'Form X_Employment Card',
    'form-xiv-employement-card': 'Form XIV_Employment Card',
    'form-xix-wage-slip': 'Form XIX_Wage Slip',
    'form-xv-service-certificate': 'Form XV_Service Certificate',
    'form-a-employee-register': 'Form A_Employee Register',
    'form-b-wage-register': 'Form B_Wage Register',
    'form-c-loan-recoveries': 'Form C_Loan Recoveries',
    'form-d-attendance-register': 'Form D_Attendance Register',
    'form-esi': 'ESI Form 11_AccidentBook',
    'form-xii-register-of-contractor': 'Form XII_Register Of Contractors',
    'form-ix': 'Form IX_Register Of Workmen',
    'form-8-service': 'Form 8_Service And Leave Account',
    'form-12-register': 'Form 12_Register Of Workers',
    'form-10-combined-musterroll': 'Form 10_Combined Musterroll',
    'form-xiii-register-of-wage': 'Form XIII_Register Of Wages',

    //Andhaman and Nichobar
    'form-a-employee-register': 'Form A_Employee Register',
    'form-b-wage-register': 'Form B_Wage Register',
    'form-c-loan-recoveries': 'Form C_Loan Recoveries',
    'form-d-attendance-register': 'Form D_Attendance Register',
    'form-esi': 'ESI Form 11_AccidentBook',
    'form-mba-maternity': 'MBA Form A_Maternity',

    //Andhra Pradesh
    'form-a-employee-register': 'Form A_Employee Register',
    'form-b-wage-register': 'Form B_Wage Register',
    'form-c-loan-recoveries': 'Form C_Loan Recoveries',
    'form-d-attendance-register': 'Form D_Attendance Register',
    'form-esi': 'ESI Form 11_AccidentBook',
    'form-mba-maternity': 'MBA Form A_Maternity',

     //Arunchal Pradesh
    'form-a-employee-register': 'Form A_Employee Register',
    'form-b-wage-register': 'Form B_Wage Register',
    'form-c-loan-recoveries': 'Form C_Loan Recoveries',
    'form-d-attendance-register': 'Form D_Attendance Register',
    'form-esi': 'ESI Form 11_AccidentBook',
    'form-mba-maternity': 'MBA Form A_Maternity',

    //Bihar
    'form-a-employee-register': 'Form A_Employee Register',
    'form-b-wage-register': 'Form B_Wage Register',
    'form-c-loan-recoveries': 'Form C_Loan Recoveries',
    'form-d-attendance-register': 'Form D_Attendance Register',
    'form-esi': 'ESI Form 11_AccidentBook',
    'form-mba-maternity': 'MBA Form A_Maternity',

    //chandigarh
    'form-a-employee-register': 'Form A_Employee Register',
    'form-b-wage-register': 'Form B_Wage Register',
    'form-c-loan-recoveries': 'Form C_Loan Recoveries',
    'form-d-attendance-register': 'Form D_Attendance Register',
    'form-esi': 'ESI Form 11_AccidentBook',
    'form-mba-maternity': 'MBA Form A_Maternity',

};

document.addEventListener('DOMContentLoaded', function () {
    const downloadBtn = document.getElementById('downloadAllBtn');
    if (downloadBtn) downloadBtn.addEventListener('click', downloadAllForms);
});

async function downloadAllForms() {
    const downloadBtn = document.getElementById('downloadAllBtn');
    if (!downloadBtn) return;
    const originalText = downloadBtn.textContent;
    downloadBtn.disabled = true;
    downloadBtn.textContent = 'Processing...';

    const loadingIndicator = document.getElementById('loading-indicator');
    if (loadingIndicator) loadingIndicator.style.display = 'flex';

    try {
        // Get all forms to download
        const formsToDownload = [];
        
        // Get all form containers
        const forms = document.querySelectorAll('#all-forms-content .form-container');
        
        forms.forEach(form => {
            const dataset = form.dataset || {};
            let principalEmployer = (dataset.principal || '').trim();
            let state = (dataset.state || '').trim();
            let formId = (dataset.formid || '').trim();
            let locationCode = (dataset.location || '').trim();

            // Fallback parsing if dataset not present
            if (!formId || !state || !locationCode) {
                const id = form.id || '';
                const partsUnderscore = id.split('_');
                locationCode = partsUnderscore.length > 1 ? partsUnderscore.pop() : locationCode;
                const left = partsUnderscore.join('_');
                const dashParts = left.split('-');
                formId = dashParts.length > 0 ? dashParts.pop() : formId;
                state = dashParts.length > 0 ? dashParts.pop() : state;
                principalEmployer = dashParts.join('-').replace(/-/g, ' ').trim();
            }

            if (formId && state && locationCode) {
                formsToDownload.push({
                    formElement: form,
                    formId: formId,
                    principalEmployer: principalEmployer,
                    state: state,
                    locationCode: locationCode
                });
            }
        });

        const totalForms = formsToDownload.length;
        if (totalForms === 0) {
            alert('No forms found to download');
            return;
        }

        console.log('Forms to download:', formsToDownload);
        showDownloadProgress(0, totalForms);

        // Process forms sequentially and collect server paths
        const serverPaths = [];

        for (let i = 0; i < formsToDownload.length; i++) {
            const item = formsToDownload[i];
            try {
                console.log(`Processing form ${i + 1}/${totalForms}:`, item);
                const result = await downloadForm(
                    item.formId, 
                    item.principalEmployer, 
                    item.state, 
                    item.locationCode, 
                    item.formElement
                );
                
                showDownloadProgress(i + 1, totalForms, item.formId, item.locationCode);

                if (result && result.success && result.server_relative_path) {
                    serverPaths.push(result.server_relative_path);
                    console.log(`✓ PDF generated: ${result.server_relative_path}`);
                } else {
                    console.warn('✗ Failed to generate PDF for', item, result?.message);
                }
                
                await new Promise(r => setTimeout(r, 300)); // Small delay between requests
            } catch (error) {
                console.error('Error processing form', item, error);
            }
        }

        console.log('All PDF processing completed. Files:', serverPaths);

        if (serverPaths.length === 0) {
            alert('Failed to generate any PDFs.');
            return;
        }

        // Create ZIP file
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
            a.download = zipRes.zip_filename || 'clra_forms_bundle.zip';
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

    } catch (err) {
        console.error('Download error:', err);
        alert('An error occurred during download. Check the console.');
    } finally {
        downloadBtn.disabled = false;
        downloadBtn.textContent = originalText;
        if (loadingIndicator) loadingIndicator.style.display = 'none';
        
        // Clean up progress popup
        setTimeout(() => {
            const progress = document.getElementById('download-progress-popup');
            if (progress) document.body.removeChild(progress);
        }, 1500);
    }
}

async function downloadForm(formId, principalEmployer, state, locationCode, formElement) {
    const clone = formElement.cloneNode(true);
    clone.style.width = '100%';
    clone.style.overflow = 'visible';

    try {
        const payload = {
            formId: formId,
            state: state,
            clientName: formStateData.clientName,
            principalEmployer: principalEmployer,
            locationCode: locationCode,
            monthYear: formStateData.monthYear,
            htmlContent: clone.outerHTML
        };

        console.log('Saving CLRA PDF for:', formId, state, locationCode);
        const response = await fetch('save_pdf1.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const result = await response.json();
        console.log('Save CLRA PDF response:', result);
        return result;
        
    } catch (error) {
        console.error("Error in downloadForm:", error);
        return { success: false, message: error.message || 'Fetch failed' };
    }
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

function showDownloadProgress(current, total, formId = '', locationCode = '') {
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

    if (formId && locationCode) {
        progress.textContent = `Processing ${current}/${total}: ${formId} — ${locationCode}`;
    } else {
        progress.textContent = `Generating PDFs... ${current} of ${total}`;
    }
    
    return progress;
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
    popup.textContent = 'All CLRA forms processed — ZIP ready for download.';

    document.body.appendChild(popup);

    setTimeout(() => {
        popup.style.opacity = '0';
        setTimeout(() => {
            if (popup.parentNode) document.body.removeChild(popup);
        }, 500);
    }, 3000);
}

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