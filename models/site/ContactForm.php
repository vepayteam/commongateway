<?php

namespace app\models\site;

use Yii;
use yii\base\Model;
use yii\validators\FileValidator;
use yii\web\UploadedFile;

/**
 * ContactForm is the model behind the contact form.
 */
class ContactForm extends Model
{
    public $name;
    public $phone;
    public $email;
    public $subject;
    public $body;
    public $verifyCode;
    public $org;
    public $type;
    public $file;

    public static $FormTypes = [
        'feedback' => 'Обратная связь',
        'contact' => 'Обратиться по сотрудничеству',
    ];
    public static $Subjectes = [
        'review' => 'Оставить отзыв',
        'idea' => 'Озвучить идею',
        'help' => 'Получить помощь',
        'security' => 'Связаться со службой безопасности',
        'cooperation' => 'Предложить сотрудничество',
        'pr' => 'Обсудить PR и маркетинг'
    ];

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['name', 'phone', 'email', 'subject', 'org', 'type'], 'string', 'max' => 200],
            // email and body are required
            [['email', 'body'], 'required'],
            // email has to be a valid email address
            ['email', 'email'],
            // verifyCode needs to be entered correctly
            //['verifyCode', 'captcha'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'verifyCode' => 'Код',
            'name' => 'Ваше имя',
            'phone' => 'Телефон',
            'email' => 'E-mail',
            'subject' => 'Тема',
            'body' => 'Сообщение',
            'org' => 'Организация',
        ];
    }

    /**
     * Sends an email to the specified email address using the information collected by this model.
     * @param string $email the target email address
     * @return bool whether the model passes validation
     */
    public function contact($email)
    {
        if ($this->validate()) {

            $this->attacheFile();

            $mailer = Yii::$app->mailer->compose('mail', ['model' => $this])
                ->setTo($email)
                ->setFrom([$this->email => $this->name])
                ->setSubject("Сообщение с сайта QR-Оплата");

            if ($this->file) {
                $mailer->attach($this->file->tempName, ['fileName' => $this->file->name]);
            }

            $mailer->send();
            if ($this->file) {
                @unlink($this->file->tempName);
            }

            return true;
        }
        return false;
    }

    public function attacheFile()
    {
        $this->file = UploadedFile::getInstanceByName('file');
        $validator = new FileValidator([
            'extensions' => ['png', 'jpg', 'gif', 'txt', 'pdf', 'doc', 'docx', 'xls', 'xlsx'],
            'mimeTypes' => ['image/png', 'image/jpg', 'image/gif', 'image/jpeg',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'text/plain', 'application/pdf', 'application/msword', 'application/vnd.ms-excel']
        ]);
        if (!$validator->validate($this->file, $errors)) {
            $this->file = null;
        }
    }
}
