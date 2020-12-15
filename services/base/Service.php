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

    public function __construct()
    {
        parent:: __construct();
        return $this;
    }

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
     */
    public function onEvents(): void
    {

    }

    //Пока не реализованно
    private function applyPropertiesModifiersIn(): void
    {

    }

    //Пока не реализованно
    private function applyPropertiesModifiersOut(): void
    {

    }

    /**
     * Check errors
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
     */
    public function loadProperties(array $params = []): void
    {
        if(is_array($params) && $params) {
            $this->load($params, '');
        }
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
     */
    public function initModel(): void
    {

    }
    
}
