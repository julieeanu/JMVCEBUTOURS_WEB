<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include your database connection
include('includes/connection.php'); // Ensure this path is correct

// Fetch the total number of bookings
$totalBookingsQuery = "SELECT COUNT(*) as total FROM Bookings";
$totalBookingsResult = mysqli_query($conn, $totalBookingsQuery);
$totalBookings = mysqli_fetch_assoc($totalBookingsResult)['total'] ?? 0;

// Fetch the total number of customers
$totalCustomersQuery = "SELECT COUNT(*) as total FROM Users";
$totalCustomersResult = mysqli_query($conn, $totalCustomersQuery);
$totalCustomers = mysqli_fetch_assoc($totalCustomersResult)['total'] ?? 0;

// Fetch the total number of pending bookings
$pendingBookingsQuery = "SELECT COUNT(*) as total FROM Bookings WHERE status = 'upcoming'";
$pendingBookingsResult = mysqli_query($conn, $pendingBookingsQuery);
$pendingBookings = mysqli_fetch_assoc($pendingBookingsResult)['total'] ?? 0;

// Fetch total income for the current month
$monthlyIncomeQuery = "
    SELECT 
        SUM(Packages.price) as total 
    FROM 
        Bookings 
    JOIN 
        Packages ON Bookings.package_id = Packages.package_id
    WHERE 
        MONTH(booking_date) = MONTH(CURRENT_DATE()) 
        AND YEAR(booking_date) = YEAR(CURRENT_DATE())
        AND Bookings.status = 'completed'
";
$monthlyIncomeResult = mysqli_query($conn, $monthlyIncomeQuery);
$monthlyIncome = mysqli_fetch_assoc($monthlyIncomeResult)['total'] ?? 0;

// Fetch the bookings data with JOINs
$query = "
    SELECT 
        Bookings.*, 
        Users.user_name, 
        Users.user_contactNumber, 
        Packages.package_name, 
        Packages.price,
        CASE 
            WHEN Bookings.alternative_package_title IS NOT NULL AND Bookings.alternative_package_title != '' 
            THEN Bookings.alternative_package_title 
            ELSE Packages.package_name 
        END AS displayed_package_name
    FROM 
        Bookings 
    JOIN 
        Users ON Bookings.user_id = Users.user_id  
    JOIN 
        Packages ON Bookings.package_id = Packages.package_id  
";

$result = mysqli_query($conn, $query);
if (!$result) {
    die("Error executing query: " . mysqli_error($conn));
}

// Fetch total income data for the current month for the line graph
$incomeQuery = "
    SELECT 
        DATE(booking_date) as date, 
        SUM(Packages.price) as total 
    FROM 
        Bookings 
    JOIN 
        Packages ON Bookings.package_id = Packages.package_id
    WHERE 
        MONTH(booking_date) = MONTH(CURRENT_DATE()) 
        AND YEAR(booking_date) = YEAR(CURRENT_DATE())
    GROUP BY 
        DATE(booking_date)
    ORDER BY 
        DATE(booking_date)
";
$incomeResult = mysqli_query($conn, $incomeQuery);
if (!$incomeResult) {
    die("Error executing income query: " . mysqli_error($conn));
}

$dates = [];
$totals = [];
while ($incomeRow = mysqli_fetch_assoc($incomeResult)) {
    $dates[] = date('l, F j', strtotime($incomeRow['date'])); // e.g., Monday, January 1
    $totals[] = (float)$incomeRow['total'];
}

