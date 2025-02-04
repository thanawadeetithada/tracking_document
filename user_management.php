<?php
session_start();

if (!isset($_SESSION['userrole']) || $_SESSION['userrole'] !== 'admin') {
    header('Location: index.php');
    exit();
}

require_once 'db.php';

$sql = "SELECT * FROM users";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการผู้ใช้งาน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">


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
            <?php if ($isAdmin): ?>
            <a class="nav-link mr-3" href="index.php"></i>&nbsp;&nbsp;รายการเอกสาร</a>
            <a class="nav-link" href="logout.php"><i class="fa-solid fa-user"></i>&nbsp;&nbsp;Logout</a>
            <?php endif; ?>
        </div>
    </div>


    <div class="card">
        <div class="header-card">
            <h3 class="text-left">จัดการผู้ใช้งาน</h3><br>
        </div>
        <br>
        <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ชื่อ-สกุล</th>
                        <th>อีเมล</th>
                        <th>สถานะ</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>

                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['userrole']); ?></td>
                        <td class="btn-action">
                            <a href="#" class="btn btn-warning btn-sm edit-btn" data-id="<?php echo $row['id']; ?>"><i
                                    class="fa-solid fa-pencil"></i></a>
                            &nbsp;&nbsp;
                            <a href="#" class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $row['id']; ?>"><i
                                    class="fa-regular fa-trash-can"></i></a>
                        </td>

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
                    <p><strong>ชื่อ-สกุล : </strong> <span id="deleteName"></span></p>
                    <p><strong>อีเมล : </strong> <span id="deleteEmail"></span></p>
                    <p><strong>สถานะ : </strong> <span id="deleteRole"></span></p>

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
                    <form method="post" action="update_user.php">
                        <input type="hidden" id="edit_id" name="id">

                        <div class="mb-3">
                            <label for="edit_name" class="col-form-label">ชื่อ-สกุล</label>
                            <input type="text" class="form-control modal-text" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="col-form-label">อีเมล</label>
                            <input class="form-control modal-text" type="email" id="edit_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_userRole" class="col-form-label">สถานะ</label>
                            <select class="form-control modal-text" id="edit_userRole" name="userrole" required>
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                            </select>
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
                            <input type="text" class="form-control modal-text" id="college" name="college" required>
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
        var name = $(this).closest('tr').find('td:nth-child(1)').text();
        var email = $(this).closest('tr').find('td:nth-child(2)').text();
        var userRolw = $(this).closest('tr').find('td:nth-child(3)').text();

        $('#edit_id').val(id);
        $('#edit_name').val(name);
        $('#edit_email').val(email);
        $('#edit_userRole').val(userRolw);
        $('#editModal').modal('show');
    });
    $(".delete-btn").on("click", function(e) {
    e.preventDefault();
    var id = $(this).data('id');
    var name = $(this).closest('tr').find('td:nth-child(1)').text();
    var email = $(this).closest('tr').find('td:nth-child(2)').text();
    var rolw = $(this).closest('tr').find('td:nth-child(3)').text(); // แก้จาก 'role' เป็น 'rolw'

    $('#deleteName').text(name);
    $('#deleteEmail').text(email);
    $('#deleteRole').text(rolw); // ใช้ 'rolw' แทน 'role'

    $('#confirmDelete').on('click', function() {
        $.ajax({
            url: 'delete_user.php',
            type: 'POST',
            data: {
                id: id
            },
            success: function(response) {
                if (response === 'success') {
                    $('#deleteModal').modal('hide');
                    location.reload(); // รีเฟรชหน้า ถ้าลบผู้ใช้ที่ล็อกอิน
                } else if (response === 'user_deleted') {
                    $('#deleteModal').modal('hide');
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