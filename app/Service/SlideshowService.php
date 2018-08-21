<?php

namespace App\Service;

use App\Models\Advertiser;
use App\Models\Image;
use App\Models\MediaAccount;
use App\Models\MediaAccountSlideshow;
use App\Models\Slideshow;
use App\Models\SlideshowImage;
use App\Repositories\Eloquent\SlideshowRepository;
use Classes\FacebookGraphClient;
use Classes\TwitterApiClient;

class SlideshowService extends BaseService {

    const MIN_VIDEO_SIZE_MB = 0.0001;
    const MIN_VIDEO_SIZE_BYTE = 100;
    const SLIDESHOW_PER_PAGE = 5;

    /** @var SlideshowRepository  */
    protected $repository;
    private $serialNo = '';

    public function __construct()
    {
        $this->repository = app(SlideshowRepository::class);
    }

    /**
     * @param $advertiserId
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getList($advertiserId)
    {
        return Slideshow::join('slideshow_images', 'slideshows.id', '=', 'slideshow_images.slideshow_id')
            ->where('slideshows.advertiser_id', $advertiserId)
            ->where('slideshows.status', '!=', '0')
            ->groupBy('slideshows.id')
            ->orderBy('slideshows.created_at', 'desc')
            ->selectRaw('slideshows.*, count(slideshow_images.id) as image_count')
            ->paginate(self::SLIDESHOW_PER_PAGE);
    }

    /**
     * @param Advertiser $advertiser
     * @param $slideshowId
     * @param $images
     * @param int $timePerImage
     * @param int $effectType
     * @param bool $fix
     * @param int $videoType
     * @return Slideshow|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public function createOrUpdateSlideshow(Advertiser $advertiser, $slideshowId, $images, $timePerImage = 2, $effectType = 0, $fix = false, $videoType = Slideshow::VIDEO_TYPE_SQUARE)
    {
        if ($slideshowId) {
            $slideshow = Slideshow::findOrFail($slideshowId);
            if ($advertiser->cannot('update', $slideshow)) {
                abort(403);
            }
        } else {
            $slideshow = new Slideshow();
            $slideshow->advertiser_id = $advertiser->id;
        }

        $imageURLs = $images->pluck('image_url');

        try {
            $timeStart = microtime(true);
            \Log::info("start new function");
            $videoName = $this->createMovieFile($advertiser->id, $imageURLs, $timePerImage, $fix, $videoType, $effectType);
            $time = microtime(true) - $timeStart;
            \Log::info(sprintf("run time: %s sec.", $time));

            if ($videoName) {
                // calculate new video size
                if (!$fix) {
                    $videoName = 'preview/' . $videoName;
                } else {
                    //remove before video
                    $path = self::getVideoPath($slideshow->name, $advertiser->id);
                    \File::delete($path);
                }

                $size = \File::size(self::getVideoPath($videoName, $advertiser->id));

                $slideshow->name = $videoName;
                $slideshow->size = $size;
                $slideshow->time_per_img = $timePerImage;
                $slideshow->duration = $timePerImage * count($images);
                $slideshow->video_type = $videoType;
                $slideshow->effect_type = $effectType;
                if ($fix) {
                    $slideshow->status = Slideshow::STATUS_CREATED;
                    $slideshow->save();
                    $saveImg = $this->replaceSlideshowImages($slideshow->id, $images);
                    if (!$saveImg) {
                        return null;
                    }
                    $slideshow->save();
                }
            } else {
                return null;
            }
        } catch (\Exception $e) {
            \Log::error($e);
            return null;
        }

        return $slideshow;
    }

    /**
     * @param $slideshowId
     * @param $images
     * @return bool
     */
    private function replaceSlideshowImages($slideshowId, $images)
    {
        \DB::beginTransaction();
        try {
            SlideshowImage::where('slideshow_id', $slideshowId)->delete();
            foreach ($images as $image) {
                $slideshowImage = new SlideshowImage();
                $slideshowImage->slideshow_id = $slideshowId;
                $slideshowImage->image_id = $image->id;
                $slideshowImage->save();
            }
            \DB::commit();

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error($e);

            return false;
        }

        return true;
    }

