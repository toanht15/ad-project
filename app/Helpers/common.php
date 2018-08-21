<?php

if (!function_exists('static_file_version')) {
    /**
     * 静的なファイルのバージョンを生成する
     *
     * @param $path
     * @return string
     */
    function static_file_version($path)
    {
        if (!$path || !is_file(public_path($path))) {
            return asset($path);
        }

        return asset($path . '?v=' . filemtime(public_path($path)));
    }
}

if (!function_exists('encode_hashtag')) {
    /**
     * ハッシュタグをハッシュする
     *
     * @param $hashtag
     * @return int
     */
    function encode_hashtag($hashtag)
    {
        return crc32($hashtag);
    }
}

/**
 *
 */
if (!function_exists('crop_center_image')) {
    function crop_center_image($path, $size, $fitHeight = true, $replace = true)
    {
        $image = \Intervention\Image\ImageManagerStatic::make($path);
        $imageWidth = $image->width();
        $imageHeight = $image->height();
        if ($fitHeight) {
            $resultWidth = round($imageHeight * $size[0] / $size[1]);
            $resultHeight = $imageHeight;
        } else {
            $resultHeight = round($imageWidth * $size[1] / $size[0]);
            $resultWidth = $imageWidth;
        }
        $x = round(($imageWidth - $resultWidth) / 2);
        $y = round(($imageHeight - $resultHeight) / 2);

        $newImage = $image->crop($resultWidth, $resultHeight, $x, $y);

        // roundで計算したので1-2pixelが違う場合があります
        // その場合は黒い背景を追加
        if ($resultHeight != $size[0] || $resultHeight / $resultWidth != $size[1] / $size[0]) {
            $background = \Intervention\Image\ImageManagerStatic::canvas($size[0], $size[1], '#000');
            $newImage->resize($size[0], $size[1], function ($c) {
                $c->aspectRatio();
            });
            $background->insert($newImage, 'center');

            $newImage = $background;
        }

        if ($replace) {
            $newImage->encode('jpg')->save($path);
        } else {
            //未対応
        }

        return $path;
    }
}

/**
 *
 */
if (!function_exists('resize_image')) {
    function resize_image($url, $fullPath, $size)
    {
        \Log::info('resize fullPath: ' . $fullPath);
        if (isset($size[0])) {
            $image = \Intervention\Image\ImageManagerStatic::make($url);
            $background = \Intervention\Image\ImageManagerStatic::canvas($size[0], $size[1], '#000');

            $image->resize($size[0], $size[1], function ($c) {
                $c->aspectRatio();
            });

            $background->insert($image, 'center');
            $background->encode('jpg')->save($fullPath);
        } else {
            \Intervention\Image\ImageManagerStatic::make($url)->encode('jpg')->save($fullPath);
        }
    }
}

if (!function_exists('download_file')) {
    /**
     * @param $url
     * @param $path
     * @param $fileName
     * @param array $size
     * @param bool $checkStaticSv
     * @return mixed|string
     */
    function download_file($url, $path, $fileName, $size = [], $checkStaticSv = true)
    {
        $imageHost = parse_url($url)['host'];
        $staticHost = parse_url(env('STATIC_DOMAIN'))['host'];

        $urlPath = parse_url($url, PHP_URL_PATH);
        $extern = last(explode('.', $urlPath));

        if (!is_dir($path . 'tmp/')) {
            mkdir($path . 'tmp/', 0777, true);
        }

        if (!is_dir($path . 'preview/')) {
            mkdir($path . 'preview/', 0777, true);
        }

        if ($checkStaticSv && $staticHost == $imageHost) {
            //サーバーである画像
            $localPath = str_replace('//', '/', public_path(parse_url($url)['path']));
            $fullPath = $path . 'tmp/' . $fileName . '.jpg';
            resize_image($localPath, $fullPath, $size);
        } else {
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }

            $fullPath = $path . 'tmp/' . $fileName . '.jpg';

            if (!is_file($fullPath)) {
                resize_image($url, $fullPath, $size);
            }
        }

        return $fullPath;
    }
}

