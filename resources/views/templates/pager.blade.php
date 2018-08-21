@if ($list->lastPage() > 1)
    <?php $list->appends(\Illuminate\Support\Facades\Input::except('page')) ?>
    <div class="text-center">
        <ul class="pagination pagination-lg">
            @if ($list->lastPage() <= 5)
                @for($i = 1; $i <= $list->lastPage(); $i++)
                    <li @if ($i == $list->currentPage())class="active" @endif><a href="{{$list->url($i)}}">{{$i}}</a></li>
                @endfor
            @else
                <li @if (1 == $list->currentPage())class="active" @endif><a href="{{$list->url(1)}}">1</a></li>
                @if ($list->currentPage() > 3)
                    <li class="active"><a>...</a></li>
                @endif
                @if ($list->currentPage() - 1 > 1)
                    <li><a href="{{$list->url($list->currentPage() - 1)}}">{{$list->currentPage() - 1}}</a></li>
                @endif
                @if ($list->currentPage() != 1 && $list->currentPage() != $list->lastPage())
                    <li class="active"><a>{{$list->currentPage()}}</a></li>
                @endif
                @if ($list->currentPage() + 1 < $list->lastPage())
                    <li><a href="{{$list->url($list->currentPage() + 1)}}">{{$list->currentPage() + 1}}</a></li>
                @endif
                @if ($list->currentPage() < $list->lastPage() - 2)
                    <li class="active"><a>...</a></li>
                @endif
                <li @if ($list->lastPage() == $list->currentPage())class="active" @endif><a href="{{$list->url($list->lastPage())}}">{{$list->lastPage()}}</a></li>
            @endif
        </ul>
    </div>
@endif