    /*
     * 指定UGC から動画を作成
     * 
     * @param   int     $adAccountId
     * @param   array   $imageUrls
     * @param   int     $timeParImage
     * @param   int     $videoType
     * @param   int     $effectType
     * 
     * @return  string  $movieFileName
     */
    private function createMovieFile($adAccountId, $imageUrls, $timePerImage = 1, $fix, $videoType = Slideshow::VIDEO_TYPE_SQUARE, $effectType = Slideshow::EFFECT_TYPE_FADEINOUT)
    {
        // 選択された静止画を取得
        try {
            // 保存先ディレクトリのパスを取得
            $path = $this->getVideoPath('', $adAccountId);

            // 画像サイズを取得
            $imageSize = $this->getVideoSize($videoType, $effectType, $fix);

            // 実行日時(ユニーク値) を取得
            $this->serialNo = (new \DateTime())->format('YmdHis');

            $imagePaths = array();
            foreach ($imageUrls as $index => $imageUrl) {
                // ファイル名を生成
                $imageFileName = sprintf('%s_%02d', $this->serialNo, ($index + 1));

                // 動画の種類にて取得する静止画の処理を分岐
                if ($videoType == Slideshow::VIDEO_TYPE_STORIES) {
                    $fullPath = download_file($imageUrl, $path, $imageFileName, [], true);
                    $fullPath = crop_center_image($fullPath, $imageSize);
                } else {
                    $fullPath = download_file($imageUrl, $path, $imageFileName, $imageSize, true);
                }

                // ファイルが保存されたパスを取得
                if (file_exists($fullPath)) {
                    $imagePaths[] = $fullPath;
                } else {
                    throw new \Exception("failed to download a ugc image.");
                }
            }
        } catch (\Exception $e) {
            \Log::error($e);

            return '';
        }

        // 取得した静止画から動画を生成
        try {
            $moviePaths = array();
            $movieFileName = '';
            switch ($effectType) {
                case Slideshow::EFFECT_TYPE_HORIZONTAL_SLIDE:   // Dynamic View
                    $movieFileName = $this->convertImageToDynamicView($imagePaths, $imageSize, $timePerImage, $path, $fix);
                    break;
                case Slideshow::EFFECT_TYPE_ZOOMIN:             // Zoom-In & Cross Fade
                    $moviePaths = $this->convertImageToZoomInMovie($imagePaths, $imageSize, $timePerImage, $path);
                    if ($moviePaths === false) {
                        throw new \Exception('failed to create temporary movies. [default]');
                    }

                    break;
                case Slideshow::EFFECT_TYPE_ZOOMOUT:            // Zoom-Out & Cross Fade
                    $moviePaths = $this->convertImageToZoomOutMovie($imagePaths, $imageSize, $timePerImage, $path);
                    if ($moviePaths === false) {
                        throw new \Exception('failed to create temporary movies. [default]');
                    }

                    break;
                default:                                        // Cross Fade
                    $moviePaths = $this->convertImageToNoEffectMovie($imagePaths, $imageSize, $timePerImage, $path);
                    if ($moviePaths === false) {
                        throw new \Exception('failed to create temporary movies. [default]');
                    }

                    break;
            }

            if ($effectType !== Slideshow::EFFECT_TYPE_HORIZONTAL_SLIDE) {
                if (!$fix) {
                    $path .= 'preview/';
                }

                if ($effectType === Slideshow::EFFECT_TYPE_ZOOMIN
                    || $effectType === Slideshow::EFFECT_TYPE_ZOOMOUT) {
                    $imageSize = array(($imageSize[0] / 2), ($imageSize[1] / 2));
                }

                $movieFileName = $this->addEffectCrossFade($moviePaths, $imageSize, $timePerImage, $path);
                if ($movieFileName === '') {
                    throw new \Exception('failed to add the effect. [cross fade]');
                }
            }

            // 後始末
            \File::delete($imagePaths);

            return $movieFileName;
        } catch (\Exception $e) {
            \Log::error($e);

            \File::delete($imagePaths);

            return '';
        }
    }

