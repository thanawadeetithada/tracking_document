<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['userrole']) && $_SESSION['userrole'] === 'admin';

require_once 'db.php';

$sql = "SELECT * FROM faculty_progress";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บัญชีรายชื่อ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <script>
    $(document).ready(function() {
        // เปิดใช้งาน Datepicker
        $("#datepicker").datepicker({
            dateFormat: "dd/mm/yy" // กำหนดรูปแบบวันที่
        });
    });
    </script>
    <style>
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

    .search-add {
        display: flex;
        justify-content: space-between;

        /* a {
            margin-bottom: 10px;
            width: fit-content;
        } */
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

    .search-name {
        width: 50%;
        margin-bottom: 10px;
    }

    .btn-header {
        margin-bottom: 10px;
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
    </style>
</head>

<body>
    <div class="navbar navbar-dark bg-dark justify-content-end">
        <div class="nav-item d-flex">
            <?php if ($isLoggedIn): ?>
            <?php if ($isAdmin): ?>
            <a class="nav-link mr-3" href="user_management.php"><i class="fa-solid fa-cogs"></i>&nbsp;&nbsp;จัดการ</a>
            <?php endif; ?>
            <a class="nav-link" href="logout.php"><i class="fa-solid fa-user"></i>&nbsp;&nbsp;Logout</a>
            <?php else: ?>
            <a class="nav-link" href="login.php"><i class="fa-solid fa-user"></i>&nbsp;&nbsp;Login</a>
            <?php endif; ?>
        </div>
    </div>


    <div class="card">
        <div class="header-card">
            <h3 class="text-left">รายการเอกสาร</h3><br>
            <div class="search-add">
                <?php if ($isLoggedIn && $isAdmin): ?>
                <label for="datepicker">เลือกวันที่: </label>
                <input type="text" id="datepicker" name="selected_date" placeholder="เลือกวันที่">

                <button type="button" class="btn btn-success btn-m btn-header"
                    onclick="window.location.href='download_excel.php';">
                    <i class="fa-solid fa-file-medical"></i> ดาวโหลด
                </button>
                <button type="button" class="btn btn-primary btn-m btn-header" data-bs-toggle="modal"
                    data-bs-target="#exampleModal">
                    <i class="fa-solid fa-file-medical"></i> เพิ่มเอกสารใหม่
                </button>

                <input type="text" class="form-control search-name" placeholder="ค้นหาด้วยชื่อ" aria-label="Small"
                    aria-describedby="inputGroup-sizing-sm">
                <?php endif; ?>
            </div>
        </div>
        <br>
        <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <?php if ($isAdmin): ?>
                        <th>เลขทะเบียนหนังสือ</th>
                        <?php endif; ?>

                        <th>ชื่อ-สกุล</th>

                        <?php if ($isAdmin): ?>
                        <th>วิทยาลัย</th>
                        <?php endif; ?>

                        <th>วัน / เดือน / ปี<br>คณะรับเล่มผลงานทางวิชาการ</th>
                        <th>วัน / เดือน / ปี<br>ผ่านอนุกรรมการตรวจสอบ</th>
                        <th>วัน / เดือน / ปี<br>ผ่านคณะกรรมการประจำ</th>
                        <th>เลขที่หนังสือ<br>นำส่งทรัพยากรบุคคล</th>
                        <?php if ($isAdmin): ?>
                        <th>ผ่านมติสภาสถาบัน<br>พระบรมราชชนก</th>
                        <th>จัดการ</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <?php if ($isAdmin): ?>
                        <td><?php echo htmlspecialchars($row['registration_number']); ?></td>
                        <?php endif; ?>
                        <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                        <?php if ($isAdmin): ?>
                        <td><?php echo htmlspecialchars($row['college']); ?></td>
                        <?php endif; ?>
                        <td>
                            <?php echo !empty($row['date_faculty_received']) && $row['date_faculty_received'] !== '0000-00-00' ? 
                                date('d/m/Y', strtotime($row['date_faculty_received'])) : '-';
                            ?>
                        </td>

                        <td>
                            <?php echo !empty($row['committee_approval_date']) && $row['committee_approval_date'] !== '0000-00-00' ? 
                                    date('d/m/Y', strtotime($row['committee_approval_date'])) : '-';
                            ?>
                        </td>

                        <td>
                            <?php 
            echo !empty($row['faculty_approval_date']) && $row['faculty_approval_date'] !== '0000-00-00' ? 
            date('d/m/Y', strtotime($row['faculty_approval_date'])) : '-';
            ?>
                        </td>

                        <td><?php echo !empty($row['book_number_HR']) ? htmlspecialchars($row['book_number_HR']) : '-'; ?>
                        </td>
                        <?php if ($isAdmin): ?>
                        <td><?php echo !empty($row['passed_institution']) ? htmlspecialchars($row['passed_institution']) : '-'; ?>
                        </td>
                        <td class="btn-action">
                            <a href="#" class="btn btn-warning btn-sm edit-btn" data-id="<?php echo $row['id']; ?>"><i
                                    class="fa-solid fa-pencil"></i></a>
                            &nbsp;&nbsp;
                            <a href="#" class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $row['id']; ?>"><i
                                    class="fa-regular fa-trash-can"></i></a>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endwhile; ?>
                </tbody>


            </table>
        </div>
        <?php else: ?>
        <h4 class="text-center">ไม่มีข้อมูล</h4>
        <?php endif; ?>
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
                            <input class="form-control modal-text" id="edit_fullname" name="fullname" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_college" class="col-form-label">วิทยาลัย</label>
                            <input type="text" class="form-control modal-text" id="edit_college" name="college"
                                required>
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
                            <label for="egistration_number" class="col-form-label">เลขทะเบียนหนังสือ</label>
                            <input type="text" class="form-control modal-text" id="egistration_number"
                                name="egistration_number" required>
                        </div>
                        <div class="mb-3">
                            <label for="fullname" class="col-form-label">ชื่อ-สกุล</label>
                            <input class="form-control modal-text" id="fullname" name="fullname" required></input>
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
$('#exampleModal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget);
    var recipient = button.data('whatever');

    var modal = $(this);
    modal.find('.modal-title').text('New message to ' + recipient);
    modal.find('.modal-body input').val(recipient);
});


$(document).ready(function() {
    $(".edit-btn").on("click", function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var registration_number = $(this).closest('tr').find('td:nth-child(1)').text();
        var fullname = $(this).closest('tr').find('td:nth-child(2)').text();
        var college = $(this).closest('tr').find('td:nth-child(3)').text();
        var date_faculty_received = $(this).closest('tr').find('td:nth-child(4)').text();
        var committee_approval_date = $(this).closest('tr').find('td:nth-child(5)').text();
        var faculty_approval_date = $(this).closest('tr').find('td:nth-child(6)').text();
        var book_number_HR = $(this).closest('tr').find('td:nth-child(7)').text();
        var passed_institution = $(this).closest('tr').find('td:nth-child(8)').text();

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

        console.log({
            id: id,
            registration_number: registration_number,
            fullname: fullname,
            college: college,
            date_faculty_received: date_faculty_received,
            committee_approval_date: committee_approval_date,
            faculty_approval_date: faculty_approval_date,
            book_number_HR: book_number_HR,
            passed_institution: passed_institution
        });

        $('#editModal').modal('show');
    });
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