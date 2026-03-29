<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/libs/PHPMailer/src/Exception.php';
require_once __DIR__ . '/libs/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/libs/PHPMailer/src/SMTP.php';

function sendOrderConfirmationEmail($toEmail, $toName, $orderDetails) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'crossza007@gmail.com';
        $mail->Password   = 'tqal pmbc onzr iheb'; // App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->CharSet    = 'UTF-8';

        // Recipients
        $mail->setFrom('crossza007@gmail.com', 'Carbon Credit Market');
        $mail->addAddress($toEmail, $toName);

        // Content layout matching the requested screenshot
        $mail->isHTML(true);
        $mail->Subject = 'ยืนยันการซื้อคาร์บอนเครดิตสำเร็จ';
        
        $htmlContent = "
        <div style='font-family: Arial, Tahoma, sans-serif; color: #333; line-height: 1.6;'>
            <h2 style='color: #10b981;'>ยืนยันการซื้อคาร์บอนเครดิตสำเร็จ</h2>
            <p>เรียนคุณ <strong>{$toName}</strong>,</p>
            <p>ระบบได้ทำการตรวจสอบและอนุมัติรายการซื้อคาร์บอนเครดิตของท่านเรียบร้อยแล้ว</p>
            
            <p><strong>รายละเอียดการทำรายการ:</strong></p>
            <ul>
                <li>รหัสคำสั่งซื้อ: <strong>{$orderDetails['order_id']}</strong></li>
                <li>ประเภท: <strong>{$orderDetails['project_type']}</strong></li>
                <li>จังหวัด: <strong>{$orderDetails['province']}</strong></li>
                <li>ปริมาณที่ซื้อ: <strong>{$orderDetails['buy_amount']}</strong></li>
                <li>จำนวนคาร์บอนเครดิต: <strong>{$orderDetails['carbon_amount']} Ton</strong></li>
                <li>ราคาที่ชำระ: <strong>{$orderDetails['price']} Token</strong></li>
                <li>วันที่ทำรายการ: <strong>{$orderDetails['transaction_date']}</strong></li>
            </ul>
            
            <p><strong>สถานะ:</strong> <span style='color: #10b981; font-weight: bold;'>สำเร็จ</span></p>
            
            <p>ระบบได้ดำเนินการโอนคาร์บอนเครดิตเข้าสู่กระเป๋าเงินของท่านเรียบร้อยแล้ว ท่านสามารถตรวจสอบยอดคงเหลือได้ในระบบ</p>
            
            <p>หากพบปัญหาหรือข้อสงสัย กรุณาติดต่อผู้ดูแลระบบ</p>
            
            <p>ขอขอบคุณที่ใช้บริการ<br><strong>ระบบตลาดกลางคาร์บอนเครดิต</strong></p>
        </div>";

        $mail->Body = $htmlContent;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>
