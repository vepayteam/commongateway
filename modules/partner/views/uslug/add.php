<?php

/* @var yii\web\View $this */
/* @var \app\models\payonline\Uslugatovar $usl */

use yii\helpers\Html;

$this->title = "конструктор виджета";

$this->params['breadtitle'] = "Конструктор виджета";
$this->params['breadcrumbs'][] = $this->params['breadtitle'];
?>
    <div class="row">

        <div class="col-sm-3">

            <div class="ibox">
                <div class="ibox-title">
                    <h5>Инструменты</h5>
                </div>
                <div class="ibox-content">
                    <div class="dropdown">
                        <button id="tLabel" type="button" class="btn btn-primary btn-block" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                            Тип виджета
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="tLabel" id="tlist">
                            <li><a href="#" data-click="prototype" data-id="one">Одна колонка</a></li>
                            <li><a href="#" data-click="prototype" data-id="two">Две колонки</a></li>
                        </ul>
                    </div>
                    <p class="m-t">Перетащите элемент в рабочую область</p>

                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            Элементы
                        </div>
                        <div class="panel-body">
                            <div class="toggle">
                                <label>Поля</label>
                                <div class="toggle-content nopadding">
                                    <div class="list-group nomargin">
                                        <a href="#" class="list-group-item" data-click="field" data-id="text"
                                           draggable='true' ondragstart="addUsl.dragElem(event)">
                                            <img src="/imgs/shopicon/fld_text.png" width="80px" style="width: 80px">
                                            Текстовое</a>
                                        <a href="#" class="list-group-item" data-click="field" data-id="dig"
                                           draggable='true' ondragstart="addUsl.dragElem(event)">
                                            <img src="/imgs/shopicon/fld_dig.png" width="80px" style="width: 80px">
                                            Числовое</a>
                                        <a href="#" class="list-group-item" data-click="field" data-id="datetime"
                                           draggable='true' ondragstart="addUsl.dragElem(event)">
                                            <img src="/imgs/shopicon/fld_date.png" width="80px" style="width: 80px">
                                            Дата / время</a>
                                        <a href="#" class="list-group-item" data-click="field" data-id="summ"
                                           draggable='true' ondragstart="addUsl.dragElem(event)">
                                            <img src="/imgs/shopicon/fld_dig.png" width="80px" style="width: 80px">
                                            Сумма</a>
                                    </div>
                                </div>
                            </div>

                            <div class="toggle">
                                <label>Контент</label>
                                <div class="toggle-content nopadding">
                                    <div class="list-group nomargin">
                                        <a href="#" class="list-group-item" data-click="field" data-id="statictext"
                                           draggable='true' ondragstart="addUsl.dragElem(event)">
                                            <img src="/imgs/shopicon/fld_statictext.png" width="80px"
                                                 style="width: 80px"> Текст</a>
                                        <a href="#" class="list-group-item" data-click="field" data-id="staticimage"
                                           draggable='true' ondragstart="addUsl.dragElem(event)">
                                            <img src="/imgs/shopicon/fld_staticimg.png" width="80px"
                                                 style="width: 80px"> Изображение</a>
                                    </div>
                                </div>
                            </div>

                            <div class="toggle">
                                <label>Селекты</label>
                                <div class="toggle-content nopadding">
                                    <div class="list-group nomargin">
                                        <a href="#" class="list-group-item" data-click="field" data-id="selects"
                                           draggable='true' ondragstart="addUsl.dragElem(event)">
                                            <img src="/imgs/shopicon/fld_sel.png" width="80px" style="width: 80px">
                                            Список</a>
                                        <a href="#" class="list-group-item" data-click="field" data-id="radios"
                                           draggable='true' ondragstart="addUsl.dragElem(event)">
                                            <img src="/imgs/shopicon/fld_radio.png" width="80px"
                                                 style="width: 80px"> Радиокнопки</a>
                                        <a href="#" class="list-group-item" data-click="field" data-id="radioimgs"
                                           draggable='true' ondragstart="addUsl.dragElem(event)">
                                            <img src="/imgs/shopicon/fld_radio.png" width="80px"
                                                 style="width: 80px"> Радиокнопки с изображениями</a>
                                        <a href="#" class="list-group-item" data-click="field" data-id="checks"
                                           draggable='true' ondragstart="addUsl.dragElem(event)">
                                            <img src="/imgs/shopicon/fld_check.png" width="80px"
                                                 style="width: 80px"> Чекбоксы</a>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-sm-8">

            <div class="ibox">
                <div class="ibox-title">
                    <h5>Создание виджета</h5>
                </div>
                <div class="ibox-content">
                    <div class="widget form-wizard">
                        <ul class="nav-justified nav nav-pills">
                            <li class="active">
                                <a href="#tab1" data-toggle="tab" aria-expanded="true" data-progress="30">
                                    <small>1.</small>
                                    <span id="uslname0"> Заказ</span>
                                </a>
                            </li>
                            <li>
                                <a href="#tab2" data-toggle="tab" data-progress="66">
                                    <small>2.</small>
                                    Контакты
                                </a>
                            </li>
                            <li>
                                <a href="#tab3" data-toggle="tab" data-progress="100">
                                    <small>3.</small>
                                    Оплата
                                </a>
                            </li>
                        </ul>
                        <div id="bar" class="progress progress-xs m-t-sm m-b-sm" style="height: 8px;">
                            <div class="progress-bar progress-bar-gray-light" style="width: 30%;"></div>
                        </div>
                        <div class="tab-content">
                            <div class="tab-pane bg-gray-lighter active" id="tab1">
                                <form action="#" method="POST" id="uslForm">
                                    <input type="hidden" name="IdPartner" value="<?=Html::encode($usl['IDPartner'])?>">
                                    <input type="hidden" name="IdUsl" value="<?=Html::encode($usl['ID'])?>">
                                    <input type="hidden" name="NameUsl" value="<?=Html::encode($usl['NameUsluga'])?>">
                                    <input type="hidden" name="NameForm" value="Заказ">
                                    <input type="hidden" name="TypeTemplate" value="two">
                                </form>
                                <div class="uslbody" id="uslbody">
                                </div>
                            </div>
                            <div class="tab-pane bg-gray-lighter" id="tab2">
                                <form action="" method="POST" data-parsley-priority-enabled="false">
                                    <fieldset>
                                        <div class="form-group col-sm-12 m-t">
                                            <label for="fio">ФИО</label>
                                            <input type="text" id="fio" name="fio" placeholder=""
                                                   class="form-control" maxlength="50">
                                            <span class="help-block">Представьтесь пожалуйста</span>
                                        </div>
                                        <div class="form-group col-sm-12">
                                            <label for="email">E-mail</label>
                                            <input type="text" id="email" name="email" placeholder=""
                                                   class="form-control" maxlength="50">
                                            <span class="help-block">Укажите адрес вашей электронной почты, на который мы направим подтверждение оплаты</span>
                                        </div>
                                        <div class="form-group col-sm-12">
                                            <label for="phone">Телефон</label>
                                            <input type="text" id="phone" name="phone" placeholder=""
                                                   class="form-control" data-inputmask-placeholder='_'
                                                   data-inputmask-jitMasking='true'
                                                   data-inputmask-mask='+7 (999) 999-99-99'>
                                            <span class="help-block">Напишите ваш телефон для связи</span>
                                        </div>
                                    </fieldset>
                                </form>
                            </div>
                            <div class="tab-pane bg-gray-lighter" id="tab3">
                                <fieldset>
                                    <div class="form-group col-sm-12">
                                        <img src="/imgs/shopicon/rsbpay.jpg" class="media-object">
                                    </div>
                                </fieldset>
                            </div>

                            <div class="wizard-bottom">
                                <div>
                                    <button class="btn btn-primary pull-right" id="uslFormSave">Сохранить</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade in" id="addFeildModal" tabindex="-1" role="dialog" aria-labelledby="addFeildModalLabel"
         aria-hidden="true" style="display: none; padding-right: 17px;">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header"><!-- modal header -->
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="addFeildModalLabel">Добавить элемент</h4>
                </div><!-- /modal header -->

                <!-- modal body -->
                <div class="modal-body" id="addFeildModalBody" style="height: 55vh; overflow-y: auto;">
                </div>
                <!-- /modal body -->

                <div class="modal-footer"><!-- modal footer -->
                    <button class="btn btn-default" data-dismiss="modal">Отмена</button>
                    <button class="btn btn-primary" id="addFeildModalOk">Добавить</button>
                </div><!-- /modal footer -->

            </div>
        </div>
    </div>

    <div class="modal fade in" id="addFormModal" tabindex="-1" role="dialog" aria-labelledby="addFormModalLabel"
         aria-hidden="true" style="display: none; padding-right: 17px;">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header"><!-- modal header -->
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="addFormModalLabel">Изменить тип виджета?</h4>
                </div><!-- /modal header -->

                <!-- modal body -->
                <div class="modal-body" id="addFormModalBody">
                    <div class="form-group">
                        <p>Текущий виджет будет очищен. Подтвердите операцию.</p>
                    </div>
                </div>
                <!-- /modal body -->

                <div class="modal-footer"><!-- modal footer -->
                    <button class="btn btn-default" data-dismiss="modal">Отмена</button>
                    <button class="btn btn-primary" id="addFormModalOk">OK</button>
                </div><!-- /modal footer -->

            </div>
        </div>
    </div>
<?php

$this->registerJs("initAdd()");
