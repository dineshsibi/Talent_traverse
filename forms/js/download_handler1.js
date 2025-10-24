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
        const forms = document.querySelectorAll('#all-forms-content .form-container');
        const totalForms = forms.length;
        if (totalForms === 0) {
            alert('No forms found to download');
            return;
        }

        const progressEl = showDownloadProgress(0, totalForms);

        for (let i = 0; i < forms.length; i++) {
            const form = forms[i];

            // Prefer explicit data attributes
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

            updateDownloadProgress(progressEl, i + 1, totalForms, formId, locationCode);

            try {
                const success = await downloadForm(formId, principalEmployer, state, locationCode, form);
                if (!success) {
                    console.error(`Failed to download form: ${formId}`);
                }
                await new Promise(r => setTimeout(r, 300));
            } catch (err) {
                console.error('Error processing form:', err);
            }
        }

        showDownloadComplete(progressEl);
    } catch (err) {
        console.error('Download error:', err);
        alert('An error occurred during download. Check the console.');
    } finally {
        downloadBtn.disabled = false;
        downloadBtn.textContent = originalText;
        if (loadingIndicator) loadingIndicator.style.display = 'none';
    }
}

async function downloadForm(formId, principalEmployer, state, locationCode, formElement) {
    const clone = formElement.cloneNode(true);
    clone.style.width = '100%';
    clone.style.overflow = 'visible';

    // wrap with full HTML + styles
    const htmlContent = buildHtmlForPdf(clone.outerHTML);

    try {
        const response = await fetch('save_pdf1.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                formId: formId,
                state: state,
                clientName: formStateData.clientName,
                principalEmployer: principalEmployer,
                locationCode: locationCode,
                monthYear: formStateData.monthYear,
                htmlContent: htmlContent
            })
        });

        const result = await response.json();

        if (result.success) {
            const filename = result.filename || (result.path ? result.path.split('/').pop() : ('form_' + Date.now() + '.pdf'));
            const publicPath = result.publicPath || (result.folderStructure ? ('downloads/' + result.folderStructure + '/' + filename) : ('downloads/' + filename));

            const downloadLink = document.createElement('a');
            downloadLink.href = publicPath;
            downloadLink.download = filename;
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);

            return true;
        } else {
            console.error('Error from server:', result.message || result);
            return false;
        }
    } catch (error) {
        console.error("Network/JS Error:", error);
        return false;
    }
}

/* Helper: wrap HTML for PDF */
function buildHtmlForPdf(innerContent) {
    const styles = `
      <style>
        body { font-family: "Times New Roman", serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        td, th { border: 1px solid #000; padding: 5px; vertical-align: top; }
        .form-container { margin-bottom: 15px; }
        h1, h2, h3, h4, h5 { margin: 5px 0; }
      </style>
    `;
    return `
      <!DOCTYPE html>
      <html>
        <head>
          <meta charset="UTF-8">
          ${styles}
        </head>
        <body>
          ${innerContent}
        </body>
      </html>
    `;
}

/* small UI helpers */
function showDownloadProgress(current, total) {
    const progress = document.createElement('div');
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
    progress.textContent = `Preparing to download ${total} forms...`;
    document.body.appendChild(progress);
    return progress;
}

function updateDownloadProgress(progressElement, current, total, formId, locationCode) {
    if (progressElement) {
        progressElement.textContent = `Processing ${current}/${total}: ${formId} — ${locationCode}`;
    }
}

function showDownloadComplete(progressElement) {
    if (progressElement) {
        progressElement.style.backgroundColor = '#4CAF50';
        progressElement.textContent = 'All forms downloaded successfully!';
        setTimeout(() => {
            progressElement.style.opacity = '0';
            setTimeout(() => { if (progressElement.parentNode) progressElement.parentNode.removeChild(progressElement); }, 500);
        }, 2000);
    }
}
