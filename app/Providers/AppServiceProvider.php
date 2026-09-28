<?php

namespace App\Providers;

use App\Models\Absensi;
use App\Models\Catatan;
use App\Models\Jurnal;
use App\Models\Proyek;
use App\Policies\AbsensiPolicy;
use App\Policies\CatatanPolicy;
use App\Policies\JurnalPolicy;
use App\Policies\PenempatanPolicy;
use App\Policies\ProyekPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Jurnal::class, JurnalPolicy::class);
        Gate::policy(Absensi::class, AbsensiPolicy::class);
        Gate::policy(Proyek::class, ProyekPolicy::class);
        Gate::policy(Catatan::class, CatatanPolicy::class);
    }
}
