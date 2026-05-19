<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/functions.php';

$calendly_url = "https://calendly.com/joice-jrj/30min";
$whatsapp_number = "02085181010";

$scenario_id = 2; // 2 = Tours

$package_id = (int)($_GET['id'] ?? 0);
if (!$package_id) {
    // die("Invalid package ID");
    $package_id=10;
}

/* Fetch package */
$stmt = $mysqli->prepare("SELECT id, name, duration_days,duration_nights FROM packages WHERE id=? LIMIT 1");
$stmt->bind_param("i", $package_id);
$stmt->execute();
$package = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        /* ===============================
           1. READ FORM INPUT
        =============================== */
        $contact_name  = trim($_POST['name']);
        $contact_email = trim($_POST['email']);
        $contact_phone = trim($_POST['phone']);
        $contact_phone = preg_replace('/[^0-9]/', '', $contact_phone);
        $travel_date   = $_POST['travel_date'];
        $adults        = (int)$_POST['adults'];
        $children      = (int)$_POST['children'];
        $child_ages      = $children>0?$_POST['child_ages']:[];
        $rooms         = 1;

        $ages = "";
        foreach ($child_ages as $key => $child_age) {
            $ages .= $key==0?". \nChildren Age: ":"";
            $ages .= ($key>0?", ":"").$child_age;
        }

        if ($adults < 1) {
            throw new Exception("At least 1 adult required");
        }

        /* ===============================
           2. CREATE bookings_tours
        =============================== */

        $tour_json = json_encode($package, JSON_UNESCAPED_UNICODE);

        $stmt = $mysqli->prepare("
            INSERT INTO bookings_tours
            (
              booking_id,
              package_id,
              tour_name,
              tour_duration,
              tour_price,
              original_amount,
              discount_id,
              discount_code,
              discount_name,
              discount_type,
              discount_value,
              discount_amount,
              final_amount,
              travellers_count,
              adults,
              children,
              rooms,
              travel_date,
              tour_json,
              travellers_json
            )
            VALUES
            (NULL,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");

        $zero = 0;
        $null = null;
        $travellers_count = $adults + $children;
        $travellers_json = json_encode([], JSON_UNESCAPED_UNICODE);

        if ($package) {
            $pduration = $package['duration_days']."D / ".$package['duration_nights']."N";
            $tour_name = $package['name'];
            $tour_json = json_encode($package, JSON_UNESCAPED_UNICODE);
            $package_id_insert = $package['id'];
        } else {
            $pduration = null;
            $tour_name = "General Enquiry";
            $tour_json = null;
            $package_id_insert = null;
        }

        $stmt->bind_param(
            "issddisssdddiiiisss",
            $package_id_insert,
            $tour_name,
            $pduration,
            $zero,  // tour_price
            $zero,  // original_amount
            $null,  // discount_id
            $null,
            $null,
            $null,
            $null,
            $zero,
            $zero,
            $travellers_count,
            $adults,
            $children,
            $rooms,
            $travel_date,
            $tour_json,
            $travellers_json
        );

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $tourBookingId = $stmt->insert_id;
        $stmt->close();


        /* ===============================
           CHECK / CREATE CUSTOMER
        =============================== */

        $contact_entity_id = null;

        $stmt = $mysqli->prepare("SELECT id, name, email FROM customers WHERE phone = ? LIMIT 1");
        $stmt->bind_param("s", $contact_phone);
        $stmt->execute();
        $existingCustomer = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($existingCustomer) {

            // Existing customer
            $contact_entity_id = $existingCustomer['id'];

        } else {

            // Insert new customer
            $stmt = $mysqli->prepare("
                INSERT INTO customers (name, email, phone, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->bind_param("sss", $contact_name, $contact_email, $contact_phone);

            if (!$stmt->execute()) {
                throw new Exception("Customer insert failed: " . $stmt->error);
            }

            $contact_entity_id = $stmt->insert_id;
            $stmt->close();
        }

        /* ===============================
           3. CREATE bookings MASTER
        =============================== */

        $status = "New";
        $itype  = "IN";
        $channel_id = 6;
        $contact_type_id = 1;

        $stmt = $mysqli->prepare("
            INSERT INTO bookings
            (date,time,contact_name,subject,notes,channel_id,contact_type_id,type_id,owner_id,assigned_to,
             status,priority,follow_date,follow_time,contact_entity_id,contact_phone,contact_email,
             entity_contact_id,related_employee_ids,related_customer_id,itype,nature)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");

        $today = date('Y-m-d');
        $time  = date('H:i:s');
        $subject = $package ? "Tour Booking - ".$package['name'] : "Tour Enquiry";
        $notes = "Booked via website".$ages;
        $owner_id = 1;

        $null = null;

        $stmt->bind_param(
            "sssssiiiisssssisssssss",
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
            $null,
            $null,
            $null,
            $contact_entity_id,
            $contact_phone,
            $contact_email,
            $null,
            $null,
            $null,
            $itype,
            $null
        );

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $booking_id = $stmt->insert_id;
        $stmt->close();

        /* ===============================
           4. LINK TOUR TO BOOKING
        =============================== */

        $stmt = $mysqli->prepare("UPDATE bookings_tours SET booking_id=? WHERE id=?");
        $stmt->bind_param("ii", $booking_id, $tourBookingId);
        $stmt->execute();
        $stmt->close();


        /* ===============================
           5. AUTO ADD FOLLOWUP ENTRY
        =============================== */

        $agent_id   = 1; // default system agent (change if needed)
        $agent_name = "System"; // or fetch logged-in user

        $note_text = $package
            ? "New tour enquiry submitted via website for ".$package['name']
            : "New general tour enquiry submitted via website";

        if (!empty($ages)) {
            $note_text .= "\n".$ages;
        }

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
        =============================== */

        $uid = $contact_entity_id;
        $utype = "customer";

        $snippet = substr($note_text, 0, 120);

        if (isset($site) && method_exists($site, 'agent_log')) {
            $site->agent_log(
                "Added booking followup for booking #$booking_id - $snippet",
                $uid,
                $utype
            );
        }


        // echo "<div class='alert alert-success text-center fw-semibold'>
        //         ✅ Booking submitted successfully!
        //       </div>";
        $formSubmitted = true;

    } catch (Exception $e) {

        // echo "<div class='alert alert-danger text-center'>
        //         ".$e->getMessage()."
        //       </div>";
        $errorMessage = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Book Tour Package</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/css/intlTelInput.css">
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/js/intlTelInput.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<link href="https://assets.calendly.com/assets/external/widget.css" rel="stylesheet">

<style>
body {
    background: linear-gradient(135deg, #f5f7fa, #e4ecf7);
}
.booking-card {
    border-radius: 15px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}
.package-box {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 10px;
    font-weight: 600;
}
.btn-primary {
    background: #0d6efd;
    border-radius: 8px;
    padding: 10px 25px;
}
::placeholder {
    font-size: 13px;
    opacity: 0.7;
}
</style>
<style>
    .back-btn {
        cursor: pointer;
        font-size: 14px;
        color: #6c757d;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 10px;
        transition: 0.2s ease;
    }

    .back-btn:hover {
        color: #000;
    }
</style>
</head>

<body>

<div class="container py-5">
<div class="row justify-content-center">
<div class="col-lg-8">

<div class="card booking-card p-4">

<?php if (!empty($formSubmitted)) : ?>

    <div class="d-flex align-items-center justify-content-center" style="min-height: 280px; height: 100vh;">
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
                    font-weight:bold;
                ">
                    ✓
                </div>
            </div>

            <h4 class="fw-bold text-success mb-2">
                Booking Submitted Successfully!
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

    <style>
    #coverSection {
        padding: 10px 0;
    }

    /* Button wrapper */
    .action-wrapper {
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
    }

    /* Button styling */
    .action-btn {
        flex: 1;
        min-width: 180px;
        max-width: 220px;
        border-radius: 14px;
        padding: 14px 18px;
        font-weight: 600;
        font-size: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.25s ease;
    }

    /* Hover animation */
    .action-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 18px rgba(0,0,0,0.08);
    }

    /* WhatsApp Premium */
    .btn-whatsapp {
        background: #25D366;
        color: #fff;
        border: none;
    }

    .btn-whatsapp:hover {
        background: #1ebe5d;
        color: #fff;
    }

    /* Smooth fade */
    .fade-section {
        opacity: 0;
        transition: opacity 0.4s ease-in-out;
        display: none;
    }

    .fade-section.active {
        opacity: 1;
        display: block;
    }

    .iti__country-list {
        z-index: 3 !important;
    }
    </style>
    <div id="coverSection" class="fade-section active">

        <div class="action-wrapper mb-4 mt-2">

            <button type="button"
                    class="btn btn-primary action-btn"
                    onclick="showEnquiryForm()">
                <i class="bi bi-pencil-square"></i>
                Enquire Now
            </button>

            <button type="button"
                    class="btn btn-outline-success action-btn"
                    onclick="openCalendly()">
                <i class="bi bi-calendar-check"></i>
                Schedule A Call
            </button>

            <button type="button"
                    class="btn btn-whatsapp action-btn"
                    onclick="openWhatsapp()">
                <i class="bi bi-whatsapp"></i>
                WhatsApp
            </button>

        </div>

        <div class="text-center mb-5 text-muted">
            Choose how you'd like to connect with us.
        </div>

    </div>
    <div id="calendlySection" class="fade-section">
        <div class="back-btn" onclick="goBackToCover()">
            <i class="bi bi-arrow-left"></i>
            Back
        </div>
        <div class="calendly-inline-widget"
             data-url="<?=$calendly_url?>?utm_package_id=<?=$package_id?>"
             style="min-width:320px;height:52em;">
        </div>
    </div>

    <div id="enquiryFormSection" class="fade-section">

        <div class="back-btn" onclick="goBackToCover()">
            <i class="bi bi-arrow-left"></i>
            Back
        </div>

        <h3 class="mb-4 text-center d-none">Tour Booking</h3>
        <?php if($package) { ?>
            <div class="package-box mb-4 text-center d-none">
                <?= htmlspecialchars($package['name']) ?>
            </div>
        <?php } ?>

    <?php if (!empty($errorMessage)) : ?>
        <div class="alert alert-danger text-center">
            <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php endif; ?>


        <form method="post">

        <input type="hidden" name="package_id" value="<?= $package?$package['id']:$package_id ?>">

        <div class="row g-3">

            <div class="col-md-4">
                <label class="form-label">Phone Number</label>
                <input type="tel"
                       id="phoneInput"
                       name="phone"
                       class="form-control" style="width: 100%;" 
                       placeholder="Enter mobile number"
                       required>
            </div>

            <div class="col-md-4">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" placeholder="Enter your full name" required>
            </div>

            <div class="col-md-4">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="example@email.com" required>
            </div>

            <div class="col-md-4">
            <label class="form-label">Travel Date</label>
            <input type="date" name="travel_date" class="form-control" required>
            </div>

            <div class="col-md-4">
            <label class="form-label">No. of Adults</label>
            <input type="number" name="adults" class="form-control jnumber" min="1" value="1" required>
            </div>

            <div class="col-md-4">
            <label class="form-label">No. of Children</label>
            <input type="number" id="childrenCount" name="children" class="form-control jnumber" min="0" max="3" value="0">
            </div>

            <div class="col-12" id="childrenAgesWrapper"></div>

            <div class="col-12 text-center mt-3">
            <button type="submit" class="btn btn-primary btn-lg">
                Enquire Now
            </button>
            </div>

        </div>
        </form>

    </div>

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
                <div class="col-md-3">
                    <input type="number" 
                           name="child_ages[]" 
                           class="form-control jnumber" 
                           placeholder="Child ${i} Age"
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

    phoneInputField.addEventListener("input", function () {

        clearTimeout(debounceTimer);

        debounceTimer = setTimeout(() => {

            const fullNumber = iti.getNumber();

            if (!fullNumber) return;
            if (!iti.isValidNumber()) return;

            if (fullNumber === lastFetchedNumber) return;

            lastFetchedNumber = fullNumber;

            fetch("get_customer.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "phone=" + encodeURIComponent(fullNumber)
            })
            .then(res => res.json())
            .then(data => {

                if (!data.success) return;

                if (!nameInput.value) {
                    nameInput.value = data.name;
                }

                if (!emailInput.value) {
                    emailInput.value = data.email;
                }

            })
            .catch(err => console.log("Fetch error:", err));

        }, 700);

    });

});
</script>

<script>
function showEnquiryForm() {
    const cover = document.getElementById("coverSection");
    const form  = document.getElementById("enquiryFormSection");

    cover.classList.remove("active");

    setTimeout(() => {
        cover.style.display = "none";
        form.classList.add("active");
    }, 300);
}

function openWhatsapp() {
    const phone = "<?=$whatsapp_number?>"; // ← replace with your number (no +)
    const message = encodeURIComponent("Hi, I would like to enquire about a tour package.");
    window.open("https://wa.me/" + phone + "?text=" + message, "_blank");
}
</script>

<script src="https://assets.calendly.com/assets/external/widget.js" type="text/javascript"></script>
<script>
// function openCalendly() {
//     Calendly.initPopupWidget({
//         url: '<?=$calendly_url?>'
//     });
//     return false;
// }
// function openCalendly() {
//     document.getElementById("coverSection").style.display = "none";
//     document.getElementById("calendlySection").style.display = "block";
// }
function showSection(sectionId) {

    const sections = [
        "coverSection",
        "enquiryFormSection",
        "calendlySection"
    ];

    sections.forEach(id => {
        document.getElementById(id).classList.remove("active");
    });

    setTimeout(() => {
        document.getElementById(sectionId).classList.add("active");
    }, 50);
}

function openCalendly() {
    showSection("calendlySection");
}

function showEnquiryForm() {
    showSection("enquiryFormSection");
}

function goBackToCover() {
    showSection("coverSection");
}
</script>

</body>
</html>