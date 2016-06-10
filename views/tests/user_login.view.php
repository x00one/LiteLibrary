@extends{master}

@blockContent{body}
    {{ $form->open() }}
        <input style="display:none" type="text" name="fakeusernameremembered"/>
        <input style="display:none" type="password" name="fakepasswordremembered"/>

        {{ $form->render('email') }}<br/>
        {{ $form->render('password') }}<br/>

        {{ $form->submit('Submit') }}
    {{ $form->close() }}
@endBlockContent
