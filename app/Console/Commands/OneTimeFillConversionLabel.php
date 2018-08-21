<?php

namespace App\Console\Commands;


use App\Service\ConversionTypeService;

class OneTimeFillConversionLabel extends BaseCommand
{

    protected $signature = 'oneTimeFillConversionLabel';


    public function doCommand()
    {
        /** @var ConversionTypeService $service */
        $service = app(ConversionTypeService::class);

        $conversionTypes = $service->getWhere([
            'label' => ''
        ]);

        foreach ($conversionTypes as $conversionType) {
            $service->updateModel([
                'label' => $conversionType->action_type,
            ], $conversionType->id);
        }
    }
}
