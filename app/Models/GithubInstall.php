<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\GithubInstall
 *
 * @property int $id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $access_token
 * @property \Carbon\Carbon|null $access_token_expiry
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GithubInstall whereAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GithubInstall whereAccessTokenExpiry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GithubInstall whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GithubInstall whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GithubInstall whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $install_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GithubInstall whereInstallId($value)
 * @property string $org_name
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GithubInstall whereOrgName($value)
 */
class GithubInstall extends Model {
  protected $fillable = ['install_id', 'access_token', 'access_token_expiry', 'org_name'];
  protected $dates = [
    'created_at',
    'updated_at',
    'deleted_at',
    'access_token_expiry',
  ];

  public function getAccessTokenAttribute($value) {
    return $value === null ? null : decrypt($value);
  }

  public function setAccessTokenAttribute($value) {
    $this->attributes['access_token'] = encrypt($value);
  }
}
