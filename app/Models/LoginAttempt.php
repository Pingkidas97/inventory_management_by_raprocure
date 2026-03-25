<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    public $timestamps = false;
    
    protected $fillable = ['user_id', 'ip_address', 'attempts', 'last_attempt'];

    private static $max_failed_attempts = 3; // Max number of failed login attempts
    private static $lockout_time_minutes = 5; // Lockout duration in minutes

    // Check if the user has failed login attempts and if they are locked out
    public static function checkLoginAttempts($username, $ip_address) {
        $login = self::where('user_id', $username)
                    ->where('ip_address', $ip_address)
                    ->first();

        if (!empty($login)) {
            return $login;
        }
        return false;
    }

    // Reset the failed attempts when login is successful
    public static function resetFailedAttempts($username, $ip_address) {
        $login = self::where('user_id', $username)
                    ->where('ip_address', $ip_address)
                    ->first();

        if (!empty($login)) {
            $login->attempts = 0;
            $login->last_attempt = date('Y-m-d H:i:s');
            $login->lockout_time = NULL;
            $login->save();
            return true;
        }
        return false;
    }

    // Increment failed login attempts
    public static function incrementFailedAttempts($username, $ip_address) {
        $login_info = self::checkLoginAttempts($username, $ip_address);
        if ($login_info) {
            $attempts = $login_info->attempts + 1;
            // Check if the user has exceeded the max failed attempts
            $login = self::find($login_info->id);
            if ($attempts >= self::$max_failed_attempts) {
                // Lockout the user for a specified time period
                $lockout_time = date('Y-m-d H:i:s', strtotime('+' . self::$lockout_time_minutes . ' minutes'));
                if (!empty($login)) {
                    $login->attempts = $attempts;
                    $login->last_attempt = date('Y-m-d H:i:s');
                    $login->lockout_time = $lockout_time;
                    $login->save();
                }
            } else {
                // Just increment the failed attempts count
                if (!empty($login)) {
                    $login->attempts = $attempts;
                    $login->last_attempt = date('Y-m-d H:i:s');
                    $login->save();
                }
            }
        } else {
            // First failed attempt, create a new record
            self::insert([
                'user_id' => $username,
                'ip_address' => $ip_address,
                'attempts' => 1,
                'last_attempt' => date('Y-m-d H:i:s')
            ]);
        }
    }
}
