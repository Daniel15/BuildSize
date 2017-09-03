<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * App\Models\User
 *
 * @property int $id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $remember_token
 * @property string $name
 * @property string $github_username
 * @property int $github_id
 * @property string $github_token
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereGithubId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereGithubToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereGithubUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable {

  protected $fillable = ['name', 'github_username', 'github_id', 'github_token'];

  public function getGithubTokenAttribute($value) {
    return decrypt($value);
  }

  public function setGithubTokenAttribute($value) {
    $this->attributes['github_token'] = encrypt($value);
  }
}
