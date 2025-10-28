// Global variables
let clientSelectedItems = [];
let statutorySelected = null;
let currentlyMappedSelected = null;
let excelData = null;
let excelColumns = [];
let mappings = []; // Changed to array to handle multiple mappings for same statutory field
let workbook = null;
let currentSheetIndex = 0;
let columnPositions = {}; // Store original positions of columns

// Date fields that need special formatting
const dateFields = [
    "Date of Birth", 
    "Date of Joining", 
    "Date of Confirmation", 
    "Date of Leaving", 
    "Payment Date"
];

// Numeric fields that can be summed (Fixed Basic to Net Pay)
const numericFields = [
    "Fixed Basic", "Fixed DA", "Fixed HRA", "Fixed Conveyance", "Fixed Special Allowance", 
    "Fixed Other Allowance", "Fixed Gross", "Basic", "DA", "HRA", "Conveyance Allowance", 
    "Special Allowance", "Statutory Bonus", "Exgratia Bonus", "Maternity Bonus", 
    "Overtime Allowance", "Medical Allowance", "Attendance Bonus", "Advance", "NFH Wages", 
    "Subsistence Allowance", "Other Allowance", "Gross Wages", "EPF", "VPF", "ESI", 
    "Ptax", "LWF", "IT/TDS", "Fines Damage or Loss", "Insurance", "Advance Recovery", 
    "Other Deduction", "Total Deduction", "Net Pay", "Employer PF", "Paid Days", "LOP"
];

// DOM elements
const clientItemsContainer = document.getElementById('client-items');
const statutoryItemsContainer = document.getElementById('statutory-items');
const mappedItemsContainer = document.getElementById('mapped-items');
const fileInfoElement = document.getElementById('file-info');
const clientUploadInput = document.getElementById('client-upload');
const resetBtn = document.getElementById('reset-btn');
const sheetSelector = document.getElementById('sheet-select');
const sheetSelectorContainer = document.getElementById('sheet-selector');

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    setupEventListeners();
});

function setupEventListeners() {
    // File upload handling
    clientUploadInput.addEventListener('change', handleFileUpload);
    
    // Sheet selection handling
    sheetSelector.addEventListener('change', handleSheetChange);
    
    // Statutory items selection
    statutoryItemsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('selectable-item')) {
            handleStatutoryItemSelection(e.target);
        }
    });

    // Client items selection (will be added dynamically)
    clientItemsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('selectable-item')) {
            handleClientItemSelection(e.target);
        }
    });

    // Mapped items selection
    mappedItemsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('selectable-item')) {
            handleMappedItemSelection(e.target);
        }
    });

    // Button events
    document.getElementById('map-btn').addEventListener('click', handleMap);
    document.getElementById('unmap-btn').addEventListener('click', handleUnmap);
    document.getElementById('export-btn').addEventListener('click', handleExport);
    resetBtn.addEventListener('click', handleReset);
}

function handleFileUpload(e) {
    const file = e.target.files[0];
    if (!file) return;

    fileInfoElement.textContent = `Selected: ${file.name} (${(file.size/1024/1024).toFixed(2)} MB)`;

    const reader = new FileReader();
    reader.onload = function(e) {
        try {
            const data = new Uint8Array(e.target.result);
            workbook = XLSX.read(data, { type: 'array', cellDates: true, cellNF: true, cellText: false });
            
            // Show sheet selector if multiple sheets exist
            if (workbook.SheetNames.length > 1) {
                sheetSelectorContainer.style.display = 'block';
                populateSheetSelector(workbook.SheetNames);
            } else {
                sheetSelectorContainer.style.display = 'none';
            }
            
            // Load the first sheet by default
            loadSheetData(0);
        } catch (error) {
            console.error('Error parsing Excel file:', error);
            alert('Error parsing Excel file. Please make sure it\'s a valid Excel file.');
        }
    };
    reader.onerror = function() {
        alert('Error reading file. Please try again.');
    };
    reader.readAsArrayBuffer(file);
}

function populateSheetSelector(sheetNames) {
    sheetSelector.innerHTML = '';
    sheetNames.forEach((name, index) => {
        const option = document.createElement('option');
        option.value = index;
        option.textContent = name;
        sheetSelector.appendChild(option);
    });
}

function handleSheetChange(e) {
    const sheetIndex = parseInt(e.target.value);
    loadSheetData(sheetIndex);
}

function loadSheetData(sheetIndex) {
    if (!workbook) return;
    
    currentSheetIndex = sheetIndex;
    const sheetName = workbook.SheetNames[sheetIndex];
    const worksheet = workbook.Sheets[sheetName];
    
    // Convert to JSON with raw values to preserve dates and formats
    excelData = XLSX.utils.sheet_to_json(worksheet, { header: 1, raw: true, defval: "" });
    
    // Get column headers (assuming first row is headers)
    excelColumns = excelData[0] || [];
    
    // Store original column positions
    columnPositions = {};
    excelColumns.forEach((column, index) => {
        if (column !== undefined && column !== null) {
            columnPositions[column] = index;
        }
    });
    
    // Display columns in client container
    displayExcelColumns();
}

