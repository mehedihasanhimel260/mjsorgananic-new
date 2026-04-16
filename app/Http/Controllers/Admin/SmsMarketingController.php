<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendBulkSmsJob;
use App\Jobs\SendSingleSmsJob;
use App\Models\SmsLog;
use App\Models\SmsTemplate;
use App\Models\User;
use App\Services\SmsGatewayService;
use Illuminate\Http\Request;

class SmsMarketingController extends Controller
{
    public function __construct(private readonly SmsGatewayService $smsGatewayService)
    {
    }

    public function index()
    {
        $setting = $this->smsGatewayService->getSetting();
        $logs = SmsLog::with(['user', 'admin'])->latest()->take(50)->get();
        $templates = SmsTemplate::query()->latest()->get();
        $userCount = User::query()->whereNotNull('phone')->count();

        return view('admin.sms-settings.index', compact('setting', 'logs', 'templates', 'userCount'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'sender_id' => 'required|string|max:255',
            'api_key' => 'required|string|max:255',
            'transaction_type' => 'required|string|in:T,D,P',
        ]);

        $setting = $this->smsGatewayService->getSetting();
        $setting->update($validated);

        return redirect()->route('admin.sms-settings.index')->with('success', 'SMS settings updated successfully.');
    }

    public function refreshBalance()
    {
        $result = $this->smsGatewayService->refreshBalance();

        return redirect()->route('admin.sms-settings.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        SmsTemplate::query()->create($validated);

        return redirect()->route('admin.sms-settings.index')->with('success', 'SMS template saved successfully.');
    }

    public function updateTemplate(Request $request, SmsTemplate $smsTemplate)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $smsTemplate->update($validated);

        return redirect()->route('admin.sms-settings.index')->with('success', 'SMS template updated successfully.');
    }

    public function destroyTemplate(SmsTemplate $smsTemplate)
    {
        $smsTemplate->delete();

        return redirect()->route('admin.sms-settings.index')->with('success', 'SMS template deleted successfully.');
    }

    public function sendSingle(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string|max:50',
            'single_message' => 'nullable|string',
            'single_template_id' => 'nullable|integer|exists:sms_templates,id',
        ]);

        $message = $this->resolveMessageFromTemplate($validated['single_template_id'] ?? null, $validated['single_message'] ?? null);

        if ($message === null) {
            return back()->with('error', 'Please write a message or select a saved template.')->withInput();
        }

        $setting = $this->smsGatewayService->getSetting();
        $setting->update([
            'last_single_message' => $message,
        ]);

        $normalizedPhone = $this->smsGatewayService->normalizePhone($validated['phone']);

        if (! $normalizedPhone) {
            return back()->with('error', 'Please provide a valid Bangladesh phone number.')->withInput();
        }

        $user = User::query()
            ->where('phone', $validated['phone'])
            ->orWhere('phone', substr($normalizedPhone, 2))
            ->orWhere('alternative_phone', $validated['phone'])
            ->orWhere('alternative_phone', substr($normalizedPhone, 2))
            ->first();

        SendSingleSmsJob::dispatch(
            phone: $normalizedPhone,
            message: $message,
            userId: $user?->id,
            sentByAdminId: auth()->guard('admin')->id(),
        );

        return redirect()->route('admin.sms-settings.index')->with('success', 'Single SMS queued successfully.');
    }

    public function sendBulk(Request $request)
    {
        $validated = $request->validate([
            'bulk_message' => 'nullable|string',
            'bulk_template_id' => 'nullable|integer|exists:sms_templates,id',
        ]);

        $message = $this->resolveMessageFromTemplate($validated['bulk_template_id'] ?? null, $validated['bulk_message'] ?? null);

        if ($message === null) {
            return back()->with('error', 'Please write a message or select a saved template.')->withInput();
        }

        $setting = $this->smsGatewayService->getSetting();
        $setting->update([
            'last_bulk_message' => $message,
        ]);

        $users = User::query()->select('id', 'phone')->whereNotNull('phone')->get();
        $recipients = [];
        $seenPhones = [];

        foreach ($users as $user) {
            $normalizedPhone = $this->smsGatewayService->normalizePhone($user->phone);

            if (! $normalizedPhone || isset($seenPhones[$normalizedPhone])) {
                continue;
            }

            $seenPhones[$normalizedPhone] = true;
            $recipients[] = [
                'phone' => $normalizedPhone,
                'user_id' => $user->id,
            ];
        }

        if ($recipients === []) {
            return back()->with('error', 'No valid user phone number found for bulk SMS.');
        }

        SendBulkSmsJob::dispatch(
            recipients: $recipients,
            message: $message,
            sentByAdminId: auth()->guard('admin')->id(),
        );

        return redirect()->route('admin.sms-settings.index')->with('success', 'Bulk SMS queued successfully for '.count($recipients).' users.');
    }

    private function resolveMessageFromTemplate(?int $templateId, ?string $message): ?string
    {
        $message = trim((string) $message);

        if ($message !== '') {
            return $message;
        }

        if (! $templateId) {
            return null;
        }

        return SmsTemplate::query()->whereKey($templateId)->value('message');
    }
}
