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
 * @property string|null $access_token_expiry
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GithubInstall whereAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GithubInstall whereAccessTokenExpiry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GithubInstall whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GithubInstall whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GithubInstall whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $install_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GithubInstall whereInstallId($value)
 */
class GithubInstall extends Model {
  protected $fillable = ['install_id', 'access_token', 'access_token_expiry', 'org_name'];

  public function getAccessTokenAttribute($value) {
    return $value === null ? null : decrypt($value);
  }

  public function setAccessTokenAttribute($value) {
    $this->attributes['access_token'] = encrypt($value);
  }
}