function displayExcelColumns() {
    clientItemsContainer.innerHTML = ''; // Clear previous content
    
    if (!excelColumns || excelColumns.length === 0) {
        clientItemsContainer.innerHTML = '<div>No columns found in the Excel file</div>';
        return;
    }
    
    excelColumns.forEach((column, index) => {
        if (column !== undefined && column !== null) { // Skip empty columns
            const item = document.createElement('div');
            item.className = 'selectable-item';
            item.textContent = column;
            item.dataset.value = column;
            item.dataset.index = index; // Store the column index for reference
            clientItemsContainer.appendChild(item);
        }
    });
}

function handleStatutoryItemSelection(item) {
    // Deselect previous selection in statutory container only
    if (statutorySelected) {
        statutorySelected.classList.remove('selected');
    }
    
    // Select current item
    item.classList.add('selected');
    statutorySelected = item;
}

function handleClientItemSelection(item) {
    // Toggle selection
    if (item.classList.contains('multi-selected')) {
        item.classList.remove('multi-selected');
        clientSelectedItems = clientSelectedItems.filter(selectedItem => selectedItem !== item);
    } else {
        item.classList.add('multi-selected');
        clientSelectedItems.push(item);
    }
}

function handleMappedItemSelection(item) {
    // Deselect previous selection
    if (currentlyMappedSelected) {
        currentlyMappedSelected.classList.remove('selected');
    }
    
    // Select current item
    item.classList.add('selected');
    currentlyMappedSelected = item;
}

function handleMap() {
    if (clientSelectedItems.length > 0 && statutorySelected) {
        const statutoryField = statutorySelected.dataset.value;
        const isNumericField = numericFields.includes(statutoryField);
        
        // Create a unique ID for this mapping
        const mappingId = Date.now() + Math.random().toString(36).substr(2, 9);
        
        // For numeric fields, check if there's an existing mapping
        let existingMappingElement = null;
        if (isNumericField) {
            existingMappingElement = Array.from(mappedItemsContainer.querySelectorAll('.selectable-item'))
                .find(item => item.dataset.statutory === statutoryField);
        }
        
        if (existingMappingElement && isNumericField) {
            // Update existing mapping for numeric fields (add to existing)
            const existingMappingId = existingMappingElement.dataset.mappingId;
            const existingMappingIndex = mappings.findIndex(m => m.id === existingMappingId);
            
            if (existingMappingIndex !== -1) {
                // Add new client columns to existing mapping
                const newClientColumns = clientSelectedItems.map(item => ({
                    value: item.dataset.value,
                    index: item.dataset.index
                }));
                
                mappings[existingMappingIndex].clientColumns.push(...newClientColumns);
                
                // Update the display text
                const allClientColumns = mappings[existingMappingIndex].clientColumns.map(col => col.value).join(' + ');
                existingMappingElement.textContent = `${allClientColumns} → ${statutoryField}`;
                existingMappingElement.dataset.client = mappings[existingMappingIndex].clientColumns.map(col => col.value).join(',');
            }
        } else {
            // Create new mapping entry
            const mappingItem = document.createElement('div');
            mappingItem.className = 'selectable-item';
            mappingItem.dataset.mappingId = mappingId;
            
            // Show all selected client columns
            const clientColumnsText = clientSelectedItems.map(item => item.textContent).join(' + ');
            mappingItem.textContent = `${clientColumnsText} → ${statutoryField}`;
            
            // Store all client column references
            mappingItem.dataset.client = clientSelectedItems.map(item => item.dataset.value).join(',');
            mappingItem.dataset.statutory = statutoryField;
            
            mappedItemsContainer.appendChild(mappingItem);
            
            // Store the mapping with unique ID
            mappings.push({
                id: mappingId,
                clientColumns: clientSelectedItems.map(item => ({
                    value: item.dataset.value,
                    index: item.dataset.index
                })),
                statutoryField: statutoryField,
                isDate: dateFields.includes(statutoryField),
                isNumeric: isNumericField
            });
        }
        
        // Remove selected client items from the client container
        clientSelectedItems.forEach(item => item.remove());
        
        // Clear selections
        clientSelectedItems = [];
        if (statutorySelected) statutorySelected.classList.remove('selected');
        statutorySelected = null;
    } else {
        alert('Please select one or more items from Client Input AND one from Statutory Input to map');
    }
}

