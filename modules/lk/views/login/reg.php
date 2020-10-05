<?php

/**
 * @var \app\services\auth\models\RegForm $regForm
 *
 **/

?>
<div class="content py-6">
    <div class="row d-flex align-center flex-wrap">
        <div class="col-xs-12 col-md-6">
            <div class="block-gradient-wrap login-form">
                <div class="block-gradient">
                    <div class="login-form-wrap">
                        <form method="post" action="/lk/login/reg">
                            <h2 class="form-header">Регистрация</h2>
                            <div class="form-group">
                                <label for="login">Логин</label>
                                <?php if ($regForm->getErrors('login')): ?>
                                    <span class="error"><?=$regForm->getErrors('login')[0]?></span>
                                <?php endif; ?>
                                <input class="form-control" id="login" name="RegForm[login]" type="text" value="<?=$regForm['login'] ?? ''?>">
                            </div>
                            <div class="form-group">
                                <label for="password">Придумайте пароль</label>
                                <?php if ($regForm->getErrors('password')): ?>
                                    <span class="error"><?=$regForm->getErrors('password')[0]?></span>
                                <?php endif; ?>
                                <input class="form-control" id="password" name="RegForm[password]" type="password">
                            </div>
                            <div class="form-group">
                                <label for="email">Действующий адрес электронной почты</label>
                                <?php if ($regForm->getErrors('email')): ?>
                                    <span class="error"><?=$regForm->getErrors('email')[0]?></span>
                                <?php endif; ?>
                                <input class="form-control" id="email" name="RegForm[email]" type="text" value="<?=$regForm['email'] ?? ''?>">
                            </div>
                            <button class="btn btn-primary w-100">Зарегистрироваться</button>
                        </form>
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
