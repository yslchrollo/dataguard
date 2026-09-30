<?php

namespace App\Services;

use App\Models\SensitiveDataCategory;
use App\Models\SecurityPolicy;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class DlpScanEngine
{
    /**
     * Approved domain patterns for corporate internal transfer.
     */
    protected array $approvedInternalDomains = [
        'company.com',
        'dataguard.corp',
        'internal.example',
        'secure.corp',
        'sharepoint.local',
        'sftp.partner.corp',
    ];

    /**
     * High-risk external personal email and dump destinations.
     */
    protected array $unapprovedDestinationPatterns = [
        'gmail.com',
        'yahoo.com',
        'hotmail.com',
        'outlook.com',
        'protonmail.com',
        'yopmail.com',
        'tempmail.com',
        'mega.nz',
        'wetransfer.com',
        'dropbox.com',
        'anonfiles.com',
        'pastebin.com',
    ];

    /**
     * Dangerous executable and script file extensions.
     */
    protected array $dangerousExtensions = [
        'exe', 'bat', 'vbs', 'ps1', 'sh', 'cmd', 'scr', 'msi', 'dll', 'jar', 'vbe', 'reg', 'pif'
    ];

    /**
     * Scan and evaluate transfer request.
     */
    public function scan(
        User $user,
        string $fileName,
        ?string $fileExtension,
        int $fileSizeBytes,
        string $destination,
        string $purpose,
        ?string $contentSample = null,
        ?UploadedFile $uploadedFile = null
    ): array {
        $detections = [];
        $riskScore = 0;
        $fileExtension = strtolower($fileExtension ?? pathinfo($fileName, PATHINFO_EXTENSION));

        // 1. Extract content to scan
        $rawText = $this->extractInspectableText($uploadedFile, $contentSample);

        // 2. Evaluate Destination Type
        $destinationEvaluation = $this->evaluateDestination($destination);
        $destinationType = $destinationEvaluation['type'];

        // Only flag unapproved personal freemails or cloud dump services
        if ($destinationEvaluation['is_unapproved']) {
            $riskScore += 20;
            $detections[] = [
                'category_id' => null,
                'detection_technique' => 'unauthorized_destination',
                'rule_name' => 'Unauthorized External Destination Detected',
                'matched_pattern' => $destinationEvaluation['matched_domain'],
                'matched_sample' => $destination,
                'severity' => 'medium',
                'details' => "Destination '{$destination}' is an unapproved consumer webmail or public cloud dump service.",
            ];
        }

        // 3. Technique 4: Suspicious File Detection
        $suspiciousFileChecks = $this->checkSuspiciousFile($fileName, $fileExtension, $uploadedFile);
        foreach ($suspiciousFileChecks as $check) {
            $riskScore += $check['risk_points'];
            $detections[] = [
                'category_id' => null,
                'detection_technique' => 'suspicious_file',
                'rule_name' => $check['rule_name'],
                'matched_pattern' => $check['pattern'],
                'matched_sample' => $fileName,
                'severity' => $check['severity'],
                'details' => $check['details'],
            ];
        }

        // 4. Technique 1: Sensitive Data Detection (Regex + Keywords)
        $sensitiveDetections = $this->inspectSensitiveContent($rawText);
        foreach ($sensitiveDetections as $sd) {
            $riskScore += $sd['risk_points'];
            $detections[] = [
                'category_id' => $sd['category_id'],
                'detection_technique' => 'sensitive_data',
                'rule_name' => $sd['rule_name'],
                'matched_pattern' => $sd['pattern'],
                'matched_sample' => $sd['sample'],
                'severity' => $sd['severity'],
                'details' => $sd['details'],
            ];
        }

        // 5. Technique 5: Unusual Data Transfer Detection (Velocity & Size)
        $unusualChecks = $this->checkUnusualTransferPatterns($user, $fileSizeBytes, $fileName);
        foreach ($unusualChecks as $uc) {
            $riskScore += $uc['risk_points'];
            $detections[] = [
                'category_id' => null,
                'detection_technique' => 'unusual_transfer',
                'rule_name' => $uc['rule_name'],
                'matched_pattern' => $uc['pattern'],
                'matched_sample' => $uc['sample'],
                'severity' => $uc['severity'],
                'details' => $uc['details'],
            ];
        }

        // 6. Technique 3: Policy Violation Detection (Active Database Policies)
        $policyViolations = $this->checkSecurityPolicies(
            $user,
            $destinationType,
            $fileExtension,
            $fileSizeBytes,
            $detections,
            $rawText
        );
        foreach ($policyViolations as $pv) {
            $riskScore += $pv['risk_points'];
            $detections[] = [
                'category_id' => null,
                'detection_technique' => 'policy_violation',
                'rule_name' => $pv['rule_name'],
                'matched_pattern' => $pv['pattern'],
                'matched_sample' => $pv['sample'],
                'severity' => $pv['severity'],
                'details' => $pv['details'],
            ];
        }

        // Clamp risk score 0 - 100
        $riskScore = min(100, max(0, $riskScore));

        // Determine Risk Level
        $riskLevel = 'low';
        if ($riskScore >= 70) {
            $riskLevel = 'critical';
        } elseif ($riskScore >= 45) {
            $riskLevel = 'high';
        } elseif ($riskScore >= 20) {
            $riskLevel = 'medium';
        }

        // Check for specific hard-block conditions
        $hasCriticalDetections = collect($detections)->contains(fn($d) => $d['severity'] === 'critical');
        $hasExecutable = collect($detections)->contains(fn($d) => $d['detection_technique'] === 'suspicious_file' && in_array($d['severity'], ['critical', 'high']));
        $hasPolicyBlock = collect($policyViolations)->contains(fn($p) => isset($p['action']) && $p['action'] === 'block');

        // Determine Final Decision: ALLOWED (Clean/Safe), BLOCKED (Threat/Malicious), FLAGGED (Suspicious)
        $decision = 'allowed';
        $decisionReason = 'Clean / No threat detected. Transfer verified and cleared all DLP security checks.';

        if ($hasExecutable || $hasCriticalDetections || $hasPolicyBlock || $riskScore >= 60) {
            $decision = 'blocked';
            $criticalReasons = collect($detections)
                ->whereIn('severity', ['critical', 'high'])
                ->pluck('rule_name')
                ->unique()
                ->take(3)
                ->implode('; ');
            $decisionReason = "BLOCKED: Policy violation detected. Reasons: " . ($criticalReasons ?: 'Risk score exceeded safety threshold.');
        } elseif ($riskScore >= 20 && count($detections) > 0) {
            $decision = 'flagged';
            $reasons = collect($detections)->pluck('rule_name')->unique()->take(2)->implode('; ');
            $decisionReason = "FLAGGED: Suspicious – pending security analyst review. Triggers: " . ($reasons ?: 'Elevated risk parameters.');
        }

        return [
            'decision' => $decision,
            'risk_score' => $riskScore,
            'risk_level' => $riskLevel,
            'decision_reason' => $decisionReason,
            'destination_type' => $destinationType,
            'detections' => $detections,
            'extracted_sample' => Str::limit($rawText, 500),
            'scanned_at' => now(),
        ];
    }

    /**
     * Inspect text for sensitive data categories.
     */
    protected function inspectSensitiveContent(string $text): array
    {
        $detections = [];
        if (empty(trim($text))) {
            return $detections;
        }

        // Load active categories from DB
        $categories = SensitiveDataCategory::where('is_active', true)->get();

        // 1. Credit card numbers - Strict check: 13-19 digits, valid card prefix, and Luhn checksum
        if (preg_match_all('/\b(?:\d{4}[-\s]?){3}\d{4}\b|\b(?:\d[ -]*?){13,19}\b/', $text, $matches)) {
            $validCards = [];
            foreach ($matches[0] as $candidate) {
                $digits = preg_replace('/\D/', '', $candidate);
                if (strlen($digits) >= 13 && strlen($digits) <= 19) {
                    // Check standard payment network prefixes: Visa (4), Mastercard (51-55, 22-27), Amex (34, 37), Discover (6011, 65)
                    $hasCardPrefix = (bool) preg_match('/^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|2[2-7][0-9]{14}|3[47][0-9]{13}|6(?:011|5[0-9]{2})[0-9]{12})$/', $digits);
                    $hasCardContext = (bool) preg_match('/(?:credit[\s_-]*card|debit[\s_-]*card|\bccn\b|card[\s_-]*(?:number|no|#)?|\bcard\b|mastercard|visa|amex|payment)/i', $text);
                    if ($hasCardPrefix && ($this->passesLuhnCheck($digits) || $hasCardContext)) {
                        $validCards[] = $candidate;
                    }
                }
            }

            if (!empty($validCards)) {
                $category = $categories->firstWhere('code', 'FINANCIAL');
                $sampleDisplay = Str::mask($validCards[0], '*', 4, -4);
                $detections[] = [
                    'category_id' => $category?->id,
                    'rule_name' => 'Financial: Credit Card Numbers Detected',
                    'pattern' => 'CREDIT_CARD_PATTERN_MATCH',
                    'sample' => $sampleDisplay . (count($validCards) > 1 ? " (+ " . (count($validCards) - 1) . " more)" : ""),
                    'severity' => 'critical',
                    'risk_points' => 45,
                    'details' => "Detected " . count($validCards) . " valid credit card number(s) violating PCI-DSS data transfer rules (Luhn algorithm validated).",
                ];
            }
        }

        // 2. Government IDs: Philippine SSS, PhilHealth, TIN or US SSN
        $piiCandidates = [];
        if (preg_match_all('/\b\d{2}-\d{7}-\d{1}\b/', $text, $sssMatches)) {
            $piiCandidates = array_merge($piiCandidates, $sssMatches[0]);
        }
        if (preg_match_all('/\b(?!000|666|9\d{2})\d{3}-(?!00)\d{2}-(?!0000)\d{4}\b/', $text, $ssnMatches)) {
            $piiCandidates = array_merge($piiCandidates, $ssnMatches[0]);
        }
        // Philippine TIN: 9 digits in 3-3-3 format with contextual keywords to avoid false positives on random reference IDs
        if (preg_match_all('/\b\d{3}-\d{3}-\d{3}\b/', $text, $tinMatches)) {
            $hasPiiContext = preg_match('/(?i)\b(tin|tax|bir|payroll|salary|employee|worker|staff|sss|card|creditcard|juan|maria|id)\b/', $text);
            if ($hasPiiContext) {
                $piiCandidates = array_merge($piiCandidates, $tinMatches[0]);
            }
        }

        if (!empty($piiCandidates)) {
            $category = $categories->firstWhere('code', 'PII');
            $detections[] = [
                'category_id' => $category?->id,
                'rule_name' => 'PII: National ID / Social Security / Tax Identifier Detected',
                'pattern' => 'GOV_ID_SSN_PATTERN',
                'sample' => Str::mask($piiCandidates[0], '*', 2, 4),
                'severity' => 'high',
                'risk_points' => 30,
                'details' => 'Detected government identifier / national security number patterns in payload.',
            ];
        }

        // 3. Credentials / Secrets / Private Keys / API Keys
        if (preg_match('/-----BEGIN (?:RSA )?PRIVATE KEY-----/i', $text, $match)) {
            $category = $categories->firstWhere('code', 'CREDENTIALS');
            $detections[] = [
                'category_id' => $category?->id,
                'rule_name' => 'Credentials: Private Cryptographic Key Detected',
                'pattern' => 'RSA_PRIVATE_KEY_HEADER',
                'sample' => '-----BEGIN RSA PRIVATE KEY----- [REDACTED]',
                'severity' => 'critical',
                'risk_points' => 50,
                'details' => 'Transfer contains private cryptographic key material.',
            ];
        }

        if (preg_match('/(?i)(?:api[_-]?key|secret[_-]?key|access[_-]?token|bearer[_-]?token)\s*[:=]\s*["\']?([a-zA-Z0-9_\-\.]{16,})["\']?/', $text, $match)) {
            $category = $categories->firstWhere('code', 'CREDENTIALS');
            $detections[] = [
                'category_id' => $category?->id,
                'rule_name' => 'Credentials: API Key or Access Secret Detected',
                'pattern' => 'API_SECRET_KEY_PATTERN',
                'sample' => Str::mask($match[0], '*', 8, 4),
                'severity' => 'critical',
                'risk_points' => 45,
                'details' => 'Detected embedded production credentials or authorization secrets.',
            ];
        }

        if (preg_match('/(?i)\b(?:password|passwd|db_pass)\s*[:=]\s*["\']?([^\s"\']{4,})["\']?/', $text, $match)) {
            $category = $categories->firstWhere('code', 'CREDENTIALS');
            $detections[] = [
                'category_id' => $category?->id,
                'rule_name' => 'Credentials: Plaintext Password Detected',
                'pattern' => 'PLAINTEXT_PASSWORD_PATTERN',
                'sample' => Str::mask($match[0], '*', 6, 2),
                'severity' => 'critical',
                'risk_points' => 40,
                'details' => 'Found plaintext password string assigned in configuration or text payload.',
            ];
        }

        // 4. Keyword checking based on database categories with word boundaries
        foreach ($categories as $cat) {
            $patterns = $cat->patterns;
            if (is_array($patterns) && !empty($patterns)) {
                $foundKeywords = [];
                foreach ($patterns as $item) {
                    $itemStr = trim((string)$item);
                    if ($itemStr === '') continue;
                    // Use word boundaries so sub-words don't match erroneously
                    if (preg_match('/\b' . preg_quote($itemStr, '/') . '\b/i', $text)) {
                        $foundKeywords[] = $itemStr;
                    }
                }

                if (!empty($foundKeywords)) {
                    $isSingleGenericPii = ($cat->code === 'PII' && count($foundKeywords) === 1 && in_array(strtolower($foundKeywords[0]), ['date of birth', 'home address']));

                    $sev = match(true) {
                        $isSingleGenericPii => 'low',
                        in_array($cat->code, ['FINANCIAL', 'CREDENTIALS']) => 'high',
                        in_array($cat->code, ['CONFIDENTIAL', 'PII']) => 'medium',
                        default => 'low',
                    };

                    // For multiple confidential keywords, assign medium risk (25 points)
                    $points = match(true) {
                        $isSingleGenericPii => 5,
                        $cat->code === 'CONFIDENTIAL' && count($foundKeywords) >= 2 => 25,
                        $sev === 'high' => 25,
                        $sev === 'medium' => 15,
                        default => 10,
                    };

                    $detections[] = [
                        'category_id' => $cat->id,
                        'rule_name' => "Sensitive Data: {$cat->name} Keywords Detected",
                        'pattern' => 'KEYWORD_MATCH_' . $cat->code,
                        'sample' => 'Keywords: ' . implode(', ', array_slice($foundKeywords, 0, 4)),
                        'severity' => $sev,
                        'risk_points' => $points,
                        'details' => "Matched " . count($foundKeywords) . " classified keywords for category '{$cat->name}'.",
                    ];
                }
            }
        }

        return $detections;
    }

    /**
     * Evaluate destination type.
     */
    protected function evaluateDestination(string $destination): array
    {
        $destLower = strtolower(trim($destination));

        // 1. Approved Internal Corporate Domains
        foreach ($this->approvedInternalDomains as $domain) {
            if (str_contains($destLower, $domain)) {
                return [
                    'type' => 'internal',
                    'is_unapproved' => false,
                    'matched_domain' => $domain,
                ];
            }
        }

        // 2. High-Risk Unapproved Consumer Webmails & Dump Sites
        foreach ($this->unapprovedDestinationPatterns as $unapproved) {
            if (str_contains($destLower, $unapproved)) {
                return [
                    'type' => 'external_unapproved',
                    'is_unapproved' => true,
                    'matched_domain' => $unapproved,
                ];
            }
        }

        // 3. Standard External Business Destination (e.g. legitimate partner, client, vendor)
        return [
            'type' => 'external',
            'is_unapproved' => false,
            'matched_domain' => 'External Business Endpoint',
        ];
    }

    /**
     * Technique 4: Check for suspicious files, executables, or disguised extensions.
     */
    protected function checkSuspiciousFile(string $fileName, string $extension, ?UploadedFile $file = null): array
    {
        $checks = [];

        if (in_array(strtolower($extension), $this->dangerousExtensions)) {
            $checks[] = [
                'rule_name' => 'Suspicious File: Restricted Executable/Script Extension',
                'pattern' => 'DANGEROUS_EXT_.' . strtoupper($extension),
                'severity' => 'critical',
                'risk_points' => 50,
                'details' => "File extension '.{$extension}' is classified as potentially dangerous executable/script code.",
            ];
        }

        $parts = explode('.', $fileName);
        if (count($parts) > 2) {
            $secondToLast = strtolower($parts[count($parts) - 2]);
            $last = strtolower($parts[count($parts) - 1]);
            if (in_array($secondToLast, ['pdf', 'doc', 'docx', 'xlsx', 'xls', 'jpg', 'png', 'txt']) && in_array($last, $this->dangerousExtensions)) {
                $checks[] = [
                    'rule_name' => 'Suspicious File: Disguised Double Extension Detected',
                    'pattern' => 'DOUBLE_EXTENSION_EVASION',
                    'severity' => 'critical',
                    'risk_points' => 55,
                    'details' => "File name '{$fileName}' uses double extension spoofing technique commonly used in malware distribution.",
                ];
            }
        }

        return $checks;
    }

    /**
     * Technique 5: Check unusual transfer patterns (volume, velocity, time).
     */
    protected function checkUnusualTransferPatterns(User $user, int $fileSizeBytes, string $fileName): array
    {
        $checks = [];

        $fifteenMB = 15 * 1024 * 1024;
        if ($fileSizeBytes > $fifteenMB) {
            $mb = round($fileSizeBytes / (1024 * 1024), 2);
            $checks[] = [
                'rule_name' => 'Unusual Data Transfer: High-Volume Payload',
                'pattern' => 'EXCESSIVE_TRANSFER_VOLUME',
                'sample' => "{$mb} MB",
                'severity' => 'medium',
                'risk_points' => 20,
                'details' => "File transfer volume ({$mb} MB) exceeds normal baseline threshold of 15 MB.",
            ];
        }

        $recentTransfersCount = Transfer::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count();

        if ($recentTransfersCount >= 4) {
            $checks[] = [
                'rule_name' => 'Unusual Data Transfer: Abnormal Velocity / Rate Spike',
                'pattern' => 'HIGH_TRANSFER_FREQUENCY',
                'sample' => "{$recentTransfersCount} transfers in 5 mins",
                'severity' => 'high',
                'risk_points' => 30,
                'details' => "User has initiated {$recentTransfersCount} data transfers within 5 minutes.",
            ];
        }

        return $checks;
    }

    /**
     * Technique 3: Check active security policies.
     */
    protected function checkSecurityPolicies(
        User $user,
        string $destinationType,
        string $fileExtension,
        int $fileSizeBytes,
        array $currentDetections,
        string $text
    ): array {
        $violations = [];
        $policies = SecurityPolicy::where('is_active', true)->get();

        foreach ($policies as $policy) {
            $config = $policy->rule_config ?? [];

            // Critical financial policy
            if ($policy->policy_type === 'sensitive_data_rule' && ($config['target_category'] ?? '') === 'FINANCIAL') {
                $hasFinancial = collect($currentDetections)->contains(fn($d) => str_contains($d['rule_name'], 'Financial') || str_contains($d['rule_name'], 'Credit Card'));
                if ($hasFinancial && $destinationType !== 'internal') {
                    $violations[] = [
                        'rule_name' => "Policy Violation: {$policy->name}",
                        'pattern' => $policy->policy_code,
                        'sample' => 'Financial Egress Violation',
                        'severity' => 'critical',
                        'risk_points' => 35,
                        'action' => 'block',
                        'details' => "Violated rule [{$policy->policy_code}]: {$policy->description}",
                    ];
                }
            }

            // Executable script restriction policy
            if ($policy->policy_type === 'file_type_restriction') {
                $restricted = $config['restricted_extensions'] ?? ['exe', 'bat', 'vbs', 'ps1', 'sh', 'dll', 'msi'];
                if (in_array(strtolower($fileExtension), array_map('strtolower', $restricted))) {
                    $violations[] = [
                        'rule_name' => "Policy Violation: {$policy->name}",
                        'pattern' => $policy->policy_code,
                        'sample' => ".{$fileExtension}",
                        'severity' => 'critical',
                        'risk_points' => 35,
                        'action' => 'block',
                        'details' => "Violated rule [{$policy->policy_code}]: Extension '.{$fileExtension}' is restricted.",
                    ];
                }
            }
        }

        return $violations;
    }

    /**
     * Extract clean human-readable text from uploaded files without injecting binary noise.
     */
    protected function extractInspectableText(?UploadedFile $file, ?string $sampleText): string
    {
        $content = $sampleText ?? '';

        if ($file && $file->isValid()) {
            $ext = strtolower($file->getClientOriginalExtension());
            $path = $file->getRealPath();

            if (in_array($ext, ['txt', 'csv', 'tsv', 'json', 'log', 'sql', 'xml', 'html', 'md', 'js', 'php', 'py', 'env', 'conf'])) {
                $fileContent = @file_get_contents($path);
                if ($fileContent) {
                    $content .= "\n" . substr($fileContent, 0, 50000);
                }
            } elseif ($ext === 'docx' && class_exists('ZipArchive')) {
                $zip = new \ZipArchive();
                if ($zip->open($path) === true) {
                    if (($index = $zip->locateName('word/document.xml')) !== false) {
                        $xml = $zip->getFromIndex($index);
                        if ($xml) {
                            $cleanDocxText = strip_tags(str_replace(['<w:p>', '<w:br/>', '<w:tab/>'], ["\n", "\n", "\t"], $xml));
                            $content .= "\n" . substr($cleanDocxText, 0, 50000);
                        }
                    }
                    $zip->close();
                }
            } elseif ($ext === 'pdf') {
                $raw = @file_get_contents($path);
                if ($raw) {
                    if (preg_match_all('/BT[\s\S]*?ET/', $raw, $blocks)) {
                        foreach ($blocks[0] as $block) {
                            if (preg_match_all('/\(([^\)]*)\)/', $block, $strings)) {
                                $content .= ' ' . implode(' ', $strings[1]);
                            }
                        }
                    }
                }
            }
            // Images, media, and raw binary archives do not inject byte noise into text regex scanner
        }

        return $content;
    }

    /**
     * Validates card number using mathematical Luhn algorithm (mod 10).
     */
    protected function passesLuhnCheck(string $number): bool
    {
        $sum = 0;
        $numDigits = strlen($number);
        $parity = $numDigits % 2;

        for ($i = 0; $i < $numDigits; $i++) {
            $digit = (int)$number[$i];
            if ($i % 2 == $parity) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
        }

        return ($sum % 10 === 0);
    }
}