    /*
     * 指定UGC から動画へ変換(エフェクトなし)
     * 
     * @param   array   $imagePaths
     * @param   array   $imageSize
     * @param   int     $timePerImage
     * @param   string  $basePath
     * 
     * @return  array   $movies
     */
    private function convertImageToNoEffectMovie($imagePaths, $imageSize, $timePerImage, $basePath)
    {
        try {
            $ffmepg = env('FFMPEG_PATH', 'ffmpeg');
            $moviePaths = array();
            foreach ($imagePaths as $index => $imagePath) {
                $moviePath = sprintf('%stmp/%s_%02d.ts', $basePath, $this->serialNo, ($index + 1));

                $command = sprintf('%s -loop 1 -i %s -c:v libx264 -an -t %d -vf scale=%s -y -pix_fmt yuv420p -r  %d %s',
                    $ffmepg, $imagePath, $timePerImage, implode(':', $imageSize),
                    Slideshow::MOVIE_FPS, $moviePath);
                $output = array();
                $result = 0;
                exec($command, $output, $result);
                if ($result !== 0) {
                    throw new \Exception('failed to create a temporary movie file. [none effect]');
                }

                $moviePaths[] = $moviePath;
            }

            return $moviePaths;
        } catch (\Exception $e) {
            \Log::error($e);

            return false;
        }
    }

    /*
     * 画像のサイズを用途により取得
     * 
     * @param   int     $videoType
     * @param   int     $effectType
     * @param   bool    $flag
     * 
     * @return  array   $size
     */
    private function getVideoSize($videoType, $effectType, $flag)
    {
        $size = array();
        if ($flag) {  // 保存用
            if ($videoType == Slideshow::VIDEO_TYPE_STORIES) {
                switch ($effectType) {
                    case Slideshow::EFFECT_TYPE_ZOOMIN:
                    case Slideshow::EFFECT_TYPE_ZOOMOUT:
                        $size = array(1440, 2560);
                        break;
                    default:
                        $size = array(720, 1280);
                        break;
                }
            } else {
                switch ($effectType) {
                    case Slideshow::EFFECT_TYPE_ZOOMIN:
                    case Slideshow::EFFECT_TYPE_ZOOMOUT:
                        $size = array(1440, 1440);
                        break;
                    default:
                        $size = array(720, 720);
                        break;
                }
            }
        } else {      // プレビュー用
            if ($videoType == Slideshow::VIDEO_TYPE_STORIES) {
                switch ($effectType) {
                    case Slideshow::EFFECT_TYPE_ZOOMIN:
                    case Slideshow::EFFECT_TYPE_ZOOMOUT:
                        $size = array(316, 560);
                        break;
                    default:
                        $size = array(158, 280);
                        break;
                }
            } else {
                switch ($effectType) {
                    case Slideshow::EFFECT_TYPE_ZOOMIN:
                    case Slideshow::EFFECT_TYPE_ZOOMOUT:
                        $size = array(560, 560);
                        break;
                    default:
                        $size = array(280, 280);
                        break;
                }
            }
        }

        return $size;
    }

