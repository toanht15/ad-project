<?php


namespace Classes;


class GoogleVisionAPIClient extends BaseAPIClient {
    /**
     * @return mixed
     */
    public function getApiAddress() {
        return 'https://vision.googleapis.com/v1/images:annotate?key=AIzaSyBaaUl9K8kkXpiX_ePbYZreM3qetAwkGS4';
    }

    public function annotateImage($url) {
        //convert it to base64
        $data = file_get_contents($url);
        $fileSize = strlen($data);
        $base64 = base64_encode($data);
        //Create this JSON
        $params = [
            'requests' => [
                [
                    'image' => [
                        'content' => $base64
                    ],
                    'features' => [
                        [
                            'type' => 'LABEL_DETECTION',
                            'maxResults' => 10
                        ],
                        [
                            'type' => 'IMAGE_PROPERTIES',
                            'maxResults' => 10
                        ]
                    ]
                ]
            ]
        ];

        return $this->request('POST', '', null, null, $params);
    }
}