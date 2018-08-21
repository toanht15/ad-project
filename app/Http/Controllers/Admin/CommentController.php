<?php

namespace App\Http\Controllers\Admin;

use App\Models\CommentTemplate;
use Classes\Constants;
use Classes\ErrorMessage;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class CommentController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function commentTemplatePage(Request $request)
    {
        $commentTemplates = CommentTemplate::all();

        return view()->make('admin.comment_template_setting', [
            'commentTemplates' => $commentTemplates
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateCommentTemplate(Request $request)
    {
        if ($request->input('id')) {
            $commentTemplate = CommentTemplate::findOrFail($request->input('id'));
        } else {
            $commentTemplate = new CommentTemplate();
        }

        $commentTemplate->prefix = $request->input('prefix');
        $commentTemplate->suffix = $request->input('suffix');
        $commentTemplate->save();
        $request->session()->flash(Constants::INFO_MESSAGE, 'コメントテンプレートを保存しました。');

        return back();
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeCommentTemplate(Request $request, $id)
    {
        $commentTemplate = CommentTemplate::find($id);
        if (!$commentTemplate) {
            $request->session()->flash(Constants::ERROR_MESSAGE, ErrorMessage::INVALID_REQUEST);
        } else {
            $commentTemplate->delete();
            $request->session()->flash(Constants::INFO_MESSAGE, 'コメントテンプレートを削除しました。');
        }

        return back();
    }
}
