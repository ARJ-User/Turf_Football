<?php
// 1. Database Connection Configuration
$host = '127.0.0.1';
$db = 'thearj'; 
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
  $pdo = new PDO($dsn, $user, $pass, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
  ]);
} catch (\PDOException $e) {
  die("Database connection failed: " . $e->getMessage());
}

// 2. Fetch the Booking ID securely from URL parameters
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $pdo->prepare("SELECT id, username, booking_date, slot_start, slot_end, status FROM football_turf WHERE id = ?");
$stmt->execute([$id]);
$booking = $stmt->fetch();

if (!$booking) {
    die("<h2>Error: Booking receipt not found.</h2>");
}

// Extract name and phone from the database string format "Name (Phone)"
preg_match('/^(.*?)\s*\((.*?)\)$/', $booking['username'], $matches);
$playerName = isset($matches[1]) ? trim($matches[1]) : $booking['username'];
$playerPhone = isset($matches[2]) ? trim($matches[2]) : 'N/A';

$formattedDate = date('d F Y', strtotime($booking['booking_date']));
$timeSlot = date('h:i A', strtotime($booking['slot_start'])) . ' - ' . date('h:i A', strtotime($booking['slot_end']));
$statusText = ($booking['status'] == 1) ? "CONFIRMED" : "PENDING APPROVAL";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt_#<?php echo $booking['id']; ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6;
            color: #1f2937;
            padding: 40px 20px;
            margin: 0;
        }
        .receipt-box {
            max-width: 550px;
            margin: 0 auto;
            background: #ffffff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border-top: 8px solid #22c55e;
        }
        .header {
            text-align: center;
            border-bottom: 2px dashed #e5e7eb;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .header h1 {
            margin: 0;
            font-size: 26px;
            color: #111827;
        }
        .header p {
            margin: 5px 0 0;
            color: #6b7280;
            font-size: 14px;
        }
        .invoice-details {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            color: #4b5563;
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 12px;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 10px;
        }
        .data-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
            font-size: 15px;
        }
        .data-row.total {
            border-top: 2px solid #111827;
            border-bottom: 2px solid #111827;
            font-weight: bold;
            font-size: 18px;
            color: #111827;
            margin-top: 20px;
        }
        .status-badge {
            display: inline-block;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 50px;
            background-color: <?php echo ($booking['status'] == 1) ? '#d1fae5' : '#fef9c3'; ?>;
            color: <?php echo ($booking['status'] == 1) ? '#065f46' : '#854d0e'; ?>;
        }
        .actions-area {
            text-align: center;
            margin-top: 30px;
        }
        .print-btn {
            background-color: #22c55e;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 15px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.2s;
        }
        .print-btn:hover {
            background-color: #16a34a;
        }

        /* Hides layout elements during standard physical printer system triggers */
        @media print {
            body { background: white; padding: 0; }
            .receipt-box { box-shadow: none; border: none; max-width: 100%; padding: 0; }
            .actions-area { display: none; }
        }
    </style>
</head>
<body>

    <div class="receipt-box">
        <div class="header">
            <h1>⚽ aLpha Turf</h1>
            <p>Premium Football Experience</p>
        </div>

        <div class="invoice-details">
            <div>
                <strong>Receipt No:</strong> #ALPH-<?php echo str_pad($booking['id'], 5, '0', STR_PAD_LEFT); ?><br>
                <strong>Status:</strong> <span class="status-badge"><?php echo $statusText; ?></span>
            </div>
            <div style="text-align: right;">
                <strong>Issued Date:</strong> <?php echo date('d M Y'); ?>
            </div>
        </div>

        <div class="section-title">Player Information</div>
        <div class="data-row">
            <span>Name:</span>
            <strong><?php echo htmlspecialchars($playerName); ?></strong>
        </div>
        <div class="data-row" style="margin-bottom: 25px;">
            <span>Phone:</span>
            <strong><?php echo htmlspecialchars($playerPhone); ?></strong>
        </div>

        <div class="section-title">Match Details</div>
        <div class="data-row">
            <span>Scheduled Date:</span>
            <strong><?php echo $formattedDate; ?></strong>
        </div>
        <div class="data-row">
            <span>Time Duration:</span>
            <strong><?php echo $timeSlot; ?></strong>
        </div>
        <div class="data-row">
            <span>Base Cost Rate:</span>
            <strong>800.00 TK</strong>
        </div>

        <div class="data-row total">
            <span>Total Payable:</span>
            <span>800.00 TK</span>
        </div>

        <div class="actions-area">
            <button class="print-btn" onclick="window.print()">Print / Save as PDF</button>
        </div>
    </div>

</body>
</html>
