<?php

namespace App\Utils;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Chanthorn\CarbonKh\ToKhmerDate;

class Util
{
    protected static $instance  = null;

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function generateId(): string
    {
        return Str::orderedUuid()->toString();
    }

   public static function translateDateToKhmer($date, $format = 'd F, Y H:i')
{
    if (empty($date)) {
        return '-';
    }

    $days = [
        'Mon' => 'ចន្ទ',
        'Tue' => 'អង្គារ',
        'Wed' => 'ពុធ',
        'Thu' => 'ព្រហស្បតិ៍',
        'Fri' => 'សុក្រ',
        'Sat' => 'សៅរ៍',
        'Sun' => 'អាទិត្យ',
    ];

    $months = [
        'January' => 'មករា',
        'February' => 'កុម្ភៈ',
        'March' => 'មីនា',
        'April' => 'មេសា',
        'May' => 'ឧសភា',
        'June' => 'មិថុនា',
        'July' => 'កក្កដា',
        'August' => 'សីហា',
        'September' => 'កញ្ញា',
        'October' => 'តុលា',
        'November' => 'វិច្ឆិកា',
        'December' => 'ធ្នូ',
    ];

    $numerals = [
        '0' => '០', '1' => '១', '2' => '២', '3' => '៣', '4' => '៤',
        '5' => '៥', '6' => '៦', '7' => '៧', '8' => '៨', '9' => '៩',
    ];

    // ✅ parse + force Cambodia timezone
    $dt = Carbon::parse($date)->timezone('Asia/Phnom_Penh');

    // ✅ Khmer period by 24-hour
    $hour = (int) $dt->format('H');
    if ($hour >= 6 && $hour <= 11) {
        $periodKh = 'ព្រឹក';
    } elseif ($hour === 12) {
        $periodKh = 'ថ្ងៃត្រង់';
    } elseif ($hour >= 13 && $hour <= 16) {
        $periodKh = 'រសៀល';
    } elseif ($hour >= 17 && $hour <= 18) {
        $periodKh = 'ល្ងាច';
    } else {
        $periodKh = 'យប់';
    }

    // build formatted date in English first
    $formatted = $dt->format($format);

    // Replace day/month words inside formatted string
    $englishDay = $dt->format('D'); // Mon
    $englishMonth = $dt->format('F'); // January

    $formatted = str_replace($englishDay, $days[$englishDay] ?? $englishDay, $formatted);
    $formatted = str_replace($englishMonth, $months[$englishMonth] ?? $englishMonth, $formatted);

    // Replace numerals
    $formatted = strtr($formatted, $numerals);

    // ✅ add period at the end (clear + consistent)
    return $formatted . ' ' . $periodKh;
}

    function translateMonthToKhmer($monthNumber)
    {
        $months = [
            '01' => 'មករា',
            '02' => 'កុម្ភៈ',
            '03' => 'មីនា',
            '04' => 'មេសា',
            '05' => 'ឧសភា',
            '06' => 'មិថុនា',
            '07' => 'កក្កដា',
            '08' => 'សីហា',
            '09' => 'កញ្ញា',
            '10' => 'តុលា',
            '11' => 'វិច្ឆិកា',
            '12' => 'ធ្នូ',
        ];

        return $months[$monthNumber] ?? $monthNumber;
    }

    function convertToKhmerNumbers($number)
    {
        $khmerNumbers = ['០', '១', '២', '៣', '៤', '៥', '៦', '៧', '៨', '៩'];

        // Convert to string
        $number = (string) $number;

        // Add leading 0 if it's a single digit and numeric
        if (is_numeric($number) && strlen($number) === 1) {
            $number = '0' . $number;
        }

        // Convert each digit to Khmer
        $converted = '';
        for ($i = 0; $i < strlen($number); $i++) {
            $digit = $number[$i];
            $converted .= is_numeric($digit) ? $khmerNumbers[$digit] : $digit;
        }

        return $converted;
    }

