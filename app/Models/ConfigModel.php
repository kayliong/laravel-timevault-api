<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'configs';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The storage format of the model's date columns.
     *
     * @var string
     */
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'config',
        'key',
        'value',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'value' => 'json',
    ];

    /**
     * Get a config value by config name and key
     *
     * @param string $config
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getValue($config, $key, $default = null)
    {
        $configRecord = self::where('config', $config)
                          ->where('key', $key)
                          ->first();

        return $configRecord ? $configRecord->value : $default;
    }

    /**
     * Set a config value
     *
     * @param string $config
     * @param string $key
     * @param mixed $value
     * @return Config
     */
    public static function setValue($config, $key, $value)
    {
        return self::updateOrCreate(
            ['config' => $config, 'key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Get all configs for a specific config name
     *
     * @param string $config
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getConfigGroup($config)
    {
        return self::where('config', $config)->get();
    }

    /**
     * Delete a specific config
     *
     * @param string $config
     * @param string $key
     * @return bool
     */
    public static function deleteConfig($config, $key)
    {
        return self::where('config', $config)
                  ->where('key', $key)
                  ->delete();
    }

    /**
     * Check if a config exists
     *
     * @param string $config
     * @param string $key
     * @return bool
     */
    public static function configExists($config, $key)
    {
        return self::where('config', $config)
                  ->where('key', $key)
                  ->exists();
    }
}