<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Alert;
use App\Models\SecurityPolicy;
use App\Models\SensitiveDataCategory;
use App\Models\Transfer;
use App\Models\TransferDetection;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Core Users
        $admin = User::create([
            'name' => 'Red Xavier Rodrigo',
            'email' => 'admin@dataguard.corp',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'department' => 'Cybersecurity & IT Admin',
            'phone' => '+63 917 555 0101',
            'status' => 'active',
            'last_login_at' => now(),
        ]);

        $analyst = User::create([
            'name' => 'Sarah Connor (Security Analyst)',
            'email' => 'analyst@dataguard.corp',
            'password' => Hash::make('password'),
            'role' => 'analyst',
            'department' => 'Security Operations Center (SOC)',
            'phone' => '+63 918 555 0102',
            'status' => 'active',
            'last_login_at' => now()->subHours(2),
        ]);

        $user = User::create([
            'name' => 'Juan Dela Cruz (Employee)',
            'email' => 'user@dataguard.corp',
            'password' => Hash::make('password'),
            'role' => 'user',
            'department' => 'Human Resources & Finance',
            'phone' => '+63 919 555 0103',
            'status' => 'active',
            'last_login_at' => now()->subHours(5),
        ]);

        $devUser = User::create([
            'name' => 'Alex Rivera (Software Engineer)',
            'email' => 'alex.rivera@dataguard.corp',
            'password' => Hash::make('password'),
            'role' => 'user',
            'department' => 'Product Engineering',
            'phone' => '+63 920 555 0104',
            'status' => 'active',
            'last_login_at' => now()->subDays(1),
        ]);

        $inactiveUser = User::create([
            'name' => 'Marcus Vance (Contractor - Inactive)',
            'email' => 'marcus.vance@external.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'department' => 'External Vendor',
            'phone' => '+63 921 555 0105',
            'status' => 'inactive',
            'last_login_at' => now()->subDays(14),
        ]);

        // 2. Sensitive Data Categories
        $catPii = SensitiveDataCategory::create([
            'name' => 'Personally Identifiable Information (PII)',
            'code' => 'PII',
            'description' => 'Personal employee and customer records such as government IDs, Social Security numbers, phone lists, and employee home addresses.',
            'risk_level' => 'high',
            'patterns' => ['ssn', 'social security', 'national id', 'passport number', 'date of birth', 'home address', 'mothers maiden name', 'philhealth', 'tin number'],
            'is_active' => true,
        ]);

        $catFin = SensitiveDataCategory::create([
            'name' => 'Financial Information',
            'code' => 'FINANCIAL',
            'description' => 'Cardholder data, bank account numbers, payroll balances, corporate revenues, and accounting audit sheets.',
            'risk_level' => 'critical',
            'patterns' => ['credit card', 'cvv', 'cardholder', 'bank account', 'iban', 'swift code', 'salary', 'payroll', 'net pay', 'gross compensation', 'invoice total', 'billing statement'],
            'is_active' => true,
        ]);

        $catCred = SensitiveDataCategory::create([
            'name' => 'Authentication Credentials & Secrets',
            'code' => 'CREDENTIALS',
            'description' => 'Access secrets including API keys, passwords, private SSH/RSA keys, database connection strings, and OAuth bearer tokens.',
            'risk_level' => 'critical',
            'patterns' => ['password', 'passwd', 'api_key', 'secret_key', 'private key', 'bearer token', 'jwt_secret', 'database_password', 'db_pass'],
            'is_active' => true,
        ]);

        $catConf = SensitiveDataCategory::create([
            'name' => 'Confidential Organizational Data',
            'code' => 'CONFIDENTIAL',
            'description' => 'Proprietary corporate records, strategic mergers, board meeting minutes, legal contracts, and intellectual property.',
            'risk_level' => 'high',
            'patterns' => ['confidential', 'proprietary', 'internal use only', 'trade secret', 'non-disclosure', 'board resolution', 'merger agreement', 'strictly restricted'],
            'is_active' => true,
        ]);

        $catSrc = SensitiveDataCategory::create([
            'name' => 'Proprietary Source Code',
            'code' => 'SOURCE_CODE',
            'description' => 'Internal codebase files, backend algorithms, architecture blueprints, and development configurations.',
            'risk_level' => 'medium',
            'patterns' => ['git remote', 'aws_access_key', 'env file', 'deploy_script', 'internal api endpoint', 'docker-compose'],
            'is_active' => true,
        ]);

        // 3. Security Policies
        $pol1 = SecurityPolicy::create([
            'policy_code' => 'POL-DLP-001',
            'name' => 'Block External Egress of Financial Records',
            'description' => 'Strictly blocks outbound transfers of payroll, credit card numbers, and banking data directed to unapproved external addresses.',
            'policy_type' => 'sensitive_data_rule',
            'action_on_violation' => 'block',
            'severity' => 'critical',
            'rule_config' => ['target_category' => 'FINANCIAL', 'enforce_destinations' => true],
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $pol2 = SecurityPolicy::create([
            'policy_code' => 'POL-DLP-002',
            'name' => 'Enforce Destination Whitelist Protocol',
            'description' => 'Monitors transfers to unauthorized consumer webmails (e.g. Gmail, Yahoo, ProtonMail) and cloud file dump servers.',
            'policy_type' => 'destination_check',
            'action_on_violation' => 'flag',
            'severity' => 'high',
            'rule_config' => ['approved_domains' => ['company.com', 'dataguard.corp', 'internal.example']],
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $pol3 = SecurityPolicy::create([
            'policy_code' => 'POL-DLP-003',
            'name' => 'Executable Binary & Script Egress Restriction',
            'description' => 'Prohibits transfer of executable binaries, shell scripts, and batch scripts (.exe, .bat, .vbs, .ps1, .sh, .dll) across all channels.',
            'policy_type' => 'file_type_restriction',
            'action_on_violation' => 'block',
            'severity' => 'critical',
            'rule_config' => ['restricted_extensions' => ['exe', 'bat', 'vbs', 'ps1', 'sh', 'dll', 'msi']],
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $pol4 = SecurityPolicy::create([
            'policy_code' => 'POL-DLP-004',
            'name' => 'Maximum File Transfer Volume Limit',
            'description' => 'Restricts transfer requests with file attachments larger than 25MB to prevent large scale bulk data exfiltration.',
            'policy_type' => 'file_size_limit',
            'action_on_violation' => 'flag',
            'severity' => 'medium',
            'rule_config' => ['max_size_mb' => 25],
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $pol5 = SecurityPolicy::create([
            'policy_code' => 'POL-DLP-005',
            'name' => 'PII Egress Prevention on Unsanctioned Channels',
            'description' => 'Prevents bulk exfiltration of customer names, email directories, and government ID patterns to third parties.',
            'policy_type' => 'sensitive_data_rule',
            'action_on_violation' => 'block',
            'severity' => 'high',
            'rule_config' => ['target_category' => 'PII'],
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        // 4. Seed Realistic Sample Transfers
        // Transfer 1: BLOCKED (Payroll and Credit Cards to Gmail)
        $trf1 = Transfer::create([
            'transfer_uuid' => 'TRF-20260828-BL01',
            'user_id' => $user->id,
            'file_name' => 'q3_employee_payroll_and_cards.csv',
            'original_file_name' => 'q3_employee_payroll_and_cards.csv',
            'file_type' => 'text',
            'file_extension' => 'csv',
            'file_size_bytes' => 148500,
            'destination' => 'personal.backup@gmail.com',
            'destination_type' => 'external_unapproved',
            'purpose' => 'Personal work backup from home office',
            'description' => 'Exported spreadsheet containing HR payroll figures and company card records.',
            'extracted_text_sample' => "EmployeeID,Name,Salary,CreditCard,TIN\n101,John Doe,85000,4532-1188-9922-3344,123-456-789\n102,Jane Smith,92000,5424-0011-2233-4455,987-654-321",
            'decision' => 'blocked',
            'risk_score' => 95,
            'risk_level' => 'critical',
            'decision_reason' => 'BLOCKED: Policy violation detected. Reasons: Financial: Credit Card Numbers Detected; Unauthorized External Destination Detected; Policy Violation: Block External Egress of Financial Records',
            'scanned_at' => now()->subHours(4),
            'created_at' => now()->subHours(4),
        ]);

        TransferDetection::create([
            'transfer_id' => $trf1->id,
            'category_id' => $catFin->id,
            'detection_technique' => 'sensitive_data',
            'rule_name' => 'Financial: Credit Card Numbers Detected',
            'matched_pattern' => 'LUHN_CREDIT_CARD_REGEX',
            'matched_sample' => '4532 **** **** 3344 (+ 1 more)',
            'severity' => 'critical',
            'details' => 'Detected 2 valid credit card number format(s) violating PCI-DSS data transfer rules.',
        ]);

        TransferDetection::create([
            'transfer_id' => $trf1->id,
            'category_id' => null,
            'detection_technique' => 'unauthorized_destination',
            'rule_name' => 'Unauthorized External Destination Detected',
            'matched_pattern' => 'gmail.com',
            'matched_sample' => 'personal.backup@gmail.com',
            'severity' => 'high',
            'details' => 'Destination matches known personal freemail provider gmail.com.',
        ]);

        TransferDetection::create([
            'transfer_id' => $trf1->id,
            'category_id' => null,
            'detection_technique' => 'policy_violation',
            'rule_name' => 'Policy Violation: Block External Egress of Financial Records',
            'matched_pattern' => 'POL-DLP-001',
            'matched_sample' => 'Sensitive Category Egress Violation',
            'severity' => 'critical',
            'details' => 'Violated rule [POL-DLP-001]: Strictly blocks outbound transfers of payroll, credit card numbers, and banking data.',
        ]);

        $alt1 = Alert::create([
            'alert_uuid' => 'ALT-2026-0081',
            'transfer_id' => $trf1->id,
            'user_id' => $user->id,
            'alert_type' => 'Sensitive Data Exfiltration Attempt',
            'severity' => 'critical',
            'title' => 'Data Egress Blocked: Policy Violation on q3_employee_payroll_and_cards.csv',
            'description' => $trf1->decision_reason,
            'status' => 'open',
            'created_at' => now()->subHours(4),
        ]);

        // Transfer 2: ALLOWED (Engineering internal document)
        $trf2 = Transfer::create([
            'transfer_uuid' => 'TRF-20260828-AL02',
            'user_id' => $devUser->id,
            'file_name' => 'architecture_specifications_v2.pdf',
            'original_file_name' => 'architecture_specifications_v2.pdf',
            'file_type' => 'document',
            'file_extension' => 'pdf',
            'file_size_bytes' => 2450000,
            'destination' => 'lead.dev@company.com',
            'destination_type' => 'internal',
            'purpose' => 'Sprint planning architecture review',
            'description' => 'System architectural diagram and tech stack description.',
            'extracted_text_sample' => "DataGuard DLP Architecture specifications. Web tier Laravel PHP, database MySQL. Authentication session RBAC.",
            'decision' => 'allowed',
            'risk_score' => 0,
            'risk_level' => 'low',
            'decision_reason' => 'Transfer request cleared all DLP inspection policies and destination validation rules.',
            'scanned_at' => now()->subHours(3),
            'created_at' => now()->subHours(3),
        ]);

        // Transfer 3: FLAGGED (Confidential keywords to external partner)
        $trf3 = Transfer::create([
            'transfer_uuid' => 'TRF-20260828-FL03',
            'user_id' => $user->id,
            'file_name' => 'vendor_partnership_brief.docx',
            'original_file_name' => 'vendor_partnership_brief.docx',
            'file_type' => 'document',
            'file_extension' => 'docx',
            'file_size_bytes' => 840000,
            'destination' => 'auditor@external-consulting.net',
            'destination_type' => 'external_unapproved',
            'purpose' => 'Third-party compliance questionnaire',
            'description' => 'Brief overview of security controls for vendor assessment.',
            'extracted_text_sample' => "This document contains INTERNAL USE ONLY materials regarding company confidential protocols.",
            'decision' => 'flagged',
            'risk_score' => 45,
            'risk_level' => 'medium',
            'decision_reason' => 'FLAGGED: Potential policy or anomaly warning. Investigation recommended. Triggers: Sensitive Data: Confidential Organizational Data Keywords Detected; Unauthorized External Destination Detected',
            'scanned_at' => now()->subHours(2),
            'created_at' => now()->subHours(2),
        ]);

        TransferDetection::create([
            'transfer_id' => $trf3->id,
            'category_id' => $catConf->id,
            'detection_technique' => 'sensitive_data',
            'rule_name' => 'Sensitive Data: Confidential Organizational Data Keywords Detected',
            'matched_pattern' => 'KEYWORD_MATCH_CONFIDENTIAL',
            'matched_sample' => 'Keywords: confidential, internal use only',
            'severity' => 'high',
            'details' => "Matched 2 classified keywords for category 'Confidential Organizational Data'.",
        ]);

        TransferDetection::create([
            'transfer_id' => $trf3->id,
            'category_id' => null,
            'detection_technique' => 'unauthorized_destination',
            'rule_name' => 'Unauthorized External Destination Detected',
            'matched_pattern' => 'external-consulting.net',
            'matched_sample' => 'auditor@external-consulting.net',
            'severity' => 'medium',
            'details' => 'Destination is outside approved corporate domain whitelist.',
        ]);

        $alt2 = Alert::create([
            'alert_uuid' => 'ALT-2026-0082',
            'transfer_id' => $trf3->id,
            'user_id' => $user->id,
            'alert_type' => 'Flagged Suspicious Activity',
            'severity' => 'medium',
            'title' => 'Suspicious Data Transfer Flagged: vendor_partnership_brief.docx',
            'description' => $trf3->decision_reason,
            'status' => 'under_review',
            'assigned_to' => $analyst->id,
            'created_at' => now()->subHours(2),
        ]);

        // Transfer 4: BLOCKED (Suspicious executable file)
        $trf4 = Transfer::create([
            'transfer_uuid' => 'TRF-20260828-BL04',
            'user_id' => $devUser->id,
            'file_name' => 'server_update_patch.bat',
            'original_file_name' => 'server_update_patch.bat',
            'file_type' => 'executable',
            'file_extension' => 'bat',
            'file_size_bytes' => 12400,
            'destination' => 'admin@external-ftp.org',
            'destination_type' => 'external_unapproved',
            'purpose' => 'Automated deployment script patch',
            'description' => 'Windows batch script for database maintenance.',
            'extracted_text_sample' => "@echo off\nset db_pass=AdminSecret2026!\nmysqldump -u root -p%db_pass% > dump.sql",
            'decision' => 'blocked',
            'risk_score' => 90,
            'risk_level' => 'critical',
            'decision_reason' => 'BLOCKED: Policy violation detected. Reasons: Suspicious File: Restricted Executable/Script Extension; Credentials: Plaintext Password Detected; Policy Violation: Executable Binary & Script Egress Restriction',
            'scanned_at' => now()->subDays(1),
            'created_at' => now()->subDays(1),
        ]);

        TransferDetection::create([
            'transfer_id' => $trf4->id,
            'category_id' => null,
            'detection_technique' => 'suspicious_file',
            'rule_name' => 'Suspicious File: Restricted Executable/Script Extension',
            'matched_pattern' => 'DANGEROUS_EXT_.BAT',
            'matched_sample' => 'server_update_patch.bat',
            'severity' => 'critical',
            'details' => "File extension '.bat' is classified as potentially dangerous script code.",
        ]);

        TransferDetection::create([
            'transfer_id' => $trf4->id,
            'category_id' => $catCred->id,
            'detection_technique' => 'sensitive_data',
            'rule_name' => 'Credentials: Plaintext Password Detected',
            'matched_pattern' => 'PLAINTEXT_PASSWORD_PATTERN',
            'matched_sample' => 'db_pass=*** [REDACTED]',
            'severity' => 'high',
            'details' => 'Found plaintext credentials assigned in script payload.',
        ]);

        $alt3 = Alert::create([
            'alert_uuid' => 'ALT-2026-0079',
            'transfer_id' => $trf4->id,
            'user_id' => $devUser->id,
            'alert_type' => 'Restricted File Type Egress',
            'severity' => 'critical',
            'title' => 'Data Egress Blocked: Policy Violation on server_update_patch.bat',
            'description' => $trf4->decision_reason,
            'status' => 'resolved',
            'assigned_to' => $analyst->id,
            'investigated_by' => $analyst->id,
            'investigation_notes' => 'Investigated transfer payload. Confirmed script contained cleartext credentials and violated POL-DLP-003. Developer Alex Rivera was contacted and reminded to use secure Git deployment pipeline instead of manual outbound file transfers. Block upheld.',
            'action_taken' => 'Confirmed Policy Violation - Block Maintained & User Reminded',
            'resolved_at' => now()->subHours(12),
            'created_at' => now()->subDays(1),
        ]);

        // Transfer 5: ALLOWED (Internal HR memo)
        $trf5 = Transfer::create([
            'transfer_uuid' => 'TRF-20260828-AL05',
            'user_id' => $user->id,
            'file_name' => 'company_holiday_schedule_2026.pdf',
            'original_file_name' => 'company_holiday_schedule_2026.pdf',
            'file_type' => 'document',
            'file_extension' => 'pdf',
            'file_size_bytes' => 520000,
            'destination' => 'all-staff@company.com',
            'destination_type' => 'internal',
            'purpose' => 'Company-wide holiday announcement',
            'description' => 'General organizational memo on official holidays.',
            'extracted_text_sample' => "Official Holiday Schedule 2026 for all corporate branches. Regular working hours resume on Mondays.",
            'decision' => 'allowed',
            'risk_score' => 0,
            'risk_level' => 'low',
            'decision_reason' => 'Transfer request cleared all DLP inspection policies and destination validation rules.',
            'scanned_at' => now()->subDays(2),
            'created_at' => now()->subDays(2),
        ]);

        // Activity Logs
        ActivityLog::record('system_initialized', 'System', 'DataGuard DLP Engine initial database seed and policy calibration completed.', ['version' => '1.0.0'], $admin);
        ActivityLog::record('policy_created', 'Policy', 'Initial baseline security policies configured.', ['policies_count' => 5], $admin);
        ActivityLog::record('transfer_blocked', 'Transfer', "Transfer [{$trf1->transfer_uuid}] submitted by Juan Dela Cruz was BLOCKED due to financial data egress.", ['risk_score' => 95], $user);
        ActivityLog::record('alert_investigated', 'Alert', "Security Analyst Sarah Connor reviewed and resolved Alert [{$alt3->alert_uuid}].", ['status' => 'resolved'], $analyst);
    }
}
