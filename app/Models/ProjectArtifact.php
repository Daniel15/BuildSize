<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ProjectArtifact
 *
 * @property int $id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string $name
 * @property int $project_id
 * @property-read \App\Models\Project $project
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProjectArtifact whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProjectArtifact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProjectArtifact whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProjectArtifact whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProjectArtifact whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string $full_path
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProjectArtifact whereFullPath($value)
 */
class ProjectArtifact extends Model {
  protected $fillable = ['name', 'full_path'];

  public function project() {
    return $this->belongsTo(Project::class);
  }
}
