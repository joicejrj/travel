<?php

// ini_set("display_errors", 0); error_reporting(E_ALL);
/**
 * JRJ TravelPort — Fare Rules AJAX Handler
 * 
 * From Search:
 *   GET /api/fare_rules.php?source=search&offering_id=o1&product_id=p0&rule_type=ShortText
 * 
 * From Reservation (after booking):
 *   GET /api/fare_rules.php?source=reservation&pnr=ABC123&rule_type=ShortText
 *   GET /api/fare_rules.php?source=reservation&pnr=ABC123&rule_type=Structured&categories=Penalties
 */
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../includes/TravelportAPI.php';

header('Content-Type: application/json');

$source   = sanitize($_GET['source'] ?? 'search');
$ruleType = sanitize($_GET['rule_type'] ?? 'ShortText');

// Validate rule type
$validTypes = ['ShortText', 'LongText', 'Structured'];
if (!in_array($ruleType, $validTypes)) {
    $ruleType = 'ShortText';
}

try {
    $api = new TravelportAPI();

    if ($source === 'reservation') {
        // ---- Fare Rules from Reservation (after booking) ----
        $pnr = strtoupper(sanitize($_GET['pnr'] ?? ''));
        $categories = sanitize($_GET['categories'] ?? '');

        if (!$pnr) {
            jsonResponse(['error' => 'Missing PNR / locator code.'], 400);
        }

        $result = $api->getFareRulesFromReservation($pnr, $ruleType, $categories ?: null);

    } else {
        // ---- Fare Rules from Search ----
        $catalogIdentifier = $_SESSION['catalog_identifier'] ?? '';
        $offeringId = sanitize($_GET['offering_id'] ?? '');
        $productId  = sanitize($_GET['product_id'] ?? '');

        if (!$catalogIdentifier) {
            jsonResponse(['error' => 'Search session expired. Please search again.'], 400);
        }
        if (!$offeringId || !$productId) {
            jsonResponse(['error' => 'Missing offering or product ID.'], 400);
        }

        $result = $api->getFareRules($catalogIdentifier, $offeringId, $productId, $ruleType);
    }

    $data = $result['data'] ?? [];

    // Parse the fare rules response
    $fareRules = [];
    $ruleList = $data['FareRuleListResponse']['FareRule']
             ?? $data['FareRule']
             ?? [];

    foreach ($ruleList as $rule) {
        $ruleType = $rule['@type'] ?? '';
        $ruleNumber = $rule['ruleNumber'] ?? '';
        $ruleId = $rule['id'] ?? '';

        if ($ruleType === 'FareRuleText' || isset($rule['TextFareRule'])) {
            // Short or Long text fare rules
            $textRules = $rule['TextFareRule'] ?? [];
            $parsedRules = [];
            foreach ($textRules as $tr) {
                $name = $tr['name'] ?? '';
                $value = $tr['value'] ?? '';
                if ($name && $value) {
                    $parsedRules[] = [
                        'name'  => $name,
                        'value' => $value,
                    ];
                }
            }
            if (!empty($parsedRules)) {
                $fareRules[] = [
                    'type'       => 'text',
                    'ruleNumber' => $ruleNumber,
                    'ruleId'     => $ruleId,
                    'rules'      => $parsedRules,
                ];
            }
        } elseif ($ruleType === 'FareRuleStructured' || isset($rule['StructuredFareRules'])) {
            // Structured fare rules (penalties, min/max stay, etc.)
            $structured = $rule['StructuredFareRules'] ?? [];
            $penalties = [];

            foreach ($structured as $sfr) {
                $ptc = $sfr['passengerTypeCodes'] ?? [];
                $fareClass = $sfr['fareClassCode'] ?? '';

                // Penalties
                if (isset($sfr['Penalties'])) {
                    foreach ($sfr['Penalties'] as $penalty) {
                        $entry = ['passengerTypes' => $ptc, 'fareClass' => $fareClass];

                        if (isset($penalty['Change'])) {
                            foreach ($penalty['Change'] as $chg) {
                                $type = $chg['@type'] ?? '';
                                $penaltyTypes = $chg['penaltyTypes'] ?? [];
                                if ($type === 'ChangeNotPermitted') {
                                    $entry['change'] = 'Not Permitted';
                                } else {
                                    $amount = '';
                                    if (isset($chg['Penalty'][0]['Amount'])) {
                                        $amt = $chg['Penalty'][0]['Amount'];
                                        $amount = ($amt['code'] ?? '') . ' ' . ($amt['value'] ?? '');
                                    }
                                    $entry['change'] = 'Permitted' . ($amount ? " — Fee: $amount" : ' — No Fee') . ($penaltyTypes ? ' (' . implode(', ', $penaltyTypes) . ')' : '');
                                }
                                $penalties[] = $entry;
                            }
                        }

                        if (isset($penalty['Cancel'])) {
                            foreach ($penalty['Cancel'] as $cnx) {
                                $type = $cnx['@type'] ?? '';
                                $penaltyTypes = $cnx['penaltyTypes'] ?? [];
                                if ($type === 'CancelNotPermitted') {
                                    $entry['cancel'] = 'Not Permitted';
                                } else {
                                    $amount = '';
                                    if (isset($cnx['Penalty'][0]['Amount'])) {
                                        $amt = $cnx['Penalty'][0]['Amount'];
                                        $amount = ($amt['code'] ?? '') . ' ' . ($amt['value'] ?? '');
                                    } elseif (isset($cnx['Penalty'][0]['Percent'])) {
                                        $amount = $cnx['Penalty'][0]['Percent'] . '%';
                                    }
                                    $entry['cancel'] = 'Permitted' . ($amount ? " — Fee: $amount" : ' — No Fee') . ($penaltyTypes ? ' (' . implode(', ', $penaltyTypes) . ')' : '');
                                }
                                $penalties[] = $entry;
                            }
                        }
                    }
                }

                // Min/Max Stay
                if (isset($sfr['MinimumStay'])) {
                    $penalties[] = ['minimumStay' => parseMinMaxStay($sfr['MinimumStay'], 'Minimum')];
                }
                if (isset($sfr['MaximumStay'])) {
                    $penalties[] = ['maximumStay' => parseMinMaxStay($sfr['MaximumStay'], 'Maximum')];
                }
                if (isset($sfr['AdvanceReservation'])) {
                    $penalties[] = ['advanceReservation' => parseAdvanceReservation($sfr['AdvanceReservation'])];
                }
                if (isset($sfr['Stopover'])) {
                    $penalties[] = ['stopover' => parseStopover($sfr['Stopover'])];
                }
            }

            if (!empty($penalties)) {
                $fareRules[] = [
                    'type'      => 'structured',
                    'penalties' => $penalties,
                ];
            }
        }
    }

    // Check for warnings/errors
    $warnings = $data['FareRuleListResponse']['Result']['Warning'] ?? $data['Result']['Warning'] ?? [];
    $errors   = $data['FareRuleListResponse']['Result']['Error'] ?? $data['Result']['Error'] ?? [];

    jsonResponse([
        'success'    => true,
        'fareRules'  => $fareRules,
        'warnings'   => $warnings,
        'errors'     => $errors,
        'rawResponse'=> $local ? $data : null,
    ]);

} catch (Exception $e) {
    jsonResponse(['error' => $e->getMessage()], 500);
}

