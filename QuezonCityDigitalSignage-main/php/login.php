<?php
// Include database connection
require_once '../connection/conn.php';

// Fetch login_tbl data
$loginData = [];
$sql = "SELECT ID, Admin_name, District, Branch FROM login_tbl";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $loginData[] = $row;
    }
}
?>
<script>
    // Pass PHP login data to JS
    const LOGIN_DATA = <?php echo json_encode($loginData); ?>;
</script>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>QC Library Digital Signage - Admin Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../CSS/login.css"/>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#2563eb', // blue primary
                        secondary: '#6b7280', // gray for text
                        accent: '#eab308',
                    },
                    fontFamily: {
                        montserrat: ['Montserrat', 'sans-serif']
                    }
                }
            }
        };
    </script>
</head>
<body class="relative min-h-screen flex items-center justify-center font-montserrat">
    <img src="../assets/wallpa.png" alt="Background" class="absolute inset-0 w-full h-full object-cover filter blur-sm" />
    <div class="relative bg-white p-8 w-full max-w-sm text-center z-10 rounded-lg shadow-lg">
        <div class="mb-6">
            <div class="mx-auto mb-2 w-40 h-28 rounded-md overflow-hidden">
                <img src="../assets/logoo.png" alt="QC Library Logo" class="w-full h-full object-cover" />
            </div>
            <h1 class="text-xl font-bold font-montserrat mt-6">QUEZON CITY PUBLIC LIBRARY</h1>
            <p class="text-sm font-montserrat">Digital Signage - Admin Portal Login</p>
        </div>
        <form id="login-form" class="space-y-4 text-left font-montserrat">
            <label for="adminName" class="block text-sm font-bold">Admin Name</label>
            <input type="text" id="adminName" name="adminName" required placeholder="Enter admin name..." class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary font-montserrat" />
            <label for="district" class="block text-sm font-bold">Select District</label>
            <select id="district" name="district" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary font-montserrat">
                <option value="" disabled selected>Choose your district...</option>
            </select>
            <label for="branch" class="block text-sm font-bold mt-4" id="branch-label">Select Branch</label>
            <select id="branch" name="branch" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary font-montserrat" disabled>
                <option value="" disabled selected>Choose your branch...</option>
            </select>
            <button type="submit" class="w-full bg-primary text-white py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary font-montserrat">LOGIN</button>
        </form>
        <div class="mt-4 text-sm">
            <!-- Removed View Live Signage Display link as requested -->
        </div>
    </div>
    <script>
        // DOM elements
    // ...existing code...

    // DOM elements
    var districtSelect = document.getElementById('district');
    var branchSelect = document.getElementById('branch');
    var branchLabel = document.getElementById('branch-label');

        // Build district and branch mapping from LOGIN_DATA
        const districtBranchMap = {};
        LOGIN_DATA.forEach(row => {
            if (!districtBranchMap[row.District]) {
                districtBranchMap[row.District] = new Set();
            }
            districtBranchMap[row.District].add(row.Branch);
        });

        // Populate district dropdown
        function populateDistricts() {
            districtSelect.innerHTML = '<option value="" disabled selected>Choose your district...</option>';
            Object.keys(districtBranchMap).forEach(district => {
                const option = document.createElement('option');
                option.value = district;
                option.textContent = district;
                districtSelect.appendChild(option);
            });
        }

        // Populate branch dropdown for selected district
        function populateBranches(selectedDistrict) {
            branchSelect.innerHTML = '<option value="" disabled selected>Choose your branch...</option>';
            if (!districtBranchMap[selectedDistrict]) {
                branchSelect.disabled = true;
                branchSelect.style.display = 'none';
                branchLabel.style.display = 'none';
                return;
            }
            districtBranchMap[selectedDistrict].forEach(branch => {
                const option = document.createElement('option');
                option.value = branch;
                option.textContent = branch;
                branchSelect.appendChild(option);
            });
            branchSelect.disabled = false;
            branchSelect.style.display = 'block';
            branchLabel.style.display = 'block';
        }

        // Hide branch selection
        function hideBranchSelection() {
            branchSelect.style.display = 'none';
            branchLabel.style.display = 'none';
            branchSelect.disabled = true;
        }

        // Handle district selection change
        function handleDistrictChange() {
            const selectedDistrict = districtSelect.value;
            // If only one branch and branch name is same as district, hide branch selection
            const branches = districtBranchMap[selectedDistrict] ? Array.from(districtBranchMap[selectedDistrict]) : [];
            if (branches.length === 1 && branches[0] === selectedDistrict) {
                hideBranchSelection();
                return;
            }
            populateBranches(selectedDistrict);
        }

        // Validate login form
        function validateLoginForm(adminName, district, branch) {
            if (!adminName || !district) {
                return false;
            }
            const branches = districtBranchMap[district] ? Array.from(districtBranchMap[district]) : [];
            if (branches.length === 1 && branches[0] === district) {
                return true;
            }
            return branch && branch !== "";
        }

        // Handle login form submission
        function handleLoginSubmit(event) {
            event.preventDefault();
            const adminName = document.getElementById('adminName').value.trim();
            const district = districtSelect.value;
            const branch = branchSelect.value;
            if (!validateLoginForm(adminName, district, branch)) {
                alert('Please enter your name and select district. If your district has multiple branches, select branch as well.');
                return;
            }
            // Verification: check if info matches a record in LOGIN_DATA
            let isValid = false;
            for (let i = 0; i < LOGIN_DATA.length; i++) {
                const row = LOGIN_DATA[i];
                // If only one branch and branch name is same as district, treat branch as district
                const branches = districtBranchMap[district] ? Array.from(districtBranchMap[district]) : [];
                const branchToCheck = (branches.length === 1 && branches[0] === district) ? district : branch;
                if (
                    row.Admin_name.trim().toLowerCase() === adminName.toLowerCase() &&
                    row.District === district &&
                    row.Branch === branchToCheck
                ) {
                    isValid = true;
                    break;
                }
            }
            if (!isValid) {
                alert('Verification failed. Please check your credentials.');
                return;
            }
            saveLoginData(adminName, district, branch);
            showVerificationLoader(document.querySelector('.bg-white'));
        }

        // Save login data to localStorage
        function saveLoginData(adminName, district, branch) {
            localStorage.setItem('loggedIn', 'true');
            localStorage.setItem('adminName', adminName);
            localStorage.setItem('district', district);
            // If only one branch and branch name is same as district, save district as branch
            const branches = districtBranchMap[district] ? Array.from(districtBranchMap[district]) : [];
            localStorage.setItem('branch', (branches.length === 1 && branches[0] === district) ? district : branch);
        }

    // ...existing code...

        // Initialize event listeners
        function initializeEventListeners() {
            districtSelect.addEventListener('change', handleDistrictChange);
            document.getElementById('login-form').addEventListener('submit', handleLoginSubmit);
        }

        // Initialize the page
        function initializePage() {
            populateDistricts();
            hideBranchSelection();
            initializeEventListeners();
        }
        initializePage();
        // // Append heading to container
        // function appendHeading(container) {
        //     const heading = document.createElement('h2');
        //     heading.textContent = 'Two-Factor Authentication';
        //     heading.className = 'text-xl font-bold text-gray-900 font-montserrat mb-4 text-center';
        //     container.appendChild(heading);
        // }

        // // Append instruction text
        // function appendInstruction(container) {
        //     const instruction = document.createElement('p');
        //     instruction.textContent = 'Select a method to receive your OTP and enter the 6-digit code.';
        //     instruction.className = 'text-sm text-gray-700 font-montserrat mb-6 text-center';
        //     container.appendChild(instruction);
        // }

        // // Create method selection UI
        // function createMethodSelection(container) {
        //     const methodContainer = document.createElement('div');
        //     methodContainer.className = 'flex justify-center mb-6 border border-gray-300 rounded-md overflow-hidden';
        //     const emailBtn = createMethodButton('Email', true);
        //     const phoneBtn = createMethodButton('Phone', false);
        //     methodContainer.appendChild(emailBtn);
        //     methodContainer.appendChild(phoneBtn);
        //     container.appendChild(methodContainer);
        //     const selectedMethod = 'Email';
        //     updateMethodSelection(emailBtn, phoneBtn, selectedMethod);
        //     return { methodContainer, emailBtn, phoneBtn, selectedMethod };
        // }

        // // Create individual method button
        // function createMethodButton(text, isSelected) {
        //     const btn = document.createElement('button');
        //     btn.textContent = text;
        //     btn.className = isSelected
        //         ? 'flex-1 py-3 px-4 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary font-montserrat border-r border-gray-300 border-b-2 border-primary'
        //         : 'flex-1 py-3 px-4 bg-gray-100 text-gray-500 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary font-montserrat border-b border-gray-300';
        //     return btn;
        // }

        // // Create Send OTP button
        // function createSendOtpButton(container, selectedMethod) {
        //     const sendOtpBtn = document.createElement('button');
        //     sendOtpBtn.textContent = 'Send OTP';
        //     sendOtpBtn.className = 'w-full bg-primary text-white py-2 rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary font-montserrat mb-6';
        //     container.appendChild(sendOtpBtn);
        //     sendOtpBtn.addEventListener('click', () => {
        //         alert(`OTP sent to your ${selectedMethod.toLowerCase()}.`);
        //         // Enable resend button after sending
        //         const resendBtn = container.querySelector('.resend-btn');
        //         if (resendBtn) {
        //             resendBtn.disabled = false;
        //             resendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        //         }
        //     });
        //     return sendOtpBtn;
        // }

        // // Create OTP input fields
        // function createOtpInputs(container) {
        //     const otpContainer = document.createElement('div');
        //     otpContainer.className = 'flex justify-center space-x-2 mb-6';
        //     const otpInputs = [];
        //     for (let i = 0; i < 6; i++) {
        //         const input = createOtpInput(otpInputs, i);
        //         otpInputs.push(input);
        //         otpContainer.appendChild(input);
        //     }
        //     container.appendChild(otpContainer);
        //     return otpInputs;
        // }

        // // Create single OTP input
        // function createOtpInput(otpInputs, index) {
        //     const input = document.createElement('input');
        //     input.type = 'text';
        //     input.maxLength = 1;
        //     input.className = 'w-10 h-10 text-center border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary font-montserrat text-lg';
        //     input.inputMode = 'numeric';
        //     input.autocomplete = 'one-time-code';
        //     input.addEventListener('input', (e) => {
        //         if (e.target.value.length === 1 && index < 5) {
        //             otpInputs[index + 1].focus();
        //         }
        //     });
        //     input.addEventListener('keydown', (e) => {
        //         if (e.key === 'Backspace' && !e.target.value && index > 0) {
        //             otpInputs[index - 1].focus();
        //         }
        //     });
        //     return input;
        // }

        // // Create Resend OTP button
        // function createResendOtpButton(container, selectedMethod) {
        //     const resendOtpBtn = document.createElement('button');
        //     resendOtpBtn.textContent = 'Resend OTP';
        //     resendOtpBtn.className = 'resend-btn w-full bg-gray-300 text-gray-500 py-2 rounded hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 font-montserrat mb-4 opacity-50 cursor-not-allowed';
        //     resendOtpBtn.disabled = true;
        //     container.appendChild(resendOtpBtn);
        //     resendOtpBtn.addEventListener('click', () => {
        //         alert(`OTP resent to your ${selectedMethod.toLowerCase()}.`);
        //     });
        //     return resendOtpBtn;
        // }

        // // Create Verify OTP button
        // function createVerifyButton(container, otpInputs) {
        //     const submitBtn = document.createElement('button');
        //     submitBtn.textContent = 'Verify OTP';
        //     submitBtn.className = 'w-full bg-primary text-white py-2 rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary font-montserrat';
        //     container.appendChild(submitBtn);
        //     submitBtn.addEventListener('click', () => {
        //         const otpValue = otpInputs.map(input => input.value).join('');
        //         if (!isValidOtp(otpValue)) {
        //             alert('Please enter a valid 6-digit OTP.');
        //             return;
        //         }
        //         showVerificationLoader(container);
        //     });
        // }

        // // Validate OTP
        // function isValidOtp(otp) {
        //     return otp.length === 6 && /^\d{6}$/.test(otp);
        // }

        // Show loader during verification
        function showVerificationLoader(container) {
            container.innerHTML = '';
            container.classList.remove('bg-white', 'shadow-lg');
            const bgImg = document.querySelector('img');
            bgImg.style.display = 'none';
            document.body.style.backgroundColor = 'white';
            const loader = document.createElement('div');
            loader.className = 'loader mx-auto';
            container.appendChild(loader);
            setTimeout(() => {
                window.location.href = 'dashboard.php';
            }, 3000);
        }

        // Update method selection UI
        function updateMethodSelection(selectedBtn, otherBtn, method) {
            selectedBtn.classList.remove('bg-gray-100', 'text-gray-500', 'border-b', 'border-gray-300');
            selectedBtn.classList.add('bg-white', 'text-gray-700', 'border-b-2', 'border-primary');
            otherBtn.classList.remove('bg-white', 'text-gray-700', 'border-b-2', 'border-primary');
            otherBtn.classList.add('bg-gray-100', 'text-gray-500', 'border-b', 'border-gray-300');
        }

        // Initialize event listeners
        function initializeEventListeners() {
            districtSelect.addEventListener('change', handleDistrictChange);
            document.getElementById('login-form').addEventListener('submit', handleLoginSubmit);
        }

        // Initialize the page
        initializeBranchSelection();
        initializeEventListeners();
    </script>
</body>
</html>
