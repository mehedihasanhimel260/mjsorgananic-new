<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    private function availableStatuses(): array
    {
        return Faq::statuses();
    }

    private function normalizeKeywords(array $keywords): array
    {
        return array_values(array_filter(array_map(function ($keyword) {
            return trim((string) $keyword);
        }, $keywords)));
    }

    public function index()
    {
        $faqs = Faq::latest()->get();

        return view('admin.faqs.index', compact('faqs'));
    }

    public function create()
    {
        $statuses = $this->availableStatuses();

        return view('admin.faqs.create', compact('statuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:1000',
            'answer' => 'required|string',
            'status' => 'required|string|in:'.implode(',', $this->availableStatuses()),
            'keyword' => 'nullable|array',
            'keyword.*' => 'nullable|string|max:255',
        ]);

        Faq::create([
            'question' => $validated['question'],
            'answer' => $validated['answer'],
            'status' => $validated['status'],
            'keyword' => $this->normalizeKeywords($validated['keyword'] ?? []),
        ]);

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ created successfully.');
    }

    public function edit(Faq $faq)
    {
        $statuses = $this->availableStatuses();

        return view('admin.faqs.edit', compact('faq', 'statuses'));
    }

    public function update(Request $request, Faq $faq)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:1000',
            'answer' => 'required|string',
            'status' => 'required|string|in:'.implode(',', $this->availableStatuses()),
            'keyword' => 'nullable|array',
            'keyword.*' => 'nullable|string|max:255',
        ]);

        $faq->update([
            'question' => $validated['question'],
            'answer' => $validated['answer'],
            'status' => $validated['status'],
            'keyword' => $this->normalizeKeywords($validated['keyword'] ?? []),
        ]);

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ updated successfully.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ deleted successfully.');
    }

    public function destroyInactive()
    {
        $deletedCount = Faq::query()
            ->where('status', Faq::STATUS_INACTIVE)
            ->delete();

        return redirect()->route('admin.faqs.index')->with('success', $deletedCount > 0
            ? $deletedCount.' inactive FAQ deleted successfully.'
            : 'No inactive FAQ found to delete.');
    }
}
