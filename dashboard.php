<?php
session_start();
require_once 'db.php'; 

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่ ถ้าไม่ให้กลับไปที่หน้า index.php
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// ดึงข้อมูลผู้ใช้จาก session
$userId = $_SESSION['user_id'];
$fullName = $_SESSION['fullname'];
$email = $_SESSION['user_email'];
$userRole = $_SESSION['user_role'];

// ตรวจสอบสิทธิ์ของผู้ใช้
if ($userRole == 'admin' || $userRole == 'superadmin') {
    // ถ้าเป็น admin ให้ดึงข้อมูลทั้งหมด
    $sql = "SELECT * FROM faculty_progress";
} elseif ($userRole == 'user') {
    // ถ้าเป็น user ให้ดึงข้อมูลเฉพาะของตัวเอง โดยตัดช่องว่างออกเพื่อตรวจสอบชื่อ
    $fullNameTrimmed = str_replace(' ', '', $fullName);
    $sql = "SELECT * FROM faculty_progress WHERE REPLACE(TRIM(fullname), ' ', '') = ?";
}

$stmt = $conn->prepare($sql);

if ($userRole == 'user') {
    $stmt->bind_param('s', $fullNameTrimmed);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บัญชีรายชื่อ</title>
    <!-- ใช้ Bootstrap และ FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <style>
    /* ปรับแต่ง UI */
    body {
        background-color: #f9fafc;
        font-family: 'Arial', sans-serif;
        height: 100vh;
        margin: 0;
    }

    .card {
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.2);
        background: white;
        margin-top: 50px;
        margin: 3% 5%;
        background-color: #ffffff;
    }

    .table th,
    .table td {
        text-align: center;
        font-size: 14px;

    }

    .table {
        background: #f8f9fa;
        border-radius: 10px;
    }

    .table th {
        background-color: #f9fafc;
        color: black;
    }

    .modal-dialog {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;

    }

    .modal-content {
        width: 100%;
        max-width: 500px;
    }

    .header-card {
        display: flex;
        justify-content: space-between;
    }

    .form-control modal-text {
        height: fit-content;
        width: 50%;
    }

    .table th:nth-child(4),
    .table td:nth-child(4) {
        width: 17%;
    }

    .table th:nth-child(5),
    .table td:nth-child(5) {
        width: 15%;
    }

    .table th:nth-child(6),
    .table td:nth-child(6) {
        width: 15%;
    }

    .table th:nth-child(7),
    .table td:nth-child(7) {
        width: 13%;
    }

    .table td:nth-child(9) {
        text-align: center;
        vertical-align: middle;
    }

    .btn-action {
        display: flex;
        justify-content: center;
        align-items: center;
    }


    .modal-text {
        width: 100%;
    }

    .modal-header {
        font-weight: bold;
        padding: 25px;
    }

    .nav-item a {
        color: white;
        margin-right: 1rem;
    }

    .navbar {
        padding: 20px;
    }

    .nav-link:hover {
        color: white;
    }

    .modal-body {
        padding: 10px 40px;
    }

    .search-add {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        align-items: flex-end;
    }

    .tab-func {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }
    </style>
</head>

