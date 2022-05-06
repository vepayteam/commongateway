<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/** @noinspection PhpFullyQualifiedNameUsageInspection */

namespace app\modules\partner\components;

use app\models\partner\PartUserAccess;
use app\models\partner\UserLk;
use yii\base\Action;
use yii\base\ActionFilter;
use yii\web\User;

/**
 * Checks access to controller's action by legacy method.
 *
 * @todo Legacy. Replace with RBAC.
 */
class CheckAccessFilter extends ActionFilter
{
    /**
     * @var bool {@see UserLk::IsAdmin()}
     */
    public $allowAdmin = false;
    /**
     * @var bool {@see UserLk::IsMfo()}
     */
    public $allowMfo = false;
    /**
     * @var bool {@see PartUserAccess::checkRazdelAccess()}
     */
    public $allowPartAccess = false;

    /**
     * {@inheritDoc}
     * @throws \Throwable
     */
    public function beforeAction($action): bool
    {
        $user = \Yii::$app->getUser();

        if (!$this->hasAccess($user, $action)) {
            $this->deny($user, $action);
            return false;
        }

        return true;
    }

    protected function hasAccess(?User $user, Action $action): bool
    {
        if ($user->isGuest) {
            return false;
        }

        return ($this->allowAdmin && UserLk::IsAdmin($user))
            || ($this->allowMfo && UserLk::IsMfo($user))
            || ($this->allowPartAccess && PartUserAccess::checkRazdelAccess($user, $action));
    }

    protected function deny(?User $user, Action $action)
    {
        $action->controller->redirect(['/partner'], 302)->send();
    }
}