<div class="col-md-12 mt20" xmlns:v-on="http://www.w3.org/1999/xhtml">
    <table class="table none-border-table">
        <tbody>
        <tr>
            <td class="table-title">
                <span class="fa fa-dot-circle-o setting-item-icon"></span>
                Letroタグ
            </td>
            <td>
                <div class="form-group col-md-10">
                    <form type="text" class="form-control input-area mln10 input-form" style="overflow: hidden">
                        {{$ownedSettings->js_tag}}
                    </form>
                    <span class="help-block mln10">
                        対象ページの'&lt;/body&gt;'の直前に埋め込みください。</span>
                </div>
            </td>
        </tr>
        <tr>
            <td class="table-title">
                <span class="fa fa-dot-circle-o setting-item-icon"></span>
                ベースドメイン
            </td>
            <td><p> {{$ownedSettings->base_domain}}</p></td>
        </tr>
        {{--cv_pages--}}
        <tr>
            <td class="table-title">
                <span class="fa fa-dot-circle-o setting-item-icon"></span>
                目標設定
            </td>
            <td>
                <div class="col-md-10 mln10" style="margin-top: -15px;">
                    <div v-if="cvPages == 0">
                        {{--cv pade empty view--}}
                        <div class="mb10">各ページのタイプ（）や、ページURLを設定することで、<br>
                            UGCのクリック数、商品詳細ページへの遷移数、コンバージョン数を計測することが出来ます
                        </div>
                        <div class="connect-button mt10">
                            <button type="button" class="btn btn-primary btn-block " v-on:click="editCVPage">設定</button>
                        </div>
                    </div>
                    <div v-else class="mb10">
                        <?php $index = 0 ?>
                        <span class="help-block">コンバージョン数（CV数）を計測するための設定になります。</span>
                        <form class="form-group" method="POST" action="{{URL::route('save_owned_cv_pages')}}" role="form" id="cv-page-form">
                            {{csrf_field()}}
                            <table class="table" id="cv-page-table">
                                <thead >
                                <tr class="cv-setting-header">
                                    <th>目標タイトル</th>
                                    <th>目標ページURL</th>
                                    <th>目標ページの種類<i class="fa fa-question-circle" data-toggle="tooltip" title="「LP」「商品詳細」「カート」「購入完了」の4種類から選択でき、コンバージョンの種類を管理することができます。"></i></th>
                                </tr>
                                </thead>
                                <tbody name="cv-page-body">
                                {{--view mode cv page--}}
                                @foreach($ownedSettings->cv_pages as $cv_page)
                                    <tr v-bind:class="{hidden : isCVEdit }">
                                        <td>{{$cv_page['label']}}</td>
                                        <td>{{ json_decode($cv_page['url_match_option'])->string}}</td>
                                        <td><?php echo \Classes\Constants::$cvTypes[$cv_page['type']]?></td>
                                    </tr>
                                @endforeach
                                {{--edit mode cv page--}}
                                @foreach($ownedSettings->cv_pages as $index => $cv_page)
                                    <tr v-bind:class="{hidden : !isCVEdit }" class="cv-pages-inputs">
                                        <?php $no = $cv_page['no'] ?>
                                        <input type="hidden" value="{{ $no }}" name="cv-pages[{{ $no }}][no]">
                                        <input type="hidden" name="cv-pages[{{ $no }}][url_type_{{ $no }}]" value="{{ json_decode($cv_page['url_match_option'])->type}}" maxlength="30">

                                        <td><input class="text form-control input-form" name="cv-pages[{{ $no }}][label_{{ $no }}]" value="{{$cv_page['label']}}"></td>
                                        <td><input class="form-control input-form" name="cv-pages[{{ $no }}][url_string_{{ $no }}]" value="{{ json_decode($cv_page['url_match_option'])->string}}"></td>
                                        <td><select class="form-control input-form" name="cv-pages[{{ $no }}][type_{{ $no }}]">
                                                @foreach(\Classes\Constants::$cvTypes as $key => $value)
                                                    <option value="{{$key}}" @if($key == $cv_page['type'])selected="selected" @endif>{{$value}}</option>
                                                @endforeach
                                            </select></td>
                                    </tr>
                                @endforeach
                                {{-- edit blank form--}}
                                {{--todo element name --}}
                                <tr v-bind:class="{hidden: !isCVEdit}" name="new-cv-page" v-for="n in plusNo" class="cv-pages-inputs">
                                    <td><input class="text form-control input-form" :name="'cv-pages['+(n + maxNo)+'][label_'+(n + maxNo)+']'" placeholder="（例）商品詳細流入計測"></td>
                                    <td><input class="form-control input-form" :name="'cv-pages['+(n + maxNo)+'][url_string_'+(n + maxNo)+']'"></td>
                                    <td><select class="form-control input-form" :name="'cv-pages['+(n + maxNo)+'][type_'+(n + maxNo)+']'">
                                            @foreach(\Classes\Constants::$cvTypes as $key => $value)
                                                <option value="{{$key}}">{{$value}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                </tbody>
                            </table>

                            {{--view mode --}}
                            <div class="btn-content-center" v-bind:class="{hidden : isCVEdit}" >
                                <button type="button" class="btn btn-cancel" v-on:click="editCVPage">
                                    <i class="fa fa-lg fa-pencil"></i>
                                    編集</button>
                            </div>

                            {{-- edit mode --}}
                            <div class="new_input_btn btn-content-center mb20" v-bind:class="{hidden : !isCVEdit} ">
                                <label v-on:click="addCVPageForm(plusNo)"><i class="fa fa-plus-circle fa-lg fontawesome-add-icon"></i>
                                追加する</div></label>
                            <div class="connect-button"  v-bind:class="{hidden : !isCVEdit }">
                                <button type="button" class="btn btn-primary btn-block" v-on:click="saveCVPage">
                                    保存</button>
                            </div>

                        </form>
                    </div>
                </div>
            </td>
        </tr>
        {{--ignore ip address--}}
        <tr>
            <td class="table-title"><span class="fa fa-dot-circle-o setting-item-icon"></span>
                除外IP設定</td>
            <td>
                <div class="col-md-10 mln10">
                    {{-- empty view --}}
                    <div v-if="!address">
                        <div class="mb10">
                            除外IPを設定することで、KPI計測から設定したIPからのアクセスを対象外とすることが出来ます。<br/>
                            例えば、自社のIPを設定することで、自社ネットワークに接続したPCやスマホからのアクセス数をカウント対象から外す事ができます
                        </div>
                        <div class="connect-button">
                            <button v-on:click="editExcludeAddress"
                                    type="button" class="btn btn-primary btn-block">設定</button>
                        </div>
                    </div>
                    <div v-else>
                        <form class="" method="POST" action="{{URL::route('save_owned_exclude_address')}}" role="form" id="add-exclude-address-form">
                            {{csrf_field()}}
                            <div>
                                <textarea v-bind:disabled="!isAddressEdit" style="width: 100%;height: 120px" id="exclude-addresses" class="input-form form-control"  name="excludeAddresses">{{join("\n", $ownedSettings->exclude_remote_address)}}</textarea>
                            </div>
                            <div id="save-exclude-address" class="col-md-12" v-bind:class="{hidden : !isAddressEdit}">
                                <span>※改行区切りで複数入力できます</span><br>
                                <div class="connect-button">
                                    <button v-on:click="saveExcludeAddress" type="button" class="btn btn-primary btn-block" id="add-exclude-address-btn">保存</button>
                                </div>
                            </div>
                        </form>

                        <div class="btn-content-center" v-bind:class="{hidden : isAddressEdit}" >
                            <button v-on:click="editExcludeAddress" type="button" class="btn btn-cancel mt10" >
                                <i class="fa fa-lg fa-pencil"></i>編集</button>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
</div>

@push("push-script-before-main-script")
    <script>
        var maxNo = {{$maxNo}},
            cvPagesCount = {{ count($ownedSettings->cv_pages) }},
            address = {{ count($ownedSettings->exclude_remote_address)}};
    </script>
@endpush
