<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CustomerTag;
use App\Models\MarketingAutomation;
use App\Models\WhatsAppMessage;
use Illuminate\Http\Request;

class MarketingController extends Controller
{
    public function campaigns()
    {
        $campaigns = Campaign::latest()->paginate(20);
        $tags = CustomerTag::orderBy('name')->get();
        return view('admin.marketing.campaigns', compact('campaigns', 'tags'));
    }

    public function storeCampaign(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:whatsapp,email,push,sms',
            'message_template' => 'required|string',
            'target_tags' => 'nullable|array',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        $campaign = Campaign::create([
            ...$validated,
            'status' => $request->scheduled_at ? 'scheduled' : 'draft',
            'created_by' => auth()->id(),
        ]);

        $campaign->update(['recipients_count' => $campaign->getTargetCustomers()->count()]);

        return back()->with('success', 'Campagne creee avec succes.');
    }

    public function destroyCampaign(Campaign $campaign)
    {
        $campaign->delete();
        return back()->with('success', 'Campagne supprimee.');
    }

    public function automations()
    {
        $automations = MarketingAutomation::latest()->get();
        return view('admin.marketing.automations', compact('automations'));
    }

    public function storeAutomation(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'trigger' => 'required|in:abandoned_cart,post_purchase,post_delivery,inactive_customer,birthday,loyalty_milestone,new_customer,vip_upgrade,custom',
            'channel' => 'required|in:whatsapp,email,push,sms',
            'message_template' => 'required|string',
            'delay_hours' => 'required|integer|min:0',
            'is_active' => 'nullable',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        MarketingAutomation::create($validated);
        return back()->with('success', 'Automatisation creee avec succes.');
    }

    public function toggleAutomation(MarketingAutomation $automation)
    {
        $automation->update(['is_active' => !$automation->is_active]);
        return back()->with('success', 'Automatisation ' . ($automation->is_active ? 'activee' : 'desactivee') . '.');
    }

    public function destroyAutomation(MarketingAutomation $automation)
    {
        $automation->delete();
        return back()->with('success', 'Automatisation supprimee.');
    }

    public function whatsappHistory()
    {
        $messages = WhatsAppMessage::with(['customer', 'order'])
            ->latest()
            ->paginate(30);

        $stats = [
            'total' => WhatsAppMessage::count(),
            'sent' => WhatsAppMessage::where('status', 'sent')->count(),
            'delivered' => WhatsAppMessage::where('status', 'delivered')->count(),
            'pending' => WhatsAppMessage::where('status', 'pending')->count(),
        ];

        return view('admin.marketing.whatsapp-history', compact('messages', 'stats'));
    }
}