    /*
     * 生成したフレーム静止画から動画を作成
     * 
     * @param   string  $basePath
     * @param   int     $timePerImage
     * @param   int     $frameNo
     * 
     * @return  string  $moviePath
     */
    private function createMovieFromFrameImages($basePath, $timePerImage, $frameNo)
    {
        // 作成したフレーム画像から動画を作成
        $ffmpeg = env('FFMPEG_PATH', 'ffmpeg');
        try {
            $moviePath = sprintf('%stmp/%s_%02d.ts', $basePath, $this->serialNo, $frameNo);

            $command = sprintf('%s -i %stmp/%s_frame%%04d.jpg -vcodec libx264 -pix_fmt yuv420p -r %d -t %d -y %s',
                $ffmpeg, $basePath, $this->serialNo, Slideshow::MOVIE_FPS, $timePerImage, $moviePath);
            exec($command, $output, $result);
            if ($result !== 0) {
                return false;
            }

            return $moviePath;
        } catch (Exception $e) {
            \Log::error($e);

            return false;
        }
    }

    /*
     * @param   array   $imagePaths
     * @param   array   $imageSize
     * @param   int     $timePerImage
     * @param   string  $basePath
     * 
     * @return  array   $movies
     */
    private function convertImageToZoomInMovie($imagePaths, $imageSize, $timePerImage, $basePath)
    {
        set_time_limit(300);

        // UGC からフレーム画像を作成するパラメータを設定
        $px = $imageSize[0] / 2 / 2;
        $py = $imageSize[1] / 2 / 2;

        $totalFrameNum = Slideshow::MOVIE_FPS * $timePerImage;
        $movePx = ceil($px / $totalFrameNum);
        $movePy = ceil($py / $totalFrameNum);

        $resizeWidth = $imageSize[0] / 2;
        $resizeHeight = $imageSize[1] / 2;

        $moviePaths = array();

        foreach ($imagePaths as $index => $imagePath) {
            $cnt = 0;
            $frameFileList = array();
            while ($cnt < $totalFrameNum) {
                try {
                    $srcImage = \Intervention\Image\ImageManagerStatic::make($imagePath);
                    $frameImgPath = sprintf('%stmp/%s_frame%04d.jpg', $basePath, $this->serialNo, $cnt);
                    if ($cnt === 0) {
                        $srcImage->resize($resizeWidth, $resizeHeight)->save($frameImgPath);
                    } else {
                        $cropWidth = $imageSize[0] - (($movePx * $cnt) * 2);
                        $cropHeight = $imageSize[1] - (($movePy * $cnt) * 2);

                        $tmp = sprintf('%stmp/tmp.jpg', $basePath);
                        $srcImage->crop($cropWidth, $cropHeight, ($movePx * $cnt), ($movePy * $cnt))->save($tmp);

                        $tmpImage = \Intervention\Image\ImageManagerStatic::make($tmp);
                        $tmpImage->resize($resizeWidth, $resizeHeight)->save($frameImgPath);

                        $tmpImage->destroy();
                        \File::delete($tmp);
                    }

                    $frameFileList[] = $frameImgPath;
                    $srcImage->destroy();

                    $cnt++;
                } catch (Exception $e) {
                    \Log::error($e);

                    return false;
                }
            }

            // 作成したフレームから動画を作成
            $moviePath = $this->createMovieFromFrameImages($basePath, $timePerImage, ($index + 1));
            if ($moviePath === false) {
                return false;
            }

            $moviePaths[] = $moviePath;

            // 後始末
            \File::delete($frameFileList);
        }

        return $moviePaths;
    }

