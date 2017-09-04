<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Branch
 *
 * @property int $id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string $org_name
 * @property string $repo_name
 * @property string $branch
 * @property string $latest_commit
 * @property string $author
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Branch whereAuthor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Branch whereBranch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Branch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Branch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Branch whereLatestCommit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Branch whereOrgName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Branch whereRepoName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Branch whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Branch extends Model {
  protected $fillable = [
    'author',
    'branch',
    'latest_commit',
    'org_name',
    'repo_name',
  ];
}
