<?php

namespace Nitm\Content\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Nitm\Content\Repositories\FileRepository as File;
use Illuminate\Http\UploadedFile;

trait SyncsProfile
{
    /**
     * Sync the avatar for a user
     * @param array $data
     */
    public function syncAvatar($data)
    {
        $data = Arr::get($data, 'avatar') ?? $data;
        if ($data instanceof UploadedFile || is_array($data)) {
            $avatar = File::store($data, $this->getStorageDirectory('avatar'));
            $file = $avatar->first();
            if (is_array($file) && isset($file['url'])) {
                $this->photo_url = $file['url'];
                $this->save();
                return $this;
            }
        }
    }

    /**
     * Sync the avatar for a user
     * @param array $data
     */
    public function syncProfile($data)
    {
        $data = Arr::get($data, 'profile', $data) ?? $data;
        if (is_array($data)) {
            $class = get_class($this->profile()->getRelated());
            $profile = $this->profile ?: new $class;
            $profile = $this->profile()->save($profile->fill($data));
            $profile->syncMetadata(Arr::get($data, 'metadata', []) ?? []);
            return $profile;
        }
    }
}