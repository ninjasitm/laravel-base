<?php

namespace Nitm\Content\Http\Controllers\Api;

use Nitm\Content\Http\Controllers\Controller;
use Nitm\Content\Repositories\FileRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Storage;
use Symfony\Component\HttpFoundation\Response;

class FileDownloadController extends Controller
{
    protected $model;

    /**
     * @inheritDoc
     */
    public function repository()
    {
        return FileRepository::class;
    }

    /**
     * Show a file
     *
     * @param Request $request     The request
     * @param mixed   $id          The id
     * @param mixed   $fingerprint The fingerprint of the file
     *
     * @return Response
     */
    public function show(Request $request, $id, $fingerprint): Response
    {
        list($model, $storagePath, $fileName, $disk) = cache()->remember(
            "file-{$id}" . Str::slug($fingerprint),
            3600,
            function () use ($id, $fingerprint) {
                return $this->prepareFile($id, $fingerprint);
            }
        );

        if (is_array($model) && $model[0] === 'redirect') {
            return redirect($model[1]);
        }

        if ($disk === 'local') {
            return response()->stream(function () use ($storagePath, $disk) {
                echo file_get_contents(Storage::disk($disk)->path($storagePath));
            }, 200, [
                'Content-Type' => $model->type,
                'Content-Length' => Storage::disk($disk)->size($storagePath),
            ]);
        }

        // Redirect to the adapter original file
        return redirect(
            Storage::disk($disk)->temporaryUrl(
                $storagePath,
                now()->addSeconds(env('TEMPORARY_FILE_EXPIRATION', 600))
            )
        );
    }

    /**
     * Download a file
     *
     * @param Request $request     The request
     * @param mixed   $id          The id
     * @param mixed   $fingerprint The fingerprint of the file
     *
     * @return Response
     */
    public function download(Request $request, $id, $fingerprint): Response
    {
        list($model, $storagePath, $fileName, $disk) = cache()->remember(
            "download-file-{$id}" . Str::slug($fingerprint),
            3600,
            function () use ($id, $fingerprint) {
                return $this->prepareFile($id, $fingerprint);
            }
        );

        if (is_array($model) && $model[0] === 'redirect') {
            return redirect($model[1]);
        }

        return Storage::disk($disk)->download($storagePath, $fileName);
    }

    /**
     * Show a file from metadata
     *
     * @param Request $request     The request
     * @param mixed   $id          The id
     * @param mixed   $fingerprint The fingerprint of the file
     *
     * @return Response
     */
    public function showFromMetadata(Request $request, $id, $fingerprint): Response
    {
        list($model, $storagePath, $fileName, $disk) = cache()->remember(
            "metadata-file-{$id}" . Str::slug($fingerprint),
            3600,
            function () use ($id, $fingerprint) {
                $method = preg_match('/.*\.[a-zA-Z]{3,}$/', $fingerprint)
                    ? 'prepareMetadataFileFromFilename' : 'prepareMetadataFile';
                return $this->$method($id, $fingerprint);
            }
        );

        if (is_array($model) && $model[0] === 'redirect') {
            return redirect($model[1]);
        }

        if ($disk === 'local') {
            return response()->stream(function () use ($model, $storagePath, $disk) {
                echo file_get_contents(Storage::disk($disk)->path($storagePath));
            }, 200, [
                'Content-Type' => $model->type,
                'Content-Length' => Storage::disk($disk)->size($storagePath),
            ]);
        }

        // Redirect to the adapter original file
        return redirect(
            Storage::disk($disk)->temporaryUrl(
                $model->storagePath,
                now()->addSeconds(env('TEMPORARY_FILE_EXPIRATION', 600))
            )
        );
    }

    /**
     * Download a file from metadata
     *
     * @param Request $request     The request
     * @param mixed   $id          The id
     * @param mixed   $fingerprint The fingerprint of the file
     *
     * @return Response
     */
    public function downloadFromMetadata(Request $request, $id, $fingerprint): Response
    {
        list($model, $storagePath, $fileName, $disk) = cache()->remember(
            "download-metadata-file-{$id}" . Str::slug($fingerprint),
            3600,
            function () use ($id, $fingerprint) {
                $method = preg_match('/.*\.[a-zA-Z]{3,}$/', $fingerprint) ? 'prepareMetadataFileFromFilename' : 'prepareMetadataFile';
                return $this->$method($id, $fingerprint);
            }
        );

        if (is_array($model) && $model[0] === 'redirect') {
            return redirect($model[1]);
        }

        return Storage::disk($disk)->download($storagePath, $fileName);
    }

