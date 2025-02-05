<?php
include_once('../database/dbconnect.php');
session_start();

if (!isset($_SESSION['admin-login'])) {
    header('Location: index.php');
    exit();
}

$searchTerm = isset($_POST['search']) ? $_POST['search'] : '';

$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = 10; 
$offset = ($currentPage - 1) * $recordsPerPage;

$sql = "SELECT confirm_task_tbl.*, 
               department_tbl.department AS department,
               CONCAT(employee_tbl.fname, ' ', employee_tbl.lname) AS full_name
        FROM confirm_task_tbl
        LEFT JOIN department_tbl ON confirm_task_tbl.department = department_tbl.id 
        LEFT JOIN employee_tbl ON confirm_task_tbl.employee = employee_tbl.id 
        WHERE employee_tbl.fname LIKE '%$searchTerm%' OR employee_tbl.lname LIKE '%$searchTerm%'
        OR confirm_task_tbl.task LIKE '%$searchTerm%' OR confirm_task_tbl.taskdescription LIKE '%$searchTerm%'
        OR confirm_task_tbl.startdate LIKE '%$searchTerm%' OR confirm_task_tbl.enddate LIKE '%$searchTerm%' 
        OR confirm_task_tbl.submitdate LIKE '%$searchTerm%' OR confirm_task_tbl.remarks LIKE '%$searchTerm%' 
        OR department_tbl.department LIKE '%$searchTerm%'
        ORDER BY confirm_task_tbl.id DESC
        LIMIT $offset, $recordsPerPage";


$result = $conn->query($sql);


$sqlCount = "SELECT COUNT(*) AS total 
             FROM confirm_task_tbl
             LEFT JOIN department_tbl ON confirm_task_tbl.department = department_tbl.id 
             LEFT JOIN employee_tbl ON confirm_task_tbl.employee = employee_tbl.id 
             WHERE employee_tbl.fname LIKE '%$searchTerm%' OR employee_tbl.lname LIKE '%$searchTerm%'
             OR confirm_task_tbl.task LIKE '%$searchTerm%' OR confirm_task_tbl.taskdescription LIKE '%$searchTerm%'
             OR confirm_task_tbl.startdate LIKE '%$searchTerm%' OR confirm_task_tbl.enddate LIKE '%$searchTerm%' 
             OR confirm_task_tbl.submitdate LIKE '%$searchTerm%' OR confirm_task_tbl.remarks LIKE '%$searchTerm%' 
             OR department_tbl.department LIKE '%$searchTerm%'";


$countResult = $conn->query($sqlCount);
$rowCount = $countResult->fetch_assoc();
$totalRecords = $rowCount['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finished Task List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
</head>
<body>
<?php include('/xampp/htdocs/task/includes/navbar.php'); ?>
<div class="container mt-3 min-vh-100">
        <div class="card shadow-lg">
            <div class="card-header bg-dark text-info">
                <h4 class = "mt-2">Finished Task List</h4>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-12 mb-3">
                        <form method="POST" class="d-inline">
                            <input type="text" name="search" class="form-control d-inline border border-dark" style="width: auto;" placeholder="Search Task">
                            <button type="submit" class="btn btn-dark mb-1 text-info">Search</button>
                        </form>
                    </div>
                    <!-- DIV TABLE STARTS HERE -->
                    <div class="col-12">
                        <table class="table table-bordered table-striped table-light display" id = "taskTables" style = "width: 100%">
                            <thead class="thead-dark" style = "font-size: 12px">
                                <tr style = "font-size: 12px" class = "table-dark">
                                    <th class = "text-center">ID</th>
                                    <th class = "text-center">Department</th>
                                    <th class = "text-center">Employee Name</th>
                                    <th class = "text-center">Task Title</th>
                                    <th class = "text-center">Task Description</th>
                                    <th class = "text-center">Start Date</th>
                                    <th class = "text-center">End Date</th>
                                    <th class = "text-center">Submitted Date</th>
                                    <th class = "text-center">Remarks</th>
                                </tr>           
                            </thead>
                            <tbody class="thead-dark" style = "font-size: 12px">
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr style = "font-size: 12px">
                                            <td><?php echo $row['id']; ?></td>
                                            <td><?php echo $row['department']; ?></td>
                                            <td><?php echo $row['full_name']; ?></td>
                                            <td><?php echo $row['task']; ?></td>
                                            <td><?php echo $row['taskdescription']; ?></td>
                                            <td><?php echo date('F j, Y', strtotime($row['startdate'])); ?></td>
                                            <td><?php echo date('F j, Y', strtotime($row['enddate'])); ?></td>
                                            <td><?php echo date('F j, Y', strtotime($row['submitdate'])); ?></td>
                                            <td><?php echo $row['remarks']; ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- DIV TABLE ENDS HERE -->

                    <!-- PAGINATION STARTS HERE -->
                    <div class="col-12 text-center mt-3">
                        <p>Showing <?php echo ($offset + 1) . ' to ' . min($offset + $recordsPerPage, $totalRecords) . ' of ' . $totalRecords . ' entries'; ?></p>
                        <ul class="pagination justify-content-center">
                            <li class="page-item bg-dark text-info <?php if ($currentPage == 1) echo 'disabled'; ?>">
                                <a class="page-link bg-dark text-info" href="?page=<?php echo $currentPage - 1; ?>">Previous</a>
                            </li>
                            <?php for ($page = 1; $page <= $totalPages; $page++): ?>
                                <li class="page-item bg-dark text-info <?php if ($page == $currentPage) echo 'active'; ?>">
                                    <a class="page-link bg-dark text-info" href="?page=<?php echo $page; ?>"><?php echo $page; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item bg-dark text-info<?php if ($currentPage == $totalPages) echo 'disabled'; ?>">
                                <a class="page-link bg-dark text-info" href="?page=<?php echo $currentPage + 1; ?>">Next</a>
                            </li>
                        </ul>
                    </div>
                    <!-- PAGINATION ENDS HERE -->
                </div>
            </div>
        </div>
    </div>
<script>
document.addEventListener("DOMContentLoaded", function () {
});
$(document).ready(function() {
    $('#taskTables').DataTable({
        paging: false,        
        searching: false,     
        ordering: true,       
        info: false,          
        lengthChange: false,  
    });
});
</script>
</body>
</html>