// Check if any bookings are returned
$bookings = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $bookings[] = $row; // Store each row in the bookings array
    }
} else {
    // Handle no bookings found case if necessary
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - JMV Tours</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" rel="stylesheet">
    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f7f8fa;
            color: #333;
        }
        .sidebar {
            background-color: #ffffff;
            border-right: 1px solid #dee2e6;
            min-height: 100vh;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
            overflow: hidden; /* Disable sidebar scrolling */
        }
        .sidebar h2 {
            font-weight: bold;
            color: #18264E;
        }
        .sidebar a {
            color: #555;
            text-decoration: none;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #18264E;
            color: #fff;
        }
        .dashboard-title {
            font-size: 26px;
            font-weight: bold;
            color: #18264E;
        }
        .dashboard-stats .card {
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .dashboard-stats .card-body {
            font-size: 16px;
            color: #444;
        }
        .dashboard-stats .card-body h3 {
            font-weight: 600;
            margin-bottom: 15px;
            color: #18264E;
        }
        .dashboard-content {
            padding: 20px;
            background-color: #f7f8fa;
        }
        .dashboard-content header {
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        .btn-outline-primary {
            border-color: #18264E;
            color: #18264E;
        }
        .btn-outline-primary:hover {
            background-color: #007bff;
            color: #fff;
        }
        .table-striped > tbody > tr:nth-child(odd) {
            background-color: #f9fafb;
        }
        .income-graph h2, .latest-bookings h2 {
            margin-top: 40px;
            margin-bottom: 20px;
            color: #18264E;
        }
        .form-control {
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: none;
        }
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .dashboard-stats .card {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <aside class="col-md-3 col-lg-2 sidebar p-3">
                <h2 class="text-center">JMV Tours</h2>
                <nav class="mt-4">
                    <ul class="nav flex-column">
                        <li class="nav-item"><a href="#" class="nav-link active">Dashboard</a></li>
                        <li class="nav-item"><a href="packages.html" class="nav-link">Packages</a></li>
                        <li class="nav-item"><a href="Bookings.php" class="nav-link">Bookings</a></li>
                         <li class="nav-item"><a href="ChangePass.php" class="nav-link">Change Password</a></li>
                        <li class="nav-item"><a href="login.html" class="nav-link">Logout</a></li>
                    </ul>
                </nav>
            </aside>

            <!-- Main Dashboard -->
            <main class="col-md-9 col-lg-10 dashboard-content">
                <header class="d-flex justify-content-between align-items-center p-3 bg-white border-bottom">
                    <h1 class="dashboard-title">Dashboard</h1>
                   
                </header>

                <!-- Dashboard Stats -->
                <section class="dashboard-stats d-flex justify-content-around flex-wrap my-4">
                    <div class="card text-center m-2" style="flex: 1 1 200px;">
                        <div class="card-body">
                            <h3>Total Income</h3>
                            <div class="d-flex justify-content-center my-2">
                                <select id="incomeFilter" class="form-select me-2" aria-label="Income Filter">
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly" selected>Monthly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                                <button id="incomeFilterButton" class="btn btn-outline-primary">Filter</button>
                            </div>
                            <p class="fs-4" id="totalIncomeDisplay">₱<?php echo number_format($monthlyIncome, 2); ?></p>
                        </div>
                    </div>
                    <div class="card text-center m-2" style="flex: 1 1 200px;">
                        <div class="card-body">
                            <h3>Total Bookings</h3>
                            <p class="fs-4"><?php echo
                            $totalBookings; ?></p>
                        </div>
                    </div>
                    <div class="card text-center m-2" style="flex: 1 1 200px;">
                        <div class="card-body">
                            <h3>Total Customers</h3>
                            <p class="fs-4"><?php echo $totalCustomers; ?></p>
                        </div>
                    </div>
                    <div class="card text-center m-2" style="flex: 1 1 200px;">
                        <div class="card-body">
                            <h3>Pending Bookings</h3>
                            <p class="fs-4"><?php echo $pendingBookings; ?></p>
                        </div>
                    </div>
                </section>

                <!-- Income Graph -->
                <section class="income-graph">
                    <h2>Daily Income for the Current Month</h2>
                    <canvas id="incomeChart"></canvas>
                </section>

               <!-- Latest Bookings -->

            </main>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#bookingsTable').DataTable();

        // Set up income chart
        var ctx = document.getElementById('incomeChart').getContext('2d');
        var incomeChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($dates); ?>,
                datasets: [{
                    label: 'Daily Income (₱)',
                    data: <?php echo json_encode($totals); ?>,
                    borderColor: 'rgba(0, 123, 255, 1)',
                    backgroundColor: 'rgba(0, 123, 255, 0.2)',
                    borderWidth: 2,
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Income (₱)'
                        },
                        beginAtZero: true
                    }
                }
            }
        });

        // Filter Income Button Event Listener
        $('#incomeFilterButton').on('click', function() {
            const filterValue = $('#incomeFilter').val();

            // Validate filter value before making the request
            if (!filterValue) {
                alert('Please select a valid filter option.');
                return;
            }

            // Make an AJAX request to fetch income based on the selected filter
            $.ajax({
                url: 'fetch_income.php',
                type: 'POST',
                data: { filter: filterValue },
                dataType: 'json',
                success: function(response) {
                    // Check if the response has the expected data
                    if (response && response.totalIncome !== undefined) {
                        // Update the displayed total income
                        $('#totalIncomeDisplay').text('₱' + response.totalIncome.toFixed(2));

                        // Update the income chart with new data
                        updateIncomeChart(response.dates, response.totals);
                    } else {
                        alert('Error fetching income data.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error: ' + status + ': ' + error);
                    alert('Failed to fetch income data. Please try again later.');
                }
            });
        });

        // Function to update the income chart with new data
        function updateIncomeChart(dates, totals) {
            var ctx = document.getElementById('incomeChart').getContext('2d');

            // Clear existing chart
            incomeChart.destroy();

            // Create a new chart with updated data
            incomeChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: [{
                        label: 'Daily Income (₱)',
                        data: totals,
                        borderColor: 'rgba(0, 123, 255, 1)',
                        backgroundColor: 'rgba(0, 123, 255, 0.2)',
                        borderWidth: 2,
                        fill: true,
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Income (₱)'
                            },
                            beginAtZero: true
                        }
                    }
                }
            });
        } // Ensure this closing brace is present
    });
    $(document).ready(function() {
    // Initialize DataTable
    $('#bookingsTable').DataTable();

    // Set up income chart
    var ctx = document.getElementById('incomeChart').getContext('2d');
    var incomeChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($dates); ?>,
            datasets: [{
                label: 'Daily Income (₱)',
                data: <?php echo json_encode($totals); ?>,
                borderColor: 'rgba(0, 123, 255, 1)',
                backgroundColor: 'rgba(0, 123, 255, 0.2)',
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true
                }
            }
        }
    });
});
</script>

</body>
</html>
