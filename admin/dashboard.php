<?php
$host = '127.0.0.1';
$db = 'thearj'; 
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES => false,
];

try {
  $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
  die("Database connection failed: " . $e->getMessage());
}

$totalBookings = $pdo->query("SELECT COUNT(*) FROM football_turf")->fetchColumn();
$confirmedCount = $pdo->query("SELECT COUNT(*) FROM football_turf WHERE status = 1")->fetchColumn();
$pendingCount = $pdo->query("SELECT COUNT(*) FROM football_turf WHERE status = 0")->fetchColumn();

$todayDate = date('Y-m-d');
$todayQuery = $pdo->prepare("SELECT COUNT(*) FROM football_turf WHERE booking_date = ?");
$todayQuery->execute([$todayDate]);
$todayCount = $todayQuery->fetchColumn();

$stmt = $pdo->query("SELECT id, username, booking_date, slot_start, slot_end, status FROM football_turf ORDER BY booking_date ASC, slot_start ASC");
$bookings = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Poppins", sans-serif; }
    body { background: #151f39; color: #ffffff; }
    .dashboard { display: flex; min-height: 100vh; }
    .sidebar { width: 260px; background: #111827; padding: 30px 20px; }
    .sidebar h2 { color: #22c55e; margin-bottom: 40px; text-align: center; }
    .sidebar ul { list-style: none; }
    .sidebar li { padding: 15px; margin-bottom: 10px; border-radius: 12px; cursor: pointer; transition: 0.3s; }
    .sidebar li:hover { background: #22c55e; color: #fff; }
    .content { flex: 1; padding: 30px; }
    .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .topbar h1 { font-size: 30px; }
    .topbar button { background: #22c55e; color: white; border: none; padding: 12px 24px; border-radius: 10px; cursor: pointer; font-weight: 600; }
    .cards { display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 30px; }
    .card { flex: 1; min-width: 220px; background: #111827; padding: 25px; border-radius: 18px; transition: 0.3s; border: 1px solid rgba(255, 255, 255, 0.05); }
    .card h3 { color: #94a3b8; font-size: 15px; margin-bottom: 10px; }
    .card h2 { color: #22c55e; font-size: 34px; }
    .card:hover { transform: translateY(-6px); }
    .table-section { background: #111827; padding: 25px; border-radius: 18px; border: 1px solid rgba(255, 255, 255, 0.05); }
    .table-header { margin-bottom: 20px; }
    .table-header h2 { font-size: 24px; }
    .table-wrapper { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    th { text-align: left; padding: 15px; color: #22c55e; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
    td { padding: 15px; border-bottom: 1px solid rgba(255, 255, 255, 0.08); }
    tbody tr:hover { background: rgba(255, 255, 255, 0.03); }
    .pending { background: #facc15; color: #111827; padding: 6px 14px; border-radius: 50px; font-size: 13px; font-weight: 600; }
    .confirmed { background: #22c55e; color: white; padding: 6px 14px; border-radius: 50px; font-size: 13px; font-weight: 600; }
    .confirm { background: #22c55e; color: white; border: none; padding: 10px 16px; border-radius: 8px; cursor: pointer; font-weight: 500; font-size: 13px; text-decoration: none; display: inline-block;}
    .delete { background: #ef4444; color: white; border: none; padding: 10px 16px; border-radius: 8px; cursor: pointer; font-weight: 500; margin-left: 5px; font-size: 13px; text-decoration: none; display: inline-block;}
    @media (max-width: 768px) { .dashboard { flex-direction: column; } .sidebar { width: 100%; } .content { padding: 20px; } .topbar { flex-direction: column; gap: 15px; align-items: flex-start; } }
  </style>
</head>
<body>
  <div class="dashboard">
    <div class="sidebar"><h2>TurfBook</h2><ul><li>Dashboard</li><li>Bookings</li><li>Confirmed</li><li>Pending</li><li>Settings</li><li>Logout</li></ul></div>
    <main class="content">
      <header class="topbar"><h1>Admin Dashboard</h1><button>Admin</button></header>
      <section class="cards">
        <div class="card"><h3>Total Bookings</h3><h2><?php echo $totalBookings; ?></h2></div>
        <div class="card"><h3>Confirmed</h3><h2><?php echo $confirmedCount; ?></h2></div>
        <div class="card"><h3>Pending</h3><h2><?php echo $pendingCount; ?></h2></div>
        <div class="card"><h3>Today</h3><h2><?php echo $todayCount; ?></h2></div>
      </section>
      <section class="table-section">
        <div class="table-header"><h2>Recent Bookings</h2></div>
        <div class="table-wrapper">
          <table>
            <thead>
              <tr><th>User Details</th><th>Booking Date</th><th>Time Slot</th><th>Status</th><th>Action Options</th></tr>
            </thead>
            <tbody>
              <?php if (!empty($bookings)): ?>
                <?php foreach ($bookings as $booking): ?>
                  <?php
                  $isPending = ($booking['status'] == 0);
                  $displayStatus = $isPending ? 'Pending' : 'Confirmed';
                  $statusClass = $isPending ? 'pending' : 'confirmed';
                  $formattedTime = date('h:i A', strtotime($booking['slot_start'])) . ' - ' . date('h:i A', strtotime($booking['slot_end']));
                  ?>
                  <tr>
                    <td><?php echo htmlspecialchars($booking['username']); ?></td>
                    <td><?php echo htmlspecialchars(date('d M Y', strtotime($booking['booking_date']))); ?></td>
                    <td><?php echo htmlspecialchars($formattedTime); ?></td>
                    <td><span class="<?php echo $statusClass; ?>"><?php echo $displayStatus; ?></span></td>
                    <td>
                      <?php if ($isPending): ?>
                        <a href="update_status.php?id=<?php echo $booking['id']; ?>&action=confirm" class="confirm">Confirm</a>
                      <?php endif; ?>
                      <a href="update_status.php?id=<?php echo $booking['id']; ?>&action=delete" class="delete" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="5" style="text-align: center; color: #94a3b8;">No bookings found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>
  </div>
</body>
</html>
