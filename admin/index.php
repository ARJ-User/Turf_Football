<?php
// 1. Database Connection Configuration
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

// 2. Handle Form Submission
$alertMessage = "";
$alertClass = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $fullName = trim($_POST['full_name'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $date = trim($_POST['date'] ?? '');
  $timeSlot = trim($_POST['time'] ?? '');

  if (empty($fullName) || empty($phone) || empty($date) || empty($timeSlot)) {
    $alertMessage = "❌ Please fill out all required fields.";
    $alertClass = "error";
  } else {
    // Split selected dropdown slot into clean TIME formats
    list($start, $end) = explode('-', $timeSlot);
    $slotStart = $start . ":00";
    $slotEnd = $end . ":00";

    $dbUsername = $fullName . " (" . $phone . ")";

    try {
      // AUTOMATIC SLOT BLOCKING CHECK (Safe positional parameters)
      $checkStmt = $pdo->prepare("
          SELECT COUNT(*) 
          FROM football_turf 
          WHERE booking_date = ? 
            AND (
              (slot_start <= ? AND slot_end > ?) OR 
              (slot_start < ? AND slot_end >= ?) OR
              (? <= slot_start AND ? >= slot_end)
            )
      ");

      $checkStmt->execute([
        $date,
        $slotStart,
        $slotStart,
        $slotEnd,
        $slotEnd,
        $slotStart,
        $slotEnd
      ]);

      if ($checkStmt->fetchColumn() > 0) {
        $alertMessage = "⚠️ Sorry, this time slot has already been reserved. Please select a different time or date.";
        $alertClass = "error";
      } else {
        // Free Slot: Insert booking request
        $insertQuery = "INSERT INTO football_turf (username, booking_date, slot_start, slot_end, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($insertQuery);
        $stmt->execute([$dbUsername, $date, $slotStart, $slotEnd, 0]);

        // Capture the new booking ID to generate the dynamic receipt link
        $lastId = $pdo->lastInsertId();

        $alertMessage = "🎉 Booking request submitted successfully! Your slot is held as pending.<br><br>
                        <a href='receipt.php?id=" . $lastId . "' target='_blank' style='display:inline-block; background:#ffffff; color:#111827; padding:10px 20px; border-radius:8px; font-weight:600; text-decoration:none; margin-top:5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>📄 Print / Download Receipt</a>";
        $alertClass = "success";
      }
    } catch (\PDOException $e) {
      $alertMessage = "❌ Error processing booking: " . $e->getMessage();
      $alertClass = "error";
    }
  }
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>aLpha Turf</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Poppins", sans-serif;
      scroll-behavior: smooth;
    }

    body {
      background: #111827;
      color: white;
    }

    .hero {
      height: 100vh;
      width: 100%;
      background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url("https://unsplash.com/photos/blue-and-grey-soccer-ball-on-green-field-under-white-and-blue-sky-during-daytime-IorqsMssQH0");
      background-repeat: no-repeat;
      background-position: center;
      background-size: cover;
      position: relative;
    }

    nav {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 25px 8%;
    }

    .logo {
      font-size: 28px;
      font-weight: 700;
    }

    .nav-btn {
      text-decoration: none;
      color: white;
      background: #22c55e;
      padding: 12px 25px;
      border-radius: 50px;
      font-weight: 600;
    }

    .hero-content {
      height: 80vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      padding: 20px;
    }

    .hero-content h1 {
      font-size: 4rem;
      max-width: 900px;
    }

    .hero-content p {
      margin: 20px 0;
      max-width: 650px;
      font-size: 18px;
    }

    .hero-btn {
      text-decoration: none;
      background-color: #22c55e;
      padding: 18px 35px;
      border-radius: 50px;
      color: white;
      font-weight: 600;
    }

    .pricing {
      padding: 100px 8%;
      text-align: center;
    }

    .pricing h2 {
      font-size: 40px;
      margin-bottom: 50px;
    }

    .pricing-container {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 25px;
    }

    .card {
      width: 300px;
      background: #16213b;
      padding: 40px;
      border-radius: 20px;
      transition: 0.3s;
    }

    .card:hover {
      transform: translateY(-8px);
    }

    .card h1 {
      margin: 20px 0;
      color: #22c55e;
    }

    .featured {
      border: 2px solid #22c55e;
    }

    .booking {
      padding: 100px 8%;
      display: flex;
      justify-content: center;
    }

    .booking-card {
      width: 100%;
      max-width: 700px;
      background: rgba(22, 33, 59, 0.8);
      padding: 40px;
      border-radius: 25px;
    }

    .booking-card h2 {
      text-align: center;
      margin-bottom: 30px;
    }

    input,
    select {
      width: 100%;
      padding: 16px;
      margin-bottom: 20px;
      border: none;
      border-radius: 10px;
      font-size: 15px;
      background: #1f2937;
      color: white;
    }

    input:focus,
    select:focus {
      outline: 2px solid #22c55e;
    }

    button {
      width: 100%;
      padding: 16px;
      background: #22c55e;
      border: none;
      border-radius: 10px;
      color: white;
      font-size: 17px;
      font-weight: 600;
      cursor: pointer;
    }

    button:hover {
      background: #16a34a;
    }

    .status-msg {
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 10px;
      text-align: center;
      font-weight: bold;
      line-height: 1.6;
    }

    .status-msg.success {
      background-color: #14532d;
      color: #4ade80;
      border: 1px solid #22c55e;
    }

    .status-msg.error {
      background-color: #7f1d1d;
      color: #fca5a5;
      border: 1px solid #ef4444;
    }

    footer {
      background: #0f172a;
      text-align: center;
      padding: 25px;
    }

    @media (max-width: 1024px) {
      nav {
        padding: 20px 5%;
      }

      .hero-content h1 {
        font-size: 2.8rem;
      }

      .pricing,
      .booking {
        padding: 60px 5%;
      }
    }

    @media (max-width: 768px) {
      .hero-content h1 {
        font-size: 2.2rem;
      }

      .card {
        width: 100%;
      }
    }
  </style>
</head>

<body>
  <header class="hero">
    <nav>
      <div class="logo">⚽ aLpha Turf</div>
      <a href="#booking" class="nav-btn"> Book Now </a>
    </nav>
    <div class="hero-content">
      <h1>Book Your Perfect Football Match</h1>
      <p>Premium football turf booking experience. Fast, simple and hassle-free booking.</p>
      <a href="#booking" class="hero-btn"> Book Your Slot Now </a>
    </div>
  </header>

  <section class="pricing">
    <h2>Pricing Information</h2>
    <div class="pricing-container">
      <div class="card">
        <h3>Morning Shift</h3>
        <h1>800 TK</h1>
        <p>Per Hour</p>
      </div>
      <div class="card featured">
        <h3>Day Shift</h3>
        <h1>800 TK</h1>
        <p>Per Hour</p>
      </div>
      <div class="card">
        <h3>Night Shift</h3>
        <h1>800 TK</h1>
        <p>Per Hour</p>
      </div>
    </div>
  </section>

  <section class="booking" id="booking">
    <div class="booking-card">
      <h2>Book Your Slot</h2>

      <?php if (!empty($alertMessage)): ?>
        <div class="status-msg <?php echo $alertClass; ?>">
          <?php echo $alertMessage; ?>
        </div>
      <?php endif; ?>

      <form action="#booking" method="POST">
        <label>Full Name:</label>
        <input type="text" name="full_name" placeholder="Enter your name" required>

        <label>Phone Number:</label>
        <input type="tel" name="phone" placeholder="Enter your phone number" required>

        <label>Select Date:</label>
        <input type="date" id="booking_date" name="date" min="<?php echo date('Y-m-d'); ?>" required>

        <label>Select Time Slot:</label>
        <select id="time_slot" name="time" required disabled>
          <option value="">-- Select Date First --</option>
          <option value="06:00-07:00">06:00 AM - 07:00 AM</option>
          <option value="07:00-08:00">07:00 AM - 08:00 AM</option>
          <option value="16:00-17:00">04:00 PM - 05:00 PM</option>
          <option value="17:00-18:00">05:00 PM - 06:00 PM</option>
          <option value="19:00-20:00">07:00 PM - 08:00 PM</option>
          <option value="20:00-21:00">08:00 PM - 09:00 PM</option>
        </select>

        <button type="submit">Confirm My Slot Booking</button>
      </form>
    </div>
  </section>

  <footer>
    <p>&copy; <?php echo date('Y'); ?> aLpha Turf. All Rights Reserved.</p>
  </footer>

  <script>
    const dateInput = document.getElementById('booking_date');
    const timeSelect = document.getElementById('time_slot');

    dateInput.addEventListener('change', function () {
      const selectedDate = this.value;
      if (!selectedDate) {
        timeSelect.disabled = true;
        timeSelect.innerHTML = '<option value="">-- Select Date First --</option>';
        return;
      }

      fetch(`check_slots.php?date=${selectedDate}`)
        .then(response => response.json())
        .then(bookedSlots => {
          timeSelect.disabled = false;
          const options = timeSelect.querySelectorAll('option');
          options.forEach(option => {
            const val = option.value;
            if (val === "") {
              option.textContent = "-- Choose a time slot --";
              return;
            }

            if (bookedSlots.includes(val)) {
              option.disabled = true;
              if (!option.textContent.includes('(Booked)')) {
                option.textContent = option.textContent + ' (Booked) ❌';
              }
            } else {
              option.disabled = false;
              option.textContent = option.textContent.replace(' (Booked) ❌', '');
            }
          });
          timeSelect.value = "";
        })