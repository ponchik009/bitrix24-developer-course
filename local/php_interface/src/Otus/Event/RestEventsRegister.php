<?

namespace Otus\Event;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class RestEventsRegister {
    /**
     * Регистрирует REST методы
     * 
     * Чтобы методы можно было выбрать в настройке прав,
     * необходимо очистить кеш
     * Bitrix\Main\Data\Cache::clearCache(true, '/rest/scope/');
     * @return array[]
     */
    public static function OnRestServiceBuildDescriptionHandler()
    {
        Loc::getMessage('REST_SCOPE_OTUS.PATIENT');

        return [
            'otus.crm' => [
                'otus.crm.addOrder' => ['Otus\Rest\DealController', 'add'],
                'otus.crm.getClients' => ['Otus\Rest\ContactController', 'getAll'],
                'otus.crm.getBranches' => ['Otus\Rest\BranchController', 'getAll'],
                'otus.crm.getMenu' => ['Otus\Rest\MenuController', 'getAll'],
                'otus.crm.getAvailabelMenu' => ['Otus\Rest\MenuController', 'getAvailabelMenu'],
            ],
        ];
    }
}