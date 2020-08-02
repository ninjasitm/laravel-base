<?php

namespace Nitm\Content\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Nitm\Content\Repositories\FileRepository as File;
use Illuminate\Http\UploadedFile;

trait RepositoryProfile
{
    /**
     * Sync the avatar for a user
     * @param array $data
     */
    public function syncAvatar($model, $data)
    {
        $data = Arr::get($data, 'avatar') ?? $data;
        if ($data instanceof UploadedFile || is_array($data)) {
            $avatar = File::store($data, $model->getStorageDirectory('avatar'));
            $file = $avatar->first();
            if (is_array($file) && isset($file['url'])) {
                $model->photo_url = $file['url'];
                $model->save();
                return $model;
            }
        }
    }

    /**
     * Sync the avatar for a user
     * @param array $data
     */
    public function syncProfile($model, $data)
    {
        $data = Arr::get($data, 'profile', $data) ?? $data;
        if (is_array($data)) {
            $profile = $model->profile()->firstOrNew([
                $model->profile()->getQualifiedForeignKeyName() => $model->id
            ]);
            $profile->fill($data)->save();
            $profile->syncMetadata(Arr::get($data, 'metadata', []) ?? []);
            return $profile;
        }
    }
}
