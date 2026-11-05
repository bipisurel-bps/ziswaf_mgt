<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Donation;

class CampaignController extends Controller
{
    public function index()
    {
        $category = request('kategori');

        $campaigns = Campaign::active()
            ->when($category, fn ($q) => $q->where('category', $category))
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('campaigns.index', compact('campaigns', 'category'));
    }

    public function show(Campaign $campaign)
    {
        abort_unless(in_array($campaign->status->value, ['active', 'completed'], true), 404);

        $donors = Donation::with('donor')
            ->where('campaign_id', $campaign->id)
            ->where('payment_status', 'paid')
            ->latest('paid_at')
            ->limit(10)
            ->get();

        return view('campaigns.show', compact('campaign', 'donors'));
    }
}