    /**
     * Prepare a regular file
     *
     * @param mixed $id          The id
     * @param mixed $fingerprint The fingerprint of the file
     *
     * @return array
     */
    protected function prepareFile($id, $fingerprint): array
    {
        $model = $this->getRepository()->findOrFail($id);
        $disk = $this->getRepository()->getIsLocalUrl($model->rawUrl) ? 'local' : $this->getRepository()->getCloudStorageDriver();

        if (
            ($fingerprint !== $model->fingerprint
                && $fingerprint !== md5($model->url)
                && $fingerprint !== $model->name)
            || !Storage::disk($disk)->exists($model->publicStoragePath)
        ) {
            if (
                $model->rawUrl
                && Str::startsWith($model->rawUrl, 'http')
                && $model->rawUrl !== url()->current()
            ) {
                return [['redirect', $model->rawUrl], $model->rawUrl, $model->name, 'local'];
            } else {
                abort(404);
            }
        }

        return [$model, $model->publicStoragePath, $model->name, $disk];
    }

    /**
     * Prepare a metadata file
     *
     * @param mixed $id          The id
     * @param mixed $fingerprint The fingerprint of the file
     *
     * @return array
     */
    protected function prepareMetadataFile($id, $fingerprint): array
    {
        $model = app(\App\Repositories\Metadata\MetadataRepository::class)->findOrFail($id);
        $metadataFileFingerprint = Arr::get($model->rawValueArray, 'fingerprint');
        $metadataFileUrl = Arr::get($model->rawValueArray, 'url');
        $metadataFileName = Arr::get($model->rawValueArray, 'name');
        $file = $this->getRepository()->search([
            'filter' => array_filter([
                'fingerprint' => $metadataFileFingerprint,
                'name' => $metadataFileName,
                'entity_id' => Arr::get($model->rawValueArray, 'entity_id'),
                'entity_type' => Arr::get($model->rawValueArray, 'entity_type'),
            ]),
        ])->first();

        if (
            $metadataFileUrl
            && Str::startsWith($metadataFileUrl, 'http')
            && $metadataFileUrl !== url()->current()
        ) {
            return [['redirect', $metadataFileUrl], $metadataFileUrl, $metadataFileName, 'local'];
        } else {
            abort(404);
        }

        $disk = $this->getRepository()->getIsLocalUrl($metadataFileUrl) ? 'local' : $this->getRepository()->getStorageDriver();

        if (
            ($fingerprint !== md5($metadataFileUrl)
                && $fingerprint !== $metadataFileFingerprint)
            || !Storage::disk($disk)->exists($file->publicStoragePath)
        ) {
            abort(404);
        }

        return [$file, $file->publicStoragePath, $metadataFileName, $disk];
    }

    /**
     * Prepare a metadata file from a file name
     *
     * @param mixed $id             The metadata id
     * @param string|null $fileName The filename
     *
     * @return array
     */
    protected function prepareMetadataFileFromFilename($id, $fileName = null): array
    {
        $model = app(\App\Repositories\Metadata\MetadataRepository::class)->findOrFail($id);
        $fileName = $fileName ?? Arr::get($model->rawValueArray, 'name');
        $file = $this->getRepository()->search([
            'filter' => [
                'name' => $fileName,
                'entity_id' => Arr::get($model->rawValueArray, 'entity_id'),
                'entity_type' => Arr::get($model->rawValueArray, 'entity_type'),
            ],
        ])->first();

        if (!$file) {
            $url = Arr::get($model->rawValueArray, 'url');
            if (
                $url
                && Str::startsWith($url, 'http')
                && $url !== url()->current()
            ) {
                dir('here');
                return [['redirect', $url], $url, $fileName, 'local'];
            } else {
                abort(404);
            }
        }

        $disk = $this->getRepository()->getIsLocalUrl($file->url) ? 'local' : $this->getRepository()->getStorageDriver();

        if (!Storage::disk($disk)->exists($file->publicStoragePath)) {
            abort(404);
        }

        return [$file, $file->publicStoragePath, $fileName, $disk];
    }
}