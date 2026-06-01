<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance System Test Suite</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold mb-2">Attendance System Test Suite</h1>
        <p class="text-gray-600 mb-6">Comprehensive testing of all system components</p>
        
        <?php
        require_once '../includes/config.php';
        require_once '../includes/functions.php';
        require_once '../includes/data.php';
        
        $tests = [];
        $passed = 0;
        $failed = 0;
        
        // Test 1: Data Generation
        $test1 = count($mockAttendanceData) > 0;
        $tests['Data Generation'] = ['passed' => $test1, 'message' => count($mockAttendanceData) . ' records generated'];
        $test1 ? $passed++ : $failed++;
        
        // Test 2: Data Structure
        $firstRecord = $mockAttendanceData[0];
        $requiredFields = ['id', 'employeeName', 'employeeId', 'date', 'checkInTime', 'checkOutTime', 'status', 'workingHours', 'department'];
        $hasAllFields = true;
        foreach ($requiredFields as $field) {
            if (!isset($firstRecord[$field])) {
                $hasAllFields = false;
                break;
            }
        }
        $tests['Data Structure'] = ['passed' => $hasAllFields, 'message' => 'All required fields present'];
        $hasAllFields ? $passed++ : $failed++;
        
        // Test 3: Status Values
        $validStatuses = ['Present', 'Absent', 'Late', 'Half Day', 'Holiday'];
        $allStatusesValid = true;
        foreach ($mockAttendanceData as $record) {
            if (!in_array($record['status'], $validStatuses)) {
                $allStatusesValid = false;
                break;
            }
        }
        $tests['Valid Statuses'] = ['passed' => $allStatusesValid, 'message' => 'All status values are valid'];
        $allStatusesValid ? $passed++ : $failed++;
        
        // Test 4: Date Format
        $datesValid = true;
        foreach ($mockAttendanceData as $record) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $record['date'])) {
                $datesValid = false;
                break;
            }
        }
        $tests['Date Format'] = ['passed' => $datesValid, 'message' => 'All dates in YYYY-MM-DD format'];
        $datesValid ? $passed++ : $failed++;
        
        // Test 5: Working Hours Logic
        $hoursValid = true;
        foreach ($mockAttendanceData as $record) {
            if (($record['status'] === 'Present' || $record['status'] === 'Late') && $record['workingHours'] <= 0) {
                $hoursValid = false;
                break;
            }
            if ($record['status'] === 'Absent' && $record['workingHours'] != 0) {
                $hoursValid = false;
                break;
            }
        }
        $tests['Working Hours Logic'] = ['passed' => $hoursValid, 'message' => 'Working hours match status logic'];
        $hoursValid ? $passed++ : $failed++;
        
        // Test 6: Employee Data Integrity
        $employees = [];
        $employeeIds = [];
        foreach ($mockAttendanceData as $record) {
            if (!in_array($record['employeeId'], $employeeIds)) {
                $employeeIds[] = $record['employeeId'];
                $employees[] = $record['employeeName'];
            }
        }
        $tests['Employee Data'] = ['passed' => count($employees) > 0, 'message' => count($employees) . ' unique employees found'];
        count($employees) > 0 ? $passed++ : $failed++;
        
        // Test 7: Department Distribution
        $departments = array_unique(array_column($mockAttendanceData, 'department'));
        $tests['Departments'] = ['passed' => count($departments) > 0, 'message' => count($departments) . ' departments: ' . implode(', ', $departments)];
        count($departments) > 0 ? $passed++ : $failed++;
        
        // Test 8: API Connectivity
        $apiUrl = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . "/../api/attendance.php";
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $apiResponse = curl_exec($ch);
        $apiStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $apiWorking = $apiStatusCode === 200 && $apiResponse !== false;
        $tests['API Connectivity'] = ['passed' => $apiWorking, 'message' => $apiWorking ? 'API responded with status 200' : 'API connection failed'];
        $apiWorking ? $passed++ : $failed++;
        
        // Test 9: Functions Available
        $requiredFunctions = ['sanitizeInput', 'validateEmail', 'formatDate', 'getStatusBadgeClass', 'calculateWorkingHours'];
        $allFunctionsExist = true;
        foreach ($requiredFunctions as $func) {
            if (!function_exists($func)) {
                $allFunctionsExist = false;
                break;
            }
        }
        $tests['Helper Functions'] = ['passed' => $allFunctionsExist, 'message' => 'All required helper functions available'];
        $allFunctionsExist ? $passed++ : $failed++;
        
        // Test 10: Config Settings
        $configSettings = ['DB_HOST', 'APP_NAME', 'ITEMS_PER_PAGE', 'TIMEZONE'];
        $configValid = true;
        foreach ($configSettings as $setting) {
            if (!defined($setting)) {
                $configValid = false;
                break;
            }
        }
        $tests['Configuration'] = ['passed' => $configValid, 'message' => 'All configuration settings defined'];
        $configValid ? $passed++ : $failed++;
        
        // Display Results
        echo '<div class="bg-white rounded-lg shadow-lg p-6 mb-6">';
        echo '<h2 class="text-2xl font-bold mb-4">Test Results</h2>';
        echo '<div class="space-y-3">';
        
        foreach ($tests as $testName => $test) {
            $status = $test['passed'] ? '✓ PASSED' : '✗ FAILED';
            $colorClass = $test['passed'] ? 'text-green-600' : 'text-red-600';
            echo "<div class='flex justify-between items-center border-b pb-2'>";
            echo "<div>";
            echo "<span class='font-medium'>{$testName}</span>";
            echo "<p class='text-sm text-gray-500'>{$test['message']}</p>";
            echo "</div>";
            echo "<span class='{$colorClass} font-bold'>{$status}</span>";
            echo "</div>";
        }
        
        echo '</div></div>';
        
        // Summary
        $totalTests = $passed + $failed;
        $percentage = ($passed / $totalTests) * 100;
        
        echo '<div class="bg-white rounded-lg shadow-lg p-6">';
        echo '<div class="text-center">';
        
        if ($passed === $totalTests) {
            echo '<div class="text-6xl mb-4">🎉</div>';
            echo '<h3 class="text-2xl font-bold text-green-600 mb-2">All Tests Passed!</h3>';
            echo '<p class="text-gray-600">The attendance system is fully functional and ready for use.</p>';
        } else {
            echo '<div class="text-6xl mb-4">⚠️</div>';
            echo '<h3 class="text-2xl font-bold text-orange-600 mb-2">Some Tests Failed</h3>';
            echo '<p class="text-gray-600">Please review the failed tests and fix the issues.</p>';
        }
        
        echo '<div class="mt-6 grid grid-cols-3 gap-4">';
        echo "<div class='bg-green-50 p-4 rounded-lg'>";
        echo "<div class='text-2xl font-bold text-green-600'>{$passed}</div>";
        echo "<div class='text-sm text-gray-600'>Passed</div>";
        echo "</div>";
        echo "<div class='bg-red-50 p-4 rounded-lg'>";
        echo "<div class='text-2xl font-bold text-red-600'>{$failed}</div>";
        echo "<div class='text-sm text-gray-600'>Failed</div>";
        echo "</div>";
        echo "<div class='bg-blue-50 p-4 rounded-lg'>";
        echo "<div class='text-2xl font-bold text-blue-600'>{$percentage}%</div>";
        echo "<div class='text-sm text-gray-600'>Success Rate</div>";
        echo "</div>";
        echo '</div>';
        
        echo '</div></div>';
        
        // Sample Data Preview
        echo '<div class="bg-white rounded-lg shadow-lg p-6 mt-6">';
        echo '<h2 class="text-2xl font-bold mb-4">Sample Data Preview</h2>';
        echo '<div class="overflow-x-auto">';
        echo '<table class="min-w-full border">';
        echo '<thead class="bg-gray-50">';
        echo '<tr>';
        echo '<th class="border p-2 text-left">Name</th>';
        echo '<th class="border p-2 text-left">ID</th>';
        echo '<th class="border p-2 text-left">Date</th>';
        echo '<th class="border p-2 text-left">Status</th>';
        echo '<th class="border p-2 text-left">Hours</th>';
        echo '<th class="border p-2 text-left">Department</th>';
        echo '</tr>';
        echo '</thead><tbody>';
        
        $sampleCount = 0;
        foreach ($mockAttendanceData as $record) {
            if ($sampleCount++ >= 10) break;
            $badgeClass = getStatusBadgeClass($record['status']);
            echo "<tr class='hover:bg-gray-50'>";
            echo "<td class='border p-2'>{$record['employeeName']}</td>";
            echo "<td class='border p-2'>{$record['employeeId']}</td>";
            echo "<td class='border p-2'>" . formatDate($record['date']) . "</td>";
            echo "<td class='border p-2'><span class='px-2 py-1 rounded-full text-xs {$badgeClass}'>{$record['status']}</span></td>";
            echo "<td class='border p-2'>{$record['workingHours']}h</td>";
            echo "<td class='border p-2'>{$record['department']}</td>";
            echo "</tr>";
        }
        echo '</tbody>;</table>';
        echo '</div>';
        echo '<p class="text-sm text-gray-500 mt-4">Showing 10 of ' . count($mockAttendanceData) . ' total records</p>';
        echo '</div>';
        ?>
    </div>
</body>
</html>