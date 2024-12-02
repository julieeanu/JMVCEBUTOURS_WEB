<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Roboto', sans-serif;
        }
        h1 {
            color: #343a40;
        }
        .sidebar {
            background-color: #ffffff;
            border-right: 1px solid #dee2e6;
            min-height: 100vh;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
        }
        .sidebar h2 {
            font-weight: bold;
            color: #333;
        }
        .sidebar a {
            color: #555;
            text-decoration: none;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #18264E;
            color: #fff;
        }
        .table thead th {
            background-color: #18264E;
            color: white;
        }
        .table tbody tr:hover {
            background-color: #e9ecef;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #18264E;
        }
         .sidebar .active-link {
    background-color: #18264E;
    color: #fff;
}

.sidebar .active-link:hover {
    background-color: #18264E; /* Ensures it stays the same on hover */
}
    </style>
    <title>Booking Management</title>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <aside class="col-md-3 col-lg-2 sidebar p-3">
                <h2 class="text-center">JMV Tours</h2>
                <nav class="mt-4">
                    <ul class="nav flex-column">
                        <li class="nav-item"><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                        <li class="nav-item"><a href="packages.html" class="nav-link">Packages</a></li>
                        <li class="nav-item">
                            <a href="#" class="nav-link active-link data-bs-toggle="collapse" data-bs-target="#bookingsDropdown" aria-expanded="false">Bookings</a>
                        </li>
                        <li class="nav-item"><a href="ChangePass.php" class="nav-link">Change Password</a></li>
                        <li class="nav-item"><a href="login.html" class="nav-link">Logout</a></li>
                    </ul>
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 dashboard-content p-4">
                <h1 class="text-center mb-4">Bookings Management</h1>
                <form method="get" class="mb-3">
                    <div class="form-row">
                        <div class="col-md-4 mb-2">
                            <select name="filter_status" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="pending" <?php echo (isset($_GET['filter_status']) && $_GET['filter_status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="completed" <?php echo (isset($_GET['filter_status']) && $_GET['filter_status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                <option value="upcoming" <?php echo (isset($_GET['filter_status']) && $_GET['filter_status'] == 'upcoming') ? 'selected' : ''; ?>>Upcoming</option>
                                <option value="canceled" <?php echo (isset($_GET['filter_status']) && $_GET['filter_status'] == 'canceled') ? 'selected' : ''; ?>>Canceled</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <button type="submit" class="btn btn-primary btn-block">Filter</button>
                        </div>
                    </div>
                </form>

                <?php
                // Include database connection
                include('includes/connection.php'); 

                require 'includes/PHPMailer/src/PHPMailer.php'; // Include PHPMailer's classes
                require 'includes/PHPMailer/src/SMTP.php';
                require 'includes/PHPMailer/src/Exception.php';

                use PHPMailer\PHPMailer\PHPMailer;
                use PHPMailer\PHPMailer\Exception;

                // Handle form submission to update booking status
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $booking_id = $_POST['booking_id'];
                    $status = $_POST['status'];
                    $user_email = '';

                    // Update logic for status
                    if ($status === 'confirmed') {
                        $newStatus = 'upcoming';

                        // Fetch user email to send confirmation
                        $stmt = $conn->prepare("SELECT u.user_email FROM Bookings b JOIN Users u ON b.user_id = u.user_id WHERE b.booking_id = ?");
                        $stmt->bind_param("i", $booking_id);
                        if ($stmt->execute()) {
                            $stmt->bind_result($user_email);
                            if ($stmt->fetch()) {
                                // Send confirmation email
                                if (!empty($user_email)) {
                                    $mail = new PHPMailer(true);
                                    try {
                                        // Server settings
                                        $mail->isSMTP();
                                        $mail->Host       = 'smtp.gmail.com';
                                        $mail->SMTPAuth   = true;
                                        $mail->Username   = 'joro.igot.swu@phinmaed.com'; // Your Gmail address
                                        $mail->Password   = 'fenyrabfkapdxjgl'; // Your Gmail password or app password
                                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                                        $mail->Port       = 587;

                                        // Recipients
                                        $mail->setFrom('joro.igot.swu@phinmaed.com', 'JMV Tours'); // Sender
                                        $mail->addAddress($user_email); // Add recipient

                                        // Content
                                        $mail->isHTML(true);
                                        $mail->Subject = 'Booking Confirmed';
                                        $mail->Body    = 'Dear User,<br><br>Your booking has been confirmed. Thank you for choosing JMV Tours!<br><br>Best Regards,<br>JMV Tours Team';

                                        $mail->send();
                                        echo "<div class='alert alert-success'>Email sent successfully to $user_email.</div>";
                                    } catch (Exception $e) {
                                        echo "<div class='alert alert-danger'>Message could not be sent. Mailer Error: {$mail->ErrorInfo}</div>";
                                    }
                                } else {
                                    echo "<div class='alert alert-danger'>No user email found for this booking.</div>";
                                }
                            } else {
                                echo "<div class='alert alert-danger'>No email found for this booking.</div>";
                            }
                        } else {
                            echo "<div class='alert alert-danger'>Error fetching email: " . $stmt->error . "</div>";
                        }
                        $stmt->close();
                    } else {
                        $newStatus = $status; // Set to pending, completed, or canceled directly
                    }

                    // Prepare and bind
                    $stmt = $conn->prepare("UPDATE Bookings SET status = ? WHERE booking_id = ?");
                    $stmt->bind_param("si", $newStatus, $booking_id);

                    if ($stmt->execute()) {
                        echo "<div class='alert alert-success'>Booking status updated successfully.</div>";
                    } else {
                        echo "<div class='alert alert-danger'>Error updating status: " . $stmt->error . "</div>";
                    }

                    $stmt->close();
                }

                // Fetch bookings data with pagination
                $filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';
                $limit = 10; // Entries per page
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $offset = ($page - 1) * $limit;

                $query = "
                    SELECT 
                        B.booking_id, 
                        U.user_name AS user_name, 
                        C.car_name AS car_name, 
                        COALESCE(P.package_name, B.alternative_package_title) AS package_name, -- Use alternative_package_title if package_name is null
                        B.departure_date, 
                        B.booking_date, 
                        B.status, 
                        B.book_by AS booked_by
                    FROM 
                        Bookings B
                    LEFT JOIN 
                        Packages P ON B.package_id = P.package_id
                    JOIN 
                        Users U ON B.user_id = U.user_id
                    JOIN 
                        Cars C ON B.car_id = C.car_id
                ";

                if ($filter_status) {
                    $query .= " WHERE B.status = '" . $conn->real_escape_string($filter_status) . "'";
                }

                $query .= " ORDER BY B.booking_date DESC LIMIT $limit OFFSET $offset";

                $result = $conn->query($query);

                // Get total number of bookings for pagination
                $countQuery = "
                    SELECT COUNT(*) as total 
                    FROM Bookings B
                    LEFT JOIN Packages P ON B.package_id = P.package_id
                    JOIN Users U ON B.user_id = U.user_id
                    JOIN Cars C ON B.car_id = C.car_id
                ";

                if ($filter_status) {
                    $countQuery .= " WHERE B.status = '" . $conn->real_escape_string($filter_status) . "'";
                }

                $countResult = $conn->query($countQuery);
                $totalRows = $countResult->fetch_assoc()['total'];
                $totalPages = ceil($totalRows / $limit);

                if ($result->num_rows > 0) {
                    echo '<table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Customer Name</th>
                                    <th>Car</th>
                                    <th>Package</th>
                                    <th>Departure Date</th>
                                    <th>Booking Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>';
                    while ($row = $result->fetch_assoc()) {
                        echo '<tr>
                                <td>' . $row['booking_id'] . '</td>
                                <td>' . $row['user_name'] . '</td>
                                <td>' . $row['car_name'] . '</td>
                                <td>' . $row['package_name'] . '</td>
                                <td>' . $row['departure_date'] . '</td>
                                <td>' . $row['booking_date'] . '</td>
                                <td>' . $row['status'] . '</td>
                                <td>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="booking_id" value="' . $row['booking_id'] . '">
                                        <select name="status" class="form-control" onchange="this.form.submit()">
                                            <option value="pending"' . ($row['status'] == 'pending' ? ' selected' : '') . '>Pending</option>
                                            <option value="confirmed"' . ($row['status'] == 'confirmed' ? ' selected' : '') . '>Confirmed</option>
                                            <option value="completed"' . ($row['status'] == 'completed' ? ' selected' : '') . '>Completed</option>
                                            <option value="canceled"' . ($row['status'] == 'canceled' ? ' selected' : '') . '>Canceled</option>
                                        </select>
                                    </form>
                                </td>
                              </tr>';
                    }
                    echo '</tbody></table>';

                    // Pagination controls
                    echo '<nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">';
                    if ($page > 1) {
                        echo '<li class="page-item"><a class="page-link" href="?page=' . ($page - 1) . '&filter_status=' . urlencode($filter_status) . '">Previous</a></li>';
                    }

                    for ($i = 1; $i <= $totalPages; $i++) {
                        echo '<li class="page-item' . ($i == $page ? ' active' : '') . '"><a class="page-link" href="?page=' . $i . '&filter_status=' . urlencode($filter_status) . '">' . $i . '</a></li>';
                    }

                    if ($page < $totalPages) {
                        echo '<li class="page-item"><a class="page-link" href="?page=' . ($page + 1) . '&filter_status=' . urlencode($filter_status) . '">Next</a></li>';
                    }
                    echo '</ul></nav>';
                } else {
                    echo '<div class="alert alert-warning">No bookings found.</div>';
                }

                // Close database connection
                $conn->close();
                ?>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