<body>
    <!-- Navbar -->
    <div class="navbar navbar-dark bg-dark justify-content-end">
        <div class="nav-item d-flex">
            <?php if ($userRole != 'user'): ?>
            <a class="nav-link mr-3" href="user_management.php"><i class="fa-solid fa-cogs"></i>&nbsp;&nbsp;จัดการ</a>
            <?php endif; ?>
            <a class="nav-link" href="logout.php"><i class="fa-solid fa-user"></i>&nbsp;&nbsp;Logout</a>
        </div>
    </div>

    <!-- ส่วนแสดงข้อมูล -->
    <div class="card">
        <div class="header-card">
            <h3 class="text-left">รายการเอกสาร</h3><br>
            <div class="search-add">
                <?php if ($userRole != 'user'): ?>
                <div class="tab-func">
                    <button type="button" class="btn btn-success btn-m btn-header"
                        onclick="window.location.href='download_excel.php';">
                        <i class="fa-solid fa-file-medical"></i> ดาวโหลด
                    </button>
                </div>
                <div class="tab-func">
                    <button type="button" class="btn btn-primary btn-m btn-header" data-bs-toggle="modal"
                        data-bs-target="#exampleModal">
                        <i class="fa-solid fa-file-medical"></i> เพิ่มเอกสารใหม่
                    </button>
                </div>
                <div class="tab-func">
                    <input type="text" class="form-control search-name" placeholder="ค้นหาด้วยชื่อ" aria-label="Small"
                        aria-describedby="inputGroup-sizing-sm">
                </div>
                <?php endif; ?>
            </div>
        </div>
        <br>
        <!-- ตารางแสดงข้อมูล -->
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <?php if ($userRole == 'admin'): ?>
                        <th>เลขทะเบียนหนังสือ</th>
                        <?php endif; ?>
                        <th>ชื่อ-สกุล</th>
                        <?php if ($userRole == 'admin'): ?>
                        <th>วิทยาลัย</th>
                        <?php endif; ?>
                        <th>วัน / เดือน / ปี<br>คณะรับเล่มผลงานทางวิชาการ</th>
                        <th>วัน / เดือน / ปี<br>ผ่านอนุกรรมการตรวจสอบ</th>
                        <th>วัน / เดือน / ปี<br>ผ่านคณะกรรมการประจำ</th>
                        <th>เลขที่หนังสือ<br>นำส่งทรัพยากรบุคคล</th>
                        <?php if ($userRole == 'admin'): ?>
                        <th>ผ่านมติสภาสถาบัน<br>พระบรมราชชนก</th>
                        <th>จัดการ</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <?php if ($userRole == 'admin'): ?>
                        <td><?php echo htmlspecialchars($row['registration_number']); ?></td>
                        <?php endif; ?>

                        <td><?php echo !empty($row['fullname']) ? htmlspecialchars($row['fullname']) : '-'; ?></td>

                        <?php if ($userRole == 'admin'): ?>
                        <td><?php echo !empty($row['college']) ? htmlspecialchars($row['college']) : '-'; ?></td>
                        <?php endif; ?>

                        <td>
                            <?php echo !empty($row['date_faculty_received']) && $row['date_faculty_received'] !== '0000-00-00' ? 
                        date('d/m/Y', strtotime($row['date_faculty_received'])) : 'อยู่ระหว่างการตรวจสอบ';
                    ?>
                        </td>
                        <td>
                            <?php echo !empty($row['committee_approval_date']) && $row['committee_approval_date'] !== '0000-00-00' ? 
                        date('d/m/Y', strtotime($row['committee_approval_date'])) : 'อยู่ระหว่างการตรวจสอบ';
                    ?>
                        </td>
                        <td>
                            <?php echo !empty($row['faculty_approval_date']) && $row['faculty_approval_date'] !== '0000-00-00' ? 
                        date('d/m/Y', strtotime($row['faculty_approval_date'])) : 'อยู่ระหว่างการตรวจสอบ';
                    ?>
                        </td>
                        <td><?php echo !empty($row['book_number_HR']) ? htmlspecialchars($row['book_number_HR']) : 'อยู่ระหว่างการตรวจสอบ'; ?>
                        </td>
                        <?php if ($userRole == 'admin'): ?>
                        <td><?php echo !empty($row['passed_institution']) ? htmlspecialchars($row['passed_institution']) : 'อยู่ระหว่างการตรวจสอบ'; ?>
                        </td>
                        <td class="btn-action">
                            <a href="#" class="btn btn-warning btn-sm edit-btn" data-id="<?php echo $row['id']; ?>"><i
                                    class="fa-solid fa-pencil"></i></a>
                            &nbsp;&nbsp;
                            <a href="#" class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $row['id']; ?>"><i
                                    class="fa-regular fa-trash-can"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center">ไม่มีข้อมูล</td>
                    </tr>
                    <?php endif; ?>
                </tbody>

            </table>
        </div>
    </div>

    <!-- Modal ยืนยันการลบ -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">คุณต้องการลบข้อมูลนี้หรือไม่?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>เลขทะเบียนหนังสือ : </strong> <span id="deleteRegistrationNumber"></span></p>
                    <p><strong>ชื่อ-สกุล : </strong> <span id="deleteFirstLast"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">ลบ</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal แก้ไขข้อมูล -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <h5 class="modal-header">
                    แก้ไขข้อมูล
                </h5>
                <div class="modal-body">
                    <form method="post" action="update_document.php">
                        <input type="hidden" id="edit_id" name="id">

                        <div class="mb-3">
                            <label for="edit_registration_number" class="col-form-label">เลขทะเบียนหนังสือ</label>
                            <input type="text" class="form-control modal-text" id="edit_registration_number"
                                name="registration_number" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_fullname" class="col-form-label">ชื่อ-สกุล</label>
                            <input class="form-control modal-text" id="edit_fullname" name="fullname">
                        </div>
                        <div class="mb-3">
                            <label for="edit_college" class="col-form-label">วิทยาลัย</label>
                            <input type="text" class="form-control modal-text" id="edit_college" name="college">
                        </div>
                        <div class="mb-3">
                            <label for="edit_date_faculty_received" class="col-form-label">วัน / เดือน / ปี
                                คณะรับเล่มผลงานทางวิชาการ</label>
                            <input type="date" class="form-control modal-text" id="edit_date_faculty_received"
                                name="date_faculty_received">
                        </div>
                        <div class="mb-3">
                            <label for="edit_committee_approval_date" class="col-form-label">วัน / เดือน / ปี
                                ผ่านอนุกรรมการตรวจสอบ</label>
                            <input type="date" class="form-control modal-text" id="edit_committee_approval_date"
                                name="committee_approval_date">
                        </div>
                        <div class="mb-3">
                            <label for="edit_faculty_approval_date" class="col-form-label">วัน / เดือน / ปี
                                ผ่านคณะกรรมการประจำ</label>
                            <input type="date" class="form-control modal-text" id="edit_faculty_approval_date"
                                name="faculty_approval_date">
                        </div>
                        <div class="mb-3">
                            <label for="edit_book_number_HR" class="col-form-label">เลขที่หนังสือ
                                นำส่งทรัพยากรบุคคล</label>
                            <input type="text" class="form-control modal-text" id="edit_book_number_HR"
                                name="book_number_HR">
                        </div>
                        <div class="mb-3">
                            <label for="edit_passed_institution"
                                class="col-form-label">ผ่านมติสภาสถาบันพระบรมราชชนก</label>
                            <input type="text" class="form-control modal-text" id="edit_passed_institution"
                                name="passed_institution">
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                            <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal เพิ่มข้อมูล -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <h5 class="modal-header">
                    เพิ่มข้อมูล
                </h5>
                <div class="modal-body">
                    <form method="post" action="insert_document.php">
                        <div class="mb-3">
                            <label for="registration_number" class="col-form-label">เลขทะเบียนหนังสือ</label>
                            <input type="text" class="form-control modal-text" id="registration_number"
                                name="registration_number" required>
                        </div>
                        <div class="mb-3">
                            <label for="fullname" class="col-form-label">ชื่อ-สกุล</label>
                            <input class="form-control modal-text" id="fullname" name="fullname"></input>
                        </div>
                        <div class="mb-3">
                            <label for="college" class="col-form-label">วิทยาลัย</label>
                            <input type="text" class="form-control modal-text" id="college" name="college">
                        </div>
                        <div class="mb-3">
                            <label for="date_faculty_received" class="col-form-label">วัน / เดือน / ปี
                                คณะรับเล่มผลงานทางวิชาการ</label>
                            <input type="date" class="form-control modal-text" id="date_faculty_received"
                                name="date_faculty_received">
                        </div>
                        <div class="mb-3">
                            <label for="committee_approval_date" class="col-form-label">วัน / เดือน / ปี
                                ผ่านอนุกรรมการตรวจสอบ</label>
                            <input type="date" class="form-control modal-text" id="committee_approval_date"
                                name="committee_approval_date">
                        </div>
                        <div class="mb-3">
                            <label for="faculty_approval_date" class="col-form-label">วัน / เดือน / ปี
                                ผ่านคณะกรรมการประจำ</label>
                            <input type="date" class="form-control modal-text" id="faculty_approval_date"
                                name="faculty_approval_date">
                        </div>
                        <div class="mb-3">
                            <label for="book_number_HR" class="col-form-label">เลขที่หนังสือ
                                นำส่งทรัพยากรบุคคล</label>
                            <input type="text" class="form-control modal-text" id="book_number_HR"
                                name="book_number_HR">
                        </div>
                        <div class="mb-3">
                            <label for="passed_institution" class="col-form-label">ผ่านมติสภาสถาบันพระบรมราชชนก</label>
                            <input type="text" class="form-control modal-text" id="passed_institution"
                                name="passed_institution">
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                            <button type="submit" class="btn btn-primary">บันทึก</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    //โชว์ modal
$('#exampleModal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget);
    var recipient = button.data('whatever');

    var modal = $(this);
    modal.find('.modal-title').text('New message to ' + recipient);
    modal.find('.modal-body input').val(recipient);
});