    /*
     * @param   array   $imagePaths
     * @param   array   $imageSize
     * @param   int     $timePerImage
     * @param   string  $basePath
     * 
     * @return  array   $movies
     */
    private function convertImageToZoomOutMovie($imagePaths, $imageSize, $timePerImage, $basePath)
    {
        set_time_limit(300);

        // UGC からフレーム画像を作成するパラメータを設定
        $px = $imageSize[0] / 2 / 2;
        $py = $imageSize[1] / 2 / 2;

        $totalFrameNum = Slideshow::MOVIE_FPS * $timePerImage;
        $movePx = ceil($px / $totalFrameNum);
        $movePy = ceil($py / $totalFrameNum);

        $resizeWidth = $imageSize[0] / 2;
        $resizeHeight = $imageSize[1] / 2;

        $moviePaths = array();

        foreach ($imagePaths as $index => $imagePath) {
            $cnt = 0;
            $frameFileList = array();
            while ($cnt < $totalFrameNum) {
                try {
                    $srcImage = \Intervention\Image\ImageManagerStatic::make($imagePath);
                    $frameImgPath = sprintf('%stmp/%s_frame%04d.jpg', $basePath, $this->serialNo,$totalFrameNum - $cnt);
                    if ($cnt === 0) {
                        $srcImage->resize($resizeWidth, $resizeHeight)->save($frameImgPath);
                    } else {
                        $cropWidth = $imageSize[0] - (($movePx * $cnt) * 2);
                        $cropHeight = $imageSize[1] - (($movePy * $cnt) * 2);

                        $tmp = sprintf('%stmp/tmp.jpg', $basePath);
                        $srcImage->crop($cropWidth, $cropHeight, ($movePx * $cnt), ($movePy * $cnt))->save($tmp);

                        $tmpImage = \Intervention\Image\ImageManagerStatic::make($tmp);
                        $tmpImage->resize($resizeWidth, $resizeHeight)->save($frameImgPath);

                        $tmpImage->destroy();
                        \File::delete($tmp);
                    }

                    $frameFileList[] = $frameImgPath;
                    $srcImage->destroy();

                    $cnt++;
                } catch (Exception $e) {
                    \Log::error($e);

                    return false;
                }
            }

            // 作成したフレームから動画を作成
            $moviePath = $this->createMovieFromFrameImages($basePath, $timePerImage, ($index + 1));
            if ($moviePath === false) {
                return false;
            }

            $moviePaths[] = $moviePath;

            // 後始末
            \File::delete($frameFileList);
        }

        return $moviePaths;
    }

