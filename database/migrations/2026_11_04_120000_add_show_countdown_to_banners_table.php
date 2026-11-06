<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->boolean('show_countdown')->default(false)->after('ends_at');
        });

        // Les dates de fin étaient saisies au jour près et enregistrées à 00:00:00,
        // ce qui coupait la bannière dès le début du dernier jour : on la prolonge jusqu'au soir.
        DB::table('banners')
            ->whereNotNull('ends_at')
            ->whereTime('ends_at', '00:00:00')
            ->orderBy('id')
            ->get(['id', 'ends_at'])
            ->each(fn ($b) => DB::table('banners')->where('id', $b->id)->update([
                'ends_at' => date('Y-m-d 23:59:59', strtotime($b->ends_at)),
            ]));
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn('show_countdown');
        });
    }
};
