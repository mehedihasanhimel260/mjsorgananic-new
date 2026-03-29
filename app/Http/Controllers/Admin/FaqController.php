<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
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
        return view('admin.faqs.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:1000',
            'answer' => 'required|string',
            'keyword' => 'nullable|array',
            'keyword.*' => 'nullable|string|max:255',
        ]);

        Faq::create([
            'question' => $validated['question'],
            'answer' => $validated['answer'],
            'keyword' => $this->normalizeKeywords($validated['keyword'] ?? []),
        ]);

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ created successfully.');
    }

    public function edit(Faq $faq)
    {
        return view('admin.faqs.edit', compact('faq'));
    }

    public function update(Request $request, Faq $faq)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:1000',
            'answer' => 'required|string',
            'keyword' => 'nullable|array',
            'keyword.*' => 'nullable|string|max:255',
        ]);

        $faq->update([
            'question' => $validated['question'],
            'answer' => $validated['answer'],
            'keyword' => $this->normalizeKeywords($validated['keyword'] ?? []),
        ]);

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ updated successfully.');
    }
}
