<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\BuildArtifact
 *
 * @property int $id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string $filename
 * @property int $size
 * @property int $build_id
 * @property int $project_artifact_id
 * @property-read \App\Models\Build $build
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BuildArtifact whereBuildId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BuildArtifact whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BuildArtifact whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BuildArtifact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BuildArtifact whereProjectArtifactId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BuildArtifact whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BuildArtifact whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \App\Models\ProjectArtifact $projectArtifact
 */
class BuildArtifact extends Model {
  protected $fillable = ['build_id', 'project_artifact_id', 'filename', 'size'];

  public function build() {
    return $this->belongsTo(Build::class);
  }

  public function projectArtifact() {
    return $this->belongsTo(ProjectArtifact::class);
  }
}
