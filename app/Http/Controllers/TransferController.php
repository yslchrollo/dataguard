<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Alert;
use App\Models\Transfer;
use App\Models\TransferDetection;
use App\Services\DlpScanEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TransferController extends Controller
{
    protected DlpScanEngine $scanEngine;

    public function __construct(DlpScanEngine $scanEngine)
    {
        $this->scanEngine = $scanEngine;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Transfer::with(['user', 'detections', 'alerts'])->latest();

        // If regular user, only view own transfers
        if ($user->isUser()) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('decision')) {
            $query->where('decision', $request->input('decision'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('file_name', 'like', "%{$search}%")
                  ->orWhere('destination', 'like', "%{$search}%")
                  ->orWhere('transfer_uuid', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%");
            });
        }

        $transfers = $query->paginate(12)->withQueryString();

        return view('transfers.index', compact('transfers'));
    }

    public function create()
    {
        return view('transfers.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'file' => ['nullable', 'file', 'max:51200'], // up to 50MB
            'file_name_override' => ['nullable', 'string', 'max:255'],
            'content_payload' => ['nullable', 'string'],
            'destination' => ['required', 'string', 'max:255'],
            'purpose' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $uploadedFile = $request->file('file');
        $contentPayload = $request->input('content_payload');

        if (!$uploadedFile && empty(trim((string)$contentPayload))) {
            return back()->withErrors(['file' => 'Please either upload a file or enter sample transfer data.'])->withInput();
        }

        // Determine file properties
        if ($uploadedFile) {
            if (!$uploadedFile->isValid()) {
                return back()->withErrors(['file' => 'The uploaded file is invalid or corrupted. Please verify the file and re-upload.'])->withInput();
            }

            if (!is_readable($uploadedFile->getRealPath())) {
                return back()->withErrors(['file' => 'The uploaded file cannot be read by the server. Please verify file permissions or re-upload.'])->withInput();
            }

            $fileSizeBytes = $uploadedFile->getSize();
            if ($fileSizeBytes === 0 && empty(trim((string)$contentPayload))) {
                return back()->withErrors(['file' => 'The uploaded file is empty (0 bytes). Please upload a valid document with content.'])->withInput();
            }

            $originalFileName = $uploadedFile->getClientOriginalName();
            $fileName = $request->filled('file_name_override') ? $request->input('file_name_override') : $originalFileName;
            $fileExtension = strtolower($uploadedFile->getClientOriginalExtension());
            $mimeType = $uploadedFile->getMimeType();
            
            // Store file securely in local storage
            $storedPath = $uploadedFile->store('dlp_transfers', 'local');
        } else {
            $fileName = $request->input('file_name_override') ?: 'raw_transfer_payload.txt';
            $originalFileName = $fileName;
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION) ?: 'txt');
            $fileSizeBytes = strlen((string)$contentPayload);
            $mimeType = 'text/plain';
            $storedPath = null;
        }

        // Determine general file type category
        $fileType = match(true) {
            in_array($fileExtension, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']) => 'document',
            in_array($fileExtension, ['txt', 'csv', 'tsv', 'log', 'md']) => 'text',
            in_array($fileExtension, ['json', 'sql', 'xml', 'html', 'js', 'php', 'py', 'java', 'cpp']) => 'source_code',
            in_array($fileExtension, ['zip', 'rar', '7z', 'tar', 'gz']) => 'archive',
            in_array($fileExtension, ['exe', 'bat', 'vbs', 'ps1', 'sh', 'msi', 'dll']) => 'executable',
            in_array($fileExtension, ['png', 'jpg', 'jpeg', 'gif', 'svg']) => 'image',
            default => 'other',
        };

        $destination = trim($request->input('destination'));
        $purpose = trim($request->input('purpose'));
        $description = $request->input('description');

        // Execute DLP Scanning Engine
        $scanResult = $this->scanEngine->scan(
            $user,
            $fileName,
            $fileExtension,
            $fileSizeBytes,
            $destination,
            $purpose,
            $contentPayload,
            $uploadedFile
        );

        $transferUuid = 'TRF-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));

        // Save Transfer Record
        $transfer = Transfer::create([
            'transfer_uuid' => $transferUuid,
            'user_id' => $user->id,
            'file_name' => $fileName,
            'original_file_name' => $originalFileName,
            'file_path' => $storedPath,
            'file_type' => $fileType,
            'file_extension' => $fileExtension,
            'file_size_bytes' => $fileSizeBytes,
            'destination' => $destination,
            'destination_type' => $scanResult['destination_type'],
            'purpose' => $purpose,
            'description' => $description,
            'extracted_text_sample' => $scanResult['extracted_sample'],
            'decision' => $scanResult['decision'],
            'risk_score' => $scanResult['risk_score'],
            'risk_level' => $scanResult['risk_level'],
            'decision_reason' => $scanResult['decision_reason'],
            'scanned_at' => $scanResult['scanned_at'],
        ]);

        // Save Detection Details
        foreach ($scanResult['detections'] as $det) {
            TransferDetection::create([
                'transfer_id' => $transfer->id,
                'category_id' => $det['category_id'] ?? null,
                'detection_technique' => $det['detection_technique'],
                'rule_name' => $det['rule_name'],
                'matched_pattern' => $det['matched_pattern'] ?? null,
                'matched_sample' => $det['matched_sample'] ?? null,
                'severity' => $det['severity'] ?? 'medium',
                'details' => $det['details'] ?? null,
            ]);
        }

        // Trigger Alert if Blocked or Flagged
        if ($scanResult['decision'] === 'blocked' || $scanResult['decision'] === 'flagged') {
            $alertUuid = 'ALT-' . now()->format('Y') . '-' . strtoupper(Str::random(6));
            $alertTitle = match($scanResult['decision']) {
                'blocked' => "Data Egress Blocked: Policy Violation on {$fileName}",
                'flagged' => "Suspicious Data Transfer Flagged: {$fileName}",
                default => "DLP Alert on {$fileName}",
            };

            Alert::create([
                'alert_uuid' => $alertUuid,
                'transfer_id' => $transfer->id,
                'user_id' => $user->id,
                'alert_type' => $scanResult['decision'] === 'blocked' ? 'Policy Violation & Data Exfiltration Attempt' : 'Flagged Suspicious Activity',
                'severity' => $scanResult['risk_level'],
                'title' => $alertTitle,
                'description' => $scanResult['decision_reason'],
                'status' => 'open',
            ]);
        }

        // Record Activity Log
        ActivityLog::record(
            'transfer_' . $scanResult['decision'],
            'Transfer',
            "User {$user->name} submitted transfer '{$fileName}' to '{$destination}'. Decision: " . strtoupper($scanResult['decision']) . " (Risk Score: {$scanResult['risk_score']})",
            [
                'transfer_uuid' => $transfer->transfer_uuid,
                'file_name' => $fileName,
                'decision' => $scanResult['decision'],
                'risk_score' => $scanResult['risk_score'],
                'detections_count' => count($scanResult['detections']),
            ],
            $user
        );

        $flashType = match($scanResult['decision']) {
            'allowed' => 'success',
            'flagged' => 'warning',
            'blocked' => 'error',
        };

        $flashMessage = match($scanResult['decision']) {
            'allowed' => 'Transfer verified and ALLOWED. Security scan passed: Clean / No threat detected.',
            'flagged' => 'Transfer has been FLAGGED: Suspicious – pending security analyst review.',
            'blocked' => 'Transfer has been BLOCKED: Malicious / Threat detected – policy violation.',
        };

        return redirect()->route('transfers.show', $transfer->transfer_uuid)->with($flashType, $flashMessage);
    }

    public function show(string $uuid)
    {
        $user = Auth::user();
        $transfer = Transfer::with(['user', 'detections.category', 'alerts'])
            ->where('transfer_uuid', $uuid)
            ->firstOrFail();

        // Enforce user permission
        if ($user->isUser() && $transfer->user_id !== $user->id) {
            abort(403, 'Unauthorized access to transfer record.');
        }

        return view('transfers.show', compact('transfer'));
    }
}
