<div class="middle-box text-center loginscreen animated fadeInDown">
    <div>
        <div>
            <h1 class="text-center"><a href="/"><img src="/imgs/logo_vepay.svg" alt="Impay" width="auto" height="40" border="0"></a></h1>
        </div>
        <h3>Авторизация</h3>
        <form class="m-t" method="post" role="form" action="" id="loginform">
            <div class="form-group login">
                <input type="text" maxlength="20" class="form-control" placeholder="Логин" name="login" required="">
            </div>
            <div class="form-group password">
                <input type="password" maxlength="20" class="form-control" placeholder="Пароль" name="passw" required="">
            </div>
            <div class="form-group token" style="display: none;">
                <input type="password" maxlength="32" class="form-control" placeholder="Токен" name="token">
            </div>
            <button type="submit" class="btn btn-primary block full-width m-b">Вход</button>

            <a href="mailto:support@vepay.online"><small>Забыли пароль?</small></a>

            <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
        </form>
        <p class="m-t"> <small>ООО "ПРОЦЕССИНГОВАЯ КОМПАНИЯ БЫСТРЫХ ПЛАТЕЖЕЙ" &copy; 2020</small> </p>
    </div>
</div>

<?php
/** @var yii\web\View $this */
$this->registerJs("
    $('#loginform').on('submit', function() {
        loginNav.login();
        return false;
    });
");