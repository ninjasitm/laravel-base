<?php

namespace Nitm\Content\Listeners;

use Nitm\Content\Models\Media;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\MediaLibrary\Events\MediaHasBeenAdded;
use Spatie\MediaLibrary\Support\File as MediaLibraryFileHelper;

class MediaVideoConverter implements ShouldQueue
{
    use InteractsWithQueue;
    use SerializesModels;

    protected $media;

    /**
     * Create the event listener.
     *
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(MediaHasBeenAdded $event)
    {
        $this->media = $event->media;
        //prevent any events from media model
        $this->media->flushEventListeners();

        if ((!$this->isVideo())
            || $this->media->getCustomProperty('status') !== Media::MEDIA_STATUS_TO_CONVERT
            // || strtolower($this->media->extension) == 'mp4' || strtolower($this->media->mime_type) == 'video/mp4'
        ) {
            $this->media->setCustomProperty('status', Media::MEDIA_STATUS_READY);
            $this->media->setCustomProperty('progress', 100);
            $this->media->save();
            return;
        }

        $this->media->setCustomProperty('status', Media::MEDIA_STATUS_PROCESSING);
        $this->media->save();

        try {
            $fullPath = $this->media->getPath();
            $newFileFullPath = pathinfo($fullPath, PATHINFO_DIRNAME)
                . DIRECTORY_SEPARATOR . pathinfo($fullPath, PATHINFO_FILENAME)
                . '-converted'
                . Media::MEDIA_VIDEO_EXT;

            if (file_exists($newFileFullPath)) {
                unlink($newFileFullPath);
            }

            $ffmpeg = FFMpeg::create([
                'ffmpeg.binaries'  => config('media-library.ffmpeg_binaries'),
                'ffprobe.binaries' => config('media-library.ffprobe_binaries'),
                'timeout'          => 3600,
                'ffmpeg.threads'   => 12,
            ]);

            $video = $ffmpeg->open($fullPath);

            $format = new X264();

            $format->on('progress', function ($video, $format, $percentage) use ($fullPath, $newFileFullPath) {
                if ($percentage >= 100) {
                    $this->mediaConvertingCompleted($fullPath, $newFileFullPath);
                } elseif (!($percentage % 10)) {
                    $this->media->setCustomProperty('progress', $percentage);
                    $this->media->save();
                }
            });

            $format->setAudioCodec(config('media-library.audio_codec', 'libvo_aacenc'))
                ->setKiloBitrate(1000)
                ->setAudioChannels(2)
                ->setAudioKiloBitrate(256);

            $video->save($format, $newFileFullPath);
        } catch (\Exception $e) {
            $this->media->setCustomProperty('status', Media::MEDIA_STATUS_FAILED);
            $this->media->setCustomProperty('error', $e->getMessage());
            $this->media->save();
        }
    }

    /**
     * @param $originalFilePath
     * @param $convertedFilePath
     */
    protected function mediaConvertingCompleted($originalFilePath, $convertedFilePath)
    {
        if (file_exists($originalFilePath)) {
            unlink($originalFilePath);
        }
        $this->media->file_name = pathinfo($convertedFilePath, PATHINFO_BASENAME);
        $this->media->mime_type = MediaLibraryFileHelper::getMimetype($convertedFilePath);
        $this->media->size = filesize($convertedFilePath);
        $this->media->setCustomProperty('status', Media::MEDIA_STATUS_READY);
        $this->media->setCustomProperty('progress', 100);
        $this->media->save();
    }


    /**
     * Is media a video?
     *
     * @return bool
     */
    protected function isVideo()
    {
        return (strpos($this->media->mime_type, 'video') !== false);
    }
}
