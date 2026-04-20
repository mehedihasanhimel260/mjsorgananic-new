<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendSingleSmsJob;
use App\Models\SmsCampaign;
use App\Models\SmsLog;
use App\Models\SmsTemplate;
use App\Models\User;
use App\Services\SmsCampaignService;
use App\Services\SmsGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SmsMarketingController extends Controller
{
    public function __construct(
        private readonly SmsGatewayService $smsGatewayService,
        private readonly SmsCampaignService $smsCampaignService,
    ) {
    }

    public function index()
    {
        $setting = $this->smsGatewayService->getSetting();
        $logs = SmsLog::with(['user', 'admin'])->latest()->paginate(20, ['*'], 'history_page');
        $templates = SmsTemplate::query()->latest()->get();
        $campaigns = SmsCampaign::query()->latest()->take(12)->get();
        $userCount = User::query()->whereNotNull('phone')->count();

        return view('admin.sms-settings.index', compact('setting', 'logs', 'templates', 'campaigns', 'userCount'));
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
            'is_weekly_active' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($validated) {
            if (! empty($validated['is_weekly_active'])) {
                SmsTemplate::query()->update(['is_weekly_active' => false]);
            }

            SmsTemplate::query()->create([
                'title' => $validated['title'],
                'message' => $validated['message'],
                'is_weekly_active' => ! empty($validated['is_weekly_active']),
            ]);
        });

        return redirect()->route('admin.sms-settings.index')->with('success', 'SMS template saved successfully.');
    }

    public function updateTemplate(Request $request, SmsTemplate $smsTemplate)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'is_weekly_active' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($validated, $smsTemplate) {
            if (! empty($validated['is_weekly_active'])) {
                SmsTemplate::query()->whereKeyNot($smsTemplate->id)->update(['is_weekly_active' => false]);
            }

            $smsTemplate->update([
                'title' => $validated['title'],
                'message' => $validated['message'],
                'is_weekly_active' => ! empty($validated['is_weekly_active']),
            ]);
        });

        return redirect()->route('admin.sms-settings.index')->with('success', 'SMS template updated successfully.');
    }

    public function destroyTemplate(SmsTemplate $smsTemplate)
    {
        $smsTemplate->delete();

        return redirect()->route('admin.sms-settings.index')->with('success', 'SMS template deleted successfully.');
    }

    public function activateTemplate(SmsTemplate $smsTemplate)
    {
        DB::transaction(function () use ($smsTemplate) {
            SmsTemplate::query()->update(['is_weekly_active' => false]);
            $smsTemplate->update(['is_weekly_active' => true]);
        });

        return redirect()->route('admin.sms-settings.index')->with('success', 'Weekly SMS active template updated successfully.');
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

    public function sendBulk()
    {
        $campaign = $this->smsCampaignService->createWeeklyCampaign();

        if (! $campaign) {
            $campaign = SmsCampaign::query()
                ->where('campaign_type', 'weekly')
                ->where('week_key', $this->smsCampaignService->currentWeekKey())
                ->latest()
                ->first();

            if (! $campaign) {
                return back()->with('error', 'Please activate one weekly SMS template first.');
            }
        }

        $jobs = $this->smsCampaignService->dispatchCampaignBatches($campaign);

        return redirect()->route('admin.sms-settings.index')->with('success', $jobs > 0
            ? 'Weekly SMS campaign dispatched successfully.'
            : 'Weekly campaign already completed or no valid recipients found.');
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
