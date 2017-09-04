<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Build
 *
 * @property int $id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property int $project_id
 * @property string $identifier
 * @property string $commit
 * @property string $committer
 * @property int|null $pull_request
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Build whereCommit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Build whereCommitter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Build whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Build whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Build whereIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Build whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Build wherePullRequest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Build whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string|null $base_branch
 * @property string|null $base_commit
 * @property string $branch
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\BuildArtifact[] $buildArtifacts
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Build whereBaseBranch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Build whereBaseCommit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Build whereBranch($value)
 */
class Build extends Model {
  protected $fillable = [
    'base_branch',
    'base_commit',
    'branch',
    'commit',
    'committer',
    'identifier',
    'project_id',
    'pull_request',
  ];

  public function buildArtifacts() {
    return $this->hasMany(BuildArtifact::class);
  }
}
