<?php

namespace App\Providers;

use App\Enums\ContactMessageStatus;
use App\Enums\EnquiryStatus;
use App\Models\AdmissionEnquiry;
use App\Models\Certificate;
use App\Models\ContactMessage;
use App\Models\Enrollment;
use App\Models\User;
use App\Payments\DummyGateway;
use App\Payments\PaymentGateway;
use App\Services\InstituteSettings;
use App\Services\MailSettings;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(InstituteSettings::class);

        $this->app->bind(PaymentGateway::class, function () {
            $driver = config('institute.payment_driver');

            if ($driver === 'dummy' && $this->app->isProduction()) {
                throw new InvalidArgumentException('The dummy payment gateway cannot be used in production. Configure a real PAYMENT_DRIVER.');
            }

            return match ($driver) {
                'dummy' => new DummyGateway,
                default => throw new InvalidArgumentException('Unsupported or missing payment driver ['.$driver.']. Set PAYMENT_DRIVER in .env.'),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureAuthorization();
        $this->configureRateLimiting();
        $this->configureMail();

        View::composer('*', function ($view) {
            $view->with('institute', app(InstituteSettings::class));
        });

        $this->configureAdminNavigation();
    }

    /**
     * Counts of work waiting in each admin section, shown as sidebar badges.
     */
    private function configureAdminNavigation(): void
    {
        View::composer('components.layouts.admin', function ($view) {
            $view->with('adminCounts', [
                'enquiries' => AdmissionEnquiry::query()->where('status', EnquiryStatus::New)->count(),
                'messages' => ContactMessage::query()->where('status', ContactMessageStatus::New)->count(),
                'certificates' => Enrollment::query()->eligibleForCertificate()->count(),
            ]);
        });
    }

    /**
     * Apply the SMTP settings saved in the admin panel whenever the mail manager is first built.
     */
    private function configureMail(): void
    {
        $this->app->afterResolving('mail.manager', function (): void {
            try {
                $this->app->make(MailSettings::class)->apply();
            } catch (QueryException) {
                // The settings table is not migrated yet: keep the .env mail configuration.
            }
        });
    }

    private function configureAuthorization(): void
    {
        Gate::define('manage-certificates', fn (User $user): bool => $user->is_active && $user->isAdmin());
        Gate::define('manage-settings', fn (User $user): bool => $user->is_active && $user->isAdmin());
        Gate::define('manage-users', fn (User $user): bool => $user->is_active && $user->isAdmin());
        Gate::define('view-activity-log', fn (User $user): bool => $user->is_active && $user->isAdmin());

        Gate::define('view-certificate', function (User $user, Certificate $certificate): bool {
            if ($user->canAccessBackOffice()) {
                return true;
            }

            return $user->isStudent() && $certificate->student?->user_id === $user->id;
        });
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('verify-certificate', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));
        RateLimiter::for('verify-employee', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));
        RateLimiter::for('result-lookup', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));
        RateLimiter::for('public-forms', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('checkout', fn (Request $request) => Limit::perMinute(10)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('admin-search', fn (Request $request) => Limit::perMinute(120)->by($request->user()?->id ?: $request->ip()));
    }
}
