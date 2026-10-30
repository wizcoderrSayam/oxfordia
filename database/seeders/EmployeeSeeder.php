<?php

namespace Database\Seeders;

use App\Enums\EmployeeStatus;
use App\Models\Employee;
use App\Services\EmployeeService;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;

/**
 * Development sample employees with photos. Never run against production.
 */
class EmployeeSeeder extends Seeder
{
    public function run(EmployeeService $employees): void
    {
        if (Employee::query()->exists()) {
            $this->command?->warn('Employees already exist – skipping sample employees.');

            return;
        }

        $people = [
            ['ravi-shankar', 'Ravi Shankar', 'Mohan Shankar', 'Sita Shankar', '1986-03-14', 'Senior Faculty – Software Development', '2019-07-01', EmployeeStatus::Active,
                "14, Gomti Nagar Extension,\nLucknow, Uttar Pradesh 226010",
                'Ravi has over twelve years of experience teaching programming and web development. He leads the Full Stack Web Development programme and mentors students through their final projects.'],
            ['priya-iyer', 'Priya Iyer', 'Venkatesh Iyer', 'Lakshmi Iyer', '1992-11-02', 'Faculty – Design & Multimedia', '2021-01-11', EmployeeStatus::Active,
                "B-207, Shanti Apartments,\nIndira Nagar, Lucknow 226016",
                'Priya is a graphic and UI/UX designer who teaches typography, branding and prototyping. Before joining the institute she worked with design studios on brand identity projects.'],
            ['suresh-kumar', 'Suresh Kumar', 'Ram Prasad', 'Kamla Devi', '1979-06-25', 'Centre Head', '2016-04-01', EmployeeStatus::Active,
                "House No. 52, Sector C,\nAliganj, Lucknow 226024",
                'Suresh manages day-to-day operations of the centre, including admissions, examinations and certification. He has been with the institute since it opened.'],
            ['neha-gupta', 'Neha Gupta', 'Anil Gupta', 'Sunita Gupta', '1995-08-19', 'Admission Counsellor', '2022-06-15', EmployeeStatus::Active,
                "31/7, Rajajipuram,\nLucknow, Uttar Pradesh 226017",
                'Neha guides prospective students in choosing the right course for their goals and helps them through the admission process.'],
            ['arjun-mehta', 'Arjun Mehta', 'Rajesh Mehta', 'Pooja Mehta', '1990-01-30', 'Lab Instructor', '2020-02-03', EmployeeStatus::Relieved,
                "9, Vikas Nagar,\nKapoorthala, Lucknow 226022",
                'Arjun supported practical lab sessions for the programming and office courses.'],
        ];

        foreach ($people as [$photo, $name, $father, $mother, $dateOfBirth, $position, $joiningDate, $status, $address, $bio]) {
            $employee = $employees->create([
                'full_name' => $name,
                'father_name' => $father,
                'mother_name' => $mother,
                'date_of_birth' => $dateOfBirth,
                'address' => $address,
                'position' => $position,
                'joining_date' => $joiningDate,
                'status' => $status,
                'bio' => $bio,
            ], new UploadedFile(database_path("seeders/images/employees/{$photo}.jpg"), "{$photo}.jpg", 'image/jpeg', null, true));

            $this->command?->info("Employee ready: {$employee->registration_number} {$employee->full_name}");
        }
    }
}
