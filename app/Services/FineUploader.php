<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class FineUploader
{
    public $request;
    public $status = 200;
    public $allowedExtensions = [];
    public $sizeLimit = null;
    public $inputName = 'qqfile';
    public $chunksDirectory;
    public $chunksPath;
    public $chunksDisk = 'local';
    public $uploadDirectory;
    public $uploadPath;
    public $uploadDisk = 'public';
    public $isImage = true;
    public $thumbnail = true;
    public $thumbnailSmall = false;
    public $thumbnailExtraSmall = false;
    public $thumbnailMedium = false;
    public $thumbnailLarge = false;
    public $thumbnailExtraLarge = false;
    public $canvasBackground;
    public $watermark = false;
    public $resize = false;
    public $resizeWidth = false;
    public $resizeHeight = false;
    public $crop = false;
    public $cropUpsize = false;
    public $cropThumbnail = false;
    public $cropWidth = false;
    public $cropHeight = false;
    public $slide = false;
    public $slideWidth = false;
    public $slideHeight = false;

    public $chunksCleanupProbability = 0.001; // Once in 1000 requests on avg
    public $chunksExpireIn = 604800; // One week

    protected $uploadName;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->chunksDirectory = config('upload.chunksDirectory');
        $this->allowedExtensions = config('upload.imageExtensions');
        $this->chunksPath = $this->getDiskPath($this->chunksDisk);
        $this->uploadPath = $this->getDiskPath($this->uploadDisk);
    }

    /**
     * Get the original filename
     */
    public function getName()
    {
        $name = null;
        if ($this->request->has('qqfilename')) {
            $name = $this->request->input('qqfilename');
        } elseif ($this->request->hasFile($this->inputName)) {
            $name = $this->request->file($this->inputName)->getClientOriginalName();
        }

        $ext = strtolower(File::extension($name));
        $name = str_slug(File::name($name)) . '.' . $ext; // use lowercase extension

        return $name;
    }

    /**
     * Get the name of the uploaded file
     */
    public function getUploadName()
    {
        return $this->uploadName;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function combineChunks()
    {
        $uuid = $this->request->input('qquuid');
        $chunksDirectory = $this->chunksDirectory . DIRECTORY_SEPARATOR . $uuid;
        if (Storage::disk($this->chunksDisk)->exists($chunksDirectory)) {
            $this->uploadName = $this->getName();
            $totalParts = (int)$this->request->input('qqtotalparts', 1);

            $rootDirectory = $this->uploadDirectory . DIRECTORY_SEPARATOR . $uuid;
            if (!Storage::disk($this->uploadDisk)->exists($rootDirectory)) {
                Storage::disk($this->uploadDisk)->makeDirectory($rootDirectory);
            }

            if ($this->isImage) {
                $uploadDirectory = $rootDirectory . DIRECTORY_SEPARATOR . config('upload.originalDirectory');
                if (!Storage::disk($this->uploadDisk)->exists($uploadDirectory)) {
                    Storage::disk($this->uploadDisk)->makeDirectory($uploadDirectory);
                }
            } else {
                $uploadDirectory = $rootDirectory;
            }

            $uploadFile = $uploadDirectory . DIRECTORY_SEPARATOR . $this->getUploadName();

            $destination = fopen($this->uploadPath . $uploadFile, 'wb');

            for ($i = 0; $i < $totalParts; $i++) {
                $chunk = fopen($this->chunksPath . $chunksDirectory . DIRECTORY_SEPARATOR . $i, 'rb');
                stream_copy_to_stream($chunk, $destination);
                fclose($chunk);
            }

            fclose($destination);

            Storage::disk($this->chunksDisk)->deleteDirectory($chunksDirectory);

            $size = Storage::disk($this->uploadDisk)->size($uploadFile);
            if (!is_null($this->sizeLimit) && $size > $this->sizeLimit) {
                Storage::disk($this->uploadDisk)->delete($uploadFile);
                $this->status = 413;
                return ['success' => false, 'uuid' => $uuid, 'preventRetry' => true];
            }

            if ($this->isImage) {
                $info = $this->processUploaded($rootDirectory, $this->getUploadName());
                $size = $info['size'];
                $width = $info['width'];
                $height = $info['height'];
            } else {
                [$width, $height, $type] = getimagesize($this->uploadPath . $rootDirectory . DIRECTORY_SEPARATOR . $this->getUploadName());
            }

            return [
                'success'=> true,
                'uuid' => $uuid,
                'fileName' => $this->getUploadName(),
                'fileExtension' => File::extension($this->getUploadName()),
                'fileSize' => $size,
                'fileWidth' => $width ?? null,
                'fileHeight' => $height ?? null,
            ];
        }
    }

    /**
     * Process the upload.
     * @param string $name Overwrites the name of the file.
     */
    public function handleUpload($name = null)
    {
        clearstatcache();

        if (File::isWritable($this->chunksPath . $this->chunksDirectory) && 1 == mt_rand(1, 1 / $this->chunksCleanupProbability)) {
            $this->cleanupChunks();
        }

        // Check that the max upload size specified in class configuration does not exceed size allowed by server config
        if ($this->toBytes(ini_get('post_max_size')) < $this->sizeLimit || $this->toBytes(ini_get('upload_max_filesize')) < $this->sizeLimit) {
            $neededRequestSize = max(1, $this->sizeLimit / 1024 / 1024) . 'M';
            return ['error' => trans('fine-uploader.errorServerMaxSize', ['size' => $neededRequestSize]), 'preventRetry' => true];
        }

        if (!File::isWritable($this->uploadPath . $this->uploadDirectory) && !is_executable($this->uploadPath . $this->uploadDirectory)) {
            return ['error' => trans('fine-uploader.errorUploadDirectoryNotWritable'), 'preventRetry' => true];
        }

        $type = $this->request->server('HTTP_CONTENT_TYPE', $this->request->server('CONTENT_TYPE'));

        if (!$type) {
            return ['error' => trans('fine-uploader.errorUpload')];
        } else if (strpos(strtolower($type), 'multipart/') !== 0) {
            return ['error' => trans('fine-uploader.errorMultipart')];
        }

        $file = $this->request->file($this->inputName);
        $size = $this->request->input('qqtotalfilesize', $file->getSize());

        if (is_null($name)) {
            $name = $this->getName();
        }

        if (is_null($name) || empty($name)) {
            return ['error' => trans('fine-uploader.errorFileNameEmpty')];
        }

        if (empty($size)) {
            return ['error' => trans('fine-uploader.errorFileEmpty')];
        }

        if (!is_null($this->sizeLimit) && $size > $this->sizeLimit) {
            return ['error' => trans('fine-uploader.errorFileSize'), 'preventRetry' => true];
        }

        $ext = strtolower(File::extension($name));
        $this->uploadName = $name;

        if ($this->allowedExtensions && !in_array($ext, array_map('strtolower', $this->allowedExtensions))) {
            $these = implode(', ', $this->allowedExtensions);
            return ['error' => trans('fine-uploader.errorFileExtension', ['extensions' => $these]), 'preventRetry' => true];
        }

        $totalParts = (int)$this->request->input('qqtotalparts', 1);
        $uuid = $this->request->input('qquuid');

        if ($totalParts > 1) { // chunked upload
            $partIndex = (int)$this->request->input('qqpartindex');

            if (!File::isWritable($this->chunksPath . $this->chunksDirectory) && !is_executable($this->chunksPath . $this->chunksDirectory)){
                return ['error' => trans('fine-uploader.errorChunksDirectoryNotWritable'), 'preventRetry' => true];
            }

            $chunksDirectory = $this->chunksDirectory . DIRECTORY_SEPARATOR . $uuid;

            if (!Storage::disk($this->chunksDisk)->exists($chunksDirectory)) {
                Storage::disk($this->chunksDisk)->makeDirectory($chunksDirectory);
            }

            $file->move($this->chunksPath . $chunksDirectory, $partIndex);

            return ['success' => true, 'uuid' => $uuid];
        } else { // non-chunked upload
            $rootDirectory = $this->uploadDirectory . DIRECTORY_SEPARATOR . $uuid;
            if (!Storage::disk($this->uploadDisk)->exists($rootDirectory)) {
                Storage::disk($this->uploadDisk)->makeDirectory($rootDirectory);
            }

            if ($this->isImage) {
                $uploadDirectory = $rootDirectory . DIRECTORY_SEPARATOR . config('upload.originalDirectory');
                if (!Storage::disk($this->uploadDisk)->exists($uploadDirectory)) {
                    Storage::disk($this->uploadDisk)->makeDirectory($uploadDirectory);
                }
            } else {
                $uploadDirectory = $rootDirectory;
            }

            if (($response = $file->move($this->uploadPath . $uploadDirectory, $this->getUploadName())) !== false) {
                if ($this->isImage) {
                    $info = $this->processUploaded($rootDirectory, $this->getUploadName());
                    $size = $info['size'];
                    $width = $info['width'];
                    $height = $info['height'];
                } else {
                    [$width, $height, $type] = getimagesize($this->uploadPath . $uploadDirectory . DIRECTORY_SEPARATOR . $this->getUploadName());
                }

                return [
                    'success'=> true,
                    'uuid' => $uuid,
                    'fileName' => $response->getFilename(),
                    'fileExtension' => $response->getExtension(),
                    'fileSize' => $size,
                    'fileWidth' => $width ?? null,
                    'fileHeight' => $height ?? null,
                ];
            }

            return ['error' => trans('fine-uploader.errorSave')];
        }
    }

    public function import($directory, $filename)
    {
        return $this->processUploaded($directory, $filename, true);
    }

    protected function processUploaded($directory, $filename, $import = false)
    {
        $file = $this->uploadPath . $directory . DIRECTORY_SEPARATOR . config('upload.originalDirectory') . DIRECTORY_SEPARATOR . $filename;

        if ($this->crop) {
            $cropDirectory = $directory . DIRECTORY_SEPARATOR . config('upload.cropDirectory');
            if (!Storage::disk($this->uploadDisk)->exists($cropDirectory)) {
                Storage::disk($this->uploadDisk)->makeDirectory($cropDirectory);
            }

            $crop = Image::make($file)->orientate();

            $crop->fit($this->cropWidth, $this->cropHeight, function ($constraint) use ($import) {
                if (!$import && !$this->cropUpsize) {
                    $constraint->upsize();
                }
            });

            if ($crop->width() < $this->cropWidth || $crop->height() < $this->cropHeight) {
                $crop->resizeCanvas($this->cropWidth, $this->cropHeight, 'center', false, $this->canvasBackground ?: config('upload.canvasBackground'));
            }

            $crop->save($this->uploadPath . $cropDirectory . DIRECTORY_SEPARATOR . $filename, config('upload.quality'));

            if (!$this->resize) {
                $size = $crop->filesize();
                $width = $crop->width();
                $height = $crop->height();
            }
        }

        if ($this->resize) {
            $img = Image::make($file)->orientate();

            if ($img->width() > $this->resizeWidth ?: config('upload.imageMaxWidth') || $img->height() > $this->resizeHeight ?: config('upload.imageMaxHeight')) {
                $img->resize($this->resizeWidth ?: config('upload.imageMaxWidth'), $this->resizeHeight ?: config('upload.imageMaxHeight'), function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            if ($this->watermark) {
                $img->insert(config('upload.watermarkImage'), config('upload.watermarkPosition'), config('upload.watermarkOffsetX'), config('upload.watermarkOffsetY'));
            }

            $img->save($this->uploadPath . $directory . DIRECTORY_SEPARATOR . $filename, config('upload.quality'));
            $size = $img->filesize();
            $width = $img->width();
            $height = $img->height();
        }

        if ($this->slide) {
            $slideDirectory = $directory . DIRECTORY_SEPARATOR . config('upload.slideDirectory');
            if (!Storage::disk($this->uploadDisk)->exists($slideDirectory)) {
                Storage::disk($this->uploadDisk)->makeDirectory($slideDirectory);
            }

            $slide = Image::make($file)->orientate();

            // $slide->fit($this->slideWidth, $this->slideHeight);
            $slide->resize($this->slideWidth, $this->slideHeight, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            if ($this->watermark) {
                $slide->insert(config('upload.watermarkImage'), config('upload.watermarkPosition'), config('upload.watermarkOffsetX'), config('upload.watermarkOffsetY'));
            }

            if ($slide->width() < $this->slideWidth || $slide->height() < $this->slideHeight) {
                $slide->resizeCanvas($this->slideWidth, $this->slideHeight, 'center', false, $this->canvasBackground ?: config('upload.canvasBackground'));
            }

            $slide->save($this->uploadPath . $slideDirectory . DIRECTORY_SEPARATOR . $filename, config('upload.quality'));
        }

        if ($this->thumbnail) {
            $this->thumbnail($directory, $filename, $file, config('upload.thumbnailDirectory'), config('upload.thumbnailWidth'), config('upload.thumbnailHeight'));
        }

        if ($this->thumbnailExtraSmall) {
            $this->thumbnail($directory, $filename, $file, config('upload.thumbnailExtraSmallDirectory'), config('upload.thumbnailExtraSmallWidth'), config('upload.thumbnailExtraSmallHeight'));
        }

        if ($this->thumbnailSmall) {
            $this->thumbnail($directory, $filename, $file, config('upload.thumbnailSmallDirectory'), config('upload.thumbnailSmallWidth'), config('upload.thumbnailSmallHeight'));
        }

        if ($this->thumbnailMedium) {
            $this->thumbnail($directory, $filename, $file, config('upload.thumbnailMediumDirectory'), config('upload.thumbnailMediumWidth'), config('upload.thumbnailMediumHeight'));
        }

        if ($this->thumbnailLarge) {
            $this->thumbnail($directory, $filename, $file, config('upload.thumbnailLargeDirectory'), config('upload.thumbnailLargeWidth'), config('upload.thumbnailLargeHeight'));
        }

        if ($this->thumbnailExtraLarge) {
            $this->thumbnail($directory, $filename, $file, config('upload.thumbnailExtraLargeDirectory'), config('upload.thumbnailExtraLargeWidth'), config('upload.thumbnailExtraLargeHeight'));
        }

        return [
            'size' => $size,
            'width' => $width,
            'height' => $height
        ];
    }

    protected function thumbnail($directory, $filename, $file, $thumbnailDirectory, $width, $height)
    {
        $directory .= DIRECTORY_SEPARATOR . $thumbnailDirectory;
        if (!Storage::disk($this->uploadDisk)->exists($directory)) {
            Storage::disk($this->uploadDisk)->makeDirectory($directory);
        }

        $thumb = Image::make($file)->orientate();

        if ($this->cropThumbnail) {
            $thumb->fit($width, $height, function ($constraint) {
                $constraint->upsize();
            });
        } else {
            $thumb->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        if ($thumb->width() < $width || $thumb->height() < $height) {
            $thumb->resizeCanvas($width, $height, 'center', false, $this->canvasBackground ?: config('upload.canvasBackground'));
        }

        $thumb->save($this->uploadPath . $directory . DIRECTORY_SEPARATOR . $filename, config('upload.quality'));
    }

    /**
     * Returns a path to use with this upload. Check that the name does not exist,
     * and appends a suffix otherwise.
     * @param string $uploadDirectory Target directory
     * @param string $filename The name of the file to use.
     */
    protected function getUniqueFilename($uploadDirectory, $filename)
    {
        // Allow only one process at the time to get a unique file name, otherwise
        // if multiple people would upload a file with the same name at the same time
        // only the latest would be saved.
        $lock = sem_get(ftok(__FILE__, 'u'));
        sem_acquire($lock);

        $name = File::name($filename);
        $ext = '.' . File::extension($filename);
        $unique = $name;
        $suffix = 0;

        // Get unique file name for the file, by appending random suffix.
        while (Storage::disk($this->uploadDisk)->exists($uploadDirectory . DIRECTORY_SEPARATOR . $unique . $ext)){
            $suffix += rand(1, 999);
            $unique = $name . '-' . $suffix;
        }

        $result =  $uploadDirectory . DIRECTORY_SEPARATOR . $unique . $ext;

        // Create an empty target file
        if (!touch($result)) {
            $result = false;
        }

        sem_release($lock);

        return $result;
    }

    /**
     * Deletes all file parts in the chunks directory for files uploaded
     * more than chunksExpireIn seconds ago
     */
    protected function cleanupChunks()
    {
        foreach (Storage::disk($this->chunksDisk)->directories($this->chunksDirectory) as $dir) {
            $path = $this->chunksDirectory . DIRECTORY_SEPARATOR . $dir;

            if ($time = @filemtime($this->chunksPath . $path)) {
                if (time() - $time > $this->chunksExpireIn) {
                    Storage::disk($this->chunksDisk)->deleteDirectory($path);
                }
            }
        }
    }

    /**
     * Converts a given size with units to bytes.
     * @param string $str
     */
    protected function toBytes($str)
    {
        $val = (int)trim($str);
        $last = strtolower($str[strlen($str) - 1]);
        switch ($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;
        }
        return $val;
    }

    public function getDiskPath($disk = null)
    {
        if ($disk) {
            return Storage::disk($disk)->getDriver()->getAdapter()->getPathPrefix();
        } else {
            return Storage::getDriver()->getAdapter()->getPathPrefix();
        }
    }
}
