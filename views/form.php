<h1 class="text-center">Form Test</h1>

<div class="container">
    <form method="post">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Email</label>
                <input value="<?= old('email') ?>" name="email" type="text" class="form-control">
                <div class="text-danger"><?= error('email') ?></div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Name</label>
                <input value="<?= old('name') ?>" name="name" type="text" class="form-control">
                <div class="text-danger"><?= error('name') ?></div>
            </div>
        </div>
        <button class="btn btn-primary" type="submit">Submit</button>
    </form>
</div>