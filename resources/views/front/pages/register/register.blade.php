@extends('front.layouts.master')

@section('title', 'Create an Account')
@section('description', 'Create a PCB account to create forum posts, access personal player statistics and more.')

@push('head')
    <script src='https://www.google.com/recaptcha/api.js'></script>
    <script>
        function submitForm() {
            document.getElementById('form').submit();
        }
    </script>
@endpush

@section('contents')

    <div class="card card--narrow card--centered">
        <div class="card__body card__body--padded">
            <h1>Create an Account</h1>

            <!-- <div class="register__instructions">
                Sign-up via social media:
            </div>

            <div class="register__social">
                <a class="login__button login__button--facebook" href="">
                    <i class="fab fa-facebook-square"></i> Facebook
                </a>
                <a class="login__button login__button--twitter" href="">
                    <i class="fab fa-twitter-square"></i> Twitter
                </a>
                <a class="login__button login__button--google" href="">
                    <i class="fab fa-google"></i> Google
                </a>
            </div>

            <hr class="divider" data-text="OR SIGN-UP MANUALLY"> -->

            <form method="post" action="{{ route('front.register.submit') }}" id="form">
                @csrf
                
                @if($errors->any())
                    <div class="alert alert--error">
                        <h3><i class="fas fa-exclamation-circle"></i> Error</h3>
                        {{ $errors->first() }}
                    </div>
                    <p>
                @endif

                <div class="form-row">
                    <label>Email Address</label>
                    <input class="input-text {{ $errors->any() ? 'input-text--error' : '' }}" name="email" type="email" placeholder="Email Address" value="{{ old('email') }}" />
                </div>
                <div class="form-row">
                    <label>Username</label>
                    <input class="input-text {{ $errors->any() ? 'input-text--error' : '' }}" name="username" type="username" placeholder="Username" value="{{ old('username') }}" />
                </div>
                <div class="form-row">
                    <label>Password</label>
                    <input class="input-text {{ $errors->any() ? 'input-text--error' : '' }}" name="password" type="password" placeholder="Password" />
                </div>
                <div class="form-row">
                    <input class="input-text {{ $errors->any() ? 'input-text--error' : '' }}" name="password_confirm" type="password" placeholder="Password (Confirm)" />
                </div>

                <div class="form-row">
                    <button
                        class="g-recaptcha button button--large button--fill button--primary"
                        data-sitekey="@recaptcha_key"
                        data-callback="submitForm"
                        >
                        <i class="fas fa-chevron-right"></i> Register
                    </button>
                </div>
            </form>

            <div class="register__fineprint">
                By signing up, you agree to our community <a href="{{ route('terms') }}">terms and conditions</a>.
            </div>

        </div>

    </div>

@endsection
