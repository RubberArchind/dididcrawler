<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'group',
    ];

    /**
     * Get setting value with proper type casting
     */
    public function getValue()
    {
        return match($this->type) {
            'boolean' => (bool) $this->value,
            'integer' => (int) $this->value,
            'decimal' => (float) $this->value,
            default => $this->value,
        };
    }

    /**
     * Get setting by key
     */
    public static function get(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        
        return $setting ? $setting->getValue() : $default;
    }

    /**
     * Set setting value
     */
    public static function set(string $key, $value, string $type = 'string', string $description = '', string $group = 'general')
    {
        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'description' => $description,
                'group' => $group,
            ]
        );
    }

    /**
     * Get settings by group
     */
    public static function getByGroup(string $group)
    {
        return self::where('group', $group)->get()->keyBy('key');
    }

    /**
     * Get global MDR fee percentage
     */
    public static function getGlobalMdrFee()
    {
        return self::get('fee_percentage', 2.5);
    }

    /**
     * Calculate transaction fee based on settings
     */
    public static function calculateTransactionFee(float $amount): float
    {
        $feePercentage = self::get('fee_percentage', 2.5);
        $minimumFee = self::get('minimum_fee', 1000);
        $maximumFee = self::get('maximum_fee', 10000);

        // Calculate fee based on percentage
        $calculatedFee = ($amount * $feePercentage) / 100;

        // Apply minimum and maximum constraints
        if ($calculatedFee < $minimumFee) {
            return $minimumFee;
        }
        
        if ($calculatedFee > $maximumFee) {
            return $maximumFee;
        }

        return $calculatedFee;
    }
}
