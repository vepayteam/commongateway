<?php

namespace app\services\base;

use yii\base\Model;
use yii\base\ModelEvent;
use app\common\entities\base\modifiers\base\Modifiers;
use app\services\exceptions\base\Main as Exception;

class Service extends Model
{
    public const ENTRY_POINT_NAME = 'initModel';

    public const EVENT_BEFORE_LOAD_PROPERTIES = 'beforeLoadProperties';
    public const EVENT_AFTER_LOAD_PROPERTIES = 'afterLoadProperties';

    public const EVENT_BEFORE_VALIDATION = 'beforeValidation';
    public const EVENT_VALIDATE_SUCCESS = 'validateSuccess';
    public const EVENT_VALIDATE_ERRORS = 'validateErrors';

    private $status;
    private $modelEvent;
    public $entryPoint;
    public $result;
    public $response;

    /**
     * @param array $params
     * @return Service
     */
    public function exec(array $params = []): Service
    {
        $class = get_called_class();
        $service = new $class;
        return $service->process($params);
    }

    /**
     * Progress
     * Стандартный алгоритм работы сервиса.
     * @param array $params
     * @return $this
     */
    public function process(array $params = []): Service
    {
        $this->modelEvent = new ModelEvent();
        $this->onEvents();
        $this->trigger(self::EVENT_BEFORE_LOAD_PROPERTIES, $this->modelEvent);
        $this->loadProperties($params);
        $this->trigger(self::EVENT_AFTER_LOAD_PROPERTIES, $this->modelEvent);
        $this->applyPropertiesModifiersIn();
        $this->trigger(self::EVENT_BEFORE_VALIDATION, $this->modelEvent);
        if($this->validate()){
            $this->status = true;
            $this->trigger(self::EVENT_VALIDATE_SUCCESS, $this->modelEvent);
            $entryPoint = (method_exists ( $this , $this->entryPoint )) ? $this->entryPoint : self::ENTRY_POINT_NAME;
            $this->$entryPoint();
            $this->applyPropertiesModifiersOut();
            $this->checkErrors();
            $this->setResponse();
        } else{
            $this->trigger(self::EVENT_VALIDATE_ERRORS, $this->modelEvent);
            $this->checkErrors();
        }
        return $this;
    }

    /**
     * On events
     * Метод предназначен для инициалтзации событий. Переопределяется в дочерних классах сервиса.
     */
    public function onEvents(): void
    {

    }

    /**
     * Пока не реализованно
     * Предполагается автоматическое преобразование свойств модели. Для входящих данных.
     */
    private function applyPropertiesModifiersIn(): void
    {

    }

    /**
     * Пока не реализованно
     * Предполагается автоматическое преобразование свойств модели. Для исходящих данных.
     */
    private function applyPropertiesModifiersOut(): void
    {

    }

    /**
     * Check errors
     * Реализация стандартного ответа. Если свойство $this->response не содержит данных, метод добавляет ошибки модели в ответ.
     */
    private function checkErrors() : void
    {
        if(!is_array($this->response) && $this->hasErrors()) {
            $this->response['errors'] = $this->getErrors();
            $this->response['errors']['status'] = true;
        } elseif(!is_array($this->response)) {
            $this->response['errors']['status'] = false;
        }
    }

    /**
     * Set response
     * Реализация стандартного ответа. Если свойство $this->response не содержит данных, метод создаёт стандартный ответ.
     */
    private function setResponse(): void
    {
        if(!is_array($this->response) && isset($this->result['response'])) {
            $this->response = ['errors' => ['status' => false]];
            $this->response['result'] = $this->result['response'];
        } elseif(!is_array($this->response)) {
            $this->response = ['errors' => ['status' => false]];
        }
    }

    /**
     * @param array $params
     * Реализация стандартного запроса.
     */
    public function loadProperties(array $params = []): void
    {
        //Если параметры переданы методу Service::exec()
        if(is_array($params) && $params) {
            $this->load($params, '');
        }
        //Добавить обработку GET запроса(в работе).
        //Добавить обработку POST запроса(в работе).
    }

    /**
     * @param bool $status
     */
    public function setStatus(bool $status): void
    {
        $this->status = $status;
    }

    /**
     * @return bool
     */
    public function checkStatus(): bool
    {
        return $this->status;
    }

    /**
     * Default entry point
     * Точка входа в сценарий по умолчанию. Переопределяется в дочерних класах сервиса.
     */
    public function initModel(): void
    {

    }
    
}