function handleUnmap() {
    if (currentlyMappedSelected) {
        const mappingId = currentlyMappedSelected.dataset.mappingId;
        const statutoryField = currentlyMappedSelected.dataset.statutory;
        const isNumericField = numericFields.includes(statutoryField);
        
        // Find the mapping by ID
        const mappingIndex = mappings.findIndex(m => m.id === mappingId);
        
        if (mappingIndex !== -1) {
            // Get the mapped client columns
            const clientColumns = mappings[mappingIndex].clientColumns;
            
            // Remove from mappings array
            mappings.splice(mappingIndex, 1);
            
            // Remove from DOM
            currentlyMappedSelected.remove();
            
            // Add the client columns back to the client container in their original positions
            clientColumns.forEach(col => {
                const item = document.createElement('div');
                item.className = 'selectable-item';
                item.textContent = col.value;
                item.dataset.value = col.value;
                item.dataset.index = col.index;
                
                // Find the correct position to insert the item
                const clientItems = Array.from(clientItemsContainer.querySelectorAll('.selectable-item'));
                let insertIndex = 0;
                
                // Find where to insert based on original position
                for (let i = 0; i < clientItems.length; i++) {
                    const currentIndex = parseInt(clientItems[i].dataset.index);
                    if (col.index < currentIndex) {
                        break;
                    }
                    insertIndex = i + 1;
                }
                
                // Insert at the correct position
                if (insertIndex < clientItems.length) {
                    clientItemsContainer.insertBefore(item, clientItems[insertIndex]);
                } else {
                    clientItemsContainer.appendChild(item);
                }
            });
            
            currentlyMappedSelected = null;
        }
    } else {
        alert('Please select a mapped item to unmap');
    }
}

function handleExport() {
    if (!excelData || excelData.length < 2) {
        alert('No data to export. Please upload an Excel file first.');
        return;
    }
    
    if (mappings.length === 0) {
        alert('No mappings defined. Please map at least one column.');
        return;
    }
    
    // Get all statutory fields
    const statutoryFields = Array.from(statutoryItemsContainer.querySelectorAll('.selectable-item')).map(el => el.dataset.value);
    
    // Prepare the output data
    const outputData = [];
    
    // Process each row of the input data (skip header row)
    for (let i = 1; i < excelData.length; i++) {
        const inputRow = excelData[i];
        const outputRow = {};
        
        // For each statutory field, find the mapped client column and get the value
        statutoryFields.forEach(field => {
            // Find all mappings for this field
            const fieldMappings = mappings.filter(m => m.statutoryField === field);
            
            if (fieldMappings.length > 0) {
                let value = '';
                
                // Check if this is a numeric field that should be summed
                const isNumericField = numericFields.includes(field);
                const isDateField = dateFields.includes(field);
                
                if (isNumericField) {
                    // For numeric fields, sum all mapped values
                    let sum = 0;
                    let hasValues = false;
                    
                    for (const mapping of fieldMappings) {
                        if (mapping.clientColumns && mapping.clientColumns.length > 0) {
                            for (const col of mapping.clientColumns) {
                                const clientColIndex = col.index;
                                
                                if (clientColIndex !== undefined && inputRow[clientColIndex] !== undefined) {
                                    let cellValue = inputRow[clientColIndex];
                                    
                                    // Convert to number if possible
                                    if (typeof cellValue === 'string') {
                                        cellValue = parseFloat(cellValue.replace(/[^0-9.-]/g, '')) || 0;
                                    } else if (typeof cellValue !== 'number') {
                                        cellValue = 0;
                                    }
                                    
                                    sum += cellValue;
                                    hasValues = true;
                                }
                            }
                        }
                    }
                    
                    value = hasValues ? sum : '';
                } else {
                    // For non-numeric fields, use the latest mapping (last one in the array)
                    const latestMapping = fieldMappings[fieldMappings.length - 1];
                    
                    if (latestMapping.clientColumns && latestMapping.clientColumns.length > 0) {
                        if (latestMapping.clientColumns.length === 1) {
                            // Single column mapping
                            const clientColIndex = latestMapping.clientColumns[0].index;
                            
                            if (clientColIndex !== undefined && inputRow[clientColIndex] !== undefined) {
                                let cellValue = inputRow[clientColIndex];
                                
                                // Handle date fields - convert to date string without time
                                if (isDateField && cellValue) {
                                    cellValue = convertToDateString(cellValue);
                                }
                                
                                value = cellValue;
                            }
                        } else {
                            // Multiple column mapping for non-numeric fields - use the first one
                            const clientColIndex = latestMapping.clientColumns[0].index;
                            
                            if (clientColIndex !== undefined && inputRow[clientColIndex] !== undefined) {
                                let cellValue = inputRow[clientColIndex];
                                
                                // Handle date fields - convert to date string without time
                                if (isDateField && cellValue) {
                                    cellValue = convertToDateString(cellValue);
                                }
                                
                                value = cellValue;
                            }
                        }
                    }
                }
                
                outputRow[field] = value !== undefined ? value : '';
            } else {
                outputRow[field] = ''; // Empty if no mapping
            }
        });
        
        // Only add row if it has some data
        if (Object.values(outputRow).some(val => val !== '')) {
            outputData.push(outputRow);
        }
    }
    
    if (outputData.length === 0) {
        alert('No valid data to export. Please check your mappings and input file.');
        return;
    }
    
    // Create a new workbook
    const wb = XLSX.utils.book_new();
    
    // Convert output data to worksheet
    const ws = XLSX.utils.json_to_sheet(outputData);
    
    // Set date formatting for date fields
    statutoryFields.forEach((field, idx) => {
        if (dateFields.includes(field)) {
            const colLetter = XLSX.utils.encode_col(idx);
            if (!ws['!cols']) ws['!cols'] = [];
            ws['!cols'][idx] = { width: 12 };
            
            // Apply date format to all cells in this column
            for (let i = 1; i <= outputData.length; i++) {
                const cellRef = colLetter + (i + 1); // +1 because header is row 1
                if (ws[cellRef] && ws[cellRef].v !== undefined && ws[cellRef].v !== '') {
                    ws[cellRef].z = 'dd-mm-yyyy'; // Set Excel date format
                    
                    // Convert string dates back to Excel serial dates for proper formatting
                    if (typeof ws[cellRef].v === 'string' && ws[cellRef].v.match(/\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{4}/)) {
                        const dateParts = ws[cellRef].v.split(/[\/\-\.]/);
                        if (dateParts.length === 3) {
                            const day = parseInt(dateParts[0], 10);
                            const month = parseInt(dateParts[1], 10) - 1;
                            const year = parseInt(dateParts[2], 10);
                            const date = new Date(year, month, day);
                        }
                    }
                }
            }
        }
    });
    
    // Add worksheet to workbook
    XLSX.utils.book_append_sheet(wb, ws, "Mapped Data");
    
    // Export the workbook
    XLSX.writeFile(wb, "mapped_statutory_data.xlsx");
}

