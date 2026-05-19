<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/functions.php';

$scenario_id = 2; // Tours

$package_id = (int)($_GET['id'] ?? 10);

/* Fetch package */
$stmt = $mysqli->prepare("SELECT id, name, duration_days,duration_nights, pricing FROM packages WHERE id=? LIMIT 1");
$stmt->bind_param("i", $package_id);
$stmt->execute();
$package = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        /* ===============================
           1. READ INPUT
        =============================== */

        $contact_name  = trim($_POST['name']);
        $contact_email = trim($_POST['email']);
        $contact_phone = preg_replace('/[^0-9]/', '', $_POST['phone']);
        $travel_date   = $_POST['travel_date'];
        $adults        = (int)$_POST['adults'];
        $children      = (int)$_POST['children'];
        $child_ages    = $children>0 ? $_POST['child_ages'] : [];

        $meeting_date  = $_POST['meeting_date'] ?? null;
        $meeting_time  = $_POST['meeting_time'] ?? null;
        $timezone      = $_POST['timezone'] ?? "Europe/London";

        $preferred_contact = $_POST['preferred_contact'] ?? 'Phone';

        $submit_type = $_POST['submit_type'] ?? '';
        if ($submit_type === 'confirm') {
            if (empty($meeting_date) || empty($meeting_time)) {
                throw new Exception("Please select meeting date and time.");
            }
        }

        if ($adults < 1) {
            throw new Exception("At least 1 adult required");
        }

        /* ===============================
           2. CREATE bookings_tours
        =============================== */

        $travellers_count = $adults + $children;
        $tour_json = json_encode($package, JSON_UNESCAPED_UNICODE);

        $stmt = $mysqli->prepare("
            INSERT INTO bookings_tours
            (booking_id,package_id,tour_name,tour_duration,tour_price,
             original_amount,discount_id,discount_code,discount_name,
             discount_type,discount_value,discount_amount,final_amount,
             travellers_count,adults,children,rooms,travel_date,tour_json,travellers_json)
            VALUES (NULL,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");

        $zero = 0;
        $null = null;
        $rooms = 1;
        $travellers_json = json_encode([]);

        $pduration = $package['duration_days']."D / ".$package['duration_nights']."N";
        $tour_name = $package['name'];

        $pricing    = json_decode($package['pricing'] ?? '{}', true) ?: [];

        $original_amount = $pricing['total_cost']??$zero;
        $tour_price = $pricing['total_cost']??$zero;
        $final_amount = $pricing['sell_price']??$zero;

        $stmt->bind_param(
            "issddissssddiiiisss",
            $package_id,
            $tour_name,
            $pduration,
            $original_amount,
            $tour_price,
            $null,
            $null,
            $null,
            $null,
            $null,
            $zero,
            $final_amount,
            $travellers_count,
            $adults,
            $children,
            $rooms,
            $travel_date,
            $tour_json,
            $travellers_json
        );

        $stmt->execute();
        $tourBookingId = $stmt->insert_id;
        $stmt->close();

        /* ===============================
           3. CUSTOMER CHECK
        =============================== */

        $stmt = $mysqli->prepare("SELECT id FROM customers WHERE phone=? LIMIT 1");
        $stmt->bind_param("s",$contact_phone);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if($res){
            $contact_entity_id = $res['id'];
        } else {
            $stmt = $mysqli->prepare("
                INSERT INTO customers (name,email,phone,created_at)
                VALUES (?,?,?,NOW())
            ");
            $stmt->bind_param("sss",$contact_name,$contact_email,$contact_phone);
            $stmt->execute();
            $contact_entity_id = $stmt->insert_id;
            $stmt->close();
        }

        /* ===============================
           4. MASTER BOOKING
        =============================== */
        if($meeting_date!=null) {
            $notes = "Meeting scheduled on $meeting_date at $meeting_time ($timezone)\n";
            $notes .= "Preferred Contact: $preferred_contact";
        }

        $stmt = $mysqli->prepare("
            INSERT INTO bookings
            (date,time,contact_name,subject,notes,channel_id,contact_type_id,type_id,
             owner_id,assigned_to,status,contact_entity_id,contact_phone,contact_email,itype,meeting_date, meeting_time, meeting_timezone)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");

        $today = date('Y-m-d');
        $time  = date('H:i:s');

        $status = "New";
        $channel_id = 6;
        $contact_type_id = 1;
        $owner_id = 1;
        $itype = "IN";

        $subject = "Tour Booking - ".$package['name'];

        $stmt->bind_param(
            "sssssiiiiisissssss",
            $today,
            $time,
            $contact_name,
            $subject,
            $notes,
            $channel_id,
            $contact_type_id,
            $scenario_id,
            $owner_id,
            $owner_id,
            $status,
            $contact_entity_id,
            $contact_phone,
            $contact_email,
            $itype,
            $meeting_date,
            $meeting_time,
            $timezone
        );

        $stmt->execute();
        $booking_id = $stmt->insert_id;
        $stmt->close();

        /* LINK TOUR */
        $stmt = $mysqli->prepare("UPDATE bookings_tours SET booking_id=? WHERE id=?");
        $stmt->bind_param("ii",$booking_id,$tourBookingId);
        $stmt->execute();
        $stmt->close();

        /* ===============================
           5. AUTO ADD FOLLOWUP ENTRY
        ================================ */

        $agent_id   = $_SESSION['user_id'] ?? 1; // logged-in user if available
        $agent_name = $_SESSION['user_name'] ?? "System";

        $note_text = $package
            ? "New tour enquiry submitted via website for ".$package['name']
            : "New general tour enquiry submitted via website";

        $note_text .= "\nTravel Date: ".$travel_date;
        $note_text .= "\nAdults: ".$adults;
        $note_text .= "\nChildren: ".$children;

        if (!empty($child_ages)) {
            $note_text .= "\nChild Ages: ".implode(", ", $child_ages);
        }

        if (!empty($meeting_date) && !empty($meeting_time)) {
            $note_text .= "\nMeeting Scheduled: $meeting_date at $meeting_time ($timezone)";
        }

        $note_text .= "\nPreferred Contact: ".$preferred_contact;

        $stmt = $mysqli->prepare("
            INSERT INTO bookings_followup
            (booking_id, note_text, created_by, created_by_name)
            VALUES (?,?,?,?)
        ");
        $stmt->bind_param("isis", $booking_id, $note_text, $agent_id, $agent_name);
        $stmt->execute();
        $stmt->close();

        /* ===============================
           6. AGENT LOG ENTRY
        ================================ */

        $uid   = $contact_entity_id;
        $utype = "customer";
        $user_ip   = $_SERVER['REMOTE_ADDR'] ?? '';

        $snippet = substr($note_text, 0, 120);

        $log_text = "Added booking followup for booking #$booking_id - $snippet";
        $logarr = [
            'agent_id'  => 1,
            'log'       => $log_text,
            'ip'        => $user_ip,
            'customer_id' => $contact_entity_id,
            'timestamp' => $datetime
        ];
        $db->insert('people_logs', $logarr);

        // add to reminder
        if (!empty($meeting_date) && !empty($meeting_time)) {

            $customer_id = $contact_entity_id;

            $reminder_at = date(
                'Y-m-d H:i:s',
                strtotime("$meeting_date $meeting_time")
            );

            // Prevent past reminders
            if (strtotime($reminder_at) > time()) {

                $created_at = date('Y-m-d H:i:s');
                $type = 'Meeting';
                $contact_id = null;

                $reminder_note = "Scheduled Meeting for Tour Booking - ".$package['name'];
                $reminder_note .= "\nPreferred Contact: ".$preferred_contact;

                $stmt = $mysqli->prepare("
                    INSERT INTO customers_reminders
                    (customer_id, reminder_at, type, contact_id, note, created_at)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                if ($stmt) {

                    $stmt->bind_param(
                        "isssss",
                        $customer_id,
                        $reminder_at,
                        $type,
                        $contact_id,
                        $reminder_note,
                        $created_at
                    );

                    $stmt->execute();
                    $stmt->close();
                }

                // Agent log
                $log_text = "New Reminder created for booking #$booking_id on " .date("d M Y h:i A", strtotime($reminder_at));
                $logarr = [
                    'agent_id'    => 1,   // Website action (no agent session)
                    'log'         => $log_text,
                    'ip'          => $user_ip,
                    'customer_id' => $customer_id,
                    'timestamp'   => $datetime
                ];
                $db->insert('people_logs', $logarr);
            }
        }

        $formSubmitted = true;

    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Enquire Tour Package</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/css/intlTelInput.css">
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/js/intlTelInput.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<link href="https://assets.calendly.com/assets/external/widget.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<style>
body {
    background: linear-gradient(135deg, #f5f7fa, #e4ecf7);
}

.booking-card {
    border-radius: 16px;
    box-shadow: 0 12px 30px rgba(0,0,0,0.08);
}

.step-section {
    display: none;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.step-section.active {
    display: block;
    opacity: 1;
}

.back-btn {
    cursor: pointer;
    font-size: 14px;
    color: #6c757d;
}

.back-btn:hover {
    color: #000;
}

.schedule-card {
    background: #fff;
    border-radius: 20px;
    padding: 8px;
    /*box-shadow: 0 15px 40px rgba(0,0,0,0.08);*/
    transition: all 0.4s ease;
}

.flatpickr-calendar {
    border-radius: 18px !important;
    box-shadow: 0 15px 35px rgba(0,0,0,0.08);
}

.flatpickr-day {
    border-radius: 50% !important;
    font-weight: 500;
}

.flatpickr-day.selected {
    background: #0d6efd !important;
    border-color: #0d6efd !important;
}

.time-slot-btn {
    padding: 12px;
    border: 1px solid #0d6efd;
    background: white;
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.25s ease;
}

.time-slot-btn:hover {
    background: #eef3ff;
}

.time-slot-btn.active {
    background: #0d6efd;
    color: white;
}
</style>
<style>
.calendar-wrapper {
    display: flex;
    gap: 60px;
    align-items: flex-start;
    justify-content: center;   /* 🔥 center entire layout */
    transition: all 0.4s ease;
}
/* Calendar side */
.calendar-col {
    display: flex;
    flex-direction: column;
    align-items: center;       /* 🔥 center calendar horizontally */
    transition: all 0.4s ease;
}
/* Time column hidden initially */
.time-col {
    width: 0;
    opacity: 0;
    overflow: hidden;
    transform: translateX(20px);
    transition: all 0.4s ease;
}
/* After selecting date */
.calendar-wrapper.active .time-col {
    width: 260px;
    opacity: 1;
    transform: translateX(0);
}
</style>
<style>
#timeSlots {
    max-height: 17em;
    overflow: auto;
}

.confirm-btn {
    width: 20em;
    margin-left: -1em;
}
</style>

<!-- step1 styles -->
<style>
:root {
  --primary: #0f172a;
  --secondary: #1e293b;
  --accent: #f59e0b;
  --accent-dark: #d97706;
  --muted: #64748b;
  --card: rgba(255,255,255,0.95);
  --border: rgba(0,0,0,0.05);
  --shadow: 0 20px 60px rgba(2, 6, 23, 0.08);
  --radius: 24px;
}

/* HERO SECTION (NO BG) */
.hero-section {
  position: relative;
  overflow: hidden;

  background: transparent;   /* ❌ no bg */
  padding: 20px 0;
  border-radius: 0;          /* remove card feel */
}

/* REMOVE glow */
.hero-section::before {
  display: none;
}

/* TEXT CONTENT */
.hero-title {
  font-size: clamp(2rem, 4vw, 3rem);
  font-weight: 800;
  line-height: 1.2;
  color: var(--primary);
  margin-bottom: 12px;
}

.hero-subtitle {
  color: var(--muted);
  font-size: 1rem;
  line-height: 1.7;
  margin-bottom: 18px;
}

/* TOP BADGE */
.eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 12px;
  border-radius: 999px;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  color: var(--primary);
  font-size: 0.85rem;
  margin-bottom: 10px;
}

/* POINT TAGS */
.hero-points {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 18px;
}

.hero-point {
  background: #ffffff;
  color: var(--primary);
  border: 1px solid #e2e8f0;
  padding: 6px 10px;
  border-radius: 999px;
  font-size: 0.85rem;
}

/* TRUST TEXT */
.hero-trust {
  margin-top: 8px;
  color: #64748b;
  font-size: 0.85rem;
}

/* FORM CARD (main focus now) */
.enquiry-card {
  background: #ffffff;
  border: 1px solid #eef2f7;
  box-shadow: 0 0px 11px rgba(15, 23, 42, 0.08);
  border-radius: var(--radius);
  padding: 25px;
}

.enquiry-card h3 {
  font-size: 1.5rem;
  font-weight: 800;
  margin-bottom: 5px;
}

.enquiry-card .subtext {
  color: var(--muted);
  font-size: 0.95rem;
}

/* FORM */
.form-label {
  font-weight: 600;
  margin-bottom: 5px;
}

.form-control {
  border-radius: 10px;
  padding: 10px 12px;
  border: 1px solid #e2e8f0;
}

.form-control:focus {
  border-color: #f59e0b;
  box-shadow: 0 0 0 0.2rem rgba(245, 158, 11, 0.12);
}

/* BUTTON */
.btn-premium {
  border-radius: 12px;
  font-weight: 700;
  transition: 0.25s ease;
}

.btn-gold {
  background: linear-gradient(135deg, #fbbf24, #f59e0b);
  border: none;
  color: #1f2937;
  box-shadow: 0 10px 20px rgba(245, 158, 11, 0.25);
}

.btn-gold:hover {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: #fff;
}

/* SMALL TEXT */
.small-meta {
  color: #94a3b8;
  font-size: 0.85rem;
}

/* MOBILE */
@media (max-width: 991px) {
  .hero-section {
    padding: 10px 0;
  }
}
</style>

</head>

<body>

<div class="container py-5">
<div class="row justify-content-center">
<div class="col-lg-12">

<div class="card booking-card p-4">

    <?php if (!empty($formSubmitted)) : ?>

    <div class="d-flex align-items-center justify-content-center" style="min-height: 350px;">
        <div class="text-center">
            <div class="mb-3">
                <div style="
                    width:70px;
                    height:70px;
                    margin:0 auto;
                    background:#e9f7ef;
                    border-radius:50%;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    font-size:32px;
                    color:#198754;
                    font-weight:bold;">
                    ✓
                </div>
            </div>

            <h4 class="fw-bold text-success mb-2">
                Enquiry Submitted Successfully!
            </h4>

            <p class="text-muted mb-1">
                Thank you for choosing us.
            </p>

            <p class="text-muted">
                Our travel expert will contact you shortly.
            </p>
        </div>
    </div>

    <?php else: ?>

    <!-- =========================
         STEP 1 – CUSTOMER DETAILS
    ========================= -->
    <form method="post" id="bookingForm">

    <div id="step1" class="step-section active">

        <?php if (!empty($errorMessage)) : ?>
            <div class="alert alert-danger text-center">
                <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php endif; ?>

        <input type="hidden" name="package_id"
               value="<?= $package ? $package['id'] : $package_id ?>">

        <!-- HERO SECTION -->
        <section class="hero-section rounded-4">

            <div class="container">
                <div class="row align-items-center g-5">

                    <!-- LEFT CONTENT -->
                    <div class="col-lg-7 text-white">

                        <div class="eyebrow">
                            <i class="bi bi-stars"></i>
                            Premium Holiday Packages
                        </div>

                        <h1 class="hero-title">
                            Plan Your Perfect Holiday With Expert Guidance
                        </h1>

                        <p class="hero-subtitle">
                            Share your travel details and our team will help you with the best options,
                            pricing, and availability tailored to your needs.
                        </p>

                        <div class="hero-points">
                            <span class="hero-point"><i class="bi bi-check2-circle me-2"></i>Quick Response</span>
                            <span class="hero-point"><i class="bi bi-check2-circle me-2"></i>Custom Packages</span>
                            <span class="hero-point"><i class="bi bi-check2-circle me-2"></i>Family Friendly</span>
                            <span class="hero-point"><i class="bi bi-check2-circle me-2"></i>Trusted Support</span>
                        </div>

                        <div class="hero-trust">
                            <i class="bi bi-shield-check me-2"></i>
                            Trusted assistance • Fast response • No obligation enquiry
                        </div>

                    </div>

                    <!-- RIGHT FORM -->
                    <div class="col-lg-5">

                        <div class="enquiry-card" id="enquiryFormCard">

                            <h3>Plan Your Holiday</h3>
                            <p class="subtext">
                                Fill the form and our team will contact you shortly.
                            </p>

                            <div class="mt-3">

                                <div class="row g-3">

                                    <!-- Phone -->
                                    <div class="col-md-6">
                                        <label class="form-label">Phone Number</label>
                                        <input type="tel"
                                               id="phoneInput"
                                               name="phone"
                                               class="form-control"
                                               placeholder="Enter mobile number"
                                               required>
                                    </div>

                                    <!-- Name -->
                                    <div class="col-md-6">
                                        <label class="form-label">Full Name</label>
                                        <input type="text"
                                               name="name"
                                               class="form-control"
                                               placeholder="Enter full name"
                                               required>
                                    </div>

                                    <!-- Email -->
                                    <div class="col-md-6">
                                        <label class="form-label">Email Address</label>
                                        <input type="email"
                                               name="email"
                                               class="form-control"
                                               placeholder="Enter email"
                                               required>
                                    </div>

                                    <!-- Travel Date -->
                                    <div class="col-md-6">
                                        <label class="form-label">Travel Date</label>
                                        <input type="date"
                                               name="travel_date"
                                               class="form-control"
                                               required>
                                    </div>

                                    <!-- Adults -->
                                    <div class="col-md-6">
                                        <label class="form-label">No. of Adults</label>
                                        <input type="number"
                                               name="adults"
                                               class="form-control jnumber"
                                               min="1"
                                               value="1"
                                               required>
                                    </div>

                                    <!-- Children -->
                                    <div class="col-md-6">
                                        <label class="form-label">No. of Children</label>
                                        <input type="number"
                                               id="childrenCount"
                                               name="children"
                                               class="form-control jnumber"
                                               min="0"
                                               max="3"
                                               value="0">
                                    </div>

                                    <!-- Children Ages -->
                                    <div class="col-12" id="childrenAgesWrapper"></div>

                                    <!-- CTA -->
                                    <div class="col-12 mt-3">
                                        <button type="button"
                                                onclick="goToStep2()"
                                                class="btn btn-premium btn-gold w-100 py-3">
                                            Continue to Next Step
                                        </button>
                                    </div>

                                </div>

                            </div>

                            <div class="text-center mt-3 small-meta">
                                No obligation enquiry • Quick response guaranteed
                            </div>

                        </div>

                    </div>

                </div>
            </div>

        </section>

    </div>



    <!-- =========================
         STEP 2 – SCHEDULE MEETING
    ========================= -->

    <div id="step2" class="step-section">
        <div class="mb-1 d-flex justify-content-between align-items-center">
            <span class="back-btn" onclick="goToStep1()">
                <i class="bi bi-arrow-left"></i> Back
            </span>

            <!-- Skip -->
            <button type="submit"
                    name="submit_type"
                    value="skip"
                    class="btn btn-outline-secondary btn-sm">
                Skip Scheduling
            </button>
        </div>

        <div class="schedule-card">

            <h4 class="mb-2 fw-semibold">
                Schedule a Call
            </h4>

            <p class="text-muted mb-4">
                Select a convenient date and time for a quick 30-minute consultation.
            </p>

            <div class="calendar-wrapper d-flex gap-4 align-items-start" id="calendarWrapper">

                <!-- Calendar -->
                <div class="calendar-col 1w-100" id="calendarColumn">
                    <div id="calendar"></div>

                    <div class="mt-4">
                        <label class="form-label">Your Timezone</label>
                        <select name="timezone"
                                class="form-select">
                            <?php
                            foreach (DateTimeZone::listIdentifiers() as $tz) {
                                $sel = ($tz == "Europe/London") ? "selected" : "";
                                echo "<option value='$tz' $sel>$tz</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <!-- Time Slots -->
                <div class="time-col" id="timeColumn">
                    <div class="time-col-inner">

                        <h6 class="mb-3 fw-semibold" id="selectedDateText"></h6>
                        <div id="timeSlots" class="d-grid gap-2"></div>

                        <div class="mt-4">
                            <label class="form-label fw-semibold">
                                Preferred Contact Method
                            </label>

                            <div class="d-flex gap-2">

                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="radio"
                                           name="preferred_contact"
                                           value="Phone"
                                           id="contactPhone" checked>
                                    <label class="form-check-label" for="contactPhone">
                                        Phone
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="radio"
                                           name="preferred_contact"
                                           value="WhatsApp"
                                           id="contactWhatsapp">
                                    <label class="form-check-label" for="contactWhatsapp">
                                        WhatsApp
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="radio"
                                           name="preferred_contact"
                                           value="Email"
                                           id="contactEmail">
                                    <label class="form-check-label" for="contactEmail">
                                        Email
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <!-- Hidden Inputs -->
            <input type="hidden"
                   name="meeting_date"
                   id="meeting_date">

            <input type="hidden"
                   name="meeting_time"
                   id="meeting_time">

            <!-- Confirm -->
            <div class="w-100 text-center">
                <button type="submit"
                        name="submit_type"
                        value="confirm"
                        class="btn btn-success confirm-btn mt-4">
                    Confirm & Continue
                </button>
            </div>

        </div>
    </div>

    </form>

    <?php endif; ?>

</div>

</div>
</div>
</div>

<script src="public/assets/js/jnumber.js?jv=<?=time()?>"></script>
<script>
document.getElementById('childrenCount').addEventListener('input', function() {

    const count = parseInt(this.value) || 0;
    const wrapper = document.getElementById('childrenAgesWrapper');
    wrapper.innerHTML = '';

    if (count > 0) {
        let html = '<label class="form-label mt-2">Ages of Children</label><div class="row g-2">';
        for (let i = 1; i <= count; i++) {
            html += `
                <div class="col-md-4">
                    <input type="number" 
                           name="child_ages[]" 
                           class="form-control jnumber" 
                           placeholder="Age ${i}" value="1"
                           min="1" max="11" required>
                </div>
            `;
        }
        html += '</div>';
        wrapper.innerHTML = html;
    }
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const phoneInputField = document.querySelector("#phoneInput");
    const nameInput  = document.querySelector('input[name="name"]');
    const emailInput = document.querySelector('input[name="email"]');
    const form       = document.querySelector("form");

    if (!phoneInputField) {
        console.log("Phone input not found");
        return;
    }

    /* Initialize intlTelInput ONLY ONCE */
    const iti = window.intlTelInput(phoneInputField, {
        initialCountry: "gb",
        preferredCountries: ["gb", "in"],
        separateDialCode: true,
        nationalMode: true,
        autoHideDialCode: false,
        formatOnDisplay: true,
        utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/js/utils.js"
    });

    /* On form submit → store full international number */
    form.addEventListener("submit", function () {
        phoneInputField.value = iti.getNumber();
    });

    let debounceTimer = null;
    let lastFetchedNumber = null;

    // phoneInputField.addEventListener("input", function () {

    //     clearTimeout(debounceTimer);

    //     debounceTimer = setTimeout(() => {

    //         const fullNumber = iti.getNumber();

    //         if (!fullNumber) return;
    //         if (!iti.isValidNumber()) return;

    //         if (fullNumber === lastFetchedNumber) return;

    //         lastFetchedNumber = fullNumber;

    //         fetch("5324562343_get_customer.php", {
    //             method: "POST",
    //             headers: {
    //                 "Content-Type": "application/x-www-form-urlencoded"
    //             },
    //             body: "phone=" + encodeURIComponent(fullNumber)
    //         })
    //         .then(res => res.json())
    //         .then(data => {

    //             if (!data.success) return;

    //             if (!nameInput.value) {
    //                 nameInput.value = data.name;
    //             }

    //             if (!emailInput.value) {
    //                 emailInput.value = data.email;
    //             }

    //         })
    //         .catch(err => console.log("Fetch error:", err));

    //     }, 700);

    // });

});
</script>

<script>
/* ==============================
   STEP NAVIGATION
============================== */

function goToStep2() {

    const form = document.getElementById("bookingForm");

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    // const phone  = form.querySelector('input[name="phone"]').value.trim();
    // const name   = form.querySelector('input[name="name"]').value.trim();
    // const email  = form.querySelector('input[name="email"]').value.trim();
    // const travel = form.querySelector('input[name="travel_date"]').value;
    // const adults = parseInt(form.querySelector('input[name="adults"]').value);
    // if (!phone || !name || !email || !travel || adults < 1) {
    //     alert("Please fill all required fields correctly before continuing.");
    //     return;
    // }

    document.getElementById("step1").classList.remove("active");
    document.getElementById("step2").classList.add("active");
}

function goToStep1() {
    document.getElementById("step2").classList.remove("active");
    document.getElementById("step1").classList.add("active");
}


/* ==============================
   CALENDAR + TIME SLOTS
============================== */
let fpInstance;

document.addEventListener("DOMContentLoaded", function () {

    const disabledWeekdays = [0,6]; // 0 = Sunday
    const disabledDates = []; // ["2026-02-25","2026-02-28"];

    fpInstance = flatpickr("#calendar", {
        inline: true,
        minDate: "today",
        dateFormat: "Y-m-d",
        disable: [
            ...disabledDates,
            function(date) {
                return disabledWeekdays.includes(date.getDay());
            }
        ],
        onChange: function(selectedDates, dateStr) {
            if (!dateStr) return;

            document.getElementById("meeting_date").value = dateStr;

            animateCalendarLayout();
            generateTimeSlots(new Date(dateStr));
        }
    });

});

function animateCalendarLayout() {
    const wrapper = document.getElementById("calendarWrapper");

    if (!wrapper.classList.contains("active")) {
        wrapper.classList.add("active");
    }
}

function generateTimeSlots(selectedDate) {

    const slotBox = document.getElementById("timeSlots");
    const label   = document.getElementById("selectedDateText");

    slotBox.innerHTML = "";
    label.innerText = selectedDate.toDateString();

    const now = new Date();

    for (let hour = 9; hour < 18; hour++) {

        for (let minute of [0, 30]) {

            if (hour === 17 && minute === 30) continue;

            const slotTime = new Date(selectedDate);
            slotTime.setHours(hour);
            slotTime.setMinutes(minute);
            slotTime.setSeconds(0);

            if (slotTime <= now) continue;

            const btn = document.createElement("button");
            btn.type = "button";
            btn.className = "time-slot-btn";
            btn.innerText = slotTime.toLocaleTimeString("en-GB", {
                hour: "numeric",
                minute: "2-digit",
                hour12: true
            });

            btn.onclick = function () {

                document.querySelectorAll(".time-slot-btn")
                    .forEach(b => b.classList.remove("active"));

                btn.classList.add("active");

                document.getElementById("meeting_time").value = btn.innerText;
            };

            slotBox.appendChild(btn);
        }
    }
}

/* ==============================
   FORM VALIDATION BEFORE SUBMIT
============================== */
let lastClickedButton = null;

document.querySelectorAll("#bookingForm button[type=submit]")
    .forEach(btn => {
        btn.addEventListener("click", function(){
            lastClickedButton = this.value;
        });
    });

document.getElementById("bookingForm").addEventListener("submit", function(e){

    if (lastClickedButton === "confirm") {

        const meetingDate = document.getElementById("meeting_date").value;
        const meetingTime = document.getElementById("meeting_time").value;

        if (!meetingDate || !meetingTime) {
            e.preventDefault();
            alert("Please select both meeting date and time.");
            return false;
        }
    }

});
</script>

</body>
</html>