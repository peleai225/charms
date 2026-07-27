<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CustomerTag;
use App\Models\MarketingAutomation;
use App\Models\WhatsAppMessage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MarketingController extends Controller
{
    public function campaigns()
    {
        $campaigns = Campaign::latest()->paginate(20);
        $tags = CustomerTag::orderBy('name')
            ->get()
            ->map(fn($t) => [
                'id'    => $t->id,
                'name'  => $t->name,
                'color' => $t->color,
            ]);

        $stats = [
            'total'     => Campaign::count(),
            'active'    => Campaign::where('status', 'active')->count(),
            'sent'      => (int) Campaign::sum('sent_count'),
            'delivered' => (int) Campaign::sum('delivered_count'),
        ];

        $campaignData = $campaigns->through(fn($c) => [
            'id'               => $c->id,
            'name'             => $c->name,
            'type'             => $c->type,
            'status'           => $c->status,
            'recipients_count' => $c->recipients_count,
            'sent_count'       => $c->sent_count,
            'scheduled_at_fmt' => $c->scheduled_at?->format('d/m/Y H:i'),
        ]);

        Inertia::setRootView('layouts.admin-inertia');
        return Inertia::render('Admin/Marketing/Campaigns', [
            'campaigns' => $campaignData,
            'tags'      => $tags,
            'stats'     => $stats,
        ]);
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
        $automations = MarketingAutomation::latest()
            ->get()
            ->map(fn($a) => [
                'id'               => $a->id,
                'name'             => $a->name,
                'trigger'          => $a->trigger,
                'channel'          => $a->channel,
                'delay_hours'      => $a->delay_hours,
                'is_active'        => $a->is_active,
                'sent_count'       => $a->sent_count ?? 0,
                'conversion_rate'  => $a->conversion_rate,
            ]);

        Inertia::setRootView('layouts.admin-inertia');
        return Inertia::render('Admin/Marketing/Automations', [
            'automations' => $automations,
        ]);
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
        $messages = WhatsAppMessage::with('customer')
            ->latest()
            ->paginate(30);

        $stats = [
            'total'     => WhatsAppMessage::count(),
            'sent'      => WhatsAppMessage::where('status', 'sent')->count(),
            'delivered' => WhatsAppMessage::where('status', 'delivered')->count(),
            'pending'   => WhatsAppMessage::where('status', 'pending')->count(),
        ];

        $messageData = $messages->through(fn($m) => [
            'id'             => $m->id,
            'phone'          => $m->phone,
            'message'        => $m->message,
            'type'           => $m->type,
            'status'         => $m->status,
            'customer_name'  => $m->customer?->full_name,
            'created_at_fmt' => $m->created_at->format('d/m/Y H:i'),
        ]);

        Inertia::setRootView('layouts.admin-inertia');
        return Inertia::render('Admin/Marketing/WhatsappHistory', [
            'messages' => $messageData,
            'stats'    => $stats,
        ]);
    }
}
