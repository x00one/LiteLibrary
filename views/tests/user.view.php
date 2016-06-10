@extends{master}

@blockContent{body}
    @if($c->loggedInUser === null)
        User not logged in.<br/>
       <a href="{{ \lib\Url::self() }}/login">Login</a>
    @else
        User logged in.<br/>
        <a href="{{ \lib\Url::self() }}/logout">Logout</a>
    @endif

    <hr/>

    {{ $form->open() }}
        {{ $form->render('title') }}<br/>
        {{ $form->render('firstname') }}<br/>
        {{ $form->render('lastname') }}<br/>

        <input style="display:none" type="text" name="fakeusernameremembered"/>
        <input style="display:none" type="password" name="fakepasswordremembered"/>

        {{ $form->render('email') }}<br/>
        {{ $form->render('password') }}<br/>

        {{ $form->submit('Submit') }}
    {{ $form->close() }}
@endBlockContent
