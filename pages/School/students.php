<?php
include '../../head.php';
include_once('../../init.php');
include '../../includings/functions.php';

// for non logged in users
include '../../includings/landingRedirection.php';
// unauthorized access from users with no school
include '../../includings/noSchool_unauthorized.php';



if ($_SESSION['role'] == 'administrator') {
   $stmt = mysqli_prepare($conn, "SELECT school_id from administrator where admin_email=?");
   mysqli_stmt_bind_param($stmt, "s", $_SESSION['email']);
   mysqli_stmt_execute($stmt);
   $result = mysqli_stmt_get_result($stmt);
   $row = mysqli_fetch_assoc($result);
   $school_id = $row['school_id'];
} else {
   $school_id = $_SESSION['school_id'];
}

// after clicking search
if (isset($_POST['submit']) && isset($_POST['search'])) {
   $tmp = $_POST['search'];
   $search = "%" . $tmp . "%";
   $stmt = mysqli_prepare($conn, "SELECT S.student_id, S.student_fname, S.student_lname, COALESCE(COUNT(P.halakah_id), 0) AS total_classes
                                 FROM student S
                                 LEFT JOIN student_halakah P ON S.student_id = P.student_id
                                 WHERE S.school_id = ? AND (S.student_fname like ? OR S.student_lname like ?)
                                 GROUP BY S.student_id;");
   mysqli_stmt_bind_param($stmt, "sss", $school_id, $search, $search);
   mysqli_stmt_execute($stmt);
   $result = mysqli_stmt_get_result($stmt);
} else {
   $stmt = mysqli_prepare($conn, "SELECT S.student_id, S.student_fname, S.student_lname, COALESCE(COUNT(P.halakah_id), 0) AS total_classes
                                 FROM student S
                                 LEFT JOIN student_halakah P ON S.student_id = P.student_id
                                 WHERE S.school_id = ?
                                 GROUP BY S.student_id;");
   mysqli_stmt_bind_param($stmt, "s", $school_id);
   mysqli_stmt_execute($stmt);
   $result = mysqli_stmt_get_result($stmt);
}

if (isset($_COOKIE['studentDeleted']) && $_COOKIE['studentDeleted'] == true) {
   echo "<script>
   alert('تم حذف الطالب(ة)');
 </script>";
} elseif (isset($_COOKIE['errorDeletingStudent']) && $_COOKIE['errorDeletingStudent'] == true) {
   echo "<script>
alert('تعذر حذف الطالب(ة) لخطب ما');

</script>";
}
?>


<title>Students</title>
<link rel="icon" href="../../assets/images/logo/Durar simplified - blue logo.png" type="image/x-icon">
<link rel="stylesheet" href="../../assets/CSS/general.css">
<link rel="stylesheet" href="../../assets/CSS/nav.css">
<link rel="stylesheet" href="../../assets/CSS/User/userProfile.css">
<link rel="stylesheet" href="../../assets/CSS/Student/studEpisode.css">
<link rel="stylesheet" href="../../assets/CSS/Admin/adminEpisode.css">
<link rel="stylesheet" href="../../assets/CSS/footer.css">
<link rel="stylesheet" href="../../assets/CSS/School/schoolMain.css">
<link rel="stylesheet" href="../../assets/CSS/School/schoolCards.css">

</head>

<body dir="rtl">
   <!--Nav bar-->
   <?php include '../../includings/schoolNav.php' ?>

   <div class="main">
      <!-- SIDE BAR -->
      <?php include 'schoolSidebar.php' ?>

      <section class="main_elements">
         <!--side bar toggler-->
         <img src="../../assets/images/icons/slide-left.png" class="sidebar-slider" alt="sid_bra_toggle">
         <section style="display: block;" class="students_list panels_container main-section" id="studs_list">
            <div class="panel">
               <div style="display: flex; justify-content: space-between; border-radius: 5px" class="title">
                  <p> قائمة الطلبة </p>
               </div><br>
               <form method="post" class="search_student">
                  <img src="../../assets/images/icons/search.png" alt="search">
                  <input type="text" name="search" placeholder="ابحث عن الطالب" required>
                  <input type="submit" name="submit" value="submit" hidden>
               </form>
            </div><br>

            <?php
            if (isset($_POST['submit']) && isset($_POST['search'])) {
               ?>
               <div style="font-size: 20px;">
                  <p>
                     <?php echo "نتائج البحث عن '$tmp'"; ?>
                  </p>
               </div><br>
            <?php } ?>

            <?php if ($_SESSION['role'] == 'administrator') { ?>
               <div class="students_list admins_list">

                  <div style="display: grid; grid-gap: 1%" class="head row">
                     <div style="display: flex; justify-content:flex-start; align-items:center; padding-right:10%"
                        class="box"> الاسم </div>
                     <div style="display: flex; justify-content:flex-start; align-items:center; padding-right:10%"
                        class="box"> اللقب </div>
                     <div style="display: flex; justify-content:flex-start; align-items:center; padding-right:10%"
                        class="box"> عدد أقسامه </div>
                     <div style="display: flex; justify-content:flex-start; align-items:center; padding-right:10%;"
                        class="box"> حذف </div>
                  </div>

                  <?php
                  $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);

                  for ($i = 0; $i < count($rows); $i++) { //alt="deleteStudent.php?student_id=$row['student_id']"
                     ?>
                     <div style="display: grid; grid-gap: 1%" class="student row">

                        <div style="display: flex; justify-content:flex-start; align-items:center; padding-right:10%"
                           class="box">
                           <?php echo $rows[$i]['student_fname'] ?>
                        </div>
                        <div style="display: flex; justify-content:flex-start; align-items:center; padding-right:10%"
                           class="box">
                           <?php echo $rows[$i]['student_lname'] ?>
                        </div>
                        <div style="display: flex; justify-content:flex-start; align-items:center; padding-right:10%"
                           class="box">
                           <?php echo $rows[$i]['total_classes'] ?>
                        </div>
                        <button class="box" style="display:flex; justify-content:center"
                           onclick="showRemoveStudentOverlay(event, <?php echo $rows[$i]['student_id']; ?>, '<?php echo $rows[$i]['student_fname'] . ' ' . $rows[$i]['student_lname']; ?>')">
                           X </button>
                     </div>
                     <?php
                  }
                  ?>
                  <?php
                  include "RemoveStudentCard.php";
                  ?>

               </div>

               <!-- ADDING Student -->
               <button class="add_student" onclick="showAddStudentOverlay()">
                  <img src="../../assets/images/icons/plus.png" alt="" srcset="">
               </button>
               <?php include 'AddStudentCard.php' ?>
            <?php } else { ?>
               <div class="students_list admins_list">

                  <div style="display: grid; grid-template-columns: 33% 33% 33%; grid-gap: 1%" class="head row">
                     <div class="box"> الاسم </div>
                     <div class="box"> اللقب </div>
                     <div class="box"> عدد أقسامه </div>
                  </div>

                  <?php

                  while ($row = mysqli_fetch_assoc($result)) { //alt="deleteStudent.php?student_id=$row['student_id']"
                     ?>
                     <div style="display: grid; grid-template-columns: 33% 33% 33%; grid-gap: 1%" class="student row">
                        <div class="box">
                           <?php echo $row['student_fname'] ?>
                        </div>
                        <div class="box">
                           <?php echo $row['student_lname'] ?>
                        </div>
                        <div class="box">
                           <?php echo $row['total_classes'] ?>
                        </div>
                        <?php include 'RemoveStudentCard.php'; ?>
                     </div>
                     <?php
                  }
                  ?>

               </div>
            <?php } ?>

         </section>

      </section>
   </div>


   <div id="footer">
      <!--footer-->
      <?php include '../../includings/schoolFooter.php' ?>
   </div>

   <script src="../../js/workers,studentManagement.js"></script>
   <script src="../../js/addStudentCard.js"></script>
   <script src="../../js/removeStudentCard.js"></script>
   <script src="../../js/sidebars.js"></script>

</body>