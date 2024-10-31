<div class="container" style="max-width: 600px;">
    <h1 class="text-center mt-5">Login</h1>
    <div class="card">
        <div class="card-body">
            <form method="post">
                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="form-label">Email</label>
                        <input value="<?= old('email') ?>" name="email" type="text" class="form-control">
                        <div class="text-danger"><?= error('email') ?></div>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Password</label>
                        <input value="<?= old('password') ?>" name="password" type="password" class="form-control">
                        <div class="text-danger"><?= error('password') ?></div>
                    </div>
                </div>
                <button class="btn btn-primary" type="submit">Submit</button>
            </form>
        </div>
    </div>

</div>