    function convertDateToKhmer($date)
    {
        if (empty($date)) {
            return '';
        }

        // Convert Arabic numerals to Khmer numerals
        $khmerNumbers = ['០', '១', '២', '៣', '៤', '៥', '៦', '៧', '៨', '៩'];

        // Khmer month names
        $khmerMonths = [
            1 => 'មករា',
            2 => 'កុម្ភៈ',
            3 => 'មិនា',
            4 => 'មេសា',
            5 => 'ឧសភា',
            6 => 'មិថុនា',
            7 => 'កក្កដា',
            8 => 'សីហា',
            9 => 'កញ្ញា',
            10 => 'តុលា',
            11 => 'វិច្ឆិកា',
            12 => 'ធ្នូ'
        ];

        // Parse the date
        $timestamp = strtotime($date);
        if (!$timestamp) {
            return ''; // Return empty if the date is invalid
        }

        // Extract date components
        $day = date('j', $timestamp);
        $month = (int) date('n', $timestamp);
        $year = date('Y', $timestamp);

        // Convert day and year to Khmer numerals
        $khmerDay = '';
        $khmerYear = '';

        foreach (str_split($day) as $digit) {
            $khmerDay .= $khmerNumbers[$digit];
        }

        foreach (str_split($year) as $digit) {
            $khmerYear .= $khmerNumbers[$digit];
        }

        // Return formatted Khmer date
        return $khmerDay . ' ' . $khmerMonths[$month] . ' ' . $khmerYear;
    }

    function convertDateToKhmerLunar($date)
    {
        if (empty($date)) {
            return '';
        }

        try {
            // Parse the given date
            $carbonDate = Carbon::parse($date);

            // Convert to Khmer lunar date
            $khmerDate = new ToKhmerDate($carbonDate);

            // Return formatted Khmer Lunar Date
            return $khmerDate->format(); // Default full format
        } catch (Exception $e) {
            return 'Invalid Date'; // Handle errors gracefully
        }
    }

    public function formatKhmerHoursAndMinutes($minutes)
    {
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        $hoursKh = $this->convertToKhmerNumbers($hours);
        $minutesKh = $this->convertToKhmerNumbers($remainingMinutes);

        return "{$hoursKh} ម៉ោង {$minutesKh} នាទី";
    }

    public static function getApprover(array $data)
    {
        $result = $data['api']->get(
            "v2/users/{$data['approverId']}/leader-approval",
            $data['token'],
            ['date' => now()->format('Y-m-d')]
        );

        $nextApprover = $result['suggested_leader_id'] ?? null;

        $skipped = [];
        if (!empty($result['skipped_attendances'])) {
            foreach ($result['skipped_attendances'] as $unavailable) {
                $skipped[] = [
                    'user_id' => $unavailable['user_id'] ?? null,
                    'mission' => $unavailable['mission'] ?? null,
                    'leave'   => $unavailable['leave'] ?? null,
                ];
            }
        }

        return [
            'status'       => $nextApprover ? 201 : 404, // dynamic based on result
            'nextApprover' => $nextApprover,
            'skipped'      => $skipped
        ];
    }

    // app/Helpers/Util.php
    public static function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public static function getFileSizeHuman($fileUrl, $precision = 2)
    {
        if (empty($fileUrl)) {
            return 'Unknown';
        }

        // Normalize path (remove leading /)
        $relativePath = ltrim($fileUrl, '/');

        // 1. Try actual storage location
        $filePath = storage_path('app/public/' . str_replace('storage/', '', $relativePath));

        // 2. If not found, fall back to public path (symlink)
        if (!file_exists($filePath)) {
            $filePath = public_path($relativePath);
        }

        // 3. Return human-readable size if exists
        if (file_exists($filePath)) {
            return self::formatBytes(filesize($filePath), $precision);
        }

        return 'Unknown';
    }
}
