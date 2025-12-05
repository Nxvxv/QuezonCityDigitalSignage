<?php
// Include database connection
require_once '../../../connection/conn.php';

// Fetch logincredentials data (Username/DistrictId/BranchId for dropdown mapping)
$loginData = [];
$stmt = $conn->prepare("SELECT LoginId, Username, DistrictId, BranchId FROM logincredentials");
if ($stmt && $stmt->execute()) {
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $loginData[] = $row;
        }
    }
}
if (isset($stmt)) { $stmt->close(); }

// Fetch districts (DistrictId, DistrictName)
$districtsData = [];
$dStmt = $conn->prepare("SELECT DistrictId, DistrictName FROM districts ORDER BY DistrictId ASC");
if ($dStmt && $dStmt->execute()) {
    $dRes = $dStmt->get_result();
    if ($dRes && $dRes->num_rows > 0) {
        while ($dRow = $dRes->fetch_assoc()) {
            $districtsData[] = $dRow;
        }
    }
}
if (isset($dStmt)) { $dStmt->close(); }

// Fetch branches (BranchId, DistrictId, BranchName)
$branchesData = [];
$bStmt = $conn->prepare("SELECT BranchId, DistrictId, BranchName FROM branches ORDER BY DistrictId ASC, BranchName ASC");
if ($bStmt && $bStmt->execute()) {
    $bRes = $bStmt->get_result();
    if ($bRes && $bRes->num_rows > 0) {
        while ($bRow = $bRes->fetch_assoc()) {
            $branchesData[] = $bRow;
        }
    }
}
if (isset($bStmt)) { $bStmt->close(); }
?>
<script>
    // Pass PHP login data to JS
    const LOGIN_DATA = <?php echo json_encode($loginData); ?>;
    const DISTRICTS = <?php echo json_encode($districtsData); ?>;
    const BRANCHES = <?php echo json_encode($branchesData); ?>;
</script>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>QC Library Digital Signage - Admin Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../css/login.css"/>
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
    <img src="../../../assets/wallpa.png" alt="Background" class="absolute inset-0 w-full h-full object-cover filter blur-sm pointer-events-none" />
    <div class="relative bg-white p-8 w-full max-w-sm text-center z-20 rounded-lg shadow-lg">
        <div class="mb-6">
            <div class="mx-auto mb-2 w-40 h-28 rounded-md overflow-hidden">
                <img src="../../../assets/logoo.png" alt="QC Library Logo" class="w-full h-full object-cover" />
            </div>
            <h1 class="text-xl font-bold font-montserrat mt-6">QUEZON CITY PUBLIC LIBRARY</h1>
            <p class="text-sm font-montserrat">Digital Signage - Admin Portal Login</p>
        </div>
        <form id="login-form" class="space-y-4 text-left font-montserrat">
            <label for="username" class="block text-sm font-bold">Username</label>
            <input type="text" id="username" name="username" required placeholder="Enter username..." class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary font-montserrat" />
            <label for="password" class="block text-sm font-bold mt-4">Password</label>
            <input type="password" id="password" name="password" required placeholder="Enter password..." class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary font-montserrat" />
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

        // Build district -> branches mapping using branches table for names
        const districtBranchMap = {};
        BRANCHES.forEach(b => {
            if (b.DistrictId === null || b.DistrictId === '') return;
            if (!districtBranchMap[b.DistrictId]) districtBranchMap[b.DistrictId] = [];
            districtBranchMap[b.DistrictId].push({ id: b.BranchId, name: b.BranchName });
        });

        // Populate district dropdown
        function getDistrictNameById(id) {
            const found = DISTRICTS.find(d => String(d.DistrictId) === String(id));
            return found ? found.DistrictName : id;
        }

        function populateDistricts() {
            districtSelect.innerHTML = '<option value="" disabled selected>Choose your district...</option>';
            DISTRICTS.forEach(d => {
                const option = document.createElement('option');
                option.value = d.DistrictId;
                option.textContent = d.DistrictName;
                districtSelect.appendChild(option);
            });
        }

        // Populate branch dropdown for selected district
        function populateBranches(selectedDistrict) {
            branchSelect.innerHTML = '<option value="" disabled selected>Choose your branch...</option>';
            const branches = districtBranchMap[selectedDistrict];
            if (!branches || branches.length === 0) {
                branchSelect.disabled = true;
                branchSelect.style.display = 'none';
                branchLabel.style.display = 'none';
                return;
            }
            branches.forEach(br => {
                const option = document.createElement('option');
                option.value = br.id;
                option.textContent = br.name;
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
            const branches = districtBranchMap[selectedDistrict] || [];
            if (branches.length === 0) {
                hideBranchSelection();
                return;
            }
            // If exactly one branch, hide dropdown & auto-select
            if (branches.length === 1) {
                hideBranchSelection();
                branchSelect.value = branches[0].id;
                return;
            }
            populateBranches(selectedDistrict);
        }

        // Validate login form
        function validateLoginForm(username, password, district, branch) {
            if (!username || !password || !district) return false;
            const branches = districtBranchMap[district] || [];
            if (branches.length === 0) return true; // no branch required
            if (branches.length === 1) return true; // implicit single branch
            return !!branch;
        }

        // Handle login form submission - send to server for authentication
        async function handleLoginSubmit(event) {
            event.preventDefault();
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const district = districtSelect.value;
            const branch = branchSelect.value;
            if (!validateLoginForm(username, password, district, branch)) {
                alert('Please fill all required fields.');
                return;
            }

            // Determine branchToSend the same way client-side expects
            const branches = districtBranchMap[district] || [];
            let branchToSend = null;
            if (branches.length === 1) {
                branchToSend = branches[0].id;
            } else if (branches.length > 1) {
                branchToSend = branch; // chosen from dropdown
            }

            try {
                const resp = await fetch('authenticate.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, password, districtId: district, branchId: branchToSend })
                });
                const data = await resp.json();
                if (data.success) {
                    saveLoginData(username, district, branchToSend);
                    window.location.href = '../index.php';
                } else {
                    alert(data.msg || 'Verification failed.');
                }
            } catch (err) {
                console.error(err);
                alert('Unable to contact server. Please try again later.');
            }
        }

        // Save login data to localStorage
        function saveLoginData(username, district, branchId) {
            localStorage.setItem('loggedIn', 'true');
            localStorage.setItem('username', username);
            localStorage.setItem('districtId', district);
            localStorage.setItem('branchId', branchId || '');
            if (branchId) {
                const branches = districtBranchMap[district] || [];
                const match = branches.find(b => String(b.id) === String(branchId));
                if (match) localStorage.setItem('branchName', match.name);
            } else {
                localStorage.removeItem('branchName');
            }
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
                window.location.href = '../index.php';
            }, 3000);
        }

        // Update method selection UI
        function updateMethodSelection(selectedBtn, otherBtn, method) {
            selectedBtn.classList.remove('bg-gray-100', 'text-gray-500', 'border-b', 'border-gray-300');
            selectedBtn.classList.add('bg-white', 'text-gray-700', 'border-b-2', 'border-primary');
            otherBtn.classList.remove('bg-white', 'text-gray-700', 'border-b-2', 'border-primary');
            otherBtn.classList.add('bg-gray-100', 'text-gray-500', 'border-b', 'border-gray-300');
        }

        // (Removed duplicate initialization block for clarity)
    </script>
</body>
</html>
