@extends('layouts.master-without-nav')

@section('title')
    @lang('translation.Lock_Screen')
@endsection

@section('body')

    <body>
    @endsection

    @section('content')

        <div class="account-pages my-5 pt-sm-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="card overflow-hidden">
                            <div class="bg-primary-subtle">
                                <div class="row">
                                    <div class="col-7">
                                        <div class="text-primary p-4">
                                            <h5 class="text-primary">Lock screen</h5>
                                            <p>Enter your password to unlock the screen!</p>
                                        </div>
                                    </div>
                                    <div class="col-5 align-self-end">
                                        <img src="{{ URL::asset('build/images/profile-img.png') }}" alt="" class="img-fluid">
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <div>
                                    <a href="index">
                                        <div class="avatar-md profile-user-wid mb-4">
                                            <span class="avatar-title rounded-circle bg-light">
                                                <img src="{{ URL::asset('build/images/logo.svg') }}" alt="" class="rounded-circle" height="34">
                                            </span>
                                        </div>
                                    </a>
                                </div>
                                <div class="p-2">
                                    <form action="index">

                                        <div class="user-thumb text-center mb-4">
                                            <img src="{{ URL::asset('build/images/users/avatar-1.jpg') }}"
                                                class="rounded-circle img-thumbnail avatar-md" alt="thumbnail">
                                            <h5 class="font-size-15 mt-3">Maria Laird</h5>
                                        </div>

                                        <div class="mb-3">
                                            <label for="userpassword">Password</label>
                                            <input type="password" class="form-control" id="userpassword"
                                                placeholder="Enter password">
                                        </div>

                                        <div class="text-end">
                                            <button class="btn btn-primary w-md waves-effect waves-light"
                                                type="submit">Unlock</button>
                                        </div>

                                    </form>
                                </div>

                            </div>
                        </div>
                        <div class="mt-5 text-center">
                            <p>Not you ? return <a href="auth-login" class="fw-medium text-primary"> Sign In </a> </p>
                            <p>© <script>
                                    document.write(new Date().getFullYear())

                                    </script>   Crafted with <i class="mdi mdi-heart text-danger"></i> by Codice 1%</p>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    @endsection