    /*
     * 指定のUGC からDynamic View を作成
     * 
     * @param   array   $imagePaths
     * @param   array   $imageSize
     * @param   int     $timePerImage
     * @param   string  $basePath
     * 
     * @return  string  $movieFileName
     */
    private function convertImageToDynamicView($imagePaths, $imageSize, $timePerImage, $basePath, $flag)
    {
        try {
            // 変数
            $ffmpeg = env('FFMPEG_PATH', 'ffmpeg');
            $output = array();
            $result = 0;
            $imageCount = count($imagePaths);

            \Log::info($imagePaths);
            $sirialNo = (new \DateTime())->format('YmdHis');

            // 指定のUGC をタイル状に横に並べ、一枚の画像へ結合
            $tileImagePath = sprintf('%stmp/%s_tile_image.jpg', $basePath, $this->serialNo);
            \Log::info("TileImagePath: ".$tileImagePath);
            $command = sprintf('%s -f image2 -i \'%stmp/%s_%%02d.jpg\' -filter_complex "tile=%dx1" %s',
                $ffmpeg, $basePath, $this->serialNo, $imageCount, $tileImagePath);
            \Log::info("command 1: " . $command);
            exec($command, $output, $result);
            if ($result !== 0) {
                \Log::ingfo('delete file : ' . $tileImagePath);
                \File::delete($tileImagePath);

                throw new \Exception('failed to create the tile images.');
            }

            // タイル状の画像の有無を確認
            if (!file_exists($tileImagePath)) {
                \File::delete($tileImagePath);

                throw new \Exception('not such file. [tile image]');
            }

            // タイル状の画像を動画へ変換
            $temporaryMoviePath = sprintf('%stmp/%s_static_movie.mp4', $basePath, $this->serialNo);

            \Log::info("temporaryMoviePath: " . $temporaryMoviePath);
            $titalPlayTime = $imageCount * $timePerImage;
            $tileImageSize = array($imageSize[0] * $imageCount, $imageSize[1]);

            $command = sprintf('%s -loop 1 -i %s -c:v libx264 -r %d -t %d -pix_fmt yuv420p -vf scale=%s %s',
                $ffmpeg, $tileImagePath, Slideshow::MOVIE_FPS, $titalPlayTime,
                implode(':', $tileImageSize), $temporaryMoviePath);
            \Log::info("command 2: " . $command);
            exec($command, $output, $result);
            if ($result !== 0) {
                \File::delete($tileImagePath);
                \File::delete($temporaryMoviePath);

                throw new \Exception('failed to convert images to temporary movie.');
            }

            if (!file_exists($temporaryMoviePath)) {
                \File::delete($tileImagePath);
                \File::delete($temporaryMoviePath);

                throw new \Exception('not such file. [temporary movie]');
            }

            // Dynamic View を生成
            $movieFileName = sprintf('%s.mp4', $this->serialNo);
            $savePath = '';
            if ($flag) {
                $savePath = sprintf('%s%s', $basePath, $movieFileName);
            } else {
                $savePath = sprintf('%spreview/%s', $basePath, $movieFileName);
            }

            $moveWidthPx = (($tileImageSize[0] - $imageSize[0]) / $titalPlayTime) + $imageCount;

            $command = sprintf('%s -i %s -filter:v "crop=in_w/%d:in_h:t*%d:0" -r %d -t %d %s',
                $ffmpeg, $temporaryMoviePath, $imageCount, $moveWidthPx,
                Slideshow::MOVIE_FPS, $titalPlayTime, $savePath);
            \Log::info($command);
            exec($command, $output, $result);
            if ($result !== 0) {
                \File::delete($tileImagePath);
                \File::delete($temporaryMoviePath);

                throw new \Exception('failed to convert dynamic view.');
            }

            if (!file_exists($savePath)) {
                \File::delete($tileImagePath);
                \File::delete($temporaryMoviePath);

                throw new \Exception('not such file. [dynamic view]');
            }

            // 後始末
            \File::delete($tileImagePath);
            \File::delete($temporaryMoviePath);

            return $movieFileName;
        } catch (\Exception $e) {
            \Log::error($e);

            return '';
        }
    }

    /*
     * クロスフィードで動画を連結
     * 
     * @param   array   $moviePaths
     * @param   array   $imageSize
     * @param   int     $timePerImage
     * @param   string  $basePath
     * 
     * @return  string  $movieFileName
     */
    private function addEffectCrossFade($moviePaths, $imageSize, $timePerImage, $basePath)
    {
        // 変数
        $ffmepg = env('FFMPEG_PATH', 'ffmpeg');
        $option = '';
        $filter = '';
        $concat = '';
        $output = array();
        $result = 0;
        $movieCount = count($moviePaths);
        $totalPlayTime = $movieCount * $timePerImage;

        // クロスフィードを使用して、動画を連結
        try {
            // ffmpeg の各種パラメータを生成
            foreach ($moviePaths as $index => $moviePath) {
                $option .= sprintf('-i %s ', $moviePath);

                // 再生順序によって使用するエフェクトを分岐
                if ($index === 0) {
                    $filter = sprintf('[%d:v]format=pix_fmts=yuva420p,fade=t=out:st=%d:d=%f:alpha=1,setpts=PTS-STARTPTS[v%d];',
                        $index, $timePerImage, Slideshow::FADE_TIME, $index);

                    $concat = sprintf('[over%d][v%d]overlay[over%d];', $index, $index, ($index + 1));
                } else if ($movieCount === ($index + 1)) {
                    $filter .= sprintf('[%d:v]format=pix_fmts=yuva420p,fade=t=in:st=0:d=%f:alpha=1,setpts=PTS-STARTPTS+%d/TB[v%d];[%d:v]trim=duration=0.1[over0];',
                        $index, Slideshow::FADE_TIME, ($timePerImage * $index), $index, $movieCount);

                    $concat .= sprintf('[over%d][v%d]overlay=format=yuv420[outv]', $index, $index);
                } else {
                    $filter .= sprintf('[%d:v]format=pix_fmts=yuva420p,fade=t=in:st=0:d=%f:alpha=1,fade=t=out:st=%d:d=%f:alpha=1,setpts=PTS-STARTPTS+%d/TB[v%d];',
                        $index, Slideshow::FADE_TIME, $timePerImage, Slideshow::FADE_TIME,
                        ($timePerImage * $index), $index, $index, $index, ($index + 1));

                    $concat .= sprintf('[over%d][v%d]overlay[over%d];', $index, $index, ($index + 1));
                }
            }

            // 動画変換を実行
            $movieFileName = sprintf('%s.mp4', $this->serialNo);
            $savePath = sprintf("%s%s", $basePath, $movieFileName);
            $command = sprintf('%s %s -f lavfi -i color=black:s=%s -filter_complex "%s%s" -vcodec libx264 -map "[outv]" -t %d -r %d %s',
                $ffmepg, $option, implode('x', $imageSize), $filter, $concat,
                $totalPlayTime, Slideshow::MOVIE_FPS, $savePath);
            exec($command, $output, $result);
            if ($result !== 0) {
                \File::delete($moviePaths);
                throw new \Exception('failed to create a movie. [effect: cross fade]');
            }

            // 後始末
            \File::delete($moviePaths);

            return $movieFileName;
        } catch (\Exception $e) {
            \Log::error($e);

            return '';
        }
    }

