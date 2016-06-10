@extends{master}

@blockContent{body}
    {{ $form->open() }}
        {{ $form->render('title') }}<br/>
        {{ $form->render('name') }}<br/>
        {{ $form->render('birthdate') }}<br/>
        {{ $form->render('opt[a]') }}<br/>
        {{ $form->render('opt[b]') }}<br/>
        {{ $form->render('pro[position]') }}<br/>
        {{ $form->render('pro[company]') }}<br/>
        {{ $form->render('is_ok') }}<br/>
        {{ $form->render('status') }}<br/>
        {{ $form->render('status.draft') }}<br/>
        {{ $form->render('status.submit') }}<br/>
        {{ $form->render('status.valid') }}<br/>
        {{ $form->render('description') }}<br/>

        {{ $form->submit('Submit') }}
    {{ $form->close() }}
@endBlockContent
