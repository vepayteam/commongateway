<div class="middle-box text-center loginscreen animated fadeInDown">
    <div>
        <div>
            <h1 class="text-center"><a href="/"><img src="/imgs/logo_vepay.svg" alt="Impay" width="auto" height="40" border="0"></a></h1>
        </div>
        <h3>Авторизация</h3>
        <form class="m-t" method="post" role="form" action="" id="loginform">
            <div class="form-group">
                <input type="text" maxlength="20" class="form-control" placeholder="Логин" name="login" required="">
            </div>
            <div class="form-group">
                <input type="password" maxlength="20" class="form-control" placeholder="Пароль" name="passw" required="">
            </div>
            <button type="submit" class="btn btn-primary block full-width m-b">Вход</button>

            <a href="mailto:support@vepay.online"><small>Забыли пароль?</small></a>

            <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
        </form>
        <p class="m-t"> <small>ООО "ПРОЦЕССИНГОВАЯ КОМПАНИЯ БЫСТРЫХ ПЛАТЕЖЕЙ" &copy; 2019</small> </p>
    </div>
</div>


<script>
    let loginKeyModule = function () {

        $('#loginform').on('submit', function () {
            toastr.options = {
                closeButton: true,
                progressBar: true,
                showMethod: 'slideDown',
                timeOut: 1500
            };

            $.ajax({
                type: "POST",
                url: '/keymodule/default/login',
                data: $('#loginform').serialize(),
                beforeSend: function () {
                },
                success: function (data) {
                    if (data.status !== 1) {
                        toastr.error("Неверный логин / пароль", "Ошибка");
                    } else {
                        window.location.href = '/keymodule/default/index';
                    }
                },
                error: function (e) {
                    if (e.status != 302) {
                        $('#loginerror').show();
                        toastr.error("Ошибка авторизации", "Ошибка");
                        window.location.reload();
                    }
                }
            });

            return false;
        });
    }
</script>

<?php
/** @var yii\web\View $this */
$this->registerJs("loginKeyModule()");