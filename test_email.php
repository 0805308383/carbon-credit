<?php
require_once 'includes/mailer.php';

$testDetails = [
    'order_id' => '000012',
    'project_type' => 'ต้นไม้',
    'province' => 'เชียงใหม่',
    'buy_amount' => '10 ต้น',
    'carbon_amount' => '1.50',
    'price' => '150.00',
    'transaction_date' => date('d M Y H:i')
];

if (sendOrderConfirmationEmail('crossza007@gmail.com', 'Test User', $testDetails)) {
    echo "Test email sent successfully!\n";
} else {
    echo "Failed to send test email.\n";
}
?>
