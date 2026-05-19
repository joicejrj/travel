<?php
require_once __DIR__ . '/config/db.php';
session_start();

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
</head>

<body>

<div class="container1 1py-5">
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

    <h3 class="mb-4 text-center d-none">Tour Booking</h3>
<?php if($package) { ?>
    <div class="package-box mb-4 text-center">
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
</body>
</html>