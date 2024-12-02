<?php
include('includes/connection.php');  // Include connection first

header('Content-Type: application/json');  // Set content type for JSON

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filter = $_POST['filter'];

    $dateCondition = '';
    $dateGroupBy = 'DATE(booking_date)'; // Default to grouping by date
    $statusCondition = " AND Bookings.status = 'completed'"; // Only completed bookings

    // Determine the date condition based on filter
    if ($filter === 'weekly') {
        $dateCondition = "WHERE booking_date >= CURDATE() - INTERVAL 7 DAY" . $statusCondition;
    } elseif ($filter === 'monthly') {
        $dateCondition = "WHERE MONTH(booking_date) = MONTH(CURDATE()) AND YEAR(booking_date) = YEAR(CURDATE())" . $statusCondition;
        $dateGroupBy = 'DATE_FORMAT(booking_date, "%Y-%m-%d")'; // Group by full date for monthly
    } elseif ($filter === 'yearly') {
        $dateCondition = "WHERE YEAR(booking_date) = YEAR(CURDATE())" . $statusCondition;
        $dateGroupBy = 'MONTH(booking_date)'; // Group by month for yearly
    }

    // Fetch total income based on the selected filter
    $incomeQuery = "
        SELECT 
            $dateGroupBy as date, 
            SUM(Packages.price) as total 
        FROM 
            Bookings 
        JOIN 
            Packages ON Bookings.package_id = Packages.package_id
        $dateCondition
        GROUP BY 
            $dateGroupBy
        ORDER BY 
            $dateGroupBy
    ";

    $incomeResult = mysqli_query($conn, $incomeQuery);
    if (!$incomeResult) {
        echo json_encode(['totalIncome' => 0, 'dates' => [], 'totals' => []]); // Handle error gracefully
        exit; // Stop script execution on error
    }

    $totalIncome = 0;
    $dates = [];
    $totals = [];
    
    while ($incomeRow = mysqli_fetch_assoc($incomeResult)) {
        $totalIncome += (float)$incomeRow['total']; // Accumulate total income

        // Format dates based on the filter
        if ($filter === 'weekly') {
            $dates[] = date('l', strtotime($incomeRow['date'])); // Day names for weekly
        } elseif ($filter === 'monthly') {
            $dates[] = date('F j', strtotime($incomeRow['date'])); // Month and day for monthly
        } elseif ($filter === 'yearly') {
            $dates[] = date('F', mktime(0, 0, 0, $incomeRow['date'], 1)); // Month for yearly
        }
        $totals[] = (float)$incomeRow['total'];
    }

    // Output final result as JSON
    echo json_encode(['totalIncome' => $totalIncome, 'dates' => $dates, 'totals' => $totals]);
}
?>
