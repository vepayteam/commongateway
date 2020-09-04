<?php

/**
 * @var \app\services\auth\models\LoginForm $loginForm
 *
 **/

?>
<div class="content py-6">
    <div class="row d-flex align-center flex-wrap">
        <div class="col-xs-12 col-md-6">
            <div class="block-gradient-wrap login-form">
                <div class="block-gradient">
                    <div class="login-form-wrap">
                        <form action="/lk/login/in" method="post">
                            <h2 class="form-header">Войти в аккаунт</h2>
                            <div class="form-group">
                                <label for="login">Логин</label>
                                <?php if($loginForm->getErrors('login')): ?>
                                    <span class="error">Неверный логин или пароль! <a href="#">Восстановить?</a></span>
                                <?php endif; ?>
                                <input class="form-control" id="login"
                                       name="LoginForm[login]"
                                       type="text"
                                       value="<?=$loginForm->login ?? ''?>"
                                >
                            </div>
                            <div class="form-group">
                                <label for="password">Пароль</label>
                                <input class="form-control" id="password" name="LoginForm[password]" type="password">
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" id="remember_me" name="remember_me" type="checkbox">
                                <label class="form-check-label" for="remember_me">Запомнить меня</label>
                            </div>
                            <button class="btn btn-primary w-100">Войти</button>
                        </form>
                        <div class="login-form-info">
                            <a class="text-center login-form-info-newuser" href="#">
                                Новый пользователь?
                            </a>
                            <div class="text-center">
                                <a class="btn btn-outline-primary login-form-info-signup w-100" type="button" href="/lk/login/reg">
                                    Зарегистрироваться
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-md-6">
            <div class="login-info">
                <h2>Кредит онлайн</h2>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore
                    et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut
                    aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
                    cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in
                    culpa qui officia deserunt mollit anim id est laborum. Lorem ipsum dolor sit amet, consectetur
                    adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim
                    veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea co</p>
                <button class="btn btn-outline-primary mt-1" type="button">Подать заявку</button>
            </div>
        </div>
    </div>
</div>