    /**
     * @param MediaAccount $mediaAccount
     * @param Slideshow $slideshow
     * @return bool
     */
    public function uploadToFacebook(MediaAccount $mediaAccount, Slideshow $slideshow)
    {
        $fbClient = new FacebookGraphClient($mediaAccount->access_token);
        $path = self::getVideoPath($slideshow->name, $mediaAccount->advertiser_id);
        $response = $fbClient->uploadVideo($path, $mediaAccount->media_account_id);

        if (isset($response['id'])) {
            $this->createMediaSlideshow($mediaAccount->id, $slideshow->id, $response['id']);

        } else {
            return false;
        }

        return true;
    }

    /**
     * @param MediaAccount $mediaAccount
     * @param Slideshow $slideshow
     * @param $tweet
     * @param $creativeType
     * @return bool
     */
    public function uploadToTwitter(MediaAccount $mediaAccount, Slideshow $slideshow, $tweet, $creativeType)
    {
        $twClient = TwitterApiClient::createInstance($mediaAccount->access_token, $mediaAccount->refresh_token, $mediaAccount->media_account_id);
        $path = self::getVideoPath($slideshow->name, $mediaAccount->advertiser_id);
        $id = $twClient->uploadMaterial($mediaAccount->media_account_id, $mediaAccount->media_user_id, $path, $tweet, $creativeType, true);
        $this->createMediaSlideshow($mediaAccount->id, $slideshow->id, $id, $tweet, $creativeType);

        return true;
    }

    /**
     * @param $serialNo
     * @param Advertiser $advertiser
     */
    public function deletePreview($serialNo, Advertiser $advertiser)
    {
        $preview_path = storage_path('app/public/videos/' . $advertiser->id . '/preview/' . $serialNo . ".mp4");
        if (file_exists($preview_path)) {
            \File::delete($preview_path);
        }
    }

    /**
     * @param $mediaAccountId
     * @param $slideshowId
     * @param $videoObjectId
     * @param string $text
     * @param string $creativeType
     */
    public function createMediaSlideshow($mediaAccountId, $slideshowId, $videoObjectId, $text = '', $creativeType = '')
    {
        /** @var MediaAccountSlideshowService $mediaSlideshowService */
        $mediaSlideshowService = app(MediaAccountSlideshowService::class);
        $mediaSlideshowService->createModel([
            'media_account_id' => $mediaAccountId,
            'slideshow_id' => $slideshowId,
            'media_object_id' => $videoObjectId,
            'text' => $text,
            'creative_type' => $creativeType
        ]);
    }