// Convert any date value to a clean date string without time
function convertToDateString(value) {
    if (!value) return value;
    
    // If it's already a string in date format, return as is
    if (typeof value === 'string') {
        // Check if it's already a date string without time
        if (value.match(/^\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{4}$/)) {
            return value;
        }
        // Try to extract date from string
        const dateMatch = value.match(/(\d{1,2})[\/\-\.] ?(\d{1,2})[\/\-\.] ?(\d{4})/);
        if (dateMatch) {
            const day = dateMatch[1].padStart(2, '0');
            const month = dateMatch[2].padStart(2, '0');
            const year = dateMatch[3];
            return `${day}-${month}-${year}`;
        }
    }
    
    // If it's a Date object
    if (value instanceof Date) {
        const day = String(value.getDate()).padStart(2, '0');
        const month = String(value.getMonth() + 1).padStart(2, '0');
        const year = value.getFullYear();
        return `${day}-${month}-${year}`;
    }
    
    // If it's an Excel serial date (number)
    if (typeof value === 'number' && value > 0) {
        // Convert Excel serial date to JavaScript Date
        const excelEpoch = new Date(1899, 11, 30); // Excel's epoch is 12/30/1899
        const date = new Date(excelEpoch.getDate() + value * 24 * 60 * 60 * 1000);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}-${month}-${year}`;
    }
    
    // Return original value if we can't parse it as date
    return value;
}

// Convert JavaScript Date to Excel serial date number
function excelDateToSerial(date) {
    if (!(date instanceof Date)) return date;
    
    // Excel uses 1/1/1900 as epoch (with 1900 incorrectly treated as a leap year)
    const excelEpoch = new Date(1899, 11, 30); // 12/30/1899
    const diff = date - excelEpoch;
    const days = diff / (24 * 60 * 60 * 1000);
    
    // Add 1 because Excel incorrectly considers 1900 a leap year
    return days + 1;
}

function handleReset() {
    // Clear uploaded file and data
    clientUploadInput.value = '';
    fileInfoElement.textContent = '';
    clientItemsContainer.innerHTML = '';
    excelData = null;
    excelColumns = [];
    mappings = [];
    workbook = null;
    columnPositions = {};
    
    // Hide sheet selector
    sheetSelectorContainer.style.display = 'none';
    sheetSelector.innerHTML = '';
    
    // Clear mapped items
    mappedItemsContainer.innerHTML = '';
    
    // Clear selections
    clientSelectedItems = [];
    if (statutorySelected) statutorySelected.classList.remove('selected');
    if (currentlyMappedSelected) currentlyMappedSelected.classList.remove('selected');
    
    statutorySelected = null;
    currentlyMappedSelected = null;
}