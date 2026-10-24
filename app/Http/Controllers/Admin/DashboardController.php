<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CertificateStatus;
use App\Enums\ContactMessageStatus;
use App\Enums\CourseStatus;
use App\Enums\EnquiryStatus;
use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AdmissionEnquiry;
use App\Models\Certificate;
use App\Models\ContactMessage;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    private const TREND_MONTHS = 6;

    public function __invoke(Request $request): View
    {
        $monthStart = now()->startOfMonth();

        $stats = [
            'courses' => Course::query()->count(),
            'published_courses' => Course::query()->where('status', CourseStatus::Published)->count(),
            'draft_courses' => Course::query()->where('status', CourseStatus::Draft)->count(),
            'students' => Student::query()->count(),
            'students_this_month' => Student::query()->where('created_at', '>=', $monthStart)->count(),
            'active_enrollments' => Enrollment::query()->whereIn('status', [EnrollmentStatus::Enrolled, EnrollmentStatus::InProgress])->count(),
            'enrollments_this_month' => Enrollment::query()->where('enrollment_date', '>=', $monthStart->toDateString())->count(),
            'certificates' => Certificate::query()->where('status', CertificateStatus::Valid)->count(),
            'certificates_this_month' => Certificate::query()->where('status', CertificateStatus::Valid)->where('issue_date', '>=', $monthStart->toDateString())->count(),
            'pending_enquiries' => AdmissionEnquiry::query()->where('status', EnquiryStatus::New)->count(),
            'unread_messages' => ContactMessage::query()->where('status', ContactMessageStatus::New)->count(),
            'eligible_for_certificate' => Enrollment::query()->eligibleForCertificate()->count(),
        ];

        $enrollmentTrend = $this->enrollmentTrend();
        $recentStudents = Student::query()->latest()->limit(5)->get();
        $recentEnrollments = Enrollment::query()->with(['student', 'course'])->latest()->limit(6)->get();
        $recentCertificates = Certificate::query()->latest()->limit(5)->get();
        $recentActivity = $request->user()->can('view-activity-log')
            ? ActivityLog::query()->with('user')->latest('id')->limit(6)->get()
            : new Collection;

        return view('admin.dashboard', compact(
            'stats', 'enrollmentTrend', 'recentStudents', 'recentEnrollments', 'recentCertificates', 'recentActivity',
        ));
    }

    /**
     * Enrollments per month for the last few months, oldest first, including empty months.
     *
     * @return list<array{label: string, name: string, count: int}>
     */
    private function enrollmentTrend(): array
    {
        $firstMonth = now()->startOfMonth()->subMonths(self::TREND_MONTHS - 1);

        $query = Enrollment::query();
        $monthKey = match ($query->getConnection()->getDriverName()) {
            'sqlite' => "strftime('%Y-%m', enrollment_date)",
            'pgsql' => "to_char(enrollment_date, 'YYYY-MM')",
            default => "DATE_FORMAT(enrollment_date, '%Y-%m')",
        };

        $countsByMonth = $query
            ->where('enrollment_date', '>=', $firstMonth->toDateString())
            ->selectRaw("{$monthKey} as month, count(*) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        return collect(range(0, self::TREND_MONTHS - 1))
            ->map(function (int $offset) use ($firstMonth, $countsByMonth): array {
                $month = $firstMonth->copy()->addMonths($offset);

                return [
                    'label' => $month->format('M'),
                    'name' => $month->format('F Y'),
                    'count' => (int) $countsByMonth->get($month->format('Y-m'), 0),
                ];
            })
            ->all();
    }
}
