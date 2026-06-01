<?php ob_start(); ?>
<main class="min-vh-100 d-flex align-items-center bg-body-tertiary">
    <section class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-7 col-lg-5">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4 p-md-5">
                        <div class="mb-4">
                            <p class="text-uppercase text-primary fw-semibold small mb-2">Clock-It</p>
                            <h1 class="h3 mb-2">Sign in</h1>
                            <p class="text-body-secondary mb-0">Use any email. Include "admin" to open the admin dashboard.</p>
                        </div>

                        <form action="/login" method="post" class="d-grid gap-3">
                            <div>
                                <label for="identifier" class="form-label">Email or employee ID</label>
                                <input
                                    id="identifier"
                                    name="identifier"
                                    type="text"
                                    class="form-control form-control-lg"
                                    placeholder="staff@clockit.app"
                                    autocomplete="username"
                                    required
                                >
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg">Continue</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
<?php $content = ob_get_clean(); require __DIR__ . '/layouts/app.php'; ?>
