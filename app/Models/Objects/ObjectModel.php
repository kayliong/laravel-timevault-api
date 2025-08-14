<?php

namespace App\Models\Objects;

use Illuminate\Database\Eloquent\Model;

class ObjectModel extends Model
{
  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'timevault_objects';

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
    'key',
    'value',
  ];

    /**
     * @param $addressId
     * @return mixed
     */
  public function getAddressInformationById($addressId)
  {
      return $this->where('id', $addressId)->first();
  }

  /**
   * @param $addressIds
   * @return mixed
   */

  public function getAddressInformationByIds($addressIds)
  {
      return $this->whereIn('id', $addressIds)->get();
  }

  /**
   * @param $filterDate
   * @param $limit
   * @return mixed
   */
  public function getUnmaskedAddress($filterDate = null, $limit = 100)
  {
      $result = $this->where('masked', 0);
                  
      if (!empty($filterDate)) {
          $result = $result->where('created_at', '<', $filterDate);
      }

      return $result->limit($limit)->get();
  }
}