    /**
     * @param $slideshowId
     * @param Advertiser $advertiser
     * @throws \Exception
     */
    public function deleteSlideshow($slideshowId, Advertiser $advertiser)
    {
        $slideshow = Slideshow::findOrFail($slideshowId);
        if (!$advertiser->can('update', $slideshow)) {
            abort(403);
        }
        $videoPath = self::getVideoPath($slideshow->name, $advertiser->id);
        \File::delete($videoPath);
        $slideshow->delete();
    }

    /**
     * @param $name
     * @param $adAccountId
     * @return string
     */
    public static function getVideoPath($name, $adAccountId)
    {
        return storage_path('app/public/videos/' . $adAccountId . '/' . $name);
    }

    /**
     * @param $name
     * @param $adAccountId
     * @return \Illuminate\Contracts\Routing\UrlGenerator|string
     */
    public static function getVideoUrl($name, $adAccountId)
    {
        return url('/public_storage/videos/' . $adAccountId . '/' . $name);
    }

    /**
     * @param $status
     * @return string
     */
    public static function getStatusHtml($status)
    {
        switch ($status) {
            case Slideshow::STATUS_DRAFT:
                return '<span class="label label-approved p5 status">作成済</span>';
            case Slideshow::STATUS_CREATED:
                return '<span class="label label-approved p5 status">作成済</span>';
            case Slideshow::STATUS_UPLOADED:
                return '<span class="label label-approved p5 status">作成済</span>';
            case Slideshow::STATUS_SPEND:
                return '<span class="label label-synchronis p5 status">出稿済</span>';
            default:
                return "";
        }
    }

    /**
     * @param $slideshowId
     * @return mixed
     */
    public function getImageOfSlideshow($slideshowId)
    {
        return $this->repository->getImagesBySlideshowId($slideshowId);
    }

    /**
     * @param $slideshowId
     * @param Advertiser $advertiser
     * @return mixed
     */
    public function getSlideshowWithImages($slideshowId, Advertiser $advertiser)
    {
        $slideshow = $this->repository->find($slideshowId);
        if (!$advertiser->can('update', $slideshow)) {
            abort(403);
        }

        $imageData = $this->getImageOfSlideshow($slideshowId);

        $slideshowData['id'] = $slideshow->id;
        $slideshowData['url'] = SlideshowService::getVideoUrl($slideshow->name, $advertiser->id);
        $slideshowData['time_per_img'] = $slideshow->time_per_img;
        $slideshowData['video_type'] = $slideshow->video_type;
        $slideshowData['effect_type'] = $slideshow->effect_type;
        $slideshowData['size'] = SlideshowService::changeVideoSizeFormat($slideshow->size);
        $slideshowData['images'] = $imageData->toArray();

        return $slideshowData;
    }

    /**
     * @param $slideshowId
     * @return mixed
     */
    public function getSlideshowTotalKpi($slideshowId)
    {
        return $this->repository->getSlideshowTotalKpi($slideshowId);
    }

    /**
     * @param $size
     * @return float|int
     */
    public static function changeVideoSizeFormat($size)
    {
        if ($size < self::MIN_VIDEO_SIZE_BYTE) return self::MIN_VIDEO_SIZE_MB;
        $mbRaw = $size / 1000000;

        if ($mbRaw < 1) {
            // process when size less than 1
            for ($i = 1; $i < 6; $i++) {
                $tmp = $mbRaw * pow(10, $i);
                if ($tmp > 1) {
                    return floor($tmp * 10) / pow(10, $i + 1);
                }
            }
        } else {
            // get value with 2 numbers after comma
            return round($mbRaw * 100) / 100;
        }
    }
}
