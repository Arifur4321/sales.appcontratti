 


<?php $__env->startSection('title'); ?>
    <?php echo app('translator')->get('translation.Login'); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('body'); ?>

    <body>
    <?php $__env->stopSection(); ?>

    <?php $__env->startSection('content'); ?>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <div class="account-pages my-5 pt-sm-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="card overflow-hidden">
                            <div class="bg-primary-subtle">
                                <div class="row">
                                    <div class="col-7">
                                        <div class="text-primary p-4">
                                            <h5 class="text-primary">Welcome Back !</h5>
                                            <p>Log in to continue to Appcontratti.</p>
                                        </div>
                                    </div>
                                    <div class="col-5 align-self-end">
                                        <img src="<?php echo e(URL::asset('build/images/profile-img.png')); ?>" alt=""
                                            class="img-fluid">
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <div class="auth-logo">
                                    <a href="index" class="auth-logo-light">
                                        <div class="avatar-md profile-user-wid mb-4">
                                            <span class="avatar-title rounded-circle bg-light">
                                                <img src="<?php echo e(URL::asset('build/images/logo-light.svg')); ?>" alt=""
                                                    class="rounded-circle" height="34">
                                            </span>
                                        </div>
                                    </a>

                                    <a href="index" class="auth-logo-dark">
                                        <div class="avatar-md profile-user-wid mb-4">
                                            <span class="avatar-title rounded-circle bg-light">
                                                <img src="<?php echo e(URL::asset('build/images/codice-logo.png')); ?>" alt=""
                                                    class="rounded-circle" height="62">
                                            </span>
                                        </div>
                                    </a>
                                </div>
                                <div class="p-2">


                                <!-- <form class="form-horizontal" method="POST" action="<?php echo e(route('login')); ?>">
                                            <?php echo csrf_field(); ?>
                                            <div class="mb-3">
                                                <label for="username" class="form-label">Email <span class="text-danger">*</span></label>
                                                <input name="email" type="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('email', 'admin@themesbrand.com')); ?>" id="username" placeholder="Enter Email" autocomplete="email" autofocus>
                                                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-feedback" role="alert">
                                                    <strong><?php echo e($message); ?></strong>
                                                </span>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>

                                            <div class="mb-3">
                                                <div class="float-end">
                                                    <?php if(Route::has('password.request')): ?>
                                                    <a href="<?php echo e(route('password.request')); ?>" class="text-muted">Forgot password?</a>
                                                    <?php endif; ?>
                                                </div>
                                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                                <div class="input-group auth-pass-inputgroup <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                                    <input type="password" name="password" class="form-control  <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="userpassword" value="12345678" placeholder="Enter password" aria-label="Password" aria-describedby="password-addon">
                                                    <button class="btn btn-light " type="button" id="password-addon"><i class="mdi mdi-eye-outline"></i></button>
                                                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong><?php echo e($message); ?></strong>
                                                    </span>
                                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                </div>
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="remember" <?php echo e(old('remember') ? 'checked' : ''); ?>>
                                                <label class="form-check-label" for="remember">
                                                    Remember me
                                                </label>
                                            </div>

                                            <div class="mt-3 d-grid">
                                                <button class="btn btn-primary waves-effect waves-light" type="submit">Log
                                                    In</button>
                                            </div>

                                            <div class="mt-4 text-center">
                                                <h5 class="font-size-14 mb-3">Sign in with</h5>

                                                <ul class="list-inline">
                                                    <li class="list-inline-item">
                                                        <a href="#" class="social-list-item bg-primary text-white border-primary">
                                                            <i class="mdi mdi-facebook"></i>
                                                        </a>
                                                    </li>
                                                    <li class="list-inline-item">
                                                        <a href="#" class="social-list-item bg-info text-white border-info">
                                                            <i class="mdi mdi-twitter"></i>
                                                        </a>
                                                    </li>
                                                    <li class="list-inline-item">
                                                        <a href="#" class="social-list-item bg-danger text-white border-danger">
                                                            <i class="mdi mdi-google"></i>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </form>   -->

                                         <form class="form-horizontal" method="POST" action="/saleslogin">
    <?php echo csrf_field(); ?>
    <div class="mb-3">
        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
        <input name="email" type="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="" id="email" placeholder="Enter Email" autocomplete="email" autofocus>
        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <span class="invalid-feedback" role="alert">
            <strong><?php echo e($message); ?></strong>
        </span>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="mb-3">
        <div class="float-end">
            <?php if(Route::has('password.request')): ?>
            <a href="<?php echo e(route('password.request')); ?>" class="text-muted">Forgot password?</a>
            <?php endif; ?>
        </div>
        <label class="form-label">Password <span class="text-danger">*</span></label>
        <div class="input-group auth-pass-inputgroup <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
            <input type="password" name="password" class="form-control  <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="password" placeholder="Enter password" aria-label="Password" aria-describedby="password-addon">
            <button class="btn btn-light " type="button" id="password-addon"><i class="mdi mdi-eye-outline"></i></button>
            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <span class="invalid-feedback" role="alert">
                <strong><?php echo e($message); ?></strong>
            </span>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
    </div>

    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="remember" <?php echo e(old('remember') ? 'checked' : ''); ?>>
        <label class="form-check-label" for="remember">
            Remember me
        </label>
    </div>

    <div class="mt-3 d-grid">
        <button class="btn btn-primary waves-effect waves-light" type="submit">Log In</button>
    </div>

    <!-- <div class="mt-4 text-center">
        <h5 class="font-size-14 mb-3">Sign in with</h5>

        <ul class="list-inline">
            <li class="list-inline-item">
                <a href="#" class="social-list-item bg-primary text-white border-primary">
                    <i class="mdi mdi-facebook"></i>
                </a>
            </li>
            <li class="list-inline-item">
                <a href="#" class="social-list-item bg-info text-white border-info">
                    <i class="mdi mdi-twitter"></i>
                </a>
            </li>
            <li class="list-inline-item">
                <a href="#" class="social-list-item bg-danger text-white border-danger">
                    <i class="mdi mdi-google"></i>
                </a>
            </li>
        </ul>
    </div> -->
</form> 




                                </div>

                            </div>
                        </div>
                        <div class="mt-5 text-center">

                            <div>
                                <!-- <p>Don't have an account ? <a href="<?php echo e(url('register')); ?>" class="fw-medium text-primary">
                                        Signup now </a> </p> -->
                                <p>© <script>
                                        document.write(new Date().getFullYear())

                                    </script> Crafted with <i class="mdi mdi-heart text-danger"></i> by Codice 1%
                                </p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <!-- end account-pages -->


        <?php if($errors->has('loginError')): ?>
            <script>
                window.addEventListener('DOMContentLoaded', function() {
                    var myModal = new bootstrap.Modal(document.getElementById('invalidLoginModal'));
                    myModal.show();
                });
            </script>
        <?php endif; ?>

        <!-- error login modal -->
        <div class="modal fade" id="invalidLoginModal" tabindex="-1" aria-labelledby="invalidLoginModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="invalidLoginModalLabel">Invalid Login</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Invalid email or password. Please try again.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master-without-nav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH F:\Giacometti\Skote_Html_Laravel_v4.2.1\Laravel\Server-Backup\New-Branch-Work-6-10-2024\working-one\sales-appcontratti\resources\views/auth/login.blade.php ENDPATH**/ ?>