<?php
namespace App\Helpers;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class General
{
    public static function getEnumValues($table, $column)
    {
        $type = DB::select(DB::raw("SHOW COLUMNS FROM $table WHERE Field = '{$column}'"))[0]->Type;
        preg_match('/^enum\((.*)\)$/', $type, $matches);
        $enum = [];

        foreach (explode(',', $matches[1]) as $value) {
            $v      = trim($value, "'");
            $enum[] = $v;
        }

        return $enum;
    }

    public static function generateRandomString($length = 50)
    {
        $characters       = '0123456789abcdefghijklmnopqrstuvwxyz-';
        $charactersLength = strlen($characters);
        $randomString     = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function encryptData($value)
    {
        $encrypter = CustomerEncrypter::getInstance()->getEncrypter();

        return $encrypter->encryptString($value);
    }

    public static function decryptData($value)
    {
        if ($value) {
            $encrypter = CustomerEncrypter::getInstance()->getEncrypter();

            return $encrypter->decryptString($value);
        }

        return '';
    }

    public static function paginate($items, $perPage = 5, $page = null)
    {
        $page        = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $total       = count($items);
        $currentpage = $page;
        $offset      = ($currentpage * $perPage) - $perPage;
        $itemstoshow = array_slice($items, $offset, $perPage);

        return new LengthAwarePaginator($itemstoshow, $total, $perPage);
    }

    public static function convertHourToReadable($dec)
    {
        $seconds = ($dec * 3600);
        $hours   = floor($dec);
        $seconds -= $hours * 3600;
        $minutes = floor($seconds / 60);
        $seconds -= $minutes * 60;

        return ($hours < 10 ? "0$hours" : $hours) . "h " . ($minutes < 10 ? "0$minutes" : $minutes) . "m";
    }

    public static function generateDocumentCode($folder_name)
    {
        $id   = DB::table('documents')->latest('id')->first()->id ?? 0;
        $code = strtoupper(str_split($folder_name, 2)[0]) . str_pad(++$id, 5, 0, STR_PAD_LEFT);

        return $code;
    }

    public static function generateLeaveCode($flag)
    {
        $id   = DB::table('leaves')->latest('id')->first()->id ?? 0;
        $code = strtoupper($flag) . str_pad(++$id, 5, 0, STR_PAD_LEFT);

        return $code;
    }

    public static function getHourlyRateOfEmployee($salary)
    {
        $employee_hourly_rate = ($salary * 12) / (7 * 5.5 * 52);

        return $employee_hourly_rate;
    }

    public static function getHourlyRateOfEmployeeForProjectCost($salary)
    {
        $employee_hourly_rate = ($salary * 12) / (8 * 5.5 * 52);

        return $employee_hourly_rate;
    }

    public static function getFilePathFromUrl($url)
    {
        $url  = parse_url($url);
        $path = $url['path'] ?? '';

        return \Str::replace('/uploads/', '', $path);
    }
}
