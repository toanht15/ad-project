<?php

namespace Classes;

use Instagram\Instagram;
use \Instagram\Media;
use \Instagram\Collection\MediaCollection;

class InstagramApi extends Instagram
{
    public function getUserMedia( $id, array $params = null ) {
        $params = (array)$params;
        return new MediaCollection( $this->proxy->getUserMedia( $id, $params ), $this->proxy );
    }
    
    public function getMedia($id) {
        try {
            $media = new Media($this->proxy->getMedia($id), $this->proxy);
            return $media;
        } catch (\Exception $e) {
            \Log::error($e);
            return null;
        }
    }
}
