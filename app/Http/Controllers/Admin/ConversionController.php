<?php

namespace App\Http\Controllers\Admin;

use App\Models\ConversionType;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ConversionController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function conversionLabelPage(Request $request)
    {
        $conversionTypes = ConversionType::all();

        return view()->make('admin.conversion_setting', [
            'conversionTypes' => $conversionTypes
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateConversionLabel(Request $request)
    {
        foreach ($request->input() as $key => $value) {
            if ($key == '_token') {
                continue;
            }
            $action = ConversionType::find($key);
            if ($action) {
                $action->label = $value;
                $action->save();
            }
        }
        return back();
    }
}
