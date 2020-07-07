<?php

namespace app\modules\suppliers;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         version="0.0.1",
 *         title="Vepay API | Поставщики",
 *         description="",
 *     ),
 *     @OA\Server(
 *         description="Test",
 *         url="http://test.vepay.online/suppliers/"
 *     ),
 *     @OA\Server(
 *         description="Prod",
 *         url="http://vepay.online/suppliers/"
 *     ),
 *     security={{"xLogin":{},"xToken":{}}}
 * )
 */
/**
 * @OA\SecurityScheme(
 *   securityScheme="xLogin",
 *   type="apiKey",
 *   in="header",
 *   name="X-LOGIN"
 * )
 * @OA\SecurityScheme(
 *   securityScheme="xToken",
 *   type="apiKey",
 *   in="header",
 *   name="X-TOKEN"
 * )
 */
class Module extends \yii\base\Module
{
    public $layout = 'suppliers';

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\suppliers\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
