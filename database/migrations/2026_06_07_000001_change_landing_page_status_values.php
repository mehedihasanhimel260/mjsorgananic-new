<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('landing_pages')->where('status', 'published')->update(['status' => 'active']);
        DB::table('landing_pages')->where('status', 'draft')->update(['status' => 'inactive']);

        $activeIds = DB::table('landing_pages')
            ->where('status', 'active')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->pluck('id');

        if ($activeIds->count() > 1) {
            DB::table('landing_pages')
                ->whereIn('id', $activeIds->slice(1)->values()->all())
                ->update(['status' => 'inactive']);
        }
    }

    public function down(): void
    {
        DB::table('landing_pages')->where('status', 'active')->update(['status' => 'published']);
        DB::table('landing_pages')->where('status', 'inactive')->update(['status' => 'draft']);
    }
};
