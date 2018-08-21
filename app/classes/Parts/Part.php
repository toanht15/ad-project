<?php

namespace Classes\Parts;

use App\Models\PartImagesTemporary;
use Illuminate\Support\Facades\Session;

class Part extends Obj
{
    public function addImage(Image $image)
    {
        $this->images->add($image);
    }

    public function updateImageWithPostId()
    {
        $img_ids = [];
        foreach ($this->images as $image) {
            $img_ids[] = (int)$image->image_id;
        }
        $site = Session::get('site');
        $tmpImages = PartImagesTemporary::where('vtdr_site_id', $site->id)->whereIn('vtdr_image_id', $img_ids)->get();

        foreach ($this->images as $image) {
            $image->add_to_part_date = (new \DateTime($image->img_created_at))->format('Y/m/d H:i');
            foreach ($tmpImages as $tmpImage) {
                if ($image->image_id == $tmpImage->vtdr_image_id) {
                    $image->post_id = $tmpImage->post_id;
//                    $image->add_to_part_date = $tmpImage->created_at->format('Y:m:d h:i');
                }
            }
        }

    }
}