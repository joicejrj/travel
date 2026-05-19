<?php
require_once __DIR__ . '/includes/header.php';
require_login();

// Default form values
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$name = $company = $phone = $whatsapp = $email = $address = $city = $state = $country = $services = $agent_id = $google_rating = $website = '';
$country = "India";
$is_edit = false;

$is_edit = ($id > 0);

// prevent edit here
if($is_edit) {
    $site->redirect('index.php?page=carriers');
}

// If editing, fetch existing supplier
if ($id > 0) {
    if ($stmt = $mysqli->prepare("SELECT id, name, company, phone, whatsapp, email, address, city, state, country, services, google_rating, website FROM carriers WHERE id = ?")) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $is_edit  = true;
            $name     = $row['name'];
            $company  = $row['company'];
            $phone    = $row['phone'];
            $whatsapp    = $row['whatsapp'];
            $email    = $row['email'];
            $address  = $row['address'];
            $city  = $row['city'];
            $state  = $row['state'];
            $country  = $row['country'];
            $services  = $row['services'];
            $google_rating  = $row['google_rating'];
            $website  = $row['website'];
        }
        $stmt->close();
    }
}
    
// var_dump($_POST);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['company'],$_POST['srand']) && $_SESSION['srand']==$_POST['srand']) {
    $id      = (int)($_POST['id'] ?? 0);
    $name    = trim($_POST['name'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $whatsapp   = trim($_POST['whatsapp'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $services = trim($_POST['services'] ?? '');
    $google_rating = ''; //trim($_POST['google_rating'] ?? '');
    $website = trim($_POST['website'] ?? '');
    $agent_id = trim($_POST['agent_id'] ?? '');
    
    $source = trim($_POST['source'] ?? 'from excel');
    $type = trim($_POST['type'] ?? 'Suspect');


    $phonesa = [];
    $phonex = preg_replace('/\D/', '', $phone);
    if($phonex!=''&&strlen($phonex>=12)) {
        $phonesa[] = $phonex;
    }
    $whatsappx = preg_replace('/\D/', '', $whatsapp);
    if($whatsappx!=''&&$phonex!=$whatsappx&&strlen($whatsappx>=12)) {
        $phonesa[] = $whatsappx;
    }
    $phones = json_encode($phonesa);

    $photo = "";
    if(isset($_FILES['photo'])) {
        $file = $_FILES['photo'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg','jpeg','png','gif'];
        if (in_array($ext, $allowedExts) && $file['size'] <= 10*1024*1024) { // max 2MB
            $newName = $site->randomstr(40).date('ymdhis').'.'.$ext;
            $dest = __DIR__.'/../uploads/carriers/'.$newName;
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $photo = $newName;
            }
        }
    }

    $ok = false;

    if ($id > 0) { // not using now
        // Update existing supplier
        // if ($stmt = $mysqli->prepare("UPDATE carriers SET name=?, company=?, phone=?, whatsapp=?, email=?, address=?, country=?, services=?, google_rating=?, website=?, updated_at=NOW() WHERE id=?")) {
        //     $stmt->bind_param("ssssssssssi", $name, $company, $phone, $whatsapp, $email, $address, $country, $services, $google_rating, $website, $id);
        //     $ok = $stmt->execute();
        //     $msg = $ok ? "Carrier updated successfully." : "Failed to update recruiter: " . $stmt->error;
        //     $stmt->close();
        // }
    } else {
        // Insert new supplier
        if ($stmt = $mysqli->prepare("INSERT INTO carriers (name, company, agent_id, phone, photo, phones, whatsapp, email, address, city, state, country, services, google_rating, website, source, type, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())")) {
            $stmt->bind_param("sssssssssssssssss", $name, $company, $agent_id, $phone, $photo, $phones, $whatsapp, $email, $address, $city, $state, $country, $services, $google_rating, $website, $source, $type);
            $ok = $stmt->execute();
            $msg = $ok ? "Carrier added successfully." : "Failed to add recruiter: " . $stmt->error;
            if ($ok) {
                $id = $mysqli->insert_id;
            }
            $stmt->close();
        }
    }


    $site->msg($msg,($ok?'success':'error'));
    $site->show_msg();
    if($ok) {
        $site->redirect('index.php?page=carriers_view&id='.$id);
    }
    
    // echo "<div style='background:#d1fae5;padding:10px;border-radius:6px;margin-bottom:10px;'>"
    //     . htmlspecialchars($msg) . "</div>";

    // $is_edit = ($id > 0);
}

$srand = $_SESSION['srand'] = rand();
?>

<style>
    body { color:#111; font-family:Arial, sans-serif; }
    form { max-width:600px; background:#fff; padding:25px; border-radius:8px; box-shadow:0 4px 8px rgba(0,0,0,0.1); margin:auto; }
    label { display:block; margin:12px 0 6px; font-weight:500; }
    input, textarea { width:100%; padding:10px; border:1px solid #ccc; border-radius:5px; font-size:14px; }
    textarea { min-height:80px; resize:vertical; }
    .form-btn { margin-top:20px; padding:12px 20px; border:none; background:#007bff; color:white; border-radius:5px; cursor:pointer; font-size:15px; }
    .form-btn:hover { background:#0069d9; }
    h1 { text-align:center; margin-bottom:25px; font-size:24px; font-weight:600; }
</style>

<h1><?php echo $is_edit ? "Edit Carrier" : "Add Carrier"; ?></h1>

<form method="post" enctype="multipart/form-data">
  <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
  <input type="hidden" name="srand" value="<?=$srand?>">


  <label for="company">Carrier Name *</label>
  <input type="text" name="company" id="company" value="<?php echo htmlspecialchars($company); ?>" required>
  
  <label for="name">Primary Contact Name</label>
  <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($name); ?>">

  <label for="phone">Phone</label>
  <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($phone); ?>">

  <label for="whatsapp">Whatsapp</label>
  <input type="text" name="whatsapp" id="whatsapp" value="<?php echo htmlspecialchars($whatsapp); ?>">

  <label for="email">Email</label>
  <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>">

  <label for="address">Address</label>
  <textarea name="address" id="address"><?php echo htmlspecialchars($address); ?></textarea>

  <label for="name">City</label>
  <input type="text" name="city" id="city" value="<?php echo htmlspecialchars($city); ?>">

  <label for="name">State</label>
  <input type="text" name="state" id="state" value="<?php echo htmlspecialchars($state); ?>">

  <label for="country">Country</label>
  <input type="text" name="country" id="country" value="<?php echo htmlspecialchars($country); ?>">

    <label for="source">Source</label>
      <select class="form-select form-control-xs rounded-pill" name="source" id="source">
        <option value="from website"  <?= ($source ?? '') === 'from website'  ? 'selected' : '' ?>>From Website</option>
        <option value="from call"     <?= ($source ?? '') === 'from call'     ? 'selected' : '' ?>>From Call</option>
        <option value="from leads"    <?= ($source ?? '') === 'from leads'    ? 'selected' : '' ?>>From Leads</option>
        <option value="from whatsapp" <?= ($source ?? '') === 'from whatsapp' ? 'selected' : '' ?>>From Whatsapp</option>
      </select>

    <label for="source">Status</label>
    <select class="form-select form-control-xs rounded-pill" name="type" id="type" placeholder="Status">
      <option value="Suspect" <?= ($type ?? '') === 'Suspect' ? 'selected' : '' ?>>
        Suspect — you have a name, not verified yet.
      </option>
      <option value="Lead (Active)" <?= ($type ?? '') === 'Lead (Active)' ? 'selected' : '' ?>>
        Lead (Active) — you’ve reached out / they responded.
      </option>
      <option value="Opportunity" <?= ($type ?? '') === 'Opportunity' ? 'selected' : '' ?>>
        Opportunity — quote/proposal/demo sent; decision pending.
      </option>
      <option value="Won" <?= ($type ?? '') === 'Won' ? 'selected' : '' ?>>
        Won — became a customer.
      </option>
      <option value="Archive" <?= ($type ?? '') === 'Archive' ? 'selected' : '' ?>>
        Archive — not a fit or chose someone else (capture reason).
      </option>
    </select>

    <label for="source">Agent</label>
    <select class="form-select form-control-xs rounded-pill" name="agent_id" id="agent_id" placeholder="Source">
      <option value=""> - Select Agent - </option>
      <?php
        $geta = $db->get('people',array('#all'=>1),'id,name');
        foreach ($geta->data as $key => $usa) {
      ?>
        <option value="<?=$usa->id?>" <?= $agent_id== $usa->id ? 'selected' : '' ?>><?=$usa->name?></option>
      <?php } ?>
    </select>

    <label for="source">Upload ID Image</label>
    <input type="file" class="form-control form-control-xs rounded-pill" name="photo" id="photo" accept="image/*">

  <label for="services">Services</label>
  <input type="text" name="services" id="services" value="<?php echo htmlspecialchars($services); ?>">

  <label for="website">Website</label>
  <input type="text" name="website" id="website" value="<?php echo htmlspecialchars($website); ?>">

  <button type="submit" class="form-btn"><?php echo $is_edit ? "Update Carrier" : "Add Carrier"; ?></button>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>