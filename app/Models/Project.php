<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Project
 *
 * @property int $id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string $host
 * @property string $org_name
 * @property string $repo_name
 * @property string $url
 * @property int|null $github_install_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectArtifact[] $artifacts
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Project whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Project whereGithubInstallId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Project whereHost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Project whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Project whereOrgName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Project whereRepoName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Project whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Project whereUrl($value)
 * @mixin \Eloquent
 * @property int $active
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Project whereActive($value)
 */
class Project extends Model {
  protected $fillable = ['host', 'org_name', 'repo_name', 'url', 'github_install_id', 'active'];

  public function artifacts() {
    return $this->hasMany(ProjectArtifact::class);
  }
}
