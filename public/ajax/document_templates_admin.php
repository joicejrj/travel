<?php
// public/ajax/document_templates.php
session_start();

require_once __DIR__ . '/../../admin/_auth.php';
require_once __DIR__ . '/../../config/db.php';        // must provide $mysqli
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
ob_clean();

header('Content-Type: application/json');

// --- CONFIG: TCPDF path and static header/footer html (customize) ---
// define('TCPDF_PATH', __DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php'); // update if needed
define('TCPDF_PATH', __DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php'); // update if needed

// Static header & footer HTML used by PDF generation (customize)
$PDF_HEADER_HTML = '<div style="text-align:center;"><h3>Al Nasr General Services EST</h3><div>Suheil Bin Mazrouei Building, Floor No. 301, Electra Street, AUH<br/>Phone: +971 2 6788866</div><div style="border-top:1px solid #000; width:100%; margin:0; padding:0;"></div></div>';
$PDF_FOOTER_HTML = ''; // '<div style="border-bottom:1px solid #000; width:100%; margin:0; padding:0;"></div><div style="text-align:center;font-size:10px;">This is system generated. Page {PAGE_NUM} of {PAGE_COUNT}.</div>';

// helper: generate slug from string
function make_slug($s){
    $s = mb_strtolower(trim($s));
    // replace non alnum with hyphen
    $s = preg_replace('/[^\p{L}\p{N}]+/u', '-', $s);
    $s = trim($s, '-');
    return $s ?: 'item';
}

// helper: ensure slug unique in templates table
function ensure_unique_slug($mysqli, $base){
    $slug = $base;
    $i = 0;
    while(true){
        $q = $mysqli->prepare("SELECT id FROM templates WHERE slug = ? LIMIT 1");
        $q->bind_param("s", $slug);
        $q->execute();
        $res = $q->get_result();
        if($res->num_rows == 0) {
            return $slug;
        }
        $i++;
        $slug = $base . '-' . $i;
        $q->close();
    }
}

// Extract placeholders from content (regex {{key}})
function extract_placeholders_from_content($content){
    $out = [];
    if(!$content) return $out;
    if(preg_match_all('/{{\s*([a-zA-Z0-9_\-]+)\s*}}/', $content, $m)){
        $keys = array_unique($m[1]);
        // we will always include name and date if present or not
        $out = array_values($keys);
    }
    return $out;
}

/**
 * Generate PDF using TCPDF and save to a file (same header/footer as preview)
 *
 * @param array  $doc        Document row containing content, title, show_header etc.
 * @param string $filepath   Full path where PDF should be saved
 */
function generate_pdf_file($doc, $filepath)
{
    // Load TCPDF
    if(!defined('TCPDF_PATH')) {
        throw new Exception("TCPDF_PATH not defined");
    }
    require_once TCPDF_PATH;

    class TMPDF extends TCPDF {
        public $header_html = '';
        public $footer_html = '';
        public function Header() {
            if ($this->header_html) {
                $this->setCellPaddings(0,0,0,0);
                $this->SetY(5);
                $this->writeHTMLCell(
                    0, 0, '', '', 
                    $this->header_html, 
                    0, 1, 0, true, 'C', true
                );
            }
        }
        public function Footer() {
            if ($this->footer_html) {
                $this->setCellPaddings(0,0,0,0);
                $this->SetY(-25);
                $this->writeHTMLCell(
                    0, 0, '', '', 
                    $this->footer_html, 
                    0, 1, 0, true, 'C', true
                );
            }
        }
    }

    // Begin PDF
    $pdf = new TMPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('System');
    $pdf->SetTitle($doc['title'] ?? 'Document');

    // Margins (if header/footer enabled)
    if ($doc['show_header'] == '1') {
        $pdf->SetMargins(15, 55, 15);
    } else {
        $pdf->SetMargins(15, 15, 15);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
    }

    $pdf->SetHeaderMargin(10);
    $pdf->SetFooterMargin(10);

    global $PDF_HEADER_HTML, $PDF_FOOTER_HTML;

    if ($doc['show_header'] == '1') {
        $pdf->header_html = $PDF_HEADER_HTML;

        // Page No placeholders
        // $footer_html = str_replace(
        //     ['{PAGE_NUM}', '{PAGE_COUNT}'],
        //     [$pdf->getAliasNumPage(), $pdf->getAliasNbPages()],
        //     $PDF_FOOTER_HTML
        // );
        $footer_html = '';

        $pdf->footer_html = $footer_html;
    }

    $pdf->AddPage();

    // Convert newline to <br>
    $content_html = nl2br($doc['content']);

    // Render HTML content
    $pdf->writeHTML($content_html, true, false, true, false, '');

    // Ensure directory exists
    $dir = dirname($filepath);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    // Save file to disk
    $pdf->Output($filepath, 'F'); // Save to file, NOT browser
}

// Basic router for AJAX actions
$action = $_REQUEST['action'] ?? null;

if ($action) {
    header('Content-Type: application/json; charset=utf-8');
    try {
        switch ($action) {

            // --------------------------------------------------
            // TEMPLATE CRUD
            // --------------------------------------------------
            case 'list_templates':
                $result = $mysqli->query("SELECT * FROM templates ORDER BY id DESC, category, subtype");
                $rows = $result->fetch_all(MYSQLI_ASSOC);
                echo json_encode(['ok' => true, 'data' => $rows]);
                break;

            case 'create_template':
                $category = $_POST['category'] ?? 'employee';
                $subtype  = trim($_POST['subtype'] ?? '');
                $desc     = $_POST['description'] ?? null;

                if (!$subtype) throw new Exception("subtype required");

                // generate slug
                $base = make_slug($subtype);
                $slug = ensure_unique_slug($mysqli, $base);

                $stmt = $mysqli->prepare("INSERT INTO templates (category, subtype, slug, description) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $category, $subtype, $slug, $desc);
                $stmt->execute();

                $site->agent_log("Created new document template $subtype with category=$category, description=$desc");

                echo json_encode(['ok' => true, 'id' => $stmt->insert_id, 'slug' => $slug]);
                break;

            case 'edit_template':
                $id       = (int)($_POST['id'] ?? 0);
                $category = $_POST['category'] ?? 'employee';
                $subtype  = trim($_POST['subtype'] ?? '');
                $desc     = $_POST['description'] ?? null;

                if (!$id || !$subtype) throw new Exception("missing data");

                // If subtype changed, regenerate slug (but keep unique)
                $base = make_slug($subtype);
                // check existing slug for this id
                $cur = $mysqli->prepare("SELECT slug, subtype FROM templates WHERE id=? LIMIT 1");
                $cur->bind_param("i", $id);
                $cur->execute();
                $cres = $cur->get_result()->fetch_assoc();
                $cur->close();

                if(!$cres) throw new Exception("template not found");

                $slug = $cres['slug'];
                if($cres['subtype'] !== $subtype){
                    $slug = ensure_unique_slug($mysqli, $base);
                }

                $stmt = $mysqli->prepare("UPDATE templates SET category=?, subtype=?, slug=?, description=?, updated_at=NOW() WHERE id=?");
                $stmt->bind_param("ssssi", $category, $subtype, $slug, $desc, $id);
                $stmt->execute();

                $site->agent_log("Updated Document Template #$id, category -> $category, tite -> $subtype, description -> $desc");

                echo json_encode(['ok' => true, 'slug' => $slug]);
                break;

            case 'delete_template':
                $id = (int)($_POST['id'] ?? 0);
                if (!$id) throw new Exception("id required");

                // delete versions and created_documents optionally
                $stmt = $mysqli->prepare("DELETE FROM template_versions WHERE template_id=?");
                $stmt->bind_param("i", $id);
                $stmt->execute();

                $stmt = $mysqli->prepare("DELETE FROM templates WHERE id=?");
                $stmt->bind_param("i", $id);
                $stmt->execute();

                $site->agent_log("Deleted document template #$id");

                echo json_encode(['ok' => true]);
                break;

            // --------------------------------------------------
            // VERSION CRUD
            // --------------------------------------------------
            case 'list_versions':
                $template_id = (int)($_GET['template_id'] ?? 0);
                $stmt = $mysqli->prepare("
                    SELECT tv.*, t.subtype 
                    FROM template_versions tv 
                    JOIN templates t ON tv.template_id = t.id 
                    WHERE tv.template_id = ? 
                    ORDER BY tv.created_at DESC
                ");
                $stmt->bind_param("i", $template_id);
                $stmt->execute();
                $res = $stmt->get_result();
                echo json_encode(['ok' => true, 'data' => $res->fetch_all(MYSQLI_ASSOC)]);
                break;

            case 'create_version':
                $template_id = (int)($_POST['template_id'] ?? 0);
                $version     = trim($_POST['version'] ?? 'v1');
                $content     = $_POST['content'] ?? '';
                $notes       = $_POST['notes'] ?? null;

                if (!$template_id || !$version) throw new Exception("template and version required");

                // check number of versions for active flag
                $stmt0 = $mysqli->prepare("SELECT COUNT(*) FROM template_versions WHERE template_id=?");
                $stmt0->bind_param("i", $template_id);
                $stmt0->execute();
                $stmt0->bind_result($count);
                $stmt0->fetch();
                $stmt0->close();

                $is_active = ($count == 0 ? 1 : 0);

                $stmt = $mysqli->prepare("
                    INSERT INTO template_versions (template_id, version, content, notes, is_active, created_by) 
                    VALUES (?, ?, ?, ?, ?, NULL)
                ");
                $stmt->bind_param("isssi", $template_id, $version, $content, $notes, $is_active);
                $stmt->execute();

                $site->agent_log("Created new Version of template #$template_id - Version $version");

                echo json_encode(['ok' => true, 'id' => $stmt->insert_id]);
                break;

            case 'edit_version':
                $id      = (int)($_POST['id'] ?? 0);
                $version = trim($_POST['version'] ?? '');
                $content = $_POST['content'] ?? '';
                $show_header = isset($_POST['show_header'])?'1':'0';
                $notes   = $_POST['notes'] ?? null;

                if (!$id || !$version) throw new Exception("missing data");

                $stmt = $mysqli->prepare("UPDATE template_versions SET version=?, content=?, notes=?, show_header=?, updated_at=NOW() WHERE id=?");
                $stmt->bind_param("ssssi", $version, $content, $notes, $show_header, $id);
                $stmt->execute();

                $site->agent_log("Updated Document Template Version #$id - Version -> $version, show_header -> ".($show_header=='1'?'enabled':'disabled'));

                echo json_encode(['ok' => true]);
                break;

            case 'delete_version':
                $id = (int)($_POST['id'] ?? 0);
                if (!$id) throw new Exception("id required");

                $stmt = $mysqli->prepare("DELETE FROM template_versions WHERE id=?");
                $stmt->bind_param("i", $id);
                $stmt->execute();

                $site->agent_log("Deleted the document template version #$id");

                echo json_encode(['ok' => true]);
                break;

            case 'set_active_version':
                $template_id = (int)($_POST['template_id'] ?? 0);
                $vid         = (int)($_POST['version_id'] ?? 0);

                if (!$template_id || !$vid) throw new Exception("missing data");

                $mysqli->begin_transaction();

                $stmt1 = $mysqli->prepare("UPDATE template_versions SET is_active=0 WHERE template_id=?");
                $stmt1->bind_param("i", $template_id);
                $stmt1->execute();

                $stmt2 = $mysqli->prepare("UPDATE template_versions SET is_active=1 WHERE id=?");
                $stmt2->bind_param("i", $vid);
                $stmt2->execute();

                $mysqli->commit();

                $site->agent_log("Template version #$vid status changed to Active");

                echo json_encode(['ok' => true]);
                break;

            case 'get_version':
                $id = (int)($_GET['id'] ?? 0);
                $stmt = $mysqli->prepare("SELECT * FROM template_versions WHERE id=? LIMIT 10");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $res = $stmt->get_result();
                echo json_encode(['ok' => true, 'data' => $res->fetch_assoc()]);
                break;

            // --------------------------------------------------
            // PLACEHOLDERS: extract placeholders from version content
            // --------------------------------------------------
            case 'get_placeholders':
                $version_id = (int)($_GET['version_id'] ?? 0);
                if(!$version_id) throw new Exception("version_id required");
                $stmt = $mysqli->prepare("SELECT content FROM template_versions WHERE id=? LIMIT 1");
                $stmt->bind_param("i", $version_id);
                $stmt->execute();
                $res = $stmt->get_result();
                $row = $res->fetch_assoc();
                $placeholders = extract_placeholders_from_content($row['content'] ?? '');
                // ensure name and date present if not already
                if(!in_array('name',$placeholders)) array_unshift($placeholders,'name');
                if(!in_array('date',$placeholders)) array_unshift($placeholders,'date');
                // remove duplicates keep order
                $placeholders = array_values(array_unique($placeholders));
                echo json_encode(['ok' => true, 'data' => $placeholders]);
                break;

            // --------------------------------------------------
            // LOAD ENTITY: fetch a row from employees/customers/recruiters by id or email
            // --------------------------------------------------
            case 'load_entity':
                $entity_type = $_GET['entity_type'] ?? 'employee';
                $identifier = $_GET['identifier'] ?? '';
                if(!$identifier) throw new Exception("identifier required");

                // map entity_type to table
                $table = null;
                if($entity_type === 'employee') $table = 'employees';
                elseif($entity_type === 'customer') $table = 'customers';
                elseif($entity_type === 'recruiter') $table = 'recruiters';
                else $table = null;

                if(!$table) return json_encode(['ok'=>true,'data'=>[]]);

                // try find by id or email
                if(is_numeric($identifier)){
                    $stmt = $mysqli->prepare("SELECT * FROM $table WHERE id = ? LIMIT 1");
                    $stmt->bind_param("i", $identifier);
                } else {
                    $stmt = $mysqli->prepare("SELECT * FROM $table WHERE email = ? LIMIT 1");
                    $stmt->bind_param("s", $identifier);
                }
                $stmt->execute();
                $res = $stmt->get_result();
                $row = $res->fetch_assoc();
                if(!$row) {
                    // try matching on phone or code fields if exists
                    $stmt = $mysqli->prepare("SELECT * FROM $table WHERE id = ? LIMIT 1");
                    if($stmt){
                        $tmpid = (int)$identifier;
                        $stmt->bind_param("i", $tmpid);
                        $stmt->execute();
                        $r2 = $stmt->get_result()->fetch_assoc();
                        if($r2) $row = $r2;
                    }
                }
                // return associative but only scalar values
                if(!$row) echo json_encode(['ok' => false, 'error' => 'Entity not found', 'data' => []]);
                else echo json_encode(['ok' => true, 'data' => $row]);
                break;

            // --------------------------------------------------
            // CREATE DOCUMENT
            // --------------------------------------------------
            case 'create_document':
                $version_id        = (int)($_POST['version_id'] ?? 0);
                $entity_type       = $_POST['entity_type'] ?? 'employee';
                $entity_identifier = $_POST['entity_identifier'] ?? null;
                $title             = trim($_POST['title'] ?? 'Document');

                // placeholders JSON → array
                $placeholders = json_decode($_POST['placeholders'] ?? '{}', true);
                if(!is_array($placeholders)) $placeholders = [];

                if (!$version_id) throw new Exception("version required");

                // fetch content
                $stmt = $mysqli->prepare("SELECT content FROM template_versions WHERE id=?");
                $stmt->bind_param("i", $version_id);
                $stmt->execute();
                $res = $stmt->get_result();
                $row = $res->fetch_assoc();
                if (!$row) throw new Exception("version not found");

                $content = $row['content'];

                // replace placeholders
                foreach ($placeholders as $k => $v) {
                    $content = str_replace('{{' . $k . '}}', $v, $content);
                }

                // auto-date if still present
                $content = str_replace('{{date}}', date('Y-m-d'), $content);

                $stmt = $mysqli->prepare("
                    INSERT INTO created_documents (template_version_id, entity_type, entity_identifier, title, content, created_by)
                    VALUES (?, ?, ?, ?, ?, NULL)
                ");
                $stmt->bind_param("issss", $version_id, $entity_type, $entity_identifier, $title, $content);
                $stmt->execute();

                $site->agent_log("Created new document $title from document template");

                echo json_encode(['ok' => true, 'id' => $stmt->insert_id]);
                break;

            // --------------------------------------------------
            // LIST / GET / DELETE DOCUMENTS
            // --------------------------------------------------
            case 'list_documents':
                $limit = isset($_POST['limit'])?$site->esc($_POST['limit']):"10";
                $offset = isset($_POST['offset'])?$site->esc($_POST['offset']):"0";
                $result = $mysqli->query("
                    SELECT cd.*, tv.version, t.subtype 
                    FROM created_documents cd
                    JOIN template_versions tv ON cd.template_version_id = tv.id
                    JOIN templates t ON tv.template_id = t.id
                    ORDER BY cd.created_at DESC
                    LIMIT $offset, $limit
                ");
                echo json_encode(['ok' => true, 'data' => $result->fetch_all(MYSQLI_ASSOC)]);
                break;

            case 'get_document':
                $id = (int)($_GET['id'] ?? 0);
                $stmt = $mysqli->prepare("
                    SELECT cd.*, tv.version, t.subtype 
                    FROM created_documents cd
                    JOIN template_versions tv ON cd.template_version_id = tv.id
                    JOIN templates t ON tv.template_id = t.id
                    WHERE cd.id=?
                ");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $res = $stmt->get_result();
                echo json_encode(['ok' => true, 'data' => $res->fetch_assoc()]);
                break;

            case 'delete_document':
                $id = (int)($_POST['id'] ?? 0);
                $stmt = $mysqli->prepare("DELETE FROM created_documents WHERE id=?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $site->agent_log("Deleted created document #$id from Created documents");
                echo json_encode(['ok' => true]);
                break;

            // --------------------------------------------------
            // DOWNLOAD PDF (generate with TCPDF)
            // --------------------------------------------------
            case 'download_pdf':
                $id = (int)($_GET['id'] ?? 0);
                if(!$id) throw new Exception("id required");

                // fetch document record
                $stmt = $mysqli->prepare("SELECT cd.*, tv.version, t.subtype, tv.show_header FROM created_documents cd JOIN template_versions tv ON cd.template_version_id = tv.id JOIN templates t ON tv.template_id = t.id WHERE cd.id=?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $doc = $stmt->get_result()->fetch_assoc();
                if(!$doc) throw new Exception('Document not found');

                // load TCPDF
                if(!file_exists(TCPDF_PATH)) throw new Exception('TCPDF not found at ' . TCPDF_PATH . '. Install TCPDF (composer require tecnickcom/tcpdf) and set TCPDF_PATH.');
                require_once TCPDF_PATH;

                // extend TCPDF to support custom header/footer HTML
                class MYPDF extends TCPDF {
                    public $header_html = '';
                    public $footer_html = '';
                    public function Header() {
                        if($this->header_html){
                            $this->setCellPaddings(0,0,0,0);
                            $this->setCellMargins(0,0,0,0);
                            
                            $this->SetY(5);
                            $this->writeHTMLCell(0, 0, '', '', $this->header_html, 0, 1, 0, true, 'C', true);
                        }
                    }
                    public function Footer() {
                        if($this->footer_html){
                            $this->setCellPaddings(0,0,0,0);
                            $this->setCellMargins(0,0,0,0);

                            $this->SetY(-30);
                            $this->writeHTMLCell(0, 0, '', '', $this->footer_html, 0, 1, 0, true, 'C', true);
                        }
                    }
                }

                // create PDF
                $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('System');
                $pdf->SetTitle($doc['title'] ?? 'Document');
                $pdf->SetSubject($doc['subtype'] ?? '');
                
                // set margins (leave room for header/footer)
                $doc['show_header']=='1'?$pdf->SetMargins(15, 60, 15):'';
                
                $pdf->SetHeaderMargin(10);
                $pdf->SetFooterMargin(10);
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(true);

                // set header/footer HTML (use config variables above)
                global $PDF_HEADER_HTML, $PDF_FOOTER_HTML;
                $pdf->header_html = $doc['show_header']=='1'?$PDF_HEADER_HTML:'';
                // replace page placeholders in footer
                $footer_html = str_replace(['{PAGE_NUM}','{PAGE_COUNT}'], [$pdf->getAliasNumPage(), $pdf->getAliasNbPages()], $PDF_FOOTER_HTML);
                $pdf->footer_html = $doc['show_header']=='1'?$footer_html:'';

                $pdf->AddPage();

                // content is HTML stored in created_documents.content — write it
                $content_html = nl2br($doc['content']);
                $pdf->writeHTML($content_html, true, false, true, false, '');

                // send PDF to browser (open in new tab)
                $pdfname = preg_replace('/[^a-z0-9_\-]+/i','_', ($doc['title'] ?? 'document')) . '.pdf';

                // output directly
                $pdf->Output($pdfname, 'I'); // 'I' to inline display in browser
                // NOTE: we do not echo json here; Output() ends script
                exit;
                break;

            case 'search_entities':
                $type = $_GET['type'] ?? '';
                $keyword = '%' . ($_GET['keyword'] ?? '') . '%';

                if ($type === 'employee') {
                    $stmt = $mysqli->prepare("SELECT id, name, email FROM employees WHERE name LIKE ? OR email LIKE ? OR id LIKE ? LIMIT 10");
                    $stmt->bind_param("sss", $keyword, $keyword, $keyword);
                }
                elseif ($type === 'customer') {
                    $stmt = $mysqli->prepare("SELECT id, company, name FROM customers WHERE company LIKE ? OR name LIKE ? OR id LIKE ? LIMIT 10");
                    $stmt->bind_param("sss", $keyword, $keyword, $keyword);
                }
                elseif ($type === 'recruiter') {
                    $stmt = $mysqli->prepare("SELECT id, name FROM recruiters WHERE name LIKE ? OR id LIKE ? LIMIT 10");
                    $stmt->bind_param("ss", $keyword, $keyword);
                }
                else {
                    echo json_encode(['ok'=>true, 'data'=>[]]);
                    exit;
                }

                $stmt->execute();
                $result = $stmt->get_result();
                echo json_encode(['ok'=>true, 'data'=>$result->fetch_all(MYSQLI_ASSOC)]);
                break;


            // --------------------------------------------------
            // ADD CREATED DOCUMENT TO PROFILE
            // --------------------------------------------------
            case 'add_to_profile':
                $doc_id        = (int)($_POST['doc_id'] ?? 0);
                $etype         = $_POST['entity_type'] ?? '';
                $identifier    = (int)($_POST['entity_identifier'] ?? 0);
                $label         = trim($_POST['title'] ?? '');
                $created_by    = $_SESSION['person_name'] ?? 'SYSTEM';

                if (!$doc_id || !$etype || !$identifier) {
                    throw new Exception("Missing data");
                }

                // Fetch created document content to generate file
                $stmt = $mysqli->prepare("SELECT cd.*, tv.version, t.subtype, tv.show_header FROM created_documents cd JOIN template_versions tv ON cd.template_version_id = tv.id JOIN templates t ON tv.template_id = t.id WHERE cd.id=?");
                $stmt->bind_param("i", $doc_id);
                $stmt->execute();
                $doc = $stmt->get_result()->fetch_assoc();
                if (!$doc) throw new Exception("Document not found");

                // Generate a PDF file on disk
                $filename = 'doc_' . $doc_id . '_' . time() . '.pdf';
                $filepath = __DIR__ . '/../../uploads/'.$etype.'s/documents/' . $filename;

                // Create the PDF using html2pdf or TCPDF logic (your existing PDF generator)
                generate_pdf_file($doc, $filepath);

                // Insert into respective table
                switch ($etype) {
                    case 'employee':
                        $table = "employees_documents";
                        $col = "employee_id";
                        break;
                    case 'customer':
                        $table = "customers_documents";
                        $col = "customer_id";
                        break;
                    case 'recruiter':
                        $table = "recruiters_documents";
                        $col = "recruiter_id";
                        break;
                    default:
                        throw new Exception("Invalid entity type");
                }

                $stmt2 = $mysqli->prepare("
                    INSERT INTO $table ($col, label, file_name, file_type, created_by) 
                    VALUES (?, ?, ?, 'pdf', ?)
                ");
                $stmt2->bind_param("isss", $identifier, $label, $filename, $created_by);
                $ok = $stmt2->execute();

                if($ok) {
                    $stm = $mysqli->query("UPDATE created_documents SET add_to_profile=1 WHERE id='$doc_id'");
                    $site->agent_log("Added new document $label from Created documents",$identifier,$etype);
                }

                echo json_encode(['ok' => true]);
                break;

            case 'datatable_documents':
                $start  = intval($_POST['start'] ?? 0);
                $length = intval($_POST['length'] ?? 10);
                $search = trim($_POST['search']['value'] ?? "");

                $start_date = $_POST["start_date"] ?? "";
                $end_date   = $_POST["end_date"] ?? "";
                $etype      = $_POST["entity_type"] ?? "";

                $where = " WHERE 1 ";

                if ($search !== "") {
                    $q = "%$search%";
                    $where .= " AND (title LIKE ? OR subtype LIKE ? OR entity_type LIKE ?) ";
                    $params = [$q,$q,$q];
                } else $params = [];

                if ($etype !== "") {
                    $where .= " AND entity_type = ? ";
                    $params[] = $etype;
                }

                if ($start_date && $end_date) {
                    $where .= " AND DATE(cd.created_at) BETWEEN ? AND ? ";
                    $params[] = $start_date;
                    $params[] = $end_date;
                }

                // Total count
                $total = $mysqli->query("SELECT COUNT(*) FROM created_documents")->fetch_row()[0];

                // Filtered count
                $stmt = $mysqli->prepare("SELECT COUNT(*) FROM created_documents as cd JOIN template_versions tv ON cd.template_version_id = tv.id
                    JOIN templates t ON tv.template_id = t.id $where");
                if (!empty($params)) {
                    // create dynamic bind string
                    $types = str_repeat("s", count($params));
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $stmt->bind_result($filtered);
                $stmt->fetch();
                $stmt->close();


                // DataTables ordering
                $orderColIndex = $_POST['order'][0]['column'] ?? 0;
                $orderDir      = $_POST['order'][0]['dir'] ?? "desc";
                $orderDir      = ($orderDir === "asc") ? "ASC" : "DESC";

                // Column map
                $columnsMap = [
                    0 => "cd.title",
                    1 => "t.subtype",
                    2 => "cd.entity_type",
                    3 => "cd.created_at"
                ];

                // Determine order column
                $orderColumn = $columnsMap[$orderColIndex] ?? "cd.created_at";

                // Query with dynamic ORDER BY
                $sql = "
                    SELECT 
                        cd.*, 
                        tv.version, 
                        t.subtype,

                        /* Resolve entity name dynamically */
                        CASE cd.entity_type
                            WHEN 'employee' THEN (SELECT name FROM employees WHERE id = cd.entity_identifier)
                            WHEN 'customer' THEN (SELECT name FROM customers WHERE id = cd.entity_identifier)
                            WHEN 'recruiter' THEN (SELECT name FROM recruiters WHERE id = cd.entity_identifier)
                            -- WHEN 'supplier' THEN (SELECT name FROM suppliers WHERE id = cd.entity_identifier)
                            ELSE ''
                        END AS entity_name

                    FROM created_documents cd
                    JOIN template_versions tv ON cd.template_version_id = tv.id
                    JOIN templates t ON tv.template_id = t.id
                    $where
                    ORDER BY $orderColumn $orderDir
                    LIMIT ?, ?
                ";


                $stmt = $mysqli->prepare($sql);

                $start  = intval($_POST['start'] ?? 0);
                $length = intval($_POST['length'] ?? 10);
                $types = "";
                $params2 = [];
                if (!empty($params)) {
                    $types .= str_repeat("s", count($params));
                    $params2 = $params; // existing params (search, filters)
                }
                // add LIMIT parameters
                $types .= "ii";
                $params2[] = $start;
                $params2[] = $length;
                // bind dynamically
                $stmt->bind_param($types, ...$params2);
                $stmt->execute();
                $result = $stmt->get_result();
                $rows = $result->fetch_all(MYSQLI_ASSOC);
                $stmt->close();

                echo json_encode([
                    "draw"            => intval($_POST['draw'] ?? 1),
                    "recordsTotal"    => $total,
                    "recordsFiltered" => $filtered,
                    "data"            => $rows
                ]);
                break;

            default:
                throw new Exception("Unknown action");
        }
    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }

    exit;
}
?>