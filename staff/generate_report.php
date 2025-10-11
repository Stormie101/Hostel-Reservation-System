<?php
require_once '../connect.php';
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;

// Get reservation ID from URL
$reservationId = $_GET['reservation_id'] ?? null;
if (!$reservationId) exit('Invalid reservation ID');

// Fetch full student + booking info
$sql = "SELECT r.reservation_id, r.student_id, r.room_id, r.status AS booking_status,
               s.full_name, s.email, s.phone, s.gender, s.program,
               rm.room_type
        FROM reservations r
        JOIN students s ON r.student_id = s.student_id
        JOIN rooms rm ON r.room_id = rm.room_id
        WHERE r.reservation_id = '$reservationId'
        LIMIT 1";


$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);
if (!$data) exit('No data found');

// Build styled HTML
$html = "
<style>
  body {
    font-family: 'Segoe UI', sans-serif;
    padding: 40px;
    color: #333;
    line-height: 1.6;
  }
  .header {
    text-align: center;
    margin-bottom: 40px;
  }
  .header h1 {
    font-size: 24px;
    color: #004aad;
    margin-bottom: 5px;
  }
  .header p {
    font-size: 14px;
    color: #666;
  }
  .section-title {
    font-size: 18px;
    font-weight: bold;
    margin-top: 30px;
    margin-bottom: 10px;
    color: #004aad;
    border-bottom: 1px solid #ccc;
    padding-bottom: 4px;
  }
  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    margin-bottom: 20px;
  }
  td {
    padding: 10px;
    border: 1px solid #ccc;
    font-size: 14px;
  }
  tr:nth-child(even) {
    background-color: #f9f9f9;
  }
  .footer {
    text-align: center;
    font-size: 12px;
    color: #777;
    margin-top: 40px;
  }
</style>

<div class='header'>
  <h1>UPTM Hostel Reservation Report</h1>
  <p>Generated for internal review and administrative purposes</p>
</div>

<p>This report summarizes the hostel booking application submitted by <strong>{$data['full_name']}</strong>, a student enrolled in the <strong>{$data['program']}</strong> program at University Poly-Tech Malaysia (UPTM). The application was processed under reservation ID <strong>{$data['reservation_id']}</strong>.</p>

<div class='section-title'>Student Profile</div>
<p>The applicant, <strong>{$data['full_name']}</strong>, is registered under student ID <strong>{$data['student_id']}</strong>. She/He can be contacted via email at <strong>{$data['email']}</strong> or by phone at <strong>{$data['phone']}</strong>. Gender: <strong>{$data['gender']}</strong>.</p>

<table>
  <tr><td><strong>Student ID</strong></td><td>{$data['student_id']}</td></tr>
  <tr><td><strong>Name</strong></td><td>{$data['full_name']}</td></tr>
  <tr><td><strong>Email</strong></td><td>{$data['email']}</td></tr>
  <tr><td><strong>Contact No.</strong></td><td>{$data['phone']}</td></tr>
  <tr><td><strong>Gender</strong></td><td>{$data['gender']}</td></tr>
  <tr><td><strong>Program</strong></td><td>{$data['program']}</td></tr>
</table>

<div class='section-title'>Room Assignment & Booking Status</div>
<p>The student was assigned to room <strong>{$data['room_id']}</strong>, categorized as a <strong>{$data['room_type']}</strong> room. The final status of the booking is recorded as <strong>{$data['booking_status']}</strong>.</p>

<table>
  <tr><td><strong>Room ID</strong></td><td>{$data['room_id']}</td></tr>
  <tr><td><strong>Room Type</strong></td><td>{$data['room_type']}</td></tr>
  <tr><td><strong>Booking Status</strong></td><td>{$data['booking_status']}</td></tr>
</table>

<p>This document serves as a formal record of the student's hostel application and its outcome. Please retain this report for administrative tracking and future reference.</p>

<div class='footer'>Report generated on " . date('d M Y, h:i A') . "</div>
";


// Generate PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("student_booking_report_{$data['student_id']}.pdf", ["Attachment" => false]);
?>
