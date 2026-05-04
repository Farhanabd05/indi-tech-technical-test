<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SlaRule;
use App\Http\Requests\SlaRule\StoreSlaRuleRequest;
use App\Http\Requests\SlaRule\UpdateSlaRuleRequest;
use App\Models\Priority;

class SlaRuleController extends Controller
{
    public function index()
    {
        // fetch all sla rules
        $slaRules = SlaRule::with('priority')->get();
        // render the view and pass the sla rules to it
        return view('admin.sla-rules.index', compact('slaRules'));
    }
    // create
    public function create()
    {
        // fetch priorities
        $priorities = Priority::all();
        // render the view and pass the priorities to it
        return view('admin.sla-rules.create', compact('priorities'));
    }
    // store
    public function store(StoreSlaRuleRequest $request)
    {
        // validate the request
        $validated = $request->validated();

        // create the sla rule
        SlaRule::create($validated);

        // redirect to the index page
        return redirect()->route('admin.sla-rules.index');
    }

    // update
    public function update(UpdateSlaRuleRequest $request, SlaRule $slaRule)
    {
        // validate the request
        $validated = $request->validated();

        // update the sla rule
        $slaRule->update($validated);

        // redirect to the index page
        return redirect()->route('admin.sla-rules.index');
    }

    public function edit ($id) {
        $slaRule = SlaRule::findOrFail($id);
        $priorities = Priority::all();
        return view('admin.sla-rules.edit', compact('slaRule', 'priorities'));
    }
}
