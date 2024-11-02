<div class="container" style="max-width: 600px;">
    <h1 class="text-center mt-5">Register</h1>
    <div class="card">
        <div class="card-body">
            <form method="post">
                <div class="row">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input value="<?= old('email') ?>" name="email" type="text" class="form-control">
                        <div class="text-danger"><?= error('email') ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input value="<?= old('name') ?>" name="name" type="text" class="form-control">
                        <div class="text-danger"><?= error('name') ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lastname</label>
                        <input value="<?= old('lastname') ?>" name="lastname" type="text" class="form-control">
                        <div class="text-danger"><?= error('lastname') ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input name="password" type="password" class="form-control">
                        <div class="text-danger"><?= error('password') ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input name="confirm_password" type="password" class="form-control">
                        <div class="text-danger"><?= error('confirm_password') ?></div>
                    </div>
                </div>
                <button class="btn btn-primary" type="submit">Submit</button>
            </form>
        </div>
    </div>

</div>