// ================================================================
// Helper functions to parse structured fare rule objects
// ================================================================

/**
 * Parse MinimumStay / MaximumStay arrays into readable text
 */
function parseMinMaxStay($stayArray, $label = 'Minimum') {
    if (!is_array($stayArray)) return 'No restrictions';
    $parts = [];
    foreach ($stayArray as $stay) {
        $type = $stay['@type'] ?? '';
        if (stripos($type, 'Indeterminate') !== false) {
            $parts[] = 'No specific ' . strtolower($label) . ' stay requirement';
            continue;
        }
        $text = '';
        // Duration-based stay
        if (isset($stay['duration'])) {
            $text = 'Stay of at least ' . formatDurationText($stay['duration']);
        }
        if (isset($stay['stayUnit']) && isset($stay['stayDuration'])) {
            $text = $stay['stayDuration'] . ' ' . $stay['stayUnit'];
        }
        // Date-based
        if (isset($stay['returnTravelDate'])) {
            $text .= ($text ? '. ' : '') . 'Return by: ' . $stay['returnTravelDate'];
        }
        if (isset($stay['departureDate'])) {
            $text .= ($text ? '. ' : '') . 'Depart by: ' . $stay['departureDate'];
        }
        // Day of week
        if (isset($stay['dayOfWeek'])) {
            $text .= ($text ? '. ' : '') . 'Day: ' . $stay['dayOfWeek'];
        }
        $parts[] = $text ?: ucfirst(preg_replace('/([A-Z])/', ' $1', str_replace(['@type', '"'], '', $type)));
    }
    return implode('. ', array_filter($parts)) ?: 'See fare conditions';
}