if (!function_exists('get_request_datetime')) {
    /**
     * リクエストから日付をとる
     *
     * @param \Illuminate\Http\Request $request
     * @param Boolean $onlyDate
     * @return array
     */
    function get_request_datetime(\Illuminate\Http\Request $request, $onlyDate = false)
    {
        $timeRange = $request->input('time_range');

        if ($timeRange) {
            $range = explode(' - ', urldecode($timeRange));
            $dateStart = isset($range[0]) ? (new DateTime($range[0]))->format('Y-m-d') : '';
            $dateStop = isset($range[1]) ? (new DateTime($range[1]))->format('Y-m-d') : '';
            $request->session()->set('dateStart', $dateStart);
            $request->session()->set('dateStop', $dateStop);
        } else {
            $dateStart = $request->session()->get('dateStart');
            $dateStop = $request->session()->get('dateStop');
        }
        if (!isset($dateStart) || !$dateStart) {
            $dateStart = (new \DateTime('-20 days'))->format('Y-m-d');
        }
        if (!isset($dateStop) || !$dateStop) {
            $dateStop = (new \DateTime())->format('Y-m-d');
        }

        if (!$onlyDate) {
            $dateStart .= ' 00:00:00';
            $dateStop .= ' 23:59:59';
        }

        return [$dateStart, $dateStop];
    }
}

if (!function_exists('get_japan_week_day')) {
    /**
     *
     * @param DateTime $dateTime
     * @return array
     */
    function get_japan_week_day(DateTime $dateTime)
    {
        $weekday = array("日", "月", "火", "水", "木", "金", "土");
        $day = (int)$dateTime->format('w');

        return $weekday[$day];
    }
}

if (!function_exists('get_all_date_of_period')) {
    /**
     *
     * @param $start
     * @param $stop
     * @return array
     */
    function get_all_date_of_period($start, $stop)
    {
        $interval = new \DateInterval('P1D');
        $begin = new \DateTime($start);
        $end = new \DateTime($stop);
        $end->add($interval);

        $period = new \DatePeriod($begin, $interval, $end);
        $dates = [];

        foreach ($period as $date) {
            $dates[] = $date->format("Y-m-d");
        }

        return $dates;
    }
}

if (!function_exists('has_special_character')) {
    /**
     * @param $string
     * @return bool
     */
    function has_special_character($string)
    {
        // alphabet character, number, hiragana, katakana, kanji, full-witdh number, full-width romanji
        $regex = '/^[0-9A-Za-zぁ-んァ-ン０-９_ー一-龯Ａ-ｚ]+$/';
        if (!preg_match($regex, $string)) {
            return true;
        }

        return false;
    }
}

if (! function_exists('download_video')) {
    /**
     * @param $url
     * @param $path
     * @param $fileName
     * @return string
     */
    function download_video($url, $path, $fileName)
    {
        $fullPath = $path.'/'.$fileName.'.mp4';
        $stream = fopen($url, 'r');
        file_put_contents($fullPath, $stream);

        return $fullPath;
    }
}

if (! function_exists('split_string_by_new_line')) {
    /**
     * @param $str
     * @return array|mixed
     */
    function split_string_by_new_line($str)
    {
        $str = str_replace("\r\n", "\n", $str);
        $str = explode("\n", $str);

        return $str;
    }
}


if (! function_exists('format_string_date_time')) {

    /**
     * Type = 1: 2018-12-31 11:59 -> 2018/12/31 11:59
     * Type = 2: 2018/12/31 11:59 -> 2018-12-31 11:59
     *
     * @param string $dateTime
     * @param int $type
     * @return string
     */
    function format_string_date_time($dateTime, $type = 1){
        if($type == 1){
            return str_replace("-","/",$dateTime);
        }
        if($type == 2){
            return str_replace("/","-",$dateTime);
        }
        return "";
    }
}


if (! function_exists('check_date_in_range')) {
    /**
     * @param $startDate
     * @param $endDate
     * @param $date
     * @return array|mixed
     */
    function check_date_in_range($startDate, $endDate, $date)
    {
        // Convert to timestamp
        $start = strtotime($startDate);
        $end = strtotime($endDate);
        $date = strtotime($date);

        if ($date < $start) {
            return -1;
        } elseif ($date > $end) {
            return 1;
        }

        return 0;
    }
}

if (! function_exists('can_use_ads')) {
    function can_use_ads ()
    {
        return Session::get('canUseAds');
    }
}

if (! function_exists('can_use_ugc_set')) {
    function can_use_ugc_set ()
    {
        return Session::get('site');
    }
}