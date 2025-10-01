<div class="login-wrapper">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 col-md-8 col-sm-10 col-12 m-auto">
                <div class="login p-5 rounded">
                    <?php if (isset($alert)) { ?>
                        <div class="alert alert-danger text-center"><?= $alert ?></div>
                    <?php } ?>
                    <form action="" method="post">
                        <div class="form-group">
                            <input type="text" name="username" class="form-control" id="user" placeholder="Username" required>
                        </div>
                        <div class="form-group">
                            <input type="password" name="password" class="form-control" id="pass" placeholder="Password" required>
                        </div>
                        <button type="submit" name="admin" class="btn btn-block btn-primary shadow-blue mt-4">Login</button>
                    </form>
                </div>
                <div class="text-center text-white mt-3">
                    <p>&copy; <?= date('Y') ?> <a class="font-weight-bold" href='<?= $faucet['url'] ?>'><?= $faucet['name'] ?></a>, Powered by <a class="font-weight-bold" href='https://github.com/BlazeCoderLab/CoinFlow-Faucet-Script' target="_blank">CoinFlow Script</a></p>
                </div>
            </div>
        </div>
    </div>
</div>