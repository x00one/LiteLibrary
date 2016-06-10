@extends{master}

@blockContent{title}My view system@endBlockContent

@blockContent{body}
    @foreach (['a', 'b', 'c'] as $l)
        @if ($l > 'a')
            {{ $l }}
        @endif
    @endforeach

    @include{included}
    @include{sub/included}

    This is the end<br/>
    aa = {{ $aa }}<br/>
    bb = {{ $bb }}
@endBlockContent