/**
 * Parse AdvanceReservation array into readable text
 */
function parseAdvanceReservation($advArray) {
    if (!is_array($advArray)) return 'No advance reservation requirement';
    $parts = [];
    foreach ($advArray as $adv) {
        $type = $adv['@type'] ?? '';
        $text = '';

        if (stripos($type, 'NotRequired') !== false || stripos($type, 'Indeterminate') !== false) {
            $parts[] = 'No advance reservation required';
            continue;
        }

        // Instant purchase
        $instant = $adv['instantPurchase'] ?? '';
        if ($instant === 'Yes') {
            $text = 'Instant purchase required (must buy at time of booking)';
        } elseif ($instant === 'No') {
            $text = 'Advance reservation required';
        }

        // Advance days/hours
        if (isset($adv['advanceDays'])) {
            $text .= ($text ? '. ' : '') . 'Reserve at least ' . $adv['advanceDays'] . ' days before departure';
        }
        if (isset($adv['advanceHours'])) {
            $text .= ($text ? '. ' : '') . 'Reserve at least ' . $adv['advanceHours'] . ' hours before departure';
        }

        // Ticketing deadline
        if (isset($adv['ticketingDeadline'])) {
            $text .= ($text ? '. ' : '') . 'Ticketing deadline: ' . $adv['ticketingDeadline'];
        }

        // Waitlist/Standby
        if (isset($adv['WaitlistStandbyCondition'])) {
            $conditions = $adv['WaitlistStandbyCondition'];
            foreach ($conditions as $wsc) {
                $val = $wsc['value'] ?? '';
                $readable = preg_replace('/([A-Z])/', ' $1', $val);
                $text .= ($text ? '. ' : '') . trim($readable);
            }
        }

        $parts[] = $text ?: 'Advance reservation required';
    }
    return implode('. ', array_filter($parts)) ?: 'See fare conditions';
}

/**
 * Parse Stopover array into readable text
 */
function parseStopover($stopArray) {
    if (!is_array($stopArray)) return 'No stopover information';
    $parts = [];
    foreach ($stopArray as $stop) {
        $type = $stop['@type'] ?? '';
        $text = '';

        if (stripos($type, 'NotPermitted') !== false) {
            $text = 'Stopovers not permitted';
        } elseif (stripos($type, 'Permitted') !== false) {
            $text = 'Stopovers permitted';
            if (isset($stop['maximumDuration'])) {
                $text .= ' (max duration: ' . formatDurationText($stop['maximumDuration']) . ')';
            }
            if (isset($stop['outbound'])) {
                $text .= '. Outbound: ' . $stop['outbound'] . ' stopovers';
            }
            if (isset($stop['inbound'])) {
                $text .= '. Inbound: ' . $stop['inbound'] . ' stopovers';
            }
        } elseif (stripos($type, 'Restriction') !== false) {
            // Geographic restrictions
            if (isset($stop['GeographicRestriction'])) {
                $geos = $stop['GeographicRestriction'];
                foreach ($geos as $geo) {
                    $geoType = $geo['geographicRestrictionType'] ?? 'Location';
                    $geoVal = $geo['value'] ?? '';
                    $text .= ($text ? ', ' : 'Restricted to: ') . $geoVal . ' (' . $geoType . ')';
                }
            } else {
                $text = 'Stopover restrictions apply';
            }
        } else {
            $text = 'Stopover conditions apply';
        }

        $parts[] = $text;
    }
    return implode('. ', array_filter($parts)) ?: 'See fare conditions';
}

/**
 * Convert ISO duration (PT45D, PT3H30M) to readable text
 */
function formatDurationText($dur) {
    if (!$dur) return '';
    $result = [];
    if (preg_match('/(\d+)D/', $dur, $m)) $result[] = $m[1] . ' days';
    if (preg_match('/(\d+)H/', $dur, $m)) $result[] = $m[1] . ' hours';
    if (preg_match('/(\d+)M/', $dur, $m)) $result[] = $m[1] . ' minutes';
    return implode(' ', $result) ?: $dur;
}