$(document).ready(function() {
    $(document).ready(function() {
        //แก้ไข
        $(".edit-btn").on("click", function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var registration_number = $(this).closest('tr').find('td:nth-child(1)').text()
                .trim();
            var fullname = $(this).closest('tr').find('td:nth-child(2)').text()
                .trim();
            var college = $(this).closest('tr').find('td:nth-child(3)').text()
                .trim();
            var date_faculty_received = $(this).closest('tr').find('td:nth-child(4)').text()
                .trim();
            var committee_approval_date = $(this).closest('tr').find('td:nth-child(5)').text()
                .trim();
            var faculty_approval_date = $(this).closest('tr').find('td:nth-child(6)').text()
                .trim();
            var book_number_HR = $(this).closest('tr').find('td:nth-child(7)').text()
                .trim();
            var passed_institution = $(this).closest('tr').find('td:nth-child(8)').text()
                .trim();

            function formatDateToInput(dateString) {
                let dateParts = dateString.split('/');
                let day = dateParts[0];
                let month = dateParts[1] - 1;
                let year = dateParts[2];

                let formattedDate = new Date(year, month, day);
                formattedDate.setDate(formattedDate.getDate() + 1);
                let inputDate = formattedDate.toISOString().split('T')[0];
                return inputDate;
            }

            $('#edit_id').val(id);
            $('#edit_registration_number').val(registration_number);
            $('#edit_fullname').val(fullname);
            $('#edit_college').val(college);
            $('#edit_date_faculty_received').val(formatDateToInput(date_faculty_received));
            $('#edit_committee_approval_date').val(formatDateToInput(committee_approval_date));
            $('#edit_faculty_approval_date').val(formatDateToInput(faculty_approval_date));
            $('#edit_book_number_HR').val(book_number_HR);
            $('#edit_passed_institution').val(passed_institution);
            $('#editModal').modal('show');
        });
    });
//ลบ
    $(".delete-btn").on("click", function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var registration_number = $(this).closest('tr').find('td:nth-child(1)').text();
        var fullname = $(this).closest('tr').find('td:nth-child(2)').text();

        $('#deleteFirstLast').text(fullname);
        $('#deleteRegistrationNumber').text(registration_number);

        $('#confirmDelete').on('click', function() {
            $.ajax({
                url: 'delete_data.php',
                type: 'POST',
                data: {
                    id: id
                },
                success: function(response) {
                    if (response === 'success') {

                        $('#deleteModal').modal('hide');

                        location
                            .reload();
                    } else {
                        alert('ไม่สามารถลบข้อมูลได้');
                    }
                },
                error: function() {
                    alert('เกิดข้อผิดพลาดในการลบข้อมูล');
                }
            });
        });

        $('#deleteModal').modal('show');
    });
});

$(document).ready(function() {
//ช่องค้นหา
    $('.search-name').on('keyup', function() {
        var searchValue = $(this).val().toLowerCase();
        $('table tbody tr').each(function() {
            var rowName = $(this).find('td:nth-child(2)').text()
                .toLowerCase();
            if (rowName.indexOf(searchValue) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    function sortTableByName() {
        var rows = $('table tbody tr').get();

        rows.sort(function(a, b) {
            var nameA = $(a).find('td:nth-child(2)').text()
                .toLowerCase();
            var nameB = $(b).find('td:nth-child(2)').text().toLowerCase();

            if (nameA < nameB) {
                return -1;
            } else if (nameA > nameB) {
                return 1;
            } else {
                return 0;
            }
        });

        $.each(rows, function(index, row) {
            $('table tbody').append(row);
        });
    }

    $('#sortNameBtn').on('click', function() {
        sortTableByName();
    });
});
</script>

</html>