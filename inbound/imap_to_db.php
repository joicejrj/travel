<?php
// inbound/imap_to_db.php - processes raw email and saves to DB (attachments + thread basic)

function imap_log($msg){
  $f = __DIR__ . '/../storage/imap.log';
  @file_put_contents($f, "[".date('c')."] $msg\n", FILE_APPEND);
}

/**
 * process_raw_email_to_db
 *
 * - $raw: raw header+body (may be partial). If imap_stream + imap_msgno provided in $meta,
 *   the function will prefer to use those to reliably extract parts and attachments.
 * - $db: mysqli handle
 * - $meta: mailbox_id, source_folder, imap_stream, imap_msgno, is_sent, sent_via
 */
function process_raw_email_to_db(string $raw, $db, array $meta = []){
  if (!$db) { imap_log('DB handle missing'); return; }

  $is_sent = isset($meta['is_sent']) ? (int)$meta['is_sent'] : 0;
  $sent_via = isset($meta['sent_via']) ? $meta['sent_via'] : 'fetch';

  // split headers/body (basic)
  $parts = preg_split("/\r\n\r\n/", $raw, 2);
  $raw_headers = $parts[0] ?? '';
  $raw_body = $parts[1] ?? '';

  // simple header parser
  $headers = [];
  $lines = preg_split("/\r\n/", $raw_headers);
  $last = null;
  foreach ($lines as $ln) {
    if (preg_match('/^\s+/', $ln) && $last) {
      $headers[$last] .= ' ' . trim($ln);
    } elseif (strpos($ln, ':') !== false) {
      [$k,$v] = explode(':', $ln, 2);
      $k = trim($k);
      $v = trim($v);
      $headers[$k] = $v;
      $last = $k;
    }
  }

  $parse_address = function($str) {
    $str = trim($str);
    if (!$str) return [];
    $addrs = [];
    $items = preg_split('/,(?=(?:[^"]*"[^"]*")*[^"]*$)/', $str);
    foreach ($items as $it) {
      if (preg_match('/(.*)<(.+@.+)>/', trim($it), $m)) {
        $addrs[] = ['name' => trim(trim($m[1],'" ')), 'email' => trim($m[2])];
      } else {
        $clean = trim($it);
        if ($clean !== '') $addrs[] = ['name' => '', 'email' => $clean];
      }
    }
    return $addrs;
  };

  $from = $headers['From'] ?? '';
  $to = $headers['To'] ?? '';
  $cc = $headers['Cc'] ?? '';
  $subject = $headers['Subject'] ?? '';
  $message_id = $headers['Message-ID'] ?? '';
  $in_reply_to = $headers['In-Reply-To'] ?? '';
  $references = $headers['References'] ?? '';

  $from_addrs = $parse_address($from);
  $from_email = $from_addrs[0]['email'] ?? '';
  $from_name = $from_addrs[0]['name'] ?? '';

  $to_addrs = $parse_address($to);
  $to_emails = implode(',', array_map(function($a){return $a['email'];}, $to_addrs));
  $cc_addrs = $parse_address($cc);
  $cc_emails = implode(',', array_map(function($a){return $a['email'];}, $cc_addrs));

  // results containers
  $body_html = null;
  $body_text = null;
  $attachments_saved = [];

  // attachment base path (create per mailbox)
  $mailbox_id = $meta['mailbox_id'] ?? 0;
  $attachment_base = __DIR__ . "/../storage/mail_attachments/mb_{$mailbox_id}/";
  if (!is_dir($attachment_base)) @mkdir($attachment_base, 0755, true);

  // helper: decode based on encoding
  $decode = function($data, $encoding) {
    switch ((int)$encoding) {
      case 3: // BASE64
        return base64_decode($data);
      case 4: // QUOTED-PRINTABLE
        return quoted_printable_decode($data);
      default:
        return $data;
    }
  };

  // helper: convert charset to UTF-8 if available
  $convert_charset = function($text, $partObj = null) {
    if (!$partObj) return $text;
    $charset = null;
    if (!empty($partObj->parameters)) {
      foreach ($partObj->parameters as $p) {
        if (isset($p->attribute) && strtolower($p->attribute) === 'charset') {
          $charset = $p->value;
          break;
        }
      }
    }
    if ($charset && function_exists('mb_convert_encoding')) {
      $charset = strtoupper($charset);
      if ($charset !== 'UTF-8') {
        $conv = @mb_convert_encoding($text, 'UTF-8', $charset);
        if ($conv !== false) return $conv;
      }
    }
    return $text;
  };

  // We'll collect text/plain and text/html parts into arrays, then choose best later
  $collected_texts = [];
  $collected_htmls = [];

  // If IMAP meta is present, use structure to separate parts and save attachments
  if (!empty($meta['imap_stream']) && !empty($meta['imap_msgno'])) {
    $stream = $meta['imap_stream'];
    $msgno = $meta['imap_msgno'];
    $struct = @imap_fetchstructure($stream, $msgno);

    // DEBUG: uncomment to log structure for a failing message
     imap_log("STRUCT for msg {$msgno}: " . print_r($struct, true));

    // Walk parts if we have a structure
    if ($struct && ( (isset($struct->parts) && count($struct->parts) > 0) || (isset($struct->type) && (int)$struct->type === 0) )) {

      // recursive walker closure; use $walk within itself via use (&$walk)
      $walk = null;
      $walk = function($part, $partno = '') use (&$walk, $stream, $msgno, &$collected_texts, &$collected_htmls, &$attachments_saved, $decode, $convert_charset, $attachment_base) {
        // If multipart, iterate children
        if (!empty($part->parts)) {
          foreach ($part->parts as $idx => $sub) {
            // Build IMAP part number (1, 1.2, 2.1 etc) as string
            $subno = ($partno === '') ? (string)($idx + 1) : $partno . '.' . ($idx + 1);
            $walk($sub, $subno);
          }
          return;
        }

        // Determine type/subtype
        $type = isset($part->type) ? (int)$part->type : null; // 0=text,1=mult,2=message,3=application,4=image...
        $subtype = isset($part->subtype) ? strtolower($part->subtype) : '';

        // Part number fallback (top-level single part)
        $actual_partno = ($partno === '') ? '1' : $partno;

        // If text/plain or text/html -> collect to arrays
        if ($type === 0 && in_array($subtype, ['plain','html'])) {
          $rawdata = @imap_fetchbody($stream, $msgno, $actual_partno, FT_PEEK);
          // Note: some PHP/IMAP setups accept imap_fetchbody($stream, $msgno, $actual_partno) — we keep partno as string
          if ($rawdata === false) $rawdata = '';
          $decoded = $decode($rawdata, $part->encoding ?? 0);
          $decoded = $convert_charset($decoded, $part);
          // extra safe decode for quoted-printable artifacts
          $decoded = quoted_printable_decode($decoded);
          if ($subtype === 'html') {
            $collected_htmls[] = $decoded;
          } else {
            $collected_texts[] = $decoded;
          }
          return;
        }

        // attachments or application/image types
        $is_attachment = false;
        $filename = '';
        if (!empty($part->dparameters)) {
          foreach ($part->dparameters as $dp) {
            if (isset($dp->attribute) && in_array(strtolower($dp->attribute), ['filename','name'])) {
              $is_attachment = true;
              $filename = $dp->value;
              break;
            }
          }
        }
        if (!$is_attachment && !empty($part->parameters)) {
          foreach ($part->parameters as $p) {
            if (isset($p->attribute) && in_array(strtolower($p->attribute), ['name','filename'])) {
              $is_attachment = true;
              $filename = $p->value;
              break;
            }
          }
        }

        if ($is_attachment || in_array($type, [3,4])) {
          $rawdata = @imap_fetchbody($stream, $msgno, $actual_partno, FT_PEEK);
          if ($rawdata === false) $rawdata = '';
          $decoded = $decode($rawdata, $part->encoding ?? 0);
          if (empty($filename)) $filename = 'attachment_' . time();
          $safe = time().'_'.bin2hex(random_bytes(5)).'_'.preg_replace('/[^a-zA-Z0-9._-]/','_',$filename);
          $path = rtrim($attachment_base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $safe;
          file_put_contents($path, $decoded);
          $attachments_saved[] = ['filename'=>$filename, 'path'=>$path, 'size'=>filesize($path), 'mime'=>($part->subtype ?? '')];
        }
        // otherwise ignore
      };

      // if top-level is type=0 (singlepart), walk with part '1'
      if ((int)($struct->type ?? -1) === 0 && empty($struct->parts)) {
        $walk($struct, '1');
      } else {
        $walk($struct, '');
      }
    } else {
      // structure missing or no parts -> fallback to raw_body
      $collected_texts[] = $raw_body;
    }
  } else {
    // no imap metadata: try to detect html
    if (stripos($raw_body, '<html') !== false) $collected_htmls[] = $raw_body;
    else $collected_texts[] = $raw_body;
  }

  // Normalize collected parts
  $collected_texts = array_map(function($s){ return trim(quoted_printable_decode($s)); }, $collected_texts);
  $collected_htmls = array_map(function($s){ return trim(quoted_printable_decode($s)); }, $collected_htmls);

  // Choose best text/html
  $best_text = '';
  $best_html = '';

  if (!empty($collected_texts)) {
    usort($collected_texts, function($a,$b){ return strlen($b) - strlen($a); });
    $best_text = $collected_texts[0];
  }

  if (!empty($collected_htmls)) {
    usort($collected_htmls, function($a,$b){ return strlen($b) - strlen($a); });
    $best_html = $collected_htmls[0];
  }

  $best_html_stripped = $best_html ? trim(strip_tags($best_html)) : '';

  if ($best_html_stripped !== '' && strlen($best_html_stripped) > strlen($best_text)) {
    $body_html = $best_html;
    $body_text = $best_html_stripped;
  } else {
    $body_text = $best_text ?: null;
    $body_html = $best_html ?: null;
  }

  // Last fallback: raw_body
  if (empty($body_text) && empty($body_html) && !empty($raw_body)) {
    if (stripos($raw_body, '<html') !== false) {
      $body_html = quoted_printable_decode($raw_body);
      $body_text = trim(strip_tags($body_html));
    } else {
      $body_text = quoted_printable_decode($raw_body);
    }
  }

  // Thread heuristics
  $thread_ref = null;
  if (!empty($in_reply_to)) $thread_ref = trim($in_reply_to);
  elseif (!empty($references)) $thread_ref = trim($references);
  $thread_id = $thread_ref ?: ($message_id ?: null);

  $folder = $meta['source_folder'] ?? ($headers['X-Folder'] ?? 'INBOX');

  // duplicate check by message_id
  if ($message_id) {
    $chk = $db->prepare("SELECT id FROM email_log WHERE message_id=? LIMIT 1");
    if ($chk) {
      $chk->bind_param('s', $message_id);
      $chk->execute();
      $chk->store_result();
      if ($chk->num_rows > 0) { imap_log("Duplicate message_id $message_id - skipping"); $chk->close(); return; }
      $chk->close();
    }
  }

  // Determine message datetime
  $message_date_raw = null;
  if (!empty($meta['imap_stream']) && !empty($meta['imap_msgno'])) {
    $stream = $meta['imap_stream'];
    $msgno  = $meta['imap_msgno'];
    $ov = @imap_fetch_overview($stream, $msgno, 0);
    if ($ov && is_array($ov) && isset($ov[0]->date)) {
      $message_date_raw = $ov[0]->date;
    }
    if (empty($message_date_raw) && !empty($headers['Date'])) {
      $message_date_raw = $headers['Date'];
    }
  } else {
    if (!empty($headers['Date'])) $message_date_raw = $headers['Date'];
  }

  $created_at_mysql = null;
  if (!empty($message_date_raw)) {
    try {
      $dt = new DateTime($message_date_raw);
      $dt->setTimezone(new DateTimeZone('UTC'));
      $created_at_mysql = $dt->format('Y-m-d H:i:s');
    } catch (Exception $e) {
      $created_at_mysql = gmdate('Y-m-d H:i:s');
    }
  } else {
    $created_at_mysql = gmdate('Y-m-d H:i:s');
  }

  // --- INSERT into email_log ---
  $stmt = $db->prepare("INSERT INTO email_log
    (mailbox_id, folder, message_id, subject, from_name, from_email, to_emails, cc, body_html, body_text, is_sent, sent_via, thread_id, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
  if ($stmt) {
    $types = 'isssssssssisss';
    $folder_val = $folder ?? '';
    $message_id_val = $message_id ?: '';
    $subject_val = $subject ?: '';
    $from_name_val = $from_name ?: '';
    $from_email_val = $from_email ?: '';
    $to_emails_val = $to_emails ?: '';
    $cc_emails_val = $cc_emails ?: '';
    $body_html_val = $body_html ?: '';
    $body_text_val = $body_text ?: '';
    $thread_id_val = $thread_id ?: '';
    $created_at_val = $created_at_mysql ?: gmdate('Y-m-d H:i:s');

    $stmt->bind_param($types,
      $mailbox_id,
      $folder_val,
      $message_id_val,
      $subject_val,
      $from_name_val,
      $from_email_val,
      $to_emails_val,
      $cc_emails_val,
      $body_html_val,
      $body_text_val,
      $is_sent,
      $sent_via,
      $thread_id_val,
      $created_at_val
    );
    $stmt->execute();
    $email_id = $stmt->insert_id;
    if ($stmt->errno) imap_log("email_log insert error: {$stmt->errno} {$stmt->error}");
    $stmt->close();

    // Only insert inbound_emails for inbound (not sent) messages and only if folder looks like INBOX
    try {
      $isInbox = (strtolower($folder ?? '') === 'inbox' || stripos($folder ?? '', 'inbox') !== false);
      if (!$is_sent && $isInbox) {
        $insertSql = "INSERT INTO inbound_emails
          (mailbox_id, created_at, sender, recipient, subject, body_html, body_text, message_id, in_reply_to, thread_id, email_log_id)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $ins = $db->prepare($insertSql);
        if (!$ins) {
          imap_log("Prepare inbound_emails failed: " . $db->error . " SQL: " . $insertSql);
        } else {
          $sender = $from_email ?: $from_name;
          $recipient = $to_emails ?: '';
          $subj = $subject ?: '';
          $bhtml = $body_html ?? '';
          $btext = $body_text ?? '';
          $mid = $message_id ?: null;
          $inReply = $in_reply_to ?: null;
          $tid = $thread_id ?: null;
          $elogid = isset($email_id) ? (int)$email_id : null;

          $ins->bind_param('isssssssssi',
            $mailbox_id,
            $created_at_val,
            $sender,
            $recipient,
            $subj,
            $bhtml,
            $btext,
            $mid,
            $inReply,
            $tid,
            $elogid
          );

          if ($ins->execute()) {
            $newInboundId = $ins->insert_id;
            imap_log("Inserted inbound_emails id {$newInboundId} linked email_log_id {$elogid} mailbox {$mailbox_id} folder {$folder} subj: " . substr($subj,0,80));
          } else {
            imap_log("Execute failed for inbound_emails insert: " . $ins->error);
          }
          $ins->close();
        }
      }
    } catch (Throwable $e) {
      imap_log("Failed to insert into inbound_emails: " . $e->getMessage());
    }

    // save attachments into email_attachments table if present
    if (!empty($attachments_saved)) {
      $att_stmt = $db->prepare("INSERT INTO email_attachments (email_id, filename, path, size, mime, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
      if ($att_stmt) {
        foreach ($attachments_saved as $a) {
          $att_stmt->bind_param('issis', $email_id, $a['filename'], $a['path'], $a['size'], $a['mime']);
          $att_stmt->execute();
        }
        $att_stmt->close();
      } else {
        imap_log("Prepare email_attachments failed: " . $db->error);
      }
    }

    imap_log("Stored email id $email_id from mailbox {$mailbox_id} folder {$folder} subject ".substr($subject,0,80)." is_sent={$is_sent} via={$sent_via}");
  } else {
    imap_log("Prepare email_log failed: " . $db->error);
  }
}
