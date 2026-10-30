<?php

namespace Database\Seeders;

use App\Enums\CourseStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\Role;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use App\Services\EnrollmentService;
use App\Services\MarkService;
use App\Services\StudentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Development sample data. Never run against production.
 */
class DatabaseSeeder extends Seeder
{
    public function run(StudentService $students, EnrollmentService $enrollments, MarkService $marks): void
    {
        $this->seedAdministrator();
        $this->call(EmployeeSeeder::class);

        if (Course::query()->exists()) {
            $this->command?->warn('Courses already exist – skipping sample academic data.');

            return;
        }

        $courses = $this->seedCatalogue();
        $this->seedStudents($courses, $students, $enrollments, $marks);
    }

    private function seedAdministrator(): void
    {
        $email = config('institute.seed_admin.email');
        $password = config('institute.seed_admin.password');

        if (! $email || ! $password) {
            $this->command?->warn('SEED_ADMIN_EMAIL / SEED_ADMIN_PASSWORD are not set – no administrator was created.');

            return;
        }

        $admin = User::query()->firstOrNew(['email' => Str::lower($email)]);
        $admin->name = config('institute.seed_admin.name');
        $admin->password = Hash::make($password);
        $admin->role = Role::Admin;
        $admin->is_active = true;
        $admin->email_verified_at ??= now();
        $admin->save();

        $this->command?->info("Administrator ready: {$admin->email}");
    }

    /**
     * @return array<string, Course>
     */
    private function seedCatalogue(): array
    {
        $categories = collect([
            'Software Development' => 'Programming and web development programmes.',
            'Design & Multimedia' => 'Graphic design, UI/UX and video editing.',
            'Data & Analytics' => 'Spreadsheets, databases and data analysis.',
            'Office & Accounting' => 'Office productivity and computerised accounting.',
        ])->mapWithKeys(fn (string $description, string $name) => [
            $name => CourseCategory::query()->create(['name' => $name, 'slug' => Str::slug($name), 'description' => $description]),
        ]);

        $definitions = [
            ['Full Stack Web Development', 'WEB-101', 'Software Development', 6, 'months', 24999, true,
                'Build modern websites and web applications with HTML, CSS, JavaScript, PHP and MySQL.', ['HTML & CSS Fundamentals', 'JavaScript Essentials', 'PHP & MySQL', 'Final Project']],
            ['Python Programming', 'PY-101', 'Software Development', 3, 'months', 12999, true,
                'Learn programming from scratch with Python, from basics to file handling and simple automation.', ['Python Basics', 'Data Structures', 'Project']],
            ['Graphic Design Professional', 'GD-201', 'Design & Multimedia', 4, 'months', 15999, true,
                'Master layout, typography and branding using industry-standard design tools.', ['Design Principles', 'Photo Editing', 'Branding Portfolio']],
            ['UI/UX Design Foundations', 'UX-110', 'Design & Multimedia', 8, 'weeks', 11999, false,
                'Research, wireframe and prototype user-friendly digital products.', ['User Research', 'Wireframing & Prototyping']],
            ['Data Analytics with Excel & SQL', 'DA-150', 'Data & Analytics', 3, 'months', 13999, false,
                'Clean, analyse and visualise data using advanced Excel and SQL.', ['Advanced Excel', 'SQL Queries', 'Dashboards']],
            ['Computerised Accounting', 'ACC-120', 'Office & Accounting', 2, 'months', null, false,
                'Practical accounting with GST, inventory and payroll on accounting software.', ['Accounting Basics', 'GST & Payroll']],
            ['Advanced Machine Learning', 'ML-301', 'Data & Analytics', 6, 'months', 29999, false,
                'Upcoming programme – draft, hidden from the public catalogue.', ['Supervised Learning', 'Deep Learning']],
        ];

        $courses = [];

        foreach ($definitions as [$title, $code, $category, $duration, $unit, $fee, $featured, $summary, $modules]) {
            $course = new Course([
                'category_id' => $categories[$category]->id,
                'title' => $title,
                'course_code' => $code,
                'short_description' => $summary,
                'description' => $summary."\n\nThe course combines instructor-led sessions with guided lab work. Each module ends with an assessment, and students who pass every module receive a verifiable certificate.\n\n[Placeholder description – replace with the actual syllabus overview.]",
                'duration_value' => $duration,
                'duration_unit' => $unit,
                'fee' => $fee,
                'is_featured' => $featured,
                'status' => $code === 'ML-301' ? CourseStatus::Draft : CourseStatus::Published,
            ]);
            $course->slug = Str::slug($title);
            $course->save();

            foreach ($modules as $index => $name) {
                $course->modules()->create(['name' => $name, 'max_marks' => 100, 'pass_marks' => 40, 'sort_order' => $index + 1]);
            }

            $courses[$code] = $course;
        }

        return $courses;
    }

    /**
     * @param  array<string, Course>  $courses
     */
    private function seedStudents(array $courses, StudentService $students, EnrollmentService $enrollments, MarkService $marks): void
    {
        $people = [
            ['Aarav Sharma', 'aarav.sharma@example.com', '9800000001', 'male'],
            ['Diya Menon', 'diya.menon@example.com', '9800000002', 'female'],
            ['Rahul Nair', 'rahul.nair@example.com', '9800000003', 'male'],
            ['Sneha Pillai', 'sneha.pillai@example.com', '9800000004', 'female'],
            ['Mohammed Faiz', null, '9800000005', 'male'],
        ];

        /** @var list<Student> $records */
        $records = [];

        foreach ($people as [$name, $email, $phone, $gender]) {
            $records[] = $students->create([
                'full_name' => $name,
                'email' => $email,
                'phone' => $phone,
                'gender' => $gender,
                'admission_date' => now()->subMonths(4)->toDateString(),
            ]);
        }

        // Completed and passed: ready for certificate issuance.
        $web = $enrollments->enroll($records[0], $courses['WEB-101'], ['enrollment_date' => now()->subMonths(4)->toDateString()]);
        $this->recordMarks($marks, $web, [88, 92, 79, 85]);
        $enrollments->update($web, ['status' => EnrollmentStatus::Completed->value, 'enrollment_date' => $web->enrollment_date->toDateString()]);

        // Completed but failed one module.
        $python = $enrollments->enroll($records[1], $courses['PY-101'], ['enrollment_date' => now()->subMonths(3)->toDateString()]);
        $this->recordMarks($marks, $python, [72, 35, 64]);
        $enrollments->update($python, ['status' => EnrollmentStatus::Completed->value, 'enrollment_date' => $python->enrollment_date->toDateString()]);

        // In progress with partial marks.
        $design = $enrollments->enroll($records[2], $courses['GD-201'], ['status' => EnrollmentStatus::InProgress]);
        $this->recordMarks($marks, $design, [81, null, null]);

        $enrollments->enroll($records[3], $courses['DA-150']);
        $enrollments->enroll($records[3], $courses['UX-110'], ['status' => EnrollmentStatus::Withdrawn]);
        $enrollments->enroll($records[4], $courses['ACC-120']);
    }

    /**
     * @param  list<int|null>  $values
     */
    private function recordMarks(MarkService $marks, Enrollment $enrollment, array $values): void
    {
        $course = $enrollment->course;
        $moduleIds = $course->modules()->pluck('id')->all();

        $marks->saveForCourse($course, [
            $enrollment->id => array_combine($moduleIds, array_slice($values, 0, count($moduleIds))),
        ]);
    }